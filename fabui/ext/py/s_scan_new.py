#!/bin/env python
# -*- coding: utf-8; -*-
#
# (c) 2016 FABtotum, http://www.fabtotum.com
#
# This file is part of FABUI.
#
# FABUI is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
#
# FABUI is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with FABUI.  If not, see <http://www.gnu.org/licenses/>.

# Import standard python module
import argparse
import time
from datetime import datetime
import gettext
import os
import errno
from fractions import Fraction
from threading import Event, Thread
import json
try:
    import queue
except ImportError:
    import Queue as queue
    
# Import external modules
from picamera import PiCamera
import numpy as np

# Import internal modules
from fabtotum.fabui.config  import ConfigService
from fabtotum.fabui.gpusher import GCodePusher
import fabtotum.utils.triangulation as tripy
import fabtotum.speedups.triangulation as tricpp
from fabtotum.utils.ascfile import ASCFile

# Set up message catalog access
tr = gettext.translation('s_scan', 'locale', fallback=True)
_ = tr.ugettext

################################################################################

class SweepScan(GCodePusher):
    """
    Sweep scan application.
    """
    
    XY_FEEDRATE     = 10000
    Z_FEEDRATE      = 1500
    E_FEEDRATE      = 800
    QUEUE_SIZE      = 64
    
    def __init__(self, log_trace, monitor_file, scan_dir, standalone = False, finalize = True, width = 2592, height = 1944, rotation = 0, iso = 800, power = 230, shutter_speed = 35000):
        
        super(SweepScan, self).__init__(log_trace, monitor_file, use_stdout=standalone)
        
        self.standalone = standalone
        self.finalize = finalize
        
        self.camera = PiCamera()
        self.camera.resolution = (width, height)
        self.camera.iso = iso
        self.camera.awb_mode = 'off'
        self.camera.awb_gains = ( Fraction(1.5), Fraction(1.2) )
        self.camera.rotation = rotation
        self.camera.shutter_speed = shutter_speed # shutter_speed in microseconds
        
        self.progress = 0.0
        self.laser_power = power
        self.scan_dir = scan_dir
        
        self.scan_stats = {
            'type'          : 'sweep',
            'projection'    : 'planar',
            'scan_total'    : 0,
            'scan_current'  : 0,
            'postprocessing_percent' : 0.0
        }
        
        self.add_monitor_group('scan', self.scan_stats)
        
        self.imq = queue.Queue(self.QUEUE_SIZE)
        
        print "__init__: done"

    def get_progress(self):
        """ Custom progress implementation """
        return self.progress
    
    def take_a_picture(self, number = 0, suffix = ''):
        """ Camera control wrapper """
        scanfile = os.path.join(self.scan_dir, "{0}{1}.jpg".format(number, suffix) )
        self.camera.capture(scanfile, quality=100)
    
    def __post_processing(self, camera_file, start_x, end_x, head_y, bed_z, a_offset, slices, cloud_file, task_id, object_id, object_name, file_name):
        """
        """
        threshold = 0
        idx = 0

        json_f = open(camera_file)
        camera = json.load(json_f)
        intrinsic = camera['intrinsic']
        extrinsic = camera['extrinsic']

        cam_m       = np.matrix( intrinsic['matrix'], dtype=float )
        dist_coefs  = np.matrix( intrinsic['dist_coefs'], dtype=float )
        width       = int(intrinsic['width'])
        height      = int(intrinsic['height'])

        #~ json_f = open('extrinsic.json')
        #~ extrinsic = json.load(json_f)

        offset      = extrinsic['offset']
        #~ dist_coefs  = np.matrix( extrinsic['dist_coef'] )
        M           = np.matrix( extrinsic['M33'] )
        R           = np.matrix( extrinsic['R33'] )
        t           = np.matrix( extrinsic['t'] )
        r           = np.matrix( extrinsic['r'] )
        
        T           = np.eye(3, dtype=float)
        
        mid_x       = float( (start_x+end_x) / 2 )
        z_offset    = float(2*offset[2] - bed_z)
        
        asc = ASCFile(cloud_file)
        
        while True:
            img_idx = self.imq.get()
            
            img_fn   = os.path.join(self.scan_dir, "{0}.jpg".format(img_idx) )
            img_l_fn = os.path.join(self.scan_dir, "{0}_l.jpg".format(img_idx) )
            
            print "post_processing: ", img_idx
            
            if img_idx == None:
                break
                
            # do processing
            #~ line_pos, threshold, w, h = tripy.process_slice(img_fn, img_l_fn, threshold)
            #~ line_pos, w, h = tripy.process_slice(img_fn, img_l_fn, threshold)
            
            xy_line, w, h = tripy.process_slice2(img_fn, img_l_fn, cam_m, dist_coefs, width, height)
            
            pos = (float(idx*(end_x-start_x)) / float(slices)) + start_x
            print "{0} / {1}".format(idx,pos)
            #print json.dumps(line_pos)

            #print len(line_pos)
            head_x = float(pos)
            
            offset = np.matrix([mid_x, head_y, z_offset])
            
            xyz_points = tripy.laser_line_to_xyz(xy_line, M, R, t, head_x, offset, T)
            #~ xyz_points = tricpp.laser_line_to_xyz(xy_line, M, R, t, head_x, offset, T)

            asc.write_points(xyz_points)
            
            idx += 1
            
            self.scan_stats 
            with self.monitor_lock:
                self.scan_stats['postprocessing_percent'] = float(idx)*100.0 / float(slices)
                self.update_monitor_file()
            
            # remove images
            os.remove(img_fn)
            os.remove(img_l_fn)
            
        print "close post processing"
        asc.close()
        
        self.store_object(task_id, object_id, object_name, cloud_file, file_name)
        
    def store_object(self, task_id, object_id, object_name, cloud_file, file_name):
        """
        Store object and file to database. If `object_id` is not zero the new file
        is added to that object. Otherwise a new object is created with name `object_name`.
        If `object_name` is empty an object name is automatically generated. Same goes for
        `file_name`.
        
        :param task_id:     Task ID used to read User ID from the task
        :param object_id:   Object ID used to add file to an object
        :param object_name: Object name used to name the new object
        :param cloud_file:  Full file path and filename to the cloud file to be stored
        :param file_name:   User file name for the cloud file
        :type task_id: int
        :type object_id: int
        :type object_name: string
        :type cloud_file: string
        :type file_name: string
        """
        obj = self.get_object(object_id)
        task = self.get_task(task_id)
        
        if not task:
            return
        
        ts = time.time()
        dt = datetime.fromtimestamp(ts)
        datestr = dt.strftime('%Y-%m-%d %H:%M:%S')
        datestr_fs_friendly = 'cloud_'+dt.strftime('%Y%m%d_%H%M%S')
        
        if not object_name:
            object_name = "Scan object ({0})".format(datestr)
        
        client_name = file_name
        
        if not file_name:
            client_name = datestr_fs_friendly
        
        if not obj:
            # File should not be part of an existing object so create a new one
            user_id = 0
            if task:
                user_id = task['user']
            
            obj = self.add_object(object_name, "", user_id)
        
        f = obj.add_file(cloud_file, client_name=client_name)
        
        if task:
            os.remove(cloud_file)

        # Update task content
        if task:
            task['id_object'] = obj['id']
            task['id_file'] = f['id']
            task.write()
    
    def run(self, task_id, object_id, object_name, file_name, camera_file, start_x, end_x, a_offset, y_offset, z_offset, slices, cloud_file):
        """
        Run the sweep scan.
        """
        
        self.prepare_task(task_id, task_type='scan')
        self.set_task_status(GCodePusher.TASK_RUNNING)
        
        self.post_processing_thread = Thread(
            target = self.__post_processing,
            args=( [camera_file, start_x, end_x, y_offset, z_offset, a_offset, slices, cloud_file,
                    task_id, object_id, object_name, file_name] )
            )
        self.post_processing_thread.start()
        
        if self.standalone:
            self.exec_macro("start_sweep_scan")
        
        LASER_ON  = 'M700 S{0}'.format(self.laser_power)
        LASER_OFF = 'M700 S0'
        
        position = start_x
        
        if start_x != 0:
            # If an offset is set .
            self.send('G0 X{0} F{1}'.format(start_x, self.XY_FEEDRATE) )  #set zero

        if a_offset != 0:
            #if an offset is set, rotates to the specified A angle.
            self.send('G0 E{0} {1}'.format(a_offset, self.E_FEEDRATE) )

        if z_offset != 0:
            #if an offset for Z (Y in the rotated reference space) is set, moves to it.
            self.send('G0 Z{0} F{1}'.format(z_offset, self.Z_FEEDRATE))  #go to y offset
            
        if y_offset != 0:
            #if an offset for Z (Y in the rotated reference space) is set, moves to it.
            self.send('G0 Y{0} F{1}'.format(y_offset, self.XY_FEEDRATE))  #go to y offset
                
        dx = abs((float(end_x)-float(start_x))/float(slices))  #mm to move each slice
        
        self.scan_stats['scan_total'] = slices
        
        #self.send('M702 S255')
        
        for i in xrange(0, slices):
            #~ #move the laser!
            print str(i) + "/" + str(slices) +" (" + str(dx*i) + "/" + str(slices) +")"
            #~ serial.write('G0 X' + str(pos) + 'F2500\r\n') 
            self.send('G0 X{0} F{1}'.format(position, self.XY_FEEDRATE))
            self.send('M400') # Wait for the move to finish

            self.send(LASER_ON) #turn laser ON
            self.take_a_picture(i, '_l')
            
            self.send(LASER_OFF) #turn laser ON
            self.take_a_picture(i)
            
            self.imq.put(i)
            
            position += dx
            
            self.scan_stats['scan_current'] = i+1
            self.progress = float(i+1)*100.0 / float(slices)
            
            with self.monitor_lock:
                self.update_monitor_file()
                
            if self.is_aborted():
                break
                
        self.imq.put(None)
        
        self.post_processing_thread.join()
                        
        if self.standalone or self.finalize:
            if self.is_aborted():
                self.set_task_status(GCodePusher.TASK_ABORTING)
            else:
                self.set_task_status(GCodePusher.TASK_COMPLETING)
            
            self.exec_macro("end_scan")
            
            if self.is_aborted():
                self.trace( _("Scan aborted.") )
                self.set_task_status(GCodePusher.TASK_ABORTED)
            else:
                self.trace( _("Scan completed.") )
                self.set_task_status(GCodePusher.TASK_COMPLETED)
        
        self.stop()

def makedirs(path):
    """ python implementation of `mkdir -p` """
    try:
        os.makedirs(path)
    except OSError as exc:  # Python >2.5
        if exc.errno == errno.EEXIST and os.path.isdir(path):
            pass
        else:
            raise
            
def main():
    config = ConfigService()

    # SETTING EXPECTED ARGUMENTS
    destination = config.get('general', 'bigtemp_path')
    
    parser = argparse.ArgumentParser(formatter_class=argparse.ArgumentDefaultsHelpFormatter)
    #~ subparsers = parser.add_subparsers(help='sub-command help', dest='type')
    
    parser.add_argument("-T", "--task-id",     help=_("Task ID."),              default=0)
    parser.add_argument("-U", "--user-id",     help=_("User ID. (future use)"), default=0)
    parser.add_argument("-O", "--object-id",   help=_("Object ID."),            default=0)
    parser.add_argument("-N", "--object-name", help=_("Object name."),          default='')
    parser.add_argument("-F", "--file-name",   help=_("File name."),            default='')
    
    parser.add_argument("-d", "--dest",     help=_("Destination folder."),     default=destination )
    parser.add_argument("-s", "--slices",   help=_("Number of slices."),       default=100)
    parser.add_argument("-i", "--iso",      help=_("ISO."),                    default=400)
    parser.add_argument("-p", "--power",    help=_("Scan laser power 0-255."), default=230)
    parser.add_argument("-W", "--width",    help=_("Image width in pixels."),  default=1920)
    parser.add_argument("-H", "--height",   help=_("Image height in pixels"),  default=1080)
    parser.add_argument("-b", "--begin",    help=_("Begin scanning from X."),  default=0)
    parser.add_argument("-e", "--end",      help=_("End scanning at X."),      default=100)
    parser.add_argument("-y", "--y-offset", help=_("Y offset."),               default=117)
    parser.add_argument("-z", "--z-offset", help=_("Z offset."),               default=180)
    parser.add_argument("-a", "--a-offset", help=_("A offset/rotation."),      default=0)
    parser.add_argument("-o", "--output",   help=_("Output point cloud file."),default=os.path.join(destination, 'cloud.asc'))
    
    # GET ARGUMENTS
    args = parser.parse_args()

    slices          = int(args.slices)
    destination     = args.dest
    iso             = int(args.iso)
    power           = int(args.power)
    start_x         = float(args.begin)
    end_x           = float(args.end)
    width           = int(args.width)
    height          = int(args.height)
    z_offset        = float(args.z_offset)
    y_offset        = float(args.y_offset)
    a_offset        = float(args.a_offset)

    task_id         = int(args.task_id)
    user_id         = int(args.user_id)
    object_id       = int(args.object_id)
    object_name     = args.object_name
    file_name       = args.file_name
    
    if task_id == 0:
        standalone  = True
    else:
        standalone  = False

    cloud_file      = args.output

    monitor_file    = config.get('general', 'task_monitor')      # TASK MONITOR FILE (write stats & task info, es: temperatures, speed, etc
    log_trace       = config.get('general', 'trace')        # TASK TRACE FILE 

    scan_dir        = os.path.join(destination, "images")

    if not os.path.exists(scan_dir):
        makedirs(scan_dir)

    camera_file     = os.path.join( config.get('hardware', 'cameras') , "camera_v1.json")

    ################################################################################

    print 'SWEEP SCAN MODULE STARTING' 
    print 'scanning from'+str(start_x)+"to"+str(end_x); 
    print 'Num of scans : [{0}]'.format(slices)
    print 'ISO  setting : ', iso
    print 'Resolution   : ', width ,'*', height, ' px'
    print 'Y-offset (y) : ', y_offset
    print 'Z-offset (z) : ', z_offset
    print 'A-Offset.    : ', a_offset

    #ESTIMATED SCAN TIME ESTIMATION
    estimated = (slices*2) / 60.0
    if estimated<1 :
        estimated *= 60.0
        unit= "Seconds"
    else:
        unit= "Minutes"

    print 'Estimated Scan time =', str(estimated) + " " + str(unit) + "  [Pessimistic]"

    app = SweepScan(log_trace, 
                    monitor_file,
                    scan_dir,
                    standalone=standalone,
                    width=width,
                    height=height,
                    iso=iso,
                    power=power)

    app_thread = Thread( 
            target = app.run, 
            args=( [task_id, object_id, object_name, file_name, camera_file, 
                    start_x, end_x, a_offset, y_offset, z_offset, slices, cloud_file] ) 
            )
    app_thread.start()
    
    # app.loop() must be started to allow callbacks
    app.loop()
    app_thread.join()

if __name__ == "__main__":
    main()

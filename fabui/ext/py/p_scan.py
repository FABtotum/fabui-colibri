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

# Import external modules
import numpy as np

# Import internal modules
from fabtotum.fabui.config  import ConfigService
from fabtotum.fabui.gpusher import GCodePusher

# Set up message catalog access
tr = gettext.translation('p_scan', 'locale', fallback=True)
_ = tr.ugettext

################################################################################

class ProbeScan(GCodePusher):
    """
    Probe scan application.
    """
    
    MINIMAL_SAFE_Z  = 36.0
    SAFE_Z_OFFSET   = 2.0
    XY_FEEDRATE     = 4000
    Z_FEEDRATE      = 1500
    E_FEEDRATE      = 800
    
    def __init__(self, log_trace, monitor_file, standalone = False, finalize = True):
        super(ProbeScan, self).__init__(log_trace, monitor_file, use_stdout=standalone)
        
        self.standalone = standalone
        self.finalize   = finalize
        self.progress = 0.0
        
        self.scan_stats = {
            'type'          : 'probe',
            'projection'    : 'planar',
            'scan_total'    : 0,
            'scan_current'  : 0,
            'point_count'   : 0,
            'cloud_size'    : 0.0
        }
        
        self.add_monitor_group('scan', self.scan_stats)
            
    def get_progress(self):
        """ Custom progress implementation """
        return self.progress
    
    def rotate_y_axis(self, point, angle):
        #Rotation matrix definition---------------------------
        rotation_matrix_y       = np.zeros(shape=(4,4))
        rotation_matrix_y[0,0]  = rotation_matrix_y[2,2] = np.cos(angle*math.pi/180)
        rotation_matrix_y[1,1]  = 1
        rotation_matrix_y[3,3]  = 1
        rotation_matrix_y[0,2]  = np.sin(angle*math.pi/180)
        rotation_matrix_y[2,0]  = -np.sin(angle*math.pi/180)
        #End rotation matrix definition------------------------
        return np.dot(point, rotation_matrix_y)
        
    def rotate_x_axis(self, point, angle):
        #Rotation matrix definition---------------------------
        rotation_matrix_x       = np.zeros(shape=(4,4))
        rotation_matrix_x[0,0]  = 1
        rotation_matrix_x[1,1]  = np.cos(angle*math.pi/180)
        rotation_matrix_x[2,2]  = np.cos(angle*math.pi/180)
        rotation_matrix_x[3,2]  = -np.sin(angle*math.pi/180)
        rotation_matrix_x[3,3]  = np.cos(angle*math.pi/180)
        #End rotation matrix definition------------------------
        return np.dot(point, rotation_matrix_x)
        
    def probe(self, x, y):
        """ 
        Probe Z at specific (X,Y). Returns Z or ``None`` on failure.
        
        :param x: X position
        :param y: Y position
        :rtype: float
        """
        self.send('G0 X{0} Y{1} F{2}'.format(x, y, self.XY_FEEDRATE) )
        self.send('M400')
        
        reply = self.send('G30', expected_reply = 'echo:', timeout = 200)
        if reply:
            print reply
            
            z = float( reply[-1].split("Z:")[1].strip() )
            z = round(z, 3)  # round to 3 decimanl points
            
            #self.trace( _("Probed {0},{1} / {2} degrees = {3}").format(x, y, 0.0, z))
            
            return [x,y,z,1]
            
        return None
    
    def save_as_cloud(self, points, cloud_file):
        """
        Save `points` to a file in asc format.
        
        :param points: Array of [x,y,z] points
        :param cloud_file: Cloud file filename
        :type points: list
        :type cloud_file: string
        """
        with open(cloud_file,"w")  as cloud_file:
            if len(points)>0:
                for row in xrange(0, len(points)):
                    cloud_file.write( '{0}, {1}, {2}\n'.format( points[row][0], points[row][1], points[row][2])) 
    
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
        os.remove(cloud_file)

        # Update task content
        if task:
            task['id_object'] = obj['id']
            task['id_file'] = f['id']
            task.write()
    
    def run(self, task_id, object_id, object_name, file_name, x1, y1, x2, y2, probe_density, cloud_file):
        """
        Run the probe scan.
        """
                
        self.prepare_task(task_id, 'scan')
        self.set_task_status(GCodePusher.TASK_RUNNING)
        
        if self.standalone:
            self.exec_macro("start_probe_scan")

        self.send('G27')        
        self.send('M401')
        
        points = None
        
        hop_z = 2.0
        step  = round(1.0 / probe_density, 3) # round to 3 decimanl points
        
        x_num = int( abs(x2 - x1) / step )
        y_num = int( abs(y2 - y1) / step )
        total_num = x_num * y_num
        probe_num = 0
        
        self.scan_stats['scan_total'] = total_num

        for x_idx in xrange(0, x_num):
            x_pos = x1 + step*x_idx
            
            if self.is_aborted():
                break
            
            for y_idx in xrange(0, y_num):
                y_pos = y1 + step*y_idx
            
                
            
                if self.is_aborted():
                    break
                # Get Z at (x_pos, y_pos)
                new_point = self.probe(x_pos, y_pos)
                
                print "probed point: ", new_point
                
                if new_point != None:
                    
                    if points == None:
                        points = np.array(new_point)
                    else:
                        points = np.vstack([points, new_point])
                    
                    safe_z = new_point[2] + hop_z
                    
                    if safe_z < self.MINIMAL_SAFE_Z:
                        safe_z = self.MINIMAL_SAFE_Z
                        
                    safe_z = safe_z + self.SAFE_Z_OFFSET
                    self.send('G0 Z{0} F{1}'.format(safe_z, self.Z_FEEDRATE) )
                    self.send('M400')
                    
                probe_num += 1
                self.scan_stats['scan_current'] = probe_num
                self.progress = ( float(probe_num) / float(total_num) ) * 100.0
                
                self.send('M401')   # Renew probe position in case it got moved.
        
        self.trace( _("Saving point cloud to file {0}").format(cloud_file) )
        self.save_as_cloud(points, cloud_file)
        
        self.store_object(task_id, object_id, object_name, cloud_file, file_name)
               
        if self.standalone:
            if self.is_aborted():
                self.set_task_status(GCodePusher.TASK_ABORTING)
            else:
                self.set_task_status(GCodePusher.TASK_COMPLETING)
                
            self.exec_macro("end_scan")
        
            if self.is_aborted():
                self.trace( _("Physical Probing aborted.") )
                self.set_task_status(GCodePusher.TASK_ABORTED)
            else:
                self.trace( _("Physical Probing completed.") )
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
    
    parser = argparse.ArgumentParser(add_help=False, formatter_class=argparse.ArgumentDefaultsHelpFormatter)
    
    parser.add_argument("-T", "--task-id",     help=_("Task ID."),              default=0)
    parser.add_argument("-U", "--user-id",     help=_("User ID. (future use)"), default=0)
    parser.add_argument("-O", "--object-id",   help=_("Object ID."),            default=0)
    parser.add_argument("-N", "--object-name", help=_("Object name."),          default='')
    parser.add_argument("-F", "--file-name",   help=_("File name."),            default='')
    
    parser.add_argument("-d", "--dest",     help=_("Destination folder."),     default=config.get('general', 'bigtemp_path') )
    parser.add_argument("-o", "--output",   help=_("Output point cloud file."),default=os.path.join(destination, 'cloud.asc'))
    parser.add_argument("-n", "--n-probes", help=_("Number of probes."),       default=1)
    parser.add_argument("-b", "--begin",    help=_("Begin scanning from X."),  default=0)
    parser.add_argument("-e", "--end",      help=_("End scanning at X."),      default=360)
    parser.add_argument("-x", "--x1",       help=_("X1."),                     default=0)
    parser.add_argument("-y", "--y1",       help=_("Y1."),                     default=0)
    parser.add_argument("-i", "--x2",       help=_("X2."),                     default=10)
    parser.add_argument("-j", "--y2",       help=_("Y2."),                     default=10)
    parser.add_argument("-z", "--safe-z",   help=_("Safe Z."),                 default=0)
    parser.add_argument('--help', action='help', help=_("Show this help message and exit") )

    # GET ARGUMENTS
    args = parser.parse_args()

    destination     = args.dest
    x1              = float(args.x1)
    y1              = float(args.y1)
    x2              = float(args.x2)
    y2              = float(args.y2)
    probe_density   = float(args.n_probes)
    safe_z          = float(args.safe_z)
    
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

    monitor_file    = config.get('general', 'task_monitor')
    log_trace       = config.get('general', 'trace')

    ############################################################################

    print 'PROBE MODULE STARTING'
    print 'scanning from ' + str(x1)+ "," +str(y1)+ " to " +str(x2)+ "," +str(y2); 
    print 'Probing density : ', probe_density , " points/mm"
    #print 'Start/End       : ', begin ,' to ', end, 'deg'

    app = ProbeScan(log_trace, monitor_file, standalone)

    app_thread = Thread( 
            target = app.run, 
            args=( [task_id, object_id, object_name, file_name, x1, y1, x2, y2, probe_density, cloud_file] )
            )
    app_thread.start()

    # app.loop() must be started to allow callbacks
    app.loop()
    app_thread.join()

if __name__ == "__main__":
    main()

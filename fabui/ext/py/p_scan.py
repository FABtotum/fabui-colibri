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
from fabtotum.utils.translation import _, setLanguage
from fabtotum.fabui.config  import ConfigService
from fabtotum.fabui.gpusher import GCodePusher
from fabtotum.totumduino.format import parseG30
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
    
    def __init__(self, log_trace, monitor_file, standalone = False, 
                finalize = True, lang = 'en_US.UTF-8', send_email=False):
        super(ProbeScan, self).__init__(log_trace, monitor_file, 
                use_stdout=standalone, lang=lang, 
                send_email=send_email)
        
        self.standalone = standalone
        self.finalize   = finalize
        self.progress = 0.0
        
        self.scan_stats = {
            'type'          : 'probe',
            'projection'    : 'planar',
            'scan_total'    : 0,
            'scan_current'  : 0,
            'point_count'   : 0,
            'cloud_size'    : 0.0,
            'file_id'       : 0,
            'object_id'     : 0
        }
        
        self.add_monitor_group('scan', self.scan_stats)
        self.ev_resume = Event()
            
    def get_progress(self):
        """ Custom progress implementation """
        return self.progress
            
    def probe(self, x, y):
        """ 
        Probe Z at specific (X,Y). Returns Z or ``None`` on failure.
        
        :param x: X position
        :param y: Y position
        :rtype: float
        """
        self.send("M401")
        self.send('G0 X{0} Y{1} F{2}'.format(x, y, self.XY_FEEDRATE) )
        self.send('M400')
        
        #reply = self.send('G30', expected_reply = 'echo:', timeout = 200)
        reply = self.send('G30', timeout = 200)
        result = parseG30(reply)
        if result:
            x = result['x']
            y = result['y']
            z = result['z']
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
        
        print "Object_name:", object_name
        print "File_name:", file_name
        print "Cloud_name:", cloud_file
        print "Task_ID:", task_id
        
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
        
        self.scan_stats['file_id']   = f['id']
        self.scan_stats['object_id'] = obj['id']
        # Update task content
        if task:
            task['id_object'] = obj['id']
            task['id_file'] = f['id']
            task.write()
    
    def state_change_callback(self, state):
        if state == 'resumed' or state == 'aborted':
            self.ev_resume.set()
    
    def run(self, task_id, object_id, object_name, file_name, x1, y1, x2, y2, probe_density, orig_safe_z, threshold, max_skip, cloud_file):
        """
        Run the probe scan.
        """
                
        self.prepare_task(task_id, 'scan')
        self.set_task_status(GCodePusher.TASK_RUNNING)
        
        if self.standalone:
            self.exec_macro("start_probe_scan")

        #~ self.send('G27')        
        #~ self.send('M401')
        
        points = None
        point_count = 0
        
        if orig_safe_z < 1.0:
            orig_safe_z = 1.0
        
        step  = round(1.0 / probe_density, 3) # round to 3 decimanl points
        
        x_num = int( abs(x2 - x1) / step )
        y_num = int( abs(y2 - y1) / step )
        total_num = x_num * y_num
        probe_num = 0
        
        self.scan_stats['scan_total'] = total_num
            
        # Planned number of skips
        skipping = 0
        # Number of skips left to do
        to_skip = 0
        
        #disble homing check for probing
        self.send("M733 S0")

        for x_idx in xrange(0, x_num):
            x_pos = x1 + step*x_idx
            
            if self.is_aborted():
                break
            
            skipping = 0
            to_skip = 0
            prev_point = None
            slope = 0.0
            
            for y_idx in xrange(0, y_num):
                y_pos = y1 + step*y_idx
                
                if self.is_paused():
                    self.trace("Paused")
                    self.ev_resume.wait()
                    self.ev_resume.clear()
                    self.trace("Resuming")
                
                if self.is_aborted():
                    break
                
                if to_skip == 0:
                
                    # Get Z at (x_pos, y_pos)
                    
                    new_point = self.probe(x_pos, y_pos)
                                        
                    #print "probed point: ", new_point
                    
                    if new_point != None:
                        # No old_z stored
                        if prev_point is None:
                            prev_point = new_point
                            slope = 0.0
                        else:
                            dz = float( abs(prev_point[2] - new_point[2]) )
                            dy = float( abs(prev_point[1] - new_point[1]) )
                            
                            try:
                                slope = dz / dy
                            except:
                                slope = 0.0
                                
                            if dz < threshold:
                                #print "** dz < threshold ", dz, threshold
                                if skipping < max_skip:
                                    skipping += 1
                                to_skip = skipping
                            else:
                                skipping -= 2
                                if skipping < 0:
                                    skipping = 0
                        
                        if points == None:
                            points = np.array(new_point)
                        else:
                            points = np.vstack([points, new_point])
                        
                        point_count += 1
                        
                        #print "-- slope", slope
                        
                        safe_z = new_point[2] + (to_skip)*step * slope
                        
                        if safe_z < self.MINIMAL_SAFE_Z:
                            safe_z = self.MINIMAL_SAFE_Z
                            
                        safe_z = safe_z + self.SAFE_Z_OFFSET + orig_safe_z
                        self.send('G0 Z{0} F{1}'.format(safe_z, self.Z_FEEDRATE) )
                        self.send('M400')
                else:
                    # Reduce the counter of points to be skipped
                    #print "skipping a point: to_skip = ", to_skip
                    to_skip -= 1
                    
                probe_num += 1
                if to_skip == 0:
                    self.scan_stats['scan_current'] = probe_num
                    self.scan_stats['point_count'] = point_count
                    self.progress = ( float(probe_num) / float(total_num) ) * 100.0
                    with self.monitor_lock:
                        self.update_monitor_file()
        
        self.progress = ( float(probe_num) / float(total_num) ) * 100.0
        
        #enable homeing check
        self.send("M733 S1")
        if not self.is_aborted():
            self.trace( _("Saving point cloud to file {0}").format(cloud_file) )
            self.save_as_cloud(points, cloud_file)
            self.store_object(task_id, object_id, object_name, cloud_file, file_name)
               
        if self.standalone or self.finalize:
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
    
    parser.add_argument("-T", "--task-id",     help="Task ID.",              default=0)
    parser.add_argument("-U", "--user-id",     help="User ID. (future use)", default=0)
    parser.add_argument("-O", "--object-id",   help="Object ID.",            default=0)
    parser.add_argument("-N", "--object-name", help="Object name.",          default='')
    parser.add_argument("-F", "--file-name",   help="File name.",            default='')
    
    parser.add_argument("-d", "--dest",     help="Destination folder.",     default=config.get('general', 'bigtemp_path') )
    parser.add_argument("-o", "--output",   help="Output point cloud file.",default=os.path.join(destination, 'cloud.asc'))
    parser.add_argument("-n", "--n-probes", help="Number of probes.",       default=1)
    parser.add_argument("-x", "--x1",       help="X1.",                     default=0)
    parser.add_argument("-y", "--y1",       help="Y1.",                     default=0)
    parser.add_argument("-i", "--x2",       help="X2.",                     default=10)
    parser.add_argument("-j", "--y2",       help="Y2.",                     default=10)
    parser.add_argument("-z", "--safe-z",   help="Safe Z.",                 default=1)
    parser.add_argument("-t", "--threshold", help="Detail threshold.",      default=0)
    parser.add_argument("-s", "--max-skip",  help="Maximum number of skipped probes.",      default=10)
    parser.add_argument("--lang",            help="Output language", 		default='en_US.UTF-8' )
    parser.add_argument('--help', action='help', help="Show this help message and exit" )
    parser.add_argument("--email",             help="Send an email on task finish", action='store_true', default=False)
    parser.add_argument("--shutdown",          help="Shutdown on task finish", action='store_true', default=False )
    
    # GET ARGUMENTS
    args = parser.parse_args()

    destination     = args.dest
    x1              = float(args.x1)
    y1              = float(args.y1)
    x2              = float(args.x2)
    y2              = float(args.y2)
    probe_density   = float(args.n_probes)
    safe_z          = float(args.safe_z)
    threshold       = float(args.threshold)
    max_skip        = float(args.max_skip)
    
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
    lang            = args.lang
    send_email      = bool(args.email)
    monitor_file    = config.get('general', 'task_monitor')
    log_trace       = config.get('general', 'trace')

    ############################################################################

    print 'PROBE MODULE STARTING'
    print 'scanning from ' + str(x1)+ "," +str(y1)+ " to " +str(x2)+ "," +str(y2); 
    print 'Probing density : ', probe_density , " points/mm"
    #print 'Start/End       : ', begin ,' to ', end, 'deg'

    app = ProbeScan(log_trace, monitor_file, standalone, lang=lang, send_email=send_email)

    app_thread = Thread( 
            target = app.run, 
            args=( [task_id, object_id, object_name, file_name, x1, y1, x2, y2, probe_density, safe_z, threshold, max_skip, cloud_file] )
            )
    app_thread.start()

    # app.loop() must be started to allow callbacks
    app.loop()
    app_thread.join()

if __name__ == "__main__":
    main()

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
import os
import gettext
from threading import Event, Thread

# Import external modules
from picamera import PiCamera

# Import internal modules
from fabtotum.fabui.config  import ConfigService
from fabtotum.fabui.gpusher import GCodePusher
from fabtotum.utils import makedirs

# Set up message catalog access
tr = gettext.translation('calibration', 'locale', fallback=True)
_ = tr.ugettext


class Capture(GCodePusher):
    """
    """
    # Raspberry Pi Camera v1
    RESOLUTIONS = [(1269,972), (1269,730), (1920,1080)]
    ROTATIONS   = [0, 270]
    # Raspberry Pi Camera v2
    #RESOLUTIONS = [(640,480), (1920,1080), (1280,720), (1640,922), (3280,2464)]
    #ROTATIONS   = [0, 270]
    
    def __init__(self, log_trace, monitor_file, scan_dir, width, height, rotation):
        super(Capture, self).__init__(log_trace, monitor_file)

        self.camera = PiCamera()
        self.camera.resolution  = (width, height)
        self.camera.rotation    = rotation
        self.progress           = 0.0
        
        self.scan_dir = scan_dir

    def get_progress(self):
        """ Custom progress implementation """
        return self.progress

    def take_a_picture(self, idx):
        """ Camera control wrapper """
        scanfile = os.path.join(self.scan_dir, "sample_{0}.jpg".format(idx) )
        self.camera.capture(scanfile, quality=100)

    def run(self, task_id, total):
        
        self.prepare_task(task_id, task_type='capture')
        self.set_task_status(GCodePusher.TASK_RUNNING)
        
        self.send("G27")
        self.send("G0 Y104 F5000")
        self.camera.start_preview()
        self.trace( _('Capture started.') )

        for idx in xrange(0, total):

            raw_input("Press Enter to capture {0}/{1}...".format(idx+1,total) )
            
            self.take_a_picture(idx)
                    
            with self.monitor_lock:
                self.progress = float(idx+1)*100.0 / float(total)
                self.update_monitor_file()
                
            if self.is_aborted():
                break
        
        self.camera.stop_preview()
        self.send('M300')
        self.trace( _('Capture finished.') )
        self.set_task_status(GCodePusher.TASK_COMPLETED)
        
        self.stop()

def main():
    config = ConfigService()

    # SETTING EXPECTED ARGUMENTS
    destination = config.get('general', 'bigtemp_path')
    
    # SETTING EXPECTED ARGUMENTS
    parser = argparse.ArgumentParser(formatter_class=argparse.ArgumentDefaultsHelpFormatter)
    parser.add_argument("task_id",          help=_("Task ID.") )
    parser.add_argument("-s", "--samples",  help="Number of captured images.",  default=20 )
    parser.add_argument("-d", "--dest",     help="Destination folder.",         default=destination )
    parser.add_argument("-W", "--width",    help=_("Image width in pixels."),   default=1269)
    parser.add_argument("-H", "--height",   help=_("Image height in pixels."),  default=972)
    parser.add_argument("-r", "--rotation", help=_("Image rotation."),          default=0)
    # GET ARGUMENTS    
    args = parser.parse_args()
    
    # INIT VARs
    task_id         = int(args.task_id)
    total           = int(args.samples)
    destination     = args.dest
    monitor_file    = config.get('general', 'task_monitor') # TASK MONITOR FILE (write stats & task info, ex: temperatures, speed, etc
    log_trace       = config.get('general', 'trace')        # TASK TRACE FILE 
    width           = int(args.width)
    height          = int(args.height)
    rotation        = int(args.rotation)
    
    output_dir      = os.path.join(destination, "samples")

    if not os.path.exists(output_dir):
        makedirs(output_dir)
    
    app = Capture(log_trace,
                  monitor_file, 
                  output_dir,
                  width=width,
                  height=height,
                  rotation=rotation)

    app_thread = Thread( 
            target = app.run, 
            args=( [task_id, total] ) 
            )
    app_thread.start()

    # app.loop() must be started to allow callbacks
    app.loop()
    app_thread.join()


if __name__ == "__main__":
    main()

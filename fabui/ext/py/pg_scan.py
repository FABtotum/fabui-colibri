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
import gettext
import os
import sys
import errno
import socket
from fractions import Fraction
from threading import Event, Thread, RLock
try:
    import queue
except ImportError:
    import Queue as queue
    
# Import external modules
from picamera import PiCamera

# Import internal modules
from fabtotum.fabui.config  import ConfigService
from fabtotum.fabui.gpusher import GCodePusher

# Set up message catalog access
tr = gettext.translation('r_scan', 'locale', fallback=True)
_ = tr.ugettext

################################################################################

class PhotogrammetryScan(GCodePusher):
    """
    Photogrammetry scan application.
    """
    
    XY_FEEDRATE     = 5000
    Z_FEEDRATE      = 1500
    E_FEEDRATE      = 800
        
    START   = 1
    CREATE  = 2
    FINISH  = 3
        
    def __init__(self, log_trace, monitor_file, scan_dir, host_address, host_port,
                standalone = False, finalize = True,
                width = 2592, height = 1944, rotation = 270, iso = 800, shutter_speed = 35000):
        super(PhotogrammetryScan, self).__init__(log_trace, monitor_file, use_stdout=standalone)
        
        self.standalone = standalone
        self.finalize   = finalize
        
        self.camera = PiCamera()
        self.camera.resolution = (width, height)
        self.camera.iso = iso
        self.camera.awb_mode = 'off'
        self.camera.awb_gains = ( Fraction(1.5), Fraction(1.2) )
        self.camera.rotation = rotation
        self.camera.shutter_speed = shutter_speed # shutter_speed in microseconds
        
        self.progress = 0.0
        self.scan_dir = scan_dir
        
        self.scan_stats = {
            'type'          : 'photogrammetry',
            'projection'    : 'rotary',
            'scan_total'    : 0,
            'scan_current'  : 0,
            'width'         : width,
            'height'        : height,
            'iso'           : iso,
        }
        
        self.add_monitor_group('scan', self.scan_stats)
        self.host_address = host_address
        self.host_port = host_port
        self.skipped_images = []
            
    def get_progress(self):
        """ Custom progress implementation """
        return self.progress
    
    def take_a_picture(self, number = 0, suffix = ''):
        """ Camera control wrapper """
        scanfile = os.path.join(self.scan_dir, "{0}{1}.jpg".format(number, suffix) )
        self.camera.capture(scanfile, quality=100)
        
        return scanfile
    
    def manage(self, action, file = '', slices = 0):        
        try:
            sock = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
            sock.settimeout(1)
            sock.connect((self.host_address, self.host_port))
            SOCK_CONNECTED = True
        except Exception as e:
            print _("Connection error:"), e
            SOCK_CONNECTED = False
            if file:
                self.skipped_images.append(file)
                return
        
        print "action:", action, "file:", file, "slices:", slices
        
        if(action == self.START):
            sock.send(str(self.START) + '\n')
            sock.send(str(slices) + '\n')
            
        elif(action == self.CREATE):
            time.sleep(2)
            try:
                os.chmod(file, 0777)
                sock.send(str(self.CREATE) + '\n')
                sock.send(file + '\n')
                
                data = sock.recv(4096)
                if(data.strip() == 'DELETE'):
                    os.remove(file)
            except Exception as e:
                print _("Unexpected error:"), e
                self.skipped_images.append(file)
                
        elif(action == self.FINISH):
            sock.send(str(self.FINISH) + '\n')
        
        sock.close();
    
    def start_transfer(self, slices):
        self.manage(self.START, slices=slices)
        
    def transfer_file(self, filename):
        self.manage(self.CREATE, file=filename)
        
    def finish_transfer(self):
        self.manage(self.FINISH)
    
    def run(self, task_id, start_a, end_a, y_offset, slices):
        """
        Run the photogrammetry scan.
        """
        
        self.prepare_task(task_id, task_type='scan')
        self.set_task_status(GCodePusher.TASK_RUNNING)
        
        if self.standalone:
            self.exec_macro("check_pre_scan")
            self.exec_macro("start_photogrammetry_scan")
        
        position = start_a
        
        if start_a != 0:
            # If an offset is set .
            self.send('G0 E{0} F{1}'.format(start_a, self.E_FEEDRATE) )
            self.send('M400')
            
        if y_offset != 0:
            #if an offset for Z (Y in the rotated reference space) is set, moves to it.
            self.send('G0 Y{0} F{1}'.format(y_offset, self.XY_FEEDRATE))  #go to y offset
            self.send('M400')
        
        deg = abs((float(end_a)-float(start_a))/float(slices))  #degrees to move each slice
        
        self.scan_stats['scan_total'] = slices
        
        self.start_transfer(slices)
        
        for i in xrange(0, slices):
            #move the laser!
            print str(i) + "/" + str(slices) +" (" + str(deg*i) + "/" + str(deg*slices) +")"
            
            self.send('G0 E{0} F{1}'.format(position, self.E_FEEDRATE))
            self.send('M400')

            filename = self.take_a_picture(i)
            self.transfer_file(filename)
            
            position += deg
            
            self.scan_stats['scan_current'] = i+1
            self.progress = float(i+1)*100.0 / float(slices)
            
            with self.monitor_lock:
                self.update_monitor_file()
                
            if self.is_aborted():
                break
        
        for image in self.skipped_images:
            print _("Resending:"), image
            self.transfer_file(image)
        
        self.finish_transfer()
        
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
    parser = argparse.ArgumentParser(add_help=False, formatter_class=argparse.ArgumentDefaultsHelpFormatter)
    
    parser.add_argument("-T", "--task-id",     help=_("Task ID."),              default=0)
    parser.add_argument("-U", "--user-id",     help=_("User ID. (future use)"), default=0)
    
    parser.add_argument(      "--address",  help=_("Remove server address.") )
    parser.add_argument(      "--port",     help=_("Remove server port."),     default=9898)
    parser.add_argument("-d", "--dest",     help=_("Destination folder."),     default=config.get('general', 'bigtemp_path') )
    parser.add_argument("-s", "--slices",   help=_("Number of slices."),       default=100)
    parser.add_argument("-i", "--iso",      help=_("ISO."),                    default=400)
    parser.add_argument("-W", "--width",    help=_("Image width in pixels."),  default=1920)
    parser.add_argument("-H", "--height",   help=_("Image height in pixels"),  default=1080)
    parser.add_argument("-b", "--begin",    help=_("Begin scanning from X."),  default=0)
    parser.add_argument("-e", "--end",      help=_("End scanning at X."),      default=360)
    parser.add_argument("-z", "--z-offset", help=_("Z offset."),               default=0)
    parser.add_argument("-y", "--y-offset", help=_("Y offset."),               default=0)
    parser.add_argument("-a", "--a-offset", help=_("A offset/rotation."),      default=0)
    parser.add_argument("--standalone", action='store_true',  help=_("Standalone operation. Does all preparations and cleanup.") )
    parser.add_argument('--help', action='help', help=_("Show this help message and exit") )

    # GET ARGUMENTS
    args = parser.parse_args()

    slices          = int(args.slices)
    destination     = args.dest
    host_address    = args.address
    host_port       = int(args.port)
    iso             = int(args.iso)
    start_a         = float(args.begin)
    end_a           = float(args.end)
    width           = int(args.width)
    height          = int(args.height)
    z_offset        = float(args.z_offset)
    y_offset        = float(args.y_offset)
    a_offset        = float(args.a_offset)
    standalone      = args.standalone
    task_id         = int(args.task_id)

    monitor_file    = config.get('general', 'task_monitor')      # TASK MONITOR FILE (write stats & task info, es: temperatures, speed, etc
    log_trace       = config.get('general', 'trace')        # TASK TRACE FILE 

    scan_dir        = os.path.join(destination, "images")

    if not os.path.exists(scan_dir):
        makedirs(scan_dir)

    ################################################################################

    app = PhotogrammetryScan(log_trace, 
                    monitor_file,
                    scan_dir,
                    standalone=standalone,
                    width=width,
                    height=height,
                    iso=iso,
                    host_address=host_address,
                    host_port=host_port)

    app_thread = Thread( 
            target = app.run, 
            args=( [task_id, start_a, end_a, y_offset, slices] ) 
            )
    app_thread.start()

    app.loop()          # app.loop() must be started to allow callbacks
    app_thread.join()

if __name__ == "__main__":
    main()

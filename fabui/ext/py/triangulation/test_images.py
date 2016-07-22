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
import errno
from fractions import Fraction
from threading import Event, Thread

# Import external modules
from picamera import PiCamera

# Import internal modules
from fabtotum.fabui.config  import ConfigService
from fabtotum.fabui.gpusher import GCodePusher

# Set up message catalog access
tr = gettext.translation('s_scan', 'locale', fallback=True)
_ = tr.ugettext

################################################################################

class TestCapture(GCodePusher):
    """
    Sweep scan application.
    """
    
    XY_FEEDRATE     = 10000
    Z_FEEDRATE      = 1500
    E_FEEDRATE      = 800
    
    def __init__(self, log_trace, monitor_file, scan_dir, standalone = False, width = 2592, height = 1944, rotation = 270, iso = 800, power = 230, shutter_speed = 35000):
        super(TestCapture, self).__init__(log_trace, monitor_file)
        
        self.standalone = standalone
        
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
            'scan_current'  : 0
        }
        
        self.add_monitor_group('scan', self.scan_stats)
            
    def trace(self, msg):
        """ for debug only, should be removed in production """
        print msg
            
    def get_progress(self):
        """ Custom progress implementation """
        return self.progress
    
    def take_a_picture(self, number = 0, suffix = ''):
        """ Camera control wrapper """
        scanfile = os.path.join(self.scan_dir, "test_{0}.jpg".format(number) )
        self.camera.capture(scanfile, quality=100)
    
    def run(self, start_z, end_z, slices, x_offset, y_offset):
        """
        Run the sweep scan.
        """
        
        self.send('G28')
                
        position = start_z
        
        if x_offset != 0:
            #if an offset for Z (Y in the rotated reference space) is set, moves to it.
            self.send('G0 X{0} F{1}'.format(x_offset, self.XY_FEEDRATE))  #go to y offset
            
        if y_offset != 0:
            #if an offset for Z (Y in the rotated reference space) is set, moves to it.
            self.send('G0 Y{0} F{1}'.format(y_offset, self.XY_FEEDRATE))  #go to y offset
        
        if start_z != 0:
            # If an offset is set .
            self.send('G0 Z{0} F{1}'.format(start_z, self.Z_FEEDRATE) )  #set zero
               
        dz = abs((float(end_z)-float(start_z))/float(slices))  #mm to move each slice
        
        self.scan_stats['scan_total'] = slices
        
        LASER_ON  = 'M700 S{0}'.format(self.laser_power)
        LASER_OFF = 'M700 S0'
        
        self.send(LASER_ON)
        
        self.camera.start_preview()
        
        for i in xrange(0, slices):
            #~ #move the laser!
            print str(i) + "/" + str(slices) + " @ " + str(position)
            #~ serial.write('G0 X' + str(pos) + 'F2500\r\n') 
            self.send('G0 Z{0} F{1}'.format(position, self.Z_FEEDRATE))
            self.send('M400') # Wait for the move to finish

            self.take_a_picture( int(position) )
           
            position += dz
            
            self.scan_stats['scan_current'] = i+1
            self.progress = float(i+1)*100.0 / float(slices)
        
        self.send(LASER_OFF)
        
        self.camera.stop_preview()
        
        self.trace( _("Scan completed.") )
        
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
    parser.add_argument("-d", "--dest",     help=_("Destination folder."),     default=config.get('general', 'bigtemp_path') )
    parser.add_argument("-s", "--slices",   help=_("Number of slices."),       default=100)
    parser.add_argument("-p", "--power",    help=_("Scan laser power 0-255."), default=0)
    parser.add_argument("-i", "--iso",      help=_("ISO."),                    default=400)
    parser.add_argument("-w", "--width",    help=_("Image width in pixels."),  default=1920)
    parser.add_argument("-h", "--height",   help=_("Image height in pixels"),  default=1080)
    parser.add_argument("-b", "--begin",    help=_("Begin scanning from X."),  default=0)
    parser.add_argument("-e", "--end",      help=_("End scanning at X."),      default=100)
    parser.add_argument("-x", "--x-offset", help=_("X offset."),               default=0)
    parser.add_argument("-y", "--y-offset", help=_("Y offset."),               default=0)
    parser.add_argument('--help', action='help', help=_("Show this help message and exit") )

    # GET ARGUMENTS
    args = parser.parse_args()

    slices          = int(args.slices)
    destination     = args.dest
    iso             = int(args.iso)
    start_z         = float(args.begin)
    end_z           = float(args.end)
    width           = int(args.width)
    height          = int(args.height)
    power           = int(args.power)
    x_offset        = float(args.x_offset)
    y_offset        = float(args.y_offset)

    monitor_file    = config.get('general', 'task_monitor')      # TASK MONITOR FILE (write stats & task info, es: temperatures, speed, etc
    log_trace       = config.get('general', 'trace')        # TASK TRACE FILE 

    scan_dir        = os.path.join(destination, "images")

    if not os.path.exists(scan_dir):
        makedirs(scan_dir)

    ################################################################################

    app = TestCapture(log_trace, 
                    monitor_file,
                    scan_dir,
                    width=width,
                    height=height,
                    iso=iso)

    app_thread = Thread( 
            target = app.run, 
            args=( [start_z, end_z, slices, x_offset, y_offset] ) 
            )
    app_thread.start()
    
    # app.loop() must be started to allow callbacks
    app.loop()
    app_thread.join()

if __name__ == "__main__":
    main()

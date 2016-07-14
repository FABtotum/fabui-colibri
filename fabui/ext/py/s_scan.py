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

def makedirs(path):
    try:
        os.makedirs(path)
    except OSError as exc:  # Python >2.5
        if exc.errno == errno.EEXIST and os.path.isdir(path):
            pass
        else:
            raise

config = ConfigService()

# SETTING EXPECTED ARGUMENTS
parser = argparse.ArgumentParser(add_help=False, formatter_class=argparse.ArgumentDefaultsHelpFormatter)
parser.add_argument("task_id",          help=_("Task ID.") )
parser.add_argument("-d", "--dest",     help=_("Destination folder."),     default=config.get('general', 'bigtemp_path') )
parser.add_argument("-s", "--slices",   help=_("Number of slices."),       default=100)
parser.add_argument("-i", "--iso",      help=_("ISO."),                    default=400)
parser.add_argument("-p", "--power",    help=_("Scan laser power 0-255."), default=230)
parser.add_argument("-w", "--width",    help=_("Image width in pixels."),  default=1920)
parser.add_argument("-h", "--height",   help=_("Image height in pixels"),  default=1080)
parser.add_argument("-b", "--begin",    help=_("Begin scanning from X."),  default=0)
parser.add_argument("-e", "--end",      help=_("End scanning at X."),      default=100)
parser.add_argument("-y", "--y-offset", help=_("Y offset."),               default=0)
parser.add_argument("-z", "--z-offset", help=_("Z offset."),               default=0)
parser.add_argument("-a", "--a-offset", help=_("A offset/rotation."),      default=0)
parser.add_argument("--standalone", action='store_true',  help=_("Standalone operation. Does all preparations and cleanup.") )
parser.add_argument('--help', action='help', help=_("Show this help message and exit") )

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
standalone      = args.standalone
task_id         = int(args.task_id)

monitor_file    = config.get('general', 'task_monitor')      # TASK MONITOR FILE (write stats & task info, es: temperatures, speed, etc
log_trace       = config.get('general', 'trace')        # TASK TRACE FILE 

scan_dir        = os.path.join(destination, "images")

if not os.path.exists(scan_dir):
    makedirs(scan_dir)

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

################################################################################

class SweepScan(GCodePusher):
    """
    Sweep scan application.
    """
    
    XY_FEEDRATE     = 5000
    Z_FEEDRATE      = 1500
    E_FEEDRATE      = 800
    
    def __init__(self, log_trace, monitor_file, scan_dir, standalone = False, width = 2592, height = 1944, rotation = 270, iso = 800, power = 230, shutter_speed = 35000):
        super(SweepScan, self).__init__(log_trace, monitor_file)
        
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
            
    def trace(selg, msg):
        """ for debug only, should be removed in production """
        print msg
            
    def get_progress(self):
        """ Custom progress implementation """
        return self.progress
    
    def take_a_picture(self, number = 0, suffix = ''):
        """ Camera control wrapper """
        scanfile = os.path.join(self.scan_dir, "{0}{1}.jpg".format(number, suffix) )
        self.camera.capture(scanfile, quality=100)
    
    def run(self, task_id, start_x, end_x, a_offset, y_offset, z_offset, slices):
        """
        Run the sweep scan.
        """
        
        self.prepare_task(task_id, task_type='scan')
        self.set_task_status(GCodePusher.TASK_STARTED)
        
        if self.standalone:
            self.exec_macro("start_sweep_scan")
        #self.send('G90')
        
        LASER_ON  = 'M700 S{0}'.format(self.laser_power)
        LASER_OFF = 'M700 S0'
        
        position = start_x
        
        if start_x != 0:
            # If an offset is set .
            self.send('G0 X{0} F{1}'.format(start_x, SweepScan.XY_FEEDRATE) )  #set zero

        if a_offset != 0:
            #if an offset is set, rotates to the specified A angle.
            self.send('G0 E{0} {1}'.format(a_offset, SweepScan.E_FEEDRATE) )

        if z_offset != 0:
            #if an offset for Z (Y in the rotated reference space) is set, moves to it.
            self.send('G0 Z{0} F{1}'.format(z_offset, SweepScan.Z_FEEDRATE))  #go to y offset
            
        if y_offset != 0:
            #if an offset for Z (Y in the rotated reference space) is set, moves to it.
            self.send('G0 Y{0} F{1}'.format(y_offset, SweepScan.XY_FEEDRATE))  #go to y offset
                
        dx = abs((float(end_x)-float(start_x))/float(slices))  #mm to move each slice
        
        self.scan_stats['scan_total'] = slices
        
        for i in xrange(0, slices):
            #~ #move the laser!
            print str(i) + "/" + str(slices) +" (" + str(dx*i) + "/" + str(slices) +")"
            #~ serial.write('G0 X' + str(pos) + 'F2500\r\n') 
            self.send('G0 X{0} F{1}'.format(position, SweepScan.XY_FEEDRATE))
            self.send('M400') # Wait for the move to finish

            self.send(LASER_ON) #turn laser ON
            self.take_a_picture(i, '_l')
            
            self.send(LASER_OFF) #turn laser ON
            self.take_a_picture(i)
            
            position += dx
            
            self.scan_stats['scan_current'] = i+1
            self.progress = float(i+1)*100.0 / float(slices)
            
            with self.monitor_lock:
                self.update_monitor_file()
                
            if self.is_aborted():
                break
            
        if self.standalone:
            self.exec_macro("end_scan")
        
        self.stop()
                
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
        args=( [task_id, start_x, end_x, a_offset, y_offset, z_offset, slices] ) 
        )
app_thread.start()

app.loop()          # app.loop() must be started to allow callbacks
app_thread.join()

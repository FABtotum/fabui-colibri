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

# Import external modules
from watchdog.observers import Observer
from watchdog.events import PatternMatchingEventHandler
from picamera import PiCamera

# Import internal modules
from fabtotum.fabui.config  import ConfigService
from fabtotum.fabui.gpusher import GCodePusher

def makedirs(path):
    try:
        os.makedirs(path)
    except OSError as exc:  # Python >2.5
        if exc.errno == errno.EEXIST and os.path.isdir(path):
            pass
        else:
            raise

config = ConfigService()

#~ usage= 'Usage: r_scan.py 

#~ -x<first point x> 
#~ -y<first point y> 
#~ -i<second point x> 
#~ -j<second point y> 
#~ -n<density num> 
#~ -a<axis increments> 
#~ -b<starting deg> 
#~ -e<ending deg> 
#~ -o<safe_z offset> 
#~ -l<log> 
#~ -d<destination> 
#~ -v=verborse 
#~ -t<trace log> 
#~ -k<task_id>\n'

#~ try:
	#~ opts, args = getopt.getopt(sys.argv[1:],"x:y:i:j:n:a:b:e:o:l:d:v:t:k:z:p:",["x=","y=","i=","j=","n=","a=","b=","e=","o=","l=","d=","v=","t=","k=", "z=", "p="])
#~ except getopt.GetoptError:
	#~ #Error handling for unknown or incorrect number of options
	#~ print "\n\nERROR!\n Correct usage:\n\n",usage
	#~ sys.exit(2)
#~ for opt, arg in opts:
	#~ if opt =='--help':
		#~ print usage 
		#~ sys.exit()
	#~ elif opt in ("-x", "--x"):
		#~ x = float(arg)
	#~ elif opt in ("-y", "--y"):
		#~ y = float(arg)
	#~ elif opt in ("-i", "--i"):
		#~ x1 = float(arg)
	#~ elif opt in ("-j", "--j"):
		#~ y1 = float(arg)
	#~ elif opt in ("-n", "--n"):
		#~ probe_density = float(arg) #number of probes each unit: can be a float
 	#~ elif opt in ("-a", "--a"):
		#~ deg = int(arg)  #must be int deg
	#~ elif opt in ("-b", "--b"):
		#~ begin = int(arg)
	#~ elif opt in ("-e", "--e"):
		#~ end = int(arg)
	#~ elif opt in ("-o", "--o"):
		#~ safe_z = float(arg) 	# float
		#~ original_safe_z=safe_z
	#~ elif opt in ("-l", "--log"):
		#~ logfile = arg
	#~ elif opt in ("-d", "--d"):
		#~ destination = arg			#dest folder
	#~ elif opt in ("-v", "--v"):
		#~ debug=1						#verbose active?
	#~ elif opt in ("-t", "--t"):
		#~ log_trace = str(arg)		#trace log
	#~ elif opt in ("-k", "--k"):
		#~ task_id = int(arg)
	#~ elif opt in ("-z", "--z"):
		#~ z_hop = float(arg)			#the amount to hop (also known as safe Z)
	#~ elif opt in ("-p", "--p"):
		#~ probe_skip = float(arg)		#adaptive control

# SETTING EXPECTED ARGUMENTS
parser = argparse.ArgumentParser(add_help=False, formatter_class=argparse.ArgumentDefaultsHelpFormatter)
parser.add_argument("-d", "--dest",     help="Destination folder.",     default=config.get('general', 'bigtemp_path') )
parser.add_argument("-n", "--n-probes", help="Number of probes.",       default=100)
parser.add_argument("-b", "--begin",    help="Begin scanning from X.",  default=0)
parser.add_argument("-e", "--end",      help="End scanning at X.",      default=360)
parser.add_argument("-x", "--x1",       help="X1.",                     default=0)
parser.add_argument("-y", "--y1",       help="Y1",                      default=0)
parser.add_argument("-i", "--x2",       help="X2.",                     default=0)
parser.add_argument("-j", "--y2",       help="Y2.",                     default=0)
parser.add_argument('--help', action='help', help='show this help message and exit')

# GET ARGUMENTS
args = parser.parse_args()

destination     = args.dest
x1              = args.x1
y1              = args.y1
x2              = args.x2
y2              = args.y2

monitor_file    = config.get('general', 'task_monitor')      # TASK MONITOR FILE (write stats & task info, es: temperatures, speed, etc
log_trace       = config.get('general', 'trace')        # TASK TRACE FILE 

################################################################################

#~ print 'ROTARY SCAN MODULE STARTING' 
#~ print 'scanning from'+str(start_a)+"to"+str(end_a); 
#~ print 'Num of scans : ', slices
#~ print 'ISO  setting : ', iso
#~ print 'Resolution   : ', width ,'*', height, ' px'
#~ print 'Laser PWM.  : ', power
#~ print 'z offset     : ', z_offset

#ESTIMATED SCAN TIME ESTIMATION
#~ estimated=(slices*1.99)/60
#~ if(estimated<1):
    #~ estimated*=60
    #~ unit= "Seconds"
#~ else:
    #~ unit= "Minutes"

#~ print 'Estimated Scan time =', str(estimated) + " " + str(unit) + "  [Pessimistic]"

#~ #ESTIMATED SCAN TIME ESTIMATION
#~ estimated = (slices*1.99)/60
#~ if estimated<1 :
    #~ estimated *= 60
    #~ unit= "Seconds"
#~ else:
    #~ unit= "Minutes"

#~ print 'Estimated Scan time =', str(estimated) + " " + str(unit) + "  [Pessimistic]"

################################################################################

#probe routine
#~ def probe(x,y):
	#~ global points_on_plane
	#~ serial_reply=""

	#~ serial.flushInput()
	#~ serial.write("G30\r\n")
	
	#~ probe_start_time = time.time()
	#~ while not serial_reply[:22]=="echo:endstops hit:  Z:":
		#~ serial_reply=serial.readline().rstrip()	
		#~ #issue G30 Xnn Ynn and waits reply.
		#~ if (time.time() - probe_start_time>90):  
			#~ #timeout management
			#~ trace("Could not probe this point")
			#~ return False
			#~ break	
		#~ pass
		
	#~ #get the z position
	#~ z=float(serial_reply.split("Z:")[1].strip())
	
	#~ new_point = [x,y,z,1]
	#~ points_on_plane = np.vstack([points_on_plane, new_point]) #append new point to the cloud.
	
	#~ trace("Probed "+str(x)+ "," +str(y) + " / " + str(deg) + " degrees = " + str(z))
	#~ return True

class ProbeScan(GCodePusher):
    def __init__(self, log_trace, monitor_file):
        super(ProbeScan, self).__init__(log_trace, monitor_file)
        
        self.progress = 0.0
            
    def get_progress(self):
        """ Custom progress implementation """
        return self.progress
    
    def probe(self):
        reply = self.send('G30', expected_reply = 'echo:')
        print reply
    
    def run(self, x1, y1, x2, y2):
        self.exec_macro("start_probe_scan")
        
        #self.send('G28 X0 Y0')
        self.send('G27')
        
        self.send('M401')
        self.probe()
        
        #self.send('G91')
        self.send('G0 Z+2 F1500')
        #self.send('M402')
        #self.send('M401') 
        #position = start_a
        
        #~ dx = abs((float(end_x)-float(start_x))/float(slices))  #mm to move each slice
        #deg = abs((float(end_a)-float(start_a))/float(slices))  #degrees to move each slice
        
        #~ for i in xrange(0, slices):
            #~ pass
        
        #self.exec_macro("end_scan")
        
        self.stop()
                
app = ProbeScan(log_trace, monitor_file)

app.run(x1, y1, x2, y2)
app.loop()

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
import numpy as np

# Import internal modules
from fabtotum.fabui.config  import ConfigService
from fabtotum.fabui.gpusher import GCodePusher

# Set up message catalog access
tr = gettext.translation('p_scan', 'locale', fallback=True)
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
parser.add_argument("task_id",          help=_("Task ID.") )
parser.add_argument("-d", "--dest",     help=_("Destination folder."),     default=config.get('general', 'bigtemp_path') )
parser.add_argument("-o", "--output",   help=_("Output cloud file."),      default='cloud.asc' )
parser.add_argument("-n", "--n-probes", help=_("Number of probes."),       default=1)
parser.add_argument("-b", "--begin",    help=_("Begin scanning from X."),  default=0)
parser.add_argument("-e", "--end",      help=_("End scanning at X."),      default=360)
parser.add_argument("-x", "--x1",       help=_("X1."),                     default=0)
parser.add_argument("-y", "--y1",       help=_("Y1."),                     default=0)
parser.add_argument("-i", "--x2",       help=_("X2."),                     default=10)
parser.add_argument("-j", "--y2",       help=_("Y2."),                     default=10)
parser.add_argument("-z", "--safe-z",   help=_("Safe Z."),                 default=0)
parser.add_argument("--standalone", action='store_true',  help=_("Standalone operation. Does all preparations and cleanup.") )
parser.add_argument('--help', action='help', help=_("Show this help message and exit") )

# GET ARGUMENTS
args = parser.parse_args()

destination     = args.dest
output_file     = args.output
x1              = float(args.x1)
y1              = float(args.y1)
x2              = float(args.x2)
y2              = float(args.y2)
probe_density   = float(args.n_probes)
safe_z          = float(args.safe_z)
standalone      = args.standalone
task_id         = int(args.task_id)

monitor_file    = config.get('general', 'task_monitor')      # TASK MONITOR FILE (write stats & task info, es: temperatures, speed, etc
log_trace       = config.get('general', 'trace')        # TASK TRACE FILE 

################################################################################

print 'PROBE MODULE STARTING'
print 'scanning from' + str(x1)+ "," +str(y1)+ " to " +str(x2)+ "," +str(y2); 
print 'Probing density : ', probe_density , " points/mm"
#print 'Start/End       : ', begin ,' to ', end, 'deg'

################################################################################

class ProbeScan(GCodePusher):
    """
    Probe scan application.
    """
    
    MINIMAL_SAFE_Z  = 36.0
    SAFE_Z_OFFSET   = 2.0
    XY_FEEDRATE     = 5000
    Z_FEEDRATE      = 1500
    E_FEEDRATE      = 800
    
    def __init__(self, log_trace, monitor_file, standalone = False):
        super(ProbeScan, self).__init__(log_trace, monitor_file)
        
        self.standalone = standalone
        self.progress = 0.0
        
        self.scan_stats = {
            'type'          : 'probe',
            'projection'    : 'planar',
            'scan_total'    : 0,
            'scan_current'  : 0
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
        self.send('G0 X{0} Y{1} F{2}'.format(x, y, ProbeScan.XY_FEEDRATE) )
        
        reply = self.send('G30', expected_reply = 'echo:', timeout = 90)
        if reply:
            print reply
            
            z = float( reply[-1].split("Z:")[1].strip() )
            z = round(z, 3)  # round to 3 decimanl points
            
            self.trace( _("Probed {0},{1} / {2} degrees = {3}").format(x, y, 0.0, z))
            
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
        with open(output_file,"w")  as cloud_file:
            if len(points)>0:
                for row in xrange(0, len(points)):
                    cloud_file.write( '{0}, {1}, {2}\n'.format( points[row][0], points[row][1], points[row][2])) 
    
    def run(self, task_id, output_file, x1, y1, x2, y2, probe_density):
        """
        Run the probe scan.
        """
                
        self.prepare_task(task_id, 'scan')
        self.set_task_status(GCodePusher.TASK_STARTED)
        
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
                    
                    if safe_z < ProbeScan.MINIMAL_SAFE_Z:
                        safe_z = ProbeScan.MINIMAL_SAFE_Z
                        
                    safe_z = safe_z + ProbeScan.SAFE_Z_OFFSET
                    self.send('G0 Z{0} F{1}'.format(safe_z, ProbeScan.Z_FEEDRATE) )
                    
                probe_num += 1
                self.scan_stats['scan_current'] = probe_num
                self.progress = ( float(probe_num) / float(total_num) ) * 100.0
        
        self.trace( _("Saving point cloud to file {0}").format(output_file) )
        self.save_as_cloud(points, output_file)
        
        self.trace( _("Physical Probing completed") )
        
        if self.standalone:
            self.exec_macro("end_scan")
        
        self.stop()
                
app = ProbeScan(log_trace, monitor_file, standalone)

app_thread = Thread( 
        target = app.run, 
        args=( [task_id, output_file, x1, y1, x2, y2, probe_density] ) 
        )
app_thread.start()
app.loop()

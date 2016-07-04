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

# Import external modules
from watchdog.observers import Observer
from watchdog.events import PatternMatchingEventHandler

# Import internal modules
from fabtotum.fabui.config  import ConfigService
from fabtotum.fabui.gpusher import GCodePusher
import fabtotum.fabui.macros.general as general_macros
import fabtotum.fabui.macros.printing as print_macros

config = ConfigService()

# SETTING EXPECTED ARGUMENTS
parser = argparse.ArgumentParser()
parser.add_argument("file",         help="Gcode file to execute.")
parser.add_argument("task_id",      help="Task ID.")
parser.add_argument("--standalone", action='store_true',  help="Standalone operatin. Does all printing preparations.")
group = parser.add_argument_group('standalone arguments')
group.add_argument("--ext_temp",   help="Extruder temperature (for UI feedback only)",  default=180, nargs='?')
group.add_argument("--bed_temp",   help="Bed temperature (for UI feedback only)",  default=50,  nargs='?')
group.add_argument("--autolevel",  action='store_true',  help="Auto bed leveling. Valid only when --standalone is used.")

# GET ARGUMENTS
args = parser.parse_args()

# INIT VARs
gcode_file      = args.file         # GCODE FILE
task_id         = args.task_id      # TASK ID  
monitor_file    = config.get('general', 'task_monitor')      # TASK MONITOR FILE (write stats & task info, es: temperatures, speed, etc
log_trace       = config.get('general', 'trace')        # TASK TRACE FILE 
ext_temp_target = args.ext_temp     # EXTRUDER TARGET TEMPERATURE (previously read from file) 
bed_temp_target = args.bed_temp     # BED TARGET TEMPERATURE (previously read from file) 
standalone      = args.standalone   # Standalong operation
autolevel       = args.autolevel    # Standalong operation
################################################################################

class PrintApplication(GCodePusher):
    """
    Additive print application.
    """
    
    def __init__(self, log_trace, monitor_file, standalone = False, autolevel = False):
        super(PrintApplication, self).__init__(log_trace, monitor_file)
        self.standalone = standalone
        self.autolevel = autolevel
    
    def trace(selg, msg):
        print msg
    
    def progress_callback(self, percentage):
        print "Progress", percentage
    
    def first_move_callback(self):
        print "Print stared."
    
    def file_done_callback(self):  
        print "Print finished."

        if self.standalone:
            print_macros.end_additive(self)
            print_macros.end_additive_safe_zone(self)
        
        self.stop()
        
    def state_change_callback(self, state):
        if state == 'paused':
            print "Print PAUSED"
        if state == 'resumed':
            print "Print RESUMED"
        if state == 'aborted':
            print "Print ABORTED"
    
    def temp_change_callback(self, action, data):
        print action, data
        
    def run(self, gcode_file, task_id, ext_temp_target, bed_temp_target):
        """
        Run the print application.
        
        :param gcode_file: GCode file containing print commands.
        :param task_id: Task ID
        :param ext_temp_target: Pre-heat temperature for the extruder
        :param bed_temp_target: Pre-heat temperature for the bed
        :type gcode_file: string
        :type task_id: int
        :type ext_temp_target: float
        :type bed_temp_target: float
        """
        
        print "Parsing file..."
        self.prepare(gcode_file, task_id, ext_temp_target, bed_temp_target)
        print "Done."
        
        if self.standalone:
            print_macros.check_pre_print(self)
            
            if self.autolevel:
                general_macros.auto_bed_leveling(self)
            else:
                general_macros.raise_bed(self)
                general_macros.home_all(self, [ext_temp_target, bed_temp_target])
            
            print_macros.start_additive(self, [ext_temp_target, bed_temp_target])
        
        self.send_file(gcode_file)
        
        print "Print initiated."


app = PrintApplication(log_trace, monitor_file, standalone, autolevel)

app.run(gcode_file, task_id, ext_temp_target, bed_temp_target)
app.loop()

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
parser.add_argument("file",         help="gcode file to execute")
parser.add_argument("command_file", help="command file")
parser.add_argument("task_id",      help="id_task")
parser.add_argument("monitor",      help="monitor file",  default=config.get('general', 'task_monitor'), nargs='?')
parser.add_argument("trace",        help="trace file",  default=config.get('general', 'trace'), nargs='?')
parser.add_argument("--ext_temp",   help="extruder temperature (for UI feedback only)",  default=180, nargs='?')
parser.add_argument("--bed_temp",   help="bed temperature (for UI feedback only)",  default=50,  nargs='?')
parser.add_argument("--standalone", help="call macros internally",  default=False, nargs='?')

# GET ARGUMENTS
args = parser.parse_args()

# INIT VARs
gcode_file      = args.file         # GCODE FILE
command_file    = args.command_file # OVERRIDE DATA FILE 
task_id         = args.task_id      # TASK ID  
monitor_file    = args.monitor      # TASK MONITOR FILE (write stats & task info, es: temperatures, speed, etc
log_trace       = args.trace        # TASK TRACE FILE 
ext_temp_target = args.ext_temp     # EXTRUDER TARGET TEMPERATURE (previously read from file) 
bed_temp_target = args.bed_temp     # BED TARGET TEMPERATURE (previously read from file) 
standalone      = args.standalone   # Standalong operation
################################################################################

class PrintApplication(GCodePusher):
    """
    Additive print application.
    """
    
    def __init__(self, log_trace, monitor_file, standalone = False):
        super(PrintApplication, self).__init__(log_trace, monitor_file)
        self.standalone = standalone
    
    def trace(selg, msg):
        print msg
    
    def progress_callback(self, percentage):
        print "Progress", percentage
    
    def first_move_callback(self):
        print "Print stared"
    
    def file_done_callback(self):  
        if self.standalone:
            print_macros.end_additive(self)
            print_macros.end_additive_safe_zone(self)
        
        self.stop()
    
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
        
        self.prepare(gcode_file, task_id, ext_temp_target, bed_temp_target)
        
        if self.standalone:
            general_macros.raise_bed(self)
            print_macros.start_additive(self, [ext_temp_target, bed_temp_target])
        
        self.send_file(gcode_file)


app = PrintApplication(log_trace, monitor_file, standalone)

app.run(gcode_file, task_id, ext_temp_target, bed_temp_target)
app.loop()

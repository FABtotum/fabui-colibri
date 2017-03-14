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
from fabtotum.utils.translation import _, setLanguage
from fabtotum.fabui.config  import ConfigService
from fabtotum.fabui.gpusher import GCodePusher
import fabtotum.fabui.macros.general as general_macros
import fabtotum.fabui.macros.printing as print_macros

################################################################################

class PrintApplication(GCodePusher):
    """
    Additive print application.
    """
    
    def __init__(self, log_trace, monitor_file, standalone = False, autolevel = False, finalize = True):
        super(PrintApplication, self).__init__(log_trace, monitor_file, use_stdout=standalone)
        self.standalone = standalone
        self.autolevel = autolevel
        self.finalize = finalize
    
    # Only for development
    #~ def trace(selg, msg):
        #~ print msg
    
    #~ def progress_callback(self, percentage):
        #~ print "Progress", percentage
    
    def print_finalize(self):
        if self.standalone or self.finalize:
            if self.is_aborted():
                self.set_task_status(GCodePusher.TASK_ABORTING)
            else:
                self.set_task_status(GCodePusher.TASK_COMPLETING)
            
            self.exec_macro("end_additive")
            
            if self.is_aborted():
                self.exec_macro("end_additive_aborted")
                self.set_task_status(GCodePusher.TASK_ABORTED)
            else:
                self.exec_macro("end_additive_safe_zone")
                self.set_task_status(GCodePusher.TASK_COMPLETED)
        
        self.stop()
    
    def first_move_callback(self):
        self.trace( _("Print STARTED") )
        
        with self.monitor_lock:
            self.print_stats['first_move'] = True
            self.set_task_status(GCodePusher.TASK_RUNNING)
            self.update_monitor_file()

    def file_done_callback(self):   
        self.print_finalize()
        
    def state_change_callback(self, state):
        if state == 'paused':
            self.trace( _("Print PAUSED") )
            self.trace( _("Please wait until the buffered moves in totumduino are finished") )
            self.exec_macro("pause_additive")
            
        if state == 'resuming':    
            self.trace( _("RESUMING Print") )
            self.exec_macro("resume_additive")
            
        if state == 'resumed':
            self.trace( _("Print RESUMED") )
        if state == 'aborted':
            self.trace( _("Print ABORTED") )
    
    def temp_change_callback(self, action, data):
        print action, data
        
    def run(self, task_id, gcode_file):
        """
        Run the print.
        
        :param gcode_file: GCode file containing print commands.
        :param task_id: Task ID
        :type gcode_file: string
        :type task_id: int
        """

        self.prepare_task(task_id, task_type='print', gcode_file=gcode_file)
        self.set_task_status(GCodePusher.TASK_RUNNING)
        
        #~ if self.standalone:
            #~ self.exec_macro("check_pre_print")
            
            #~ if self.autolevel:
                #~ self.exec_macro("raise_bed")
                #~ self.exec_macro("auto_bed_leveling")
            #~ else:
                #~ self.exec_macro("home_all")
                #~ #general_macros.home_all(self, [ext_temp_target, bed_temp_target])
            
            #~ self.exec_macro("start_print")
        
        self.send_file(gcode_file)
        #self.push_file()
        
        self.trace( _("Print initialized.") )

def main():
    config = ConfigService()
    

    # SETTING EXPECTED ARGUMENTS
    parser = argparse.ArgumentParser(formatter_class=argparse.ArgumentDefaultsHelpFormatter)
    parser.add_argument("-T", "--task-id",     help=_("Task ID."),      default=0)
    parser.add_argument("-F", "--file-name",   help=_("File name."),    required=True)
    parser.add_argument("--autolevel",  action='store_true',  help=_("Auto bed leveling. Valid only when --standalone is used."), default=False)
    
    # GET ARGUMENTS
    args = parser.parse_args()

    # INIT VARs
    gcode_file      = args.file_name     # GCODE FILE
    task_id         = args.task_id
    autolevel       = args.autolevel
    if task_id == 0:
        standalone  = True
    else:
        standalone  = False
        
    monitor_file    = config.get('general', 'task_monitor')      # TASK MONITOR FILE (write stats & task info, es: temperatures, speed, etc
    log_trace       = config.get('general', 'trace')        # TASK TRACE FILE 
    
    app = PrintApplication(log_trace, monitor_file, standalone, autolevel)

    app.run(task_id, gcode_file)
    app.loop()

if __name__ == "__main__":
    main()

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
# import general constants
from fabtotum.fabui.constants import ERROR_WIRE_END, ERROR_EXTRUDE_MINTEMP, ERROR_AMBIENT_TEMP, ERROR_LONG_EXTRUSION, ERROR_MAX_TEMP
#import needed macros 
import fabtotum.fabui.macros.general as general_macros
import fabtotum.fabui.macros.printing as print_macros


################################################################################

class PrintApplication(GCodePusher):
    """
    Additive print application.
    """
    
    def __init__(self, log_trace, monitor_file, standalone = False, 
                    autolevel = False, finalize = True,
                    lang = 'en_US.UTF-8', send_email=False):
        super(PrintApplication, self).__init__(log_trace, monitor_file, 
                use_stdout=standalone, lang=lang, send_email=send_email)
                
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
            
            z_override = float(self.override_stats['z_override'])
            if z_override:
                self.trace( _("Saving Z override") )
                info = self.config.get_current_head_info()
                nozzle_offset = float(info['nozzle_offset'])
                nozzle_offset += z_override
                info['nozzle_offset'] = nozzle_offset
                self.config.save_current_head_info(info)
            
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
            #~ self.pusher_stats['first_move'] = True
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
    
    def error_callback(self, error_no):
        """ 
        Triggered when an error occures.
        :param error_no: Error number
        """
        message = _("Warning: print paused due to error {0}".format(error_no))
        if(self.is_paused() == False):
            if(error_no == ERROR_WIRE_END):
                message = _("Warning: filament is about to end")
            elif(error_no == ERROR_EXTRUDE_MINTEMP):
                message = _("Warning: cannot extrude filament: the nozzle temperature is too low")
            elif(error_no == ERROR_AMBIENT_TEMP):
                 message = _("Warning: ambient temperature is less then 15&deg;C. Cannot continue")
            elif(error_no == ERROR_LONG_EXTRUSION):
                message = _("Warning: cannot extrude so much filament!")
            elif(error_no == ERROR_MAX_TEMP):
                message = _("Warning: extruder Temperature critical, shutting down")
            self.trace(message)
            self.task_stats['message'] = message
            self.pause()
        
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
                #~ self.exec_macro("auto_bed_leveling")
            #~ else:
                #~ self.exec_macro("home_all")
                #~ #general_macros.home_all(self, [ext_temp_target, bed_temp_target])
            
            #~ self.exec_macro("start_print")
        
        self.send_file(gcode_file)
        
        self.trace( _("Print initialized.") )

def main():
    config = ConfigService()
    

    # SETTING EXPECTED ARGUMENTS
    parser = argparse.ArgumentParser(formatter_class=argparse.ArgumentDefaultsHelpFormatter)
    parser.add_argument("-T", "--task-id",     help="Task ID.",      default=0)
    parser.add_argument("-F", "--file-name",   help="File name.",    required=True)
    parser.add_argument("--autolevel",  action='store_true',  help="Auto bed leveling. Valid only when --standalone is used.", default=False)
    parser.add_argument("--lang",              help="Output language", default='en_US.UTF-8' )
    parser.add_argument("--email",             help="Send an email on task finish", action='store_true', default=False)
    parser.add_argument("--shutdown",          help="Shutdown on task finish", action='store_true', default=False )
    
    # GET ARGUMENTS
    args = parser.parse_args()

    # INIT VARs
    gcode_file      = args.file_name     # GCODE FILE
    task_id         = args.task_id
    autolevel       = args.autolevel
    lang            = args.lang
    send_email      = bool(args.email)
    
    if task_id == 0:
        standalone  = True
    else:
        standalone  = False
        
    monitor_file    = config.get('general', 'task_monitor')      # TASK MONITOR FILE (write stats & task info, es: temperatures, speed, etc
    log_trace       = config.get('general', 'trace')        # TASK TRACE FILE 
    
    app = PrintApplication(log_trace, monitor_file, standalone, autolevel, lang=lang, send_email=send_email)

    app.run(task_id, gcode_file)
    app.loop()

if __name__ == "__main__":
    main()

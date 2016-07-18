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
from threading import Event, Thread

# Import internal modules
from fabtotum.fabui.config  import ConfigService
from fabtotum.fabui.gpusher import GCodePusher
import fabtotum.fabui.macros.general as general_macros
import fabtotum.fabui.macros.printing as print_macros

# Set up message catalog access
tr = gettext.translation('autotune', 'locale', fallback=True)
_ = tr.ugettext

################################################################################

class PIDAutotune(GCodePusher):
    """
    Automatic PID tuninig.
    """
    
    def __init__(self, log_trace, monitor_file):
        super(PIDAutotune, self).__init__(log_trace, monitor_file)
        
        self.autotune_stats = {
            'P' : 0.0,
            'I' : 0.0,
            'D' : 0.0
        }
        
        self.add_monitor_group('autotune', self.autotune_stats)

    def run(self, task_id, extruder, temperature, cycles):
        
        self.prepare_task(task_id, task_type='autotune')
        self.set_task_status(GCodePusher.TASK_STARTED)
        
        self.trace( _('PID Autotune started.') )
        
        reply = self.send('M303 E{0} S{1} C{2}'.format(extruder, temperature, cycles), expected_reply = 'PID Autotune finished!' )
        #print reply
#~ DEBUG :   >> [M303 E0 S200 C8] [ bias: 150 d: 104 min: 198.88 max: 201.61]
#~ DEBUG :   >> [M303 E0 S200 C8] [ Ku: 96.99 Tu: 18.09]
#~ DEBUG :   >> [M303 E0 S200 C8] [ Classic PID]
#~ DEBUG :   >> [M303 E0 S200 C8] [ Kp: 58.19]
#~ DEBUG :   >> [M303 E0 S200 C8] [ Ki: 6.43]
#~ DEBUG :   >> [M303 E0 S200 C8] [ Kd: 131.57]
#~ DEBUG :   >> [M303 E0 S200 C8] [PID Autotune finished! Put the last Kp, Ki and Kd constants from above into Configuration.h]        
        #Ku: 96.99 Tu: 18.09', u' Classic PID ', u' Kp: 58.19', u' Ki: 6.43', u' Kd: 131.57'
        if len(reply) > 6:
            Kp = reply[-4]
            Ki = reply[-3]
            Kd = reply[-2]
            
            self.autotune_stats['P'] = Kp
            self.autotune_stats['I'] = Ki
            self.autotune_stats['D'] = Kd
            
            trace('Result: P: {0}, I: {1}, D: {2}'.format(Kp, Ki, Kd) )
            
        else:
            trace( _('No results. Failed.') )
            
        
        self.send('M300')
        self.trace( _('PID Autotune finished.') )
        self.set_task_status(GCodePusher.TASK_COMPLETED)
        
        self.stop()

def main():
    config = ConfigService()

    # SETTING EXPECTED ARGUMENTS
    parser = argparse.ArgumentParser(formatter_class=argparse.ArgumentDefaultsHelpFormatter)
    parser.add_argument("task_id",          help=_("Task ID.") )
    parser.add_argument("-e", "--extruder", help=_("Extruder to select."),              default=0)
    parser.add_argument("-t", "--temp",     help=_("Temperature used for PID tunind."), default=200)
    parser.add_argument("-c", "--cycles",   help=_("Number of tuning cycles"),          default=8)

    # GET ARGUMENTS
    args = parser.parse_args()

    # INIT VARs
    task_id         = int(args.task_id)      # TASK ID  
    monitor_file    = config.get('general', 'task_monitor')      # TASK MONITOR FILE (write stats & task info, ex: temperatures, speed, etc
    log_trace       = config.get('general', 'trace')        # TASK TRACE FILE 
    temperature     = int(args.temp)
    extruder        = int(args.extruder)
    cycles          = int(args.cycles)
    
    app = PIDAutotune(log_trace, monitor_file)

    app_thread = Thread( 
            target = app.run, 
            args=( [task_id, extruder, temperature, cycles] ) 
            )
    app_thread.start()

    # app.loop() must be started to allow callbacks
    app.loop()
    app_thread.join()


if __name__ == "__main__":
    main()

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

__authors__ = "Daniel Kesler"
__license__ = "GPL - https://opensource.org/licenses/GPL-3.0"
__version__ = "1.0"

# Import standard python module
import os
import sys
import re
import json
import argparse
import time
import logging
from threading import Event, Thread, RLock

import gettext

# Import external modules
from watchdog.observers import Observer
from watchdog.events import PatternMatchingEventHandler

# Import internal modules
from fabtotum.fabui.config import ConfigService
from fabtotum.utils.gcodefile import GCodeFile, GCodeInfo
from fabtotum.utils.pyro.gcodeclient import GCodeServiceClient

from fabtotum.fabui.macros.all import PRESET_MAP

# Set up message catalog access
tr = gettext.translation('gpusher', 'locale', fallback=True)
_ = tr.ugettext

ERROR_MESSAGE = {
    #error codes
    'UNKNOWN_ERROR'             : _(''),
    'NO_ERROR'                  : _(''),
    'ERROR_KILLED'              : _(''),
    'ERROR_STOPPED'             : _(''),
    'ERROR_DOOR_OPEN'           : _(''),
    'ERROR_MIN_TEMP'            : _('Extruder temperature below minimal.'),
    'ERROR_MAX_TEMP'            : _('Extruder temperature above maximal.'),
    'ERROR_MAX_BED_TEMP'        : _('Bed temperature above maximal.'),
    'ERROR_X_MAX_ENDSTOP'       : _('X max end-stop triggered.'),
    'ERROR_X_MIN_ENDSTOP'       : _('X min end-stop triggered.'),
    'ERROR_Y_MAX_ENDSTOP'       : _('Y max end-stop triggered.'),
    'ERROR_Y_MIN_ENDSTOP'       : _('Y min end-stop triggered.'),
    'ERROR_IDLE_SAFETY'         : _(''),
    #error codes for FABUI configurable functionalities
    'ERROR_Y_BOTH_TRIGGERED'    : _(''),
    'ERROR_Z_BOTH_TRIGGERED'    : _('')
}

################################################################################
def parse_temperature(line):
    match = re.search('ok\sT:(?P<T>[0-9]+\.[0-9]+)\s\/(?P<TT>[0-9]+\.[0-9]+)\sB:(?P<B>[0-9]+\.[0-9]+)\s\/(?P<BT>[0-9]+\.[0-9]+)\s', line)
    if match:
        return ( match.group('T'), match.group('TT'), match.group('B'), match.group('BT') )

class GCodePusher(object):
    """
    GCode pusher.
    """
    
    def __init__(self, log_trace, monitor_file = None, gcs = None, config = None, use_callback = True):
                        
        self.monitor_lock = RLock()
        self.monitor_info = {
            "progress"              : 0.0,
            "paused"                : False,
            "print_started"         : False,
            "started"               : time.time(),
            "auto_shutdown"         : False,
            "completed"             : False,
            "completed_time"        : 0,
            "layer_count"           : 0,
            "current_layer"         : 0,
            "filename"              : "",
            "task_id"               : 0,
            "ext_temp"              : 0.0,
            "ext_temp_target"       : 0.0,
            "bed_temp"              : 0.0,
            "bed_temp_target"       : 0.0,
            "z_override"            : 0.0,
            "rpm"                   : 0.0,
            "laser"                 : 0.0,
            "fan"                   : 0.0,    
            "speed"                 : 100.0,
            "flow_rate"             : 100.0,
            "tip"                   : False,
            "message"               : '',
            "current_line_number"   : 0,
            "gcode_info"            : None
        }
        
        self.monitor_file = monitor_file
        
        self.trace_file = log_trace
        
        self.trace_logger = logging.getLogger('Trace')
        self.trace_logger.setLevel(logging.INFO)
        
        ch = logging.FileHandler(log_trace)
        formatter = logging.Formatter("%(message)s")
        ch.setFormatter(formatter)
        ch.setLevel(logging.INFO)
        self.trace_logger.addHandler(ch)
        
        if not config:
            self.config = ConfigService()
        else:
            self.config = config
        
        if not gcs:
            self.gcs = GCodeServiceClient()
        else:
            self.gcs = gcs
        
        if use_callback:
            self.gcs.register_callback(self.callback_handler)
        
        self.macro_error = 0
        self.macro_warning = 0
        self.macro_skipped = 0
        
        self.progress_monitor = None
    
    def writeMonitor(self):
        """
        Write stats to monitor file
        """
        _layers =   {
                    'total' : str(self.monitor_info['layer_count']), 
                    'actual': str(self.monitor_info['current_layer'])
                    }
 
        _stats  =   {
                    "percent"           : str(self.monitor_info['progress']),
                    "line_number"       : str(self.monitor_info['current_line_number']),
                    "extruder"          : str(self.monitor_info['ext_temp']),
                    "bed"               : str(self.monitor_info['bed_temp']),
                    "extruder_target"   : str(self.monitor_info['ext_temp_target']),
                    "bed_target"        : str(self.monitor_info['bed_temp_target'] ),
                    "z_override"        : str(self.monitor_info['z_override']),
                    "layers"            : str(self.monitor_info['layer_count']),
                    "rpm"               : str(self.monitor_info['rpm']),
                    "fan"               : str(self.monitor_info['fan']),
                    "speed"             : str(self.monitor_info['speed']),
                    "flow_rate"         : str(self.monitor_info['flow_rate'])
                    }
                                
        _tip    =   {
                    "show"              : str(self.monitor_info['tip']),
                    "message"           : str(self.monitor_info['message'])
                    }

        if self.monitor_info["gcode_info"]:
            filename = self.monitor_info["gcode_info"]["filename"]
            line_count = self.monitor_info["gcode_info"]["line_count"]
        else:
            filename =''
            line_count = 0

         
        _print  =   {
                    "name"              : str(filename),
                    "lines"             : str(line_count),
                    "print_started"     : str(self.monitor_info["print_started"]),
                    "started"           : str(self.monitor_info["started"]),
                    "duration"          : str( time.time() - float(self.monitor_info["started"]) ),
                    "paused"            : str(self.monitor_info["paused"]),
                    "completed"         : str(self.monitor_info["completed"]),
                    "completed_time"    : str(self.monitor_info["completed_time"]),
                    "shutdown"          : str(self.monitor_info["auto_shutdown"]),
                    "tip"               : _tip,
                    "stats"             : _stats
                    }

        engine = 'unknown'
        if self.monitor_info["gcode_info"]:
            if 'slicer' in self.monitor_info["gcode_info"]:
                engine = self.monitor_info["gcode_info"]["slicer"]
        
        stats   =   {
                    "type"      : "print", 
                    "print"     : _print,
                    "engine"    : str(engine),
                    "task_id"   : self.monitor_info["task_id"]
                    }
            
        if self.monitor_file:
            with open(self.monitor_file,'w+') as file:
                file.write(json.dumps(stats))
    
    def trace(self, log_msg):
        """ 
        Write to log message to trace file
        
        :param log_msg: Log message
        :type log_msg: string
        """
        #logging.info(log_msg)
        self.trace_logger.info(log_msg)
        pass
        
    def resetTrace(self):
        """ Reset trace file """
        with open(self.trace_file, 'w'):
            pass
    
    def __shutdown_procedure(self):
        self.trace( _("Schutting down...") )
        
        # Wait for all commands to be finished
        reply = self.gcs.send('M400')
        
        # Tell totumduino Raspberry is going to sleep :'(
        reply = self.gcs.send('M729')
        
        # Stop the GCodeService connection
        self.gcs.stop()
        
        os.system('poweroff')
                
    def first_move_callback(self):
        self.trace( _("Task Started") )
    
    def __first_move_callback(self):
        """
        Triggered when first move command in file executed
        """
        
        self.monitor_lock.acquire()
        self.monitor_info['print_started'] = True
        self.monitor_lock.release()
        
        self.first_move_callback()
    
    def gcode_comment_callback(self, data):
        """
        Triggered when a comment in gcode file is detected
        """
        pass
    
    def __gcode_comment_callback(self, data):

        self.monitor_lock.acquire()
        if 'layer' in data:
            self.monitor_info['current_layer'] = data['layer']
        self.monitor_lock.release()
        
        self.gcode_comment_callback(data)

    def temp_change_callback(self, action, data):
        """
        Triggered when temperature change is detected
        """
        pass
        
    def __temp_change_callback(self, action, data):

        self.monitor_lock.acquire()
        
        if action == 'ext_bed':
            #print "Ext: {0}, Bed: {1}".format(data[0], data[1])
            self.monitor_info['ext_temp'] = float(data[0])
            self.monitor_info['bed_temp'] = float(data[1])
        elif action == 'bed':
            #print "Bed: {0}".format(data[0])
            self.monitor_info['bed_temp'] = float(data[0])
        elif action == 'ext':
            #print "Ext: {0}".format(data[0])
            self.monitor_info['ext_temp'] = float(data[0])
        elif action == 'all':
            self.monitor_info["ext_temp"]           = float(data[0])
            self.monitor_info["ext_temp_target"]    = float(data[1])
            self.monitor_info["bed_temp"]           = float(data[2])
            self.monitor_info["bed_temp_target"]    = float(data[3])
            
        print data
            
        self.monitor_lock.release()
        
        self.writeMonitor()
        
        self.temp_change_callback(action, data)
    
    def gcode_action_callback(self, action, data):
        """
        Triggered when action hook for a gcode command is activated 
        """
        pass
                
    def __gcode_action_callback(self, action, data):

        monitor_write = False

        self.monitor_lock.acquire()
        
        if action == 'heating':

            if data[0] == 'M109':
                self.trace( _("Wait for nozzle temperature to reach {0}&deg;C").format(data[1]) )
                self.monitor_info['ext_temp_target'] = float(data[1])
                monitor_write = True
            elif data[0] == 'M190':
                self.trace( _("Wait for bed temperature to reach {0}&deg;C").format(data[1]) )
                self.monitor_info['bed_temp_target'] = float(data[1])
                monitor_write = True
            elif data[0] == 'M104':
                self.trace( _("Nozzle temperature set to {0}&deg;C").format(data[1]) )
                self.monitor_info['ext_temp_target'] = float(data[1])
                monitor_write = True
            elif data[0] == 'M140':
                self.trace( _("Bed temperature set to {0}&deg;C").format(data[1]) )
                self.monitor_info['bed_temp_target'] = float(data[1])
                monitor_write = True
            
        elif action == 'cooling':
            
            if data[0] == 'M106':
                value = int((float( data[1] ) / 255) * 100)
                self.trace( _("Fan value set to {0}%").format(value) )
                self.monitor_info['fan'] = float(data[1])
                monitor_write = True
            elif data[0] == 'M107':
                self.trace( _("Fan off") )
                self.monitor_info['fan'] = 0.0
                monitor_write = True
                
        elif action == 'printing':
            
            if data[0] == 'M220': # Speed factor
                value = float( data[1] )
                self.trace( _("Speed factor set to {0}%").format(value) )
                self.monitor_info['speed'] = float(data[1])
                monitor_write = True
            elif data[0] == 'M221': # Extruder flow
                value = float( data[1] )
                self.trace( _("Extruder flow set to {0}%").format(value) )
                self.monitor_info['flow_rate'] = float(data[1])
                monitor_write = True
                
        elif action == 'milling':
            if data[0] == 'M0':
                """ .. todo: do something with it """
                pass
            elif data[0] == 'M1':
                """ .. todo: do something with it """
                pass
            elif data[0] == 'M3':
                self.trace( _("Milling motor RPM set to {0}%").format(value) )
                self.monitor_info['rpm'] = float(data[1])
                monitor_write = True
            elif data[0] == 'M4':
                self.trace( _("Milling motor RPM set to {0}%").format(value) )
                self.monitor_info['rpm'] = float(data[1])
                monitor_write = True
            elif data[0] == 'M6':
                """ .. todo: Check whether laser power should be scaled from 0-255 to 0.0-100.0 """
                self.trace( _("Laser power set to {0}%").format(value) )
                self.monitor_info['laser'] = float(data[1])
                monitor_write = True
                
        elif action == 'message':
            print "MSG: {0}".format(data)
            
        self.monitor_lock.release()
        
        if monitor_write:
            self.writeMonitor()
        
        self.gcode_action_callback(action, data)

    def file_done_callback(self):
        """ Triggered when gcode file execution is completed """
        if self.monitor_info["auto_shutdown"]:
            self.__shutdown_procedure()
        else:
            self._stop()
            
    def __file_done_callback(self, data):
        """
        Internal file done callback preventing user from overloading it.
        """
        self.monitor_lock.acquire()
        
        self.monitor_info["completed_time"] = int(time.time())
        self.monitor_info["completed"] = True        
        self.monitor_info['progress'] = 100.0 #gcs.get_progress()
        self.writeMonitor()
        
        self.monitor_lock.release()
        
        self.file_done_callback()
    
    def state_change_callback(self, data):
        """
        Triggered when state is changed. (paused/resumed)
        """
        pass
        
    def __state_change_callback(self, data):
        self.monitor_lock.acquire()
        
        if data == 'paused':
            self.trace( _("Print is now paused") )
            self.monitor_info["paused"] = True
        elif data == 'resumed':
            self.monitor_info["paused"] = False
            
        self.monitor_lock.release()
        
        self.writeMonitor()
        
        self.state_change_callback(data)
    
    def progress_callback(self, percentage):
        """ 
        Triggered when progress percentage changes 
        
        :param percentage: Progress percentage 0.0 to 100.0
        :type percentage: float
        """
        pass
    
    def error_callback(self, error_no, error_msg):
        """ 
        Triggered when an error occures.
        
        :param error_no: Error number
        :param error_msg: Error message
        :type error_no: int
        :type error_msg: string
        """
        pass
    
    def __error_callback(self, error_no, error_msg):
        # TODO: process errors
        # TODO: complete ERROR_MESSAGE
        self.error_callback(error_no, error_msg)
    
    def __config_change_callback(id, data):
        print "__config_change_callback", id, data 
        if id == 'shutdown':
            self.monitor_info["auto_shutdown"] = (data == 'on')
            
    
    def callback_handler(self, action, data):
        print "callback_handler", action, data
        if action == 'file_done':
            self.__file_done_callback(data)
        elif action == 'gcode_comment':
            self.__gcode_comment_callback(data)
        elif action.startswith('gcode_action'):
            self.__gcode_action_callback(action.split(':')[1], data)
        elif action == 'first_move':
            self.__first_move_callback()
        elif action.startswith('temp_change'):
            self.__temp_change_callback(action.split(':')[1], data)
        elif action == 'state_change':
            self.__state_change_callback(data)
        elif action.startswith('config:'):
            self.__config_change_callback(action.split(':')[1], data)
        elif action == 'error':
            self.__error_callback(data[0], data[1])

    def progress_monitor_thread(self):
        old_progress = -1
        monitor_write = False
        
        while self.gcs.still_running():
            
            progress = self.gcs.get_progress()
                
            if old_progress != progress:
                old_progress = progress
                self.monitor_lock.acquire()
                self.monitor_info['progress'] = progress
                self.monitor_lock.release()
                self.progress_callback(progress)
                monitor_write = True

            if monitor_write:
                self.writeMonitor()
                monitor_write = False

            time.sleep(2)
       
    def prepare(self, gcode_file, task_id,
                    ext_temp_target = 0.0,
                    bed_temp_target = 0.0,
                    rpm = 0):
        """
        
        
        :param gcode_file:
        :param task_id:
        :param ext_temp_target:
        :param bed_temp_target:
        :param rpm: ???
        """
        
        gfile = GCodeFile(gcode_file)
        
        self.monitor_info["progress"] = 0.0
        self.monitor_info["paused"] = False
        self.monitor_info["print_started"] = False
        self.monitor_info["started"] = time.time()
        self.monitor_info["auto_shutdown"] = False
        self.monitor_info["completed"] = False
        self.monitor_info["completed_time"] = 0
        self.monitor_info["layer_count" ] = 0
        self.monitor_info["current_layer"] = 0
        self.monitor_info["filename"] = gcode_file
        self.monitor_info["task_id"] = task_id
        #self.monitor_info["ext_temp"] = ext_temp
        self.monitor_info["ext_temp_target"] = ext_temp_target
        #self.monitor_info["bed_temp"] = bed_temp
        self.monitor_info["bed_temp_target"] = bed_temp_target
        self.monitor_info["z_override"] = 0.0
        self.monitor_info["rpm"] = 0
        self.monitor_info["fan"] = 0.0
        self.monitor_info["speed"] = 100.0
        self.monitor_info["flow_rate"] = 100.0
        self.monitor_info["tip"] = False
        self.monitor_info["message"] = ''
        self.monitor_info["current_line_number"] = 0
        self.monitor_info["gcode_info"] = gfile.info
        
        if self.monitor_file:
            print "Creating monitor thread"
            
            self.progress_monitor = Thread( target=self.progress_monitor_thread )
            self.progress_monitor.start() 
        else:
            print "Skipping monitor thread"
        
        if gfile.info['type'] == GCodeInfo.PRINT:
            # READ TEMPERATURES BEFORE PRINT STARTS (improve UI feedback response)
            reply = self.gcs.send("M105")
            if reply:
                ext_temp, ext_temp_target, bed_temp, bed_temp_target = parse_temperature(reply[0])

        self.monitor_info["ext_temp"] = ext_temp
        self.monitor_info["ext_temp_target"] = ext_temp_target
        self.monitor_info["bed_temp"] = bed_temp
        self.monitor_info["bed_temp_target"] = bed_temp_target
        self.monitor_info["z_override"] = 0.0
        self.monitor_info["rpm"] = rpm
        
        self.resetTrace()
    
    def loop(self):
        """
        Wait for all GCodePusher threads to finish.
        """
        self.gcs.loop()
        if self.progress_monitor:
            self.progress_monitor.join()
        time.sleep(0.5)
        
    def __stop_thread(self):
        print "Trying to stop..."
        self.gcs.stop()   
        print "Stopped."
        
    def stop(self):
        """
        Signal all GCodePusher threads to stop.
        """
        stop_thread = Thread( target = self.__stop_thread )
        stop_thread.start()
    
    def exec_macro(self, preset, args = None, atomic = True):
        """
        Execute macro command.
        """
        self.reset_macro_status()
        if atomic:
            self.macro_start()
        
        if preset in PRESET_MAP:
            PRESET_MAP[preset](self, args)
        else:
            print _("Preset '{0}' not found").format(preset)
        
        if atomic:
            self.macro_end()
        
        if self.macro_error > 0:
            return False
            
        return True
    
    def reset_macro_status(self):
        """
        Reset macro status counters to zero.
        """
        self.macro_warning = 0
        self.macro_error = 0
        self.macro_skipped = 0
    
    def macro_start(self):
        #self.gcs.set_atomic_group('macro')
        self.gcs.atomic_begin(group = 'macro')
        
    def macro_end(self):
         self.gcs.atomic_end()
         #self.gcs.set_atomic_group(None)
    
    def macro(self, code, expected_reply, timeout, error_msg, delay_after, warning=False, verbose=True):
        """
        Send a command and check it's reply.
        
        :param code: gcode
        :param expected_reply: Expected reply
        :param error_msg: Error message to display
        :param timeout: Reply timeout in seconds
        :param delay_after: Time in seconds to wait after receiving the rely
        :param warning: Treat wrong reply as warning not as error
        :param verbose: Whether initial message should be displayed or not.
        :type code: string
        :type expected_reply: string
        :type timeout: float
        :type error_msg: string
        :type delay_after: float
        :type warning: bool
        :type verbose: bool
        """
        if self.macro_error == 0:
            if verbose:
                self.trace(error_msg)
            
            reply = self.gcs.send(code, timeout=timeout, group = 'macro')
            if expected_reply:
                # Check if the reply is as expected
                if reply:
                    if reply[0] != expected_reply:
                        if warning:
                            self.trace(error_msg + _(": Warning!"))
                            self.macro_warning += 1
                        else:
                            self.trace(error_msg + _(": Failed ({0})".format(reply[0]) ))
                            self.macro_error += 1
                else:
                    self.trace(error_msg + _(": Failed ({0})".format('<aborted>') ))
                    self.macro_error += 1
        else:
            self.trace(error_msg + _(": Skipped"))
            self.macro_skipped += 1
                
        #time.sleep(delay_after) #wait the desired amount
        
    def send(self, code, block = True, timeout = None, trace = None, group = 'gcode'):
        """
        Send a single gcode command and display trace message.
        """
        if trace:
            self.trace(trace)
            
        #TODO: ConnectionClosedError
        return self.gcs.send(code, block=block, timeout=timeout, group=group)
        
    def send_file(self, filename):
        """
        """
        return self.gcs.send_file(filename)
 

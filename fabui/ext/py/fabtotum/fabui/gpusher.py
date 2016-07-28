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
import gettext
import logging
from threading import Event, Thread, RLock


# Import external modules

# Import internal modules
from fabtotum.fabui.config import ConfigService
from fabtotum.utils.gcodefile import GCodeFile, GCodeInfo
from fabtotum.utils.pyro.gcodeclient import GCodeServiceClient
from fabtotum.database      import Database, timestamp2datetime
from fabtotum.database.task import Task

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
    
    TASK_PREPARING      = 'preparing'
    TASK_RUNNING        = 'running'
    TASK_PAUSED         = 'paused'
    TASK_COMPLETED      = 'completed'
    TASK_COMPLETING     = 'completing'
    TASK_ABORTING       = 'aborting'
    TASK_ABORTED        = 'aborted'
    
    TYPE_PRINT          = 'print'
    TYPE_MILL           = 'mill'
    TYPE_SCAN           = 'scan'
    TYPE_LASER          = 'laser'
    
    UPDATE_PERIOD       = 2 # seconds
    
    def __init__(self, log_trace, monitor_file = None, gcs = None, config = None, use_callback = True, use_stdout = False):
                        
        self.monitor_lock = RLock()
        
        self.gcode_info = None
        self.use_stdout = use_stdout
        # Task specific attributes
        self.task_stats = {
            'type'                  : 'unknown',
            'id'                    : 0,
            'pid'                   : os.getpid(),
            'status'                : GCodePusher.TASK_PREPARING,
            'started_time'          : time.time(),
            'completed_time'        : 0,
            'duration'              : 0,
            'percent'               : 0.0,
            'auto_shutdown'         : False,
            'send_email'            : False,
            'message'               : ''
        }
        
        # Pusher/File specific attributes
        self.pusher_stats = {
            'filename'              : '',
            'line_total'            : 0,
            'line_current'          : 0,
            'type'                  : GCodeInfo.RAW
        }
        
        # Pusher/File specific attributes
        self.override_stats = {
            'z_override'            : 0.0,
            'fan'                   : 0,
            'rpm'                   : 0,
            'laser'                 : 0,
            'flow_rate'             : 0,
            'speed'                 : 0    
        }
        
        self.standardized_stats = {
            'task'      : self.task_stats,
            'gpusher'   : self.pusher_stats,
            'override'  : self.override_stats
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
    
    def add_monitor_group(self, group, content = {}):
        """
        Add a custom group to monitor file with a specific content.
        If not content is provided, the groups is initialized with an empty dict.
        
        :param group: Group name.
        :param content: Dictinary containg group attributes.
        :type group: string
        :type content: dict
        """
        if group not in self.standardized_stats:
            self.standardized_stats[group] = content
            
        return self.standardized_stats[group]
        
    def remove_monitor_group(self, group):
        """
        Remove a custom group from the monitor file.
        
        :param group: Group name.
        :type group: string
        """
        if group not in ['task', 'override', 'gpusher']:
            del self.standardized_stats[group]
    
    def update_monitor_file(self):
        """
        Write stats to monitor file
        """
        # Update duration
        self.task_stats['duration'] = str( time.time() - float(self.task_stats['started_time']) )
        
        print "update_monitor_file:", self.task_stats['status']
        
        if self.monitor_file:
            with open(self.monitor_file,'w+') as file:
                file.write( json.dumps(self.standardized_stats) )
        
    
    def trace(self, log_msg):
        """ 
        Write to log message to trace file
        
        :param log_msg: Log message
        :type log_msg: string
        """
        if self.use_stdout:
            print log_msg
        else:
            self.trace_logger.info(log_msg)
        
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
        self.first_move_callback()
    
    def gcode_comment_callback(self, data):
        """
        Triggered when a comment in gcode file is detected
        """
        pass
    
    def __gcode_comment_callback(self, data):
        
        if 'layer' in data:
            with self.monitor_lock:
                if 'print' in self.standardized_stats:
                    self.standardized_stats['print']['layer_current'] = data['layer']
                    self.update_monitor_file()
        
        self.gcode_comment_callback(data)

    def temp_change_callback(self, action, data):
        """
        Triggered when temperature change is detected
        """
        pass
        
    def __temp_change_callback(self, action, data):

        #~ self.monitor_lock.acquire()
        
        #~ if action == 'ext_bed':
            #~ #print "Ext: {0}, Bed: {1}".format(data[0], data[1])
            #~ self.monitor_info['ext_temp'] = float(data[0])
            #~ self.monitor_info['bed_temp'] = float(data[1])
        #~ elif action == 'bed':
            #~ #print "Bed: {0}".format(data[0])
            #~ self.monitor_info['bed_temp'] = float(data[0])
        #~ elif action == 'ext':
            #~ #print "Ext: {0}".format(data[0])
            #~ self.monitor_info['ext_temp'] = float(data[0])
        #~ elif action == 'all':
            #~ self.monitor_info["ext_temp"]           = float(data[0])
            #~ self.monitor_info["ext_temp_target"]    = float(data[1])
            #~ self.monitor_info["bed_temp"]           = float(data[2])
            #~ self.monitor_info["bed_temp_target"]    = float(data[3])
            
        #~ print data
            
        #~ self.monitor_lock.release()
        
        #~ self.update_monitor_file()
        
        self.temp_change_callback(action, data)
    
    def gcode_action_callback(self, action, data):
        """
        Triggered when action hook for a gcode command is activated 
        """
        pass
                
    def __gcode_action_callback(self, action, data):

        monitor_write = False

        with self.monitor_lock:
            if action == 'heating':

                if data[0] == 'M109':
                    self.trace( _("Wait for nozzle temperature to reach {0}&deg;C").format(data[1]) )
                elif data[0] == 'M190':
                    self.trace( _("Wait for bed temperature to reach {0}&deg;C").format(data[1]) )
                elif data[0] == 'M104':
                    self.trace( _("Nozzle temperature set to {0}&deg;C").format(data[1]) )
                elif data[0] == 'M140':
                    self.trace( _("Bed temperature set to {0}&deg;C").format(data[1]) )
                
            elif action == 'cooling':
                
                if data[0] == 'M106':
                    value = int((float( data[1] ) / 255) * 100)
                    self.trace( _("Fan value set to {0}%").format(value) )
                    self.override_stats['fan'] = float(data[1])
                    monitor_write = True
                elif data[0] == 'M107':
                    self.trace( _("Fan off") )
                    self.override_stats['fan'] = 0.0
                    monitor_write = True
            
            elif action == 'z_override':
                self.override_stats['z_override'] = str(data[0])
            
            elif action == 'printing':
                
                if data[0] == 'M220': # Speed factor
                    value = float( data[1] )
                    self.trace( _("Speed factor set to {0}%").format(value) )
                    self.override_stats['speed'] = float(data[1])
                    monitor_write = True
                elif data[0] == 'M221': # Extruder flow
                    value = float( data[1] )
                    self.trace( _("Extruder flow set to {0}%").format(value) )
                    self.override_stats['flow_rate'] = float(data[1])
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
                    self.override_stats['rpm'] = float(data[1])
                    monitor_write = True
                elif data[0] == 'M4':
                    self.trace( _("Milling motor RPM set to {0}%").format(value) )
                    self.override_stats['rpm'] = float(data[1])
                    monitor_write = True
                elif data[0] == 'M6':
                    """ .. todo: Check whether laser power should be scaled from 0-255 to 0.0-100.0 """
                    self.trace( _("Laser power set to {0}%").format(value) )
                    self.override_stats['laser'] = float(data[1])
                    monitor_write = True
                    
            elif action == 'message':
                self.task_stats['message'] = data
                print "MSG: {0}".format(data)
            
            if monitor_write:
                self.update_monitor_file()
        
        self.gcode_action_callback(action, data)

    def file_done_callback(self):
        """ Triggered when gcode file execution is completed """
        if self.task_stats["auto_shutdown"]:
            self.__shutdown_procedure()
        else:
            self._stop()
            
    def __file_done_callback(self, data):
        """
        Internal file done callback preventing user from overloading it.
        """
        with self.monitor_lock:        
            self.task_stats['percent'] = 100.0
            self.update_monitor_file()
        
        self.file_done_callback()
    
    def set_task_status(self, status):
        with self.monitor_lock:
            self.task_stats['status'] = status 
            self.update_monitor_file()

        if (status == GCodePusher.TASK_COMPLETED or
            status == GCodePusher.TASK_ABORTED):        
            self.task_stats['completed_time'] = time.time()
            self.__update_task_db()
        
        if (status == GCodePusher.TASK_COMPLETED or
            status == GCodePusher.TASK_COMPLETING or
            status == GCodePusher.TASK_ABORTING or
            status == GCodePusher.TASK_ABORTED or
            status == GCodePusher.TASK_RUNNING):
            self.__update_task_db()
    
    def is_aborted(self):
        return ( self.task_stats['status'] == GCodePusher.TASK_ABORTED
              or self.task_stats['status'] == GCodePusher.TASK_ABORTING)
        
    def is_paused(self):
        return self.task_stats['status'] == GCodePusher.TASK_PAUSED
        
    def is_started(self):
        return self.task_stats['status'] == GCodePusher.TASK_RUNNING
        
    def is_completed(self):
        return self.task_stats['status'] == GCodePusher.TASK_COMPLETED
    
    def state_change_callback(self, data):
        """
        Triggered when state is changed. (paused/resumed)
        """
        pass
        
    def __state_change_callback(self, data):
        
        
        with self.monitor_lock:        
            if data == 'paused':
                #~ self.trace( _("Task has been paused") )
                self.task_stats['status'] = GCodePusher.TASK_PAUSED
                #self.monitor_info["paused"] = True
                
            elif data == 'resumed':
                #~ self.trace( _("Task has been resumed") )
                self.task_stats['status'] = GCodePusher.TASK_RUNNING
            
            elif data == 'aborted':
                #~ self.trace( _("Task has been aborted") )
                self.task_stats['status'] = GCodePusher.TASK_ABORTING
                self.__update_task_db()
                        
            self.update_monitor_file()
        
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
            self.task_stats["auto_shutdown"] = (data == 'on')
        elif id == 'reload':
            self.config.reload()
    
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

    def get_progress(self):
        """ 
        Get progress of file push or override this function for custom progress calculation.
        """
        return self.gcs.get_progress()

    def progress_monitor_thread(self):
        old_progress = -1
        monitor_write = False
        
        while self.gcs.still_running():
            
            progress = self.get_progress()
                
            if old_progress != progress:
                old_progress = progress
                self.progress_callback(progress)
            
            # Write progress even if it did not change because duration is update
            # during update_monitor_file()
            with self.monitor_lock:
                self.task_stats['percent'] = progress
                self.update_monitor_file()

            time.sleep(GCodePusher.UPDATE_PERIOD)
    
    def prepare_task(self, task_id, task_type = 'unknown', gcode_file = None, auto_shutdown = False, send_email = False):
        
        self.task_stats['type']             = task_type
        self.task_stats['id']               = task_id
        self.task_stats['status']           = GCodePusher.TASK_PREPARING
        self.task_stats['started_time']     = time.time()
        self.task_stats['completed_time']   = 0
        self.task_stats['duration']         = 0
        self.task_stats['percent']          = 0.0
        self.task_stats['auto_shutdown']    = auto_shutdown
        self.task_stats['send_email']       = send_email
        self.task_stats['message']          = ''
                
        self.override_stats['z_override']   = 0.0
        self.override_stats['fan']          = 0
        self.override_stats['rpm']          = 0
        self.override_stats['laser']        = 0
        self.override_stats['flow_rate']    = 100.0
        self.override_stats['speed']        = 100.0
        
        if gcode_file:
            gfile = GCodeFile(gcode_file)
            self.pusher_stats['filename']       = gcode_file
            self.pusher_stats['line_total']     = gfile.info['line_count']
            self.pusher_stats['line_current']   = 0
            self.pusher_stats['type']           = gfile.info['type']
            
            if gfile.info['type'] == GCodeInfo.PRINT:
                engine = 'unknown'
                if 'slicer' in gfile.info:
                    engine = gfile.info['slicer']
                    
                layer_total = 0
                if 'layer_count' in gfile.info:
                    layer_total = int(gfile.info['layer_count'])
                
                if 'print' not in self.standardized_stats:
                    self.print_stats = {
                        'layer_total'   : layer_total,
                        'layer_current' : 0,
                        'engine'        : engine
                    }
                    self.add_monitor_group('print', self.print_stats)
                else:
                    self.standardized_stats['print']['engine'] = engine
                    self.standardized_stats['print']['layer_current'] = 0
                    self.standardized_stats['print']['layer_total'] = layer_total
                    
            elif gfile.info['type'] == GCodeInfo.MILL or gfile.info['type'] == GCodeInfo.DRILL:
                    self.mill_stats = {
                        # Place holder
                    }
                    self.add_monitor_group('mill', self.mill_stats)
                    
            elif gfile.info['type'] == GCodeInfo.LASER:
                    self.laser_stats = {
                        # Place holder
                    }
                    self.add_monitor_group('laser', self.laser_stats)
                    
        self.resetTrace()
        
        if self.monitor_file:
            print "Creating monitor thread"
            
            self.progress_monitor = Thread( target=self.progress_monitor_thread )
            self.progress_monitor.start() 
        else:
            print "Skipping monitor thread"
            
        #~ self.task_db = Task(self.db, task_id)
        
        with self.monitor_lock:
            self.update_monitor_file()
    
    def __update_task_db(self):
        """
        Converts task_stats to compatible format for sys_tasks table and writes
        the values to the database.
        """
        db          = Database(self.config)
        task_id     = self.task_stats['id']
        task_db     = Task(db, task_id)
        
        if (self.task_stats['status'] == GCodePusher.TASK_PREPARING or
            self.task_stats['status'] == GCodePusher.TASK_RUNNING or
            self.task_stats['status'] == GCodePusher.TASK_PAUSED):
            
            task_db['status'] = GCodePusher.TASK_RUNNING
            
        elif (self.task_stats['status'] == GCodePusher.TASK_COMPLETED or
            self.task_stats['status'] == GCodePusher.TASK_ABORTING or
            self.task_stats['status'] == GCodePusher.TASK_ABORTED):
            task_db['status'] = self.task_stats['status']
            
            task_db['finish_date'] = timestamp2datetime( self.task_stats['completed_time'] )
        
        print "DB.write, status:", self.task_stats['status']
        task_db.write()
    
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
                
        time.sleep(delay_after) #wait the desired amount
        
    def send(self, code, block = True, timeout = None, trace = None, group = 'gcode', expected_reply = 'ok'):
        """
        Send a single gcode command and display trace message.
        """
        if trace:
            self.trace(trace)
            
        #TODO: ConnectionClosedError
        return self.gcs.send(code, block=block, timeout=timeout, group=group, expected_reply=expected_reply)
        
    def send_file(self, filename):
        """
        """
        return self.gcs.send_file(filename)
 

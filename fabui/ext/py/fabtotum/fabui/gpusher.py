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
from fabtotum.utils.translation import _, setLanguage
from fabtotum.fabui.config import ConfigService
from fabtotum.utils.gcodefile import GCodeFile, GCodeInfo
from fabtotum.utils.gmacro import GMacroHandler
from fabtotum.utils.pyro.gcodeclient import GCodeServiceClient
from fabtotum.database      import Database, timestamp2datetime, TableItem
from fabtotum.database.task import Task
from fabtotum.database.file import File
from fabtotum.database.object  import Object
from fabtotum.database.obj_file import ObjFile

from fabtotum.fabui.constants import FAN_MAX_VALUE

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
    TASK_TERMINATED     = 'terminated'
    
    TYPE_PRINT          = 'print'
    TYPE_MILL           = 'mill'
    TYPE_SCAN           = 'scan'
    TYPE_LASER          = 'laser'
    
    UPDATE_PERIOD       = 2 # seconds
    
    def __init__(self, log_trace = None, monitor_file = None, gcs = None, 
				 config = None, use_callback = True, use_stdout = False, 
				 update_period = 2, lang = 'en_US.UTF-8', send_email=False, auto_shutdown=False):
                        
        self.monitor_lock = RLock()
        
        self.UPDATE_PERIOD = update_period
        self.gcode_info = None
        self.lang = lang
        _ = setLanguage(self.lang)
        self.use_stdout = use_stdout
        # Task specific attributes
        self.task_stats = {
            'type'                  : 'unknown',
            'controller'            : 'unknown',
            'id'                    : 0,
            'pid'                   : os.getpid(),
            'status'                : GCodePusher.TASK_PREPARING,
            'started_time'          : time.time(),
            'estimated_time'        : 0,
            'completed_time'        : 0,
            'duration'              : 0,
            'percent'               : 0.0,
            'auto_shutdown'         : auto_shutdown,
            'send_email'            : send_email,
            'message'               : ''
        }
        
        # Pusher/File specific attributes
        self.pusher_stats = {
            'file': {
                'full_path' : '',
                'name'      : '',
            },
            'line_total'            : 0,
            'line_current'          : 0,
            'type'                  : GCodeInfo.RAW,
            'first_move'            : False
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
                
        if not config:
            self.config = ConfigService()
        else:
            self.config = config
        
        if not gcs:
            self.gcs = GCodeServiceClient()
        else:
            self.gcs = gcs
        
        if not monitor_file:
            monitor_file = self.config.get('general', 'task_monitor')
            
        if not log_trace:
            log_trace = self.config.get('general', 'trace')
        
        self.monitor_file = monitor_file
        self.trace_file = log_trace
        
        self.trace_logger = logging.getLogger('Trace')
        self.trace_logger.setLevel(logging.INFO)
        
        ch = logging.FileHandler(log_trace)
        formatter = logging.Formatter("%(message)s")
        ch.setFormatter(formatter)
        ch.setLevel(logging.INFO)
        self.trace_logger.addHandler(ch)
        
        self.temperatres_file = self.config.get('general', 'temperature')
        
        if use_callback:
            self.gcs.register_callback(self.callback_handler)
        
        self.gmacro = GMacroHandler(self.gcs, self.config, self.trace, self.resetTrace, lang=self.lang)
        
        self.progress_monitor = None
        self.db = Database(self.config)
    
    def __send_task_email(self):
        
        if not self.task_stats["send_email"]:
            return
        
        import shlex, subprocess
        cmd = 'sudo -u www-data php /usr/share/fabui/index.php Std sendTaskEmail/{0}'.format( self.task_stats['id'] )
        try:
            output = subprocess.check_output( shlex.split(cmd) )
            self.trace( _("Email sent") )
        except subprocess.CalledProcessError as e:
            self.trace( _("Email sending failed") )
            
    def __self_destruct(self):
        import signal
        print 'Initiating SIGKILL'
        pid = os.getpid()
        os.kill(pid, signal.SIGKILL)
        print 'SIGKILL Failed'
    
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
        #with open(self.trace_file, 'w'):
         #   pass
        open(self.trace_file, 'w').close()
    
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
        self.pusher_stats['first_move'] = True
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
                    
                    if(len(data) > 1) :
                        s_value = data[1]
                    else:
                        s_value = FAN_MAX_VALUE
                        
                    value = int((float( s_value ) / FAN_MAX_VALUE) * 100)
                    self.trace( _("Fan value set to {0}%").format(value))
                    self.override_stats['fan'] = float(s_value)
                    monitor_write = True
                elif data[0] == 'M107':
                    self.trace( _("Fan off") )
                    self.override_stats['fan'] = 0.0
                    monitor_write = True
            
            elif action == 'z_override':
                self.override_stats['z_override'] = float(data[0])
            
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
                    value = int( data[1] )
                    self.trace( _("Milling motor RPM set to {0}").format(value) )
                    self.override_stats['rpm'] = float(data[1])
                    monitor_write = True
                elif data[0] == 'M4':
                    value = int( data[1] )
                    self.trace( _("Milling motor RPM set to {0}").format(value) )
                    self.override_stats['rpm'] = float(data[1])
                    monitor_write = True
                elif data[0] == 'M6':
                    value = float( data[1] )
                    """ .. todo: Check whether laser power should be scaled from 0-255 to 0.0-100.0 """
                    self.trace( _("Laser power set to {0}%").format(value) )
                    self.override_stats['laser'] = float(data[1])
                    monitor_write = True
                    
            elif action == 'pause':
                self.pause()
            
            elif action == 'message':
                self.task_stats['message'] = data
                self.trace( data )
            
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
        
        if self.task_stats['status'] == self.TASK_COMPLETED:
            self.__send_task_email()
    
    def finish_task(self):
        self.gcs.finish()
    
    def set_task_status(self, status):
        """
        Set task status.
        
        :param status: Can be one of `GCodePusher.TASK_*` values
        """
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
              or self.task_stats['status'] == GCodePusher.TASK_ABORTING )
        
    def is_paused(self):
        return self.task_stats['status'] == GCodePusher.TASK_PAUSED
        
    def is_started(self):
        return self.task_stats['status'] == GCodePusher.TASK_RUNNING
        
    def is_completed(self):
        return ( self.task_stats['status'] == GCodePusher.TASK_COMPLETED
              or self.task_stats['status'] == GCodePusher.TASK_COMPLETING )
    
    def state_change_callback(self, data):
        """
        Triggered when state is changed. (paused/resumed)
        """
        pass
        
    def __state_change_callback(self, data):
        """
        """
        
        with self.monitor_lock:        
            if data == 'paused':
                #~ self.trace( _("Task has been paused") )
                self.task_stats['status'] = GCodePusher.TASK_PAUSED
                #self.monitor_info["paused"] = True
            elif data == 'resumed':
                self.task_stats['status'] = GCodePusher.TASK_RUNNING
                
            elif data == 'aborted':
                #~ self.trace( _("Task has been aborted") )
                self.task_stats['status'] = GCodePusher.TASK_ABORTING
                self.__update_task_db()
                
            elif data == 'terminated':
                self.trace( _("Task has been terminated") )
                self.task_stats['status'] = GCodePusher.TASK_TERMINATED
                self.update_monitor_file()
                self.__self_destruct()
                
            self.update_monitor_file()
        
        self.state_change_callback(data)
        
        with self.monitor_lock:   
            if data == 'resuming':
                self.gcs.resumed()
    
    def progress_callback(self, percentage):
        """ 
        Triggered when progress percentage changes 
        
        :param percentage: Progress percentage 0.0 to 100.0
        :type percentage: float
        """
        pass
    
    def error_callback(self, error_no):
        """ 
        Triggered when an error occures.
        
        :param error_no: Error number
        :param error_msg: Error message
        :type error_no: int
        :type error_msg: string
        """
        pass
    
    def __error_callback(self, error_no):
        # TODO: process errors
        # TODO: complete ERROR_MESSAGE
        self.error_callback(error_no)
    
    def __config_change_callback(self, id, data):
        if id == 'shutdown':
            with self.monitor_lock:
                self.task_stats["auto_shutdown"] = (data == 'on')
                self.update_monitor_file()
        elif id == 'email':
            with self.monitor_lock:
                self.task_stats["send_email"] = (data == 'on')
                self.update_monitor_file()
        elif id == 'reload':
            self.config.reload()
    
    def callback_handler(self, action, data):

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
            self.__error_callback(data[0])
        elif action == 'self_descruct':
            print 'Self Descruct sequence activated...'
            self.__self_destruct()
        else:
            self.custom_action_callback(action, data)

    def custom_action_callback(self, action, data):
        """
        Handle user defined action
        """
        pass

    def get_progress(self):
        """ 
        Get progress of file push or override this function for custom progress calculation.
        """
        return self.gcs.get_progress()

    def __progress_monitor_thread(self):
        old_progress = -1
        monitor_write = False
        
        while self.gcs.still_running():
            
            progress = self.get_progress()
                
            if old_progress != progress:
                old_progress = progress
                dur = float(self.task_stats['duration'])
                
                first_move = self.pusher_stats['first_move']
                
                if progress == 0.0 or first_move == False:
                    self.task_stats['estimated_time'] = 0
                else:
                    self.task_stats['estimated_time'] = (( dur / float(progress)) * 100.0)
                
                print self.task_stats['estimated_time'], progress
                
                self.progress_callback(progress)
            
            # Write progress even if it did not change because duration is update
            # during update_monitor_file()
            with self.monitor_lock:
                self.task_stats['percent'] = progress
                self.update_monitor_file()

            time.sleep(GCodePusher.UPDATE_PERIOD)
    
    def prepare_task(self, task_id, task_type = 'unknown', task_controller = 'make', gcode_file = None):
        
        self.task_stats['type']             = task_type
        self.task_stats['controller']       = task_controller
        self.task_stats['id']               = task_id
        self.task_stats['status']           = GCodePusher.TASK_PREPARING
        self.task_stats['started_time']     = time.time()
        self.task_stats['completed_time']   = 0
        self.task_stats['estimated_time']   = 0
        self.task_stats['duration']         = 0
        self.task_stats['percent']          = 0.0
        #~ self.task_stats['auto_shutdown']    = auto_shutdown # configured in __init __
        #~ self.task_stats['send_email']       = send_email    # configured in __init __
        self.task_stats['message']          = ''
                
        self.override_stats['z_override']   = 0.0
        self.override_stats['fan']          = 0
        self.override_stats['rpm']          = 0
        self.override_stats['laser']        = 0
        self.override_stats['flow_rate']    = 100.0
        self.override_stats['speed']        = 100.0
        
        if gcode_file:
            gfile = GCodeFile(gcode_file)
            task_db = self.get_task(task_id)
            file = self.get_file(task_db['id_file'])
            self.pusher_stats['file']['full_path'] = gcode_file
            self.pusher_stats['file']['name']      = file['client_name']
            self.pusher_stats['line_total']        = gfile.info['line_count']
            self.pusher_stats['line_current']      = 0
            self.pusher_stats['type']              = gfile.info['type']
            self.pusher_stats['first_move']        = False
            
            
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
                    
            #~ elif gfile.info['type'] == GCodeInfo.LASER:
                    #~ self.laser_stats = {
                        #~ # Place holder
                    #~ }
                    #~ self.add_monitor_group('laser', self.laser_stats)
                    
        self.resetTrace()
        
        if self.monitor_file:            
            self.progress_monitor = Thread( target=self.__progress_monitor_thread )
            self.progress_monitor.start() 
        
        with self.monitor_lock:
            self.update_monitor_file()
    
    def __update_task_db(self):
        """
        Converts task_stats to compatible format for sys_tasks table and writes
        the values to the database.
        """
        task_id     = self.task_stats['id']
        
        if task_id == 0:
            return
        
        task_db     = Task(self.db, task_id)
        
        if (self.task_stats['status'] == GCodePusher.TASK_PREPARING or
            self.task_stats['status'] == GCodePusher.TASK_RUNNING or
            self.task_stats['status'] == GCodePusher.TASK_PAUSED):
            
            task_db['status'] = GCodePusher.TASK_RUNNING
            
        elif (self.task_stats['status'] == GCodePusher.TASK_COMPLETED or
            self.task_stats['status'] == GCodePusher.TASK_ABORTING or
            self.task_stats['status'] == GCodePusher.TASK_ABORTED):
            task_db['status'] = self.task_stats['status']
            
            task_db['finish_date'] = timestamp2datetime( self.task_stats['completed_time'] )

        task_db['type']         = self.task_stats['type']
        task_db['controller']   = self.task_stats['controller']
        
        tid = task_db.write()
        
        if task_id == TableItem.DEFAULT:
            self.task_stats['id'] = tid
            with self.monitor_lock:
                self.update_monitor_file()
    
    def loop(self):
        """
        Wait for all GCodePusher threads to finish.
        """
        self.gcs.loop()
        if self.progress_monitor:
            self.progress_monitor.join()
        time.sleep(0.5)
        
    def __stop_thread(self):
        self.gcs.stop()   
        
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
        return self.gmacro.run(preset, args, atomic, reset_trace=False)
        
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
        Send a file to totumduino. File will be send line by line and it's progress 
        can be monitored using `get_progress` function.
        When the file has been completely sent `file_done_callback` will be called.
        """
        return self.gcs.send_file(filename)
 
    def get_temperature_history(self):
        """
        Return temperature history data.
        """
        try:
            json_f = open(self.temperatres_file, 'r')
            return json.load(json_f)
        except:
            return {}
            
 
    #### Object related API ####
 
    def get_task(self, task_id):
        t = Task(self.db, task_id)
        if t.exists():
            return t
        return None
 
    def get_file(self, file_id):
        f = File(self.db, file_id)
        if f.exists():
            return f
        return None
 
    def get_object(self, object_id):
        obj = Object(self.db, object_id)
        if obj.exists():
            return obj
        return None
 
    def add_object(self, name, desc, user_id, public=Object.PUBLIC):
        """
        Add object to database.
        """
        obj = Object(self.db, user_id=user_id, name=name, desc=desc, public=public)
        obj.write()
        
        return obj
        
    def delete_object(self, object_id):
        """
        Remove object from database and all the files associated to it.
        """        
        to_delete = []
        
        obj = self.get_object(object_id)
        if obj:
            ofmap = ObjFile(self.db)
            
            files = ofmap.object_files(object_id)
            for fid in files:
                f = File(self.db, file_id=fid)
                aids = ofmap.file_associations(fid)
                # Check if file is only associated to one object
                # if so we can remove it from db and filesystem
                if len(aids) == 1:
                    to_delete.append( f['full_path'] )
                    f.delete()
            
            # Remove all associations with object_id
            aids = ofmap.object_associations(object_id)
            ofmap.delete(aids)
            
            obj.delete()
            
            for f in to_delete:
                try:
                    os.remove(f)
                except Exception as e: 
                    pass
    
    def pause(self):
        self.gcs.pause()
    
    def resume(self):
        self.gcs.resume()


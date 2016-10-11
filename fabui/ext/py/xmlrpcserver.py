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
import os
import signal
import argparse
import logging
import time
import json

# Import external modules

# Import internal modules
from fabtotum.os.paths     import RUN_PATH
from fabtotum.fabui.config import ConfigService
from fabtotum.utils.gcodefile import GCodeFile, GCodeInfo
from fabtotum.utils.pyro.gcodeclient import GCodeServiceClient
from fabtotum.database      import Database, timestamp2datetime, TableItem
from fabtotum.database.task import Task
from fabtotum.database.file import File
from fabtotum.database.object  import Object
from fabtotum.database.obj_file import ObjFile

from fabtotum.fabui.macros.all import PRESET_MAP

from fabtotum.utils.xmlrpc.servercontainer import ServerContainer

def signal_handler(signal, frame):
    print "You pressed Ctrl+C!"
    logger.debug("Shutting down services. Please wait...")
    rpc.stop()

class ExposeCommands:
    
    def __init__(self, gcs, config, log_trace):
        self.gcs = gcs
        self.config = config
        self.macro_warning = 0
        self.macro_error = 0
        self.macro_skipped = 0
        
        self.use_stdout = False
        self.trace_file = log_trace
        
        self.trace_logger = logging.getLogger('Trace')
        self.trace_logger.setLevel(logging.INFO)
        
        ch = logging.FileHandler(log_trace)
        formatter = logging.Formatter("%(message)s")
        ch.setFormatter(formatter)
        ch.setLevel(logging.INFO)
        self.trace_logger.addHandler(ch)
    
    def __resetTrace(self):
        """ Reset trace file """
        with open(self.trace_file, 'w'):
            pass
    
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
    
    def send(self, code, block = True, timeout = None):
        """
        Send GCode and receive it's reply.
        """
        return self.gcs.send(code, block=block, timeout=timeout)
    
    def do_macro(self, preset, args = None, atomic = True):
        """
        Execute macro command.
        """
        self.__reset_macro_status()
        self.__resetTrace()
        if atomic:
            self.__macro_start()
        
        try:
            if preset in PRESET_MAP:
                reply = PRESET_MAP[preset](self, args)
        except Exception as e:
            print "Error:", e.strerr
            self.macro_error = 1
            reply = e.strerr
        
        print 'reply:', reply
        
        if atomic:
            self.__macro_end()
        
        if self.macro_error > 0:
            response = False
        else:
            response = True
            
        if reply is None:
            reply = 'ok'
            
        result = {}
        result['response']  = response
        result['reply']     = reply
        
        return json.dumps(result)
    
    def __reset_macro_status(self):
        """
        Reset macro status counters to zero.
        """
        self.macro_warning = 0
        self.macro_error = 0
        self.macro_skipped = 0
    
    def __macro_start(self):
        """ 
        Start macro execution block. This will activate atomic execution and 
        only commands marked as `macro` will be executed. Others will be aborted.
        """
        self.gcs.atomic_begin(group = 'macro')
        
    def __macro_end(self):
        """ End macro execution block and atomic execution. """
        self.gcs.atomic_end()
    
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
        reply = None
        
        print ">>", code
        
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
        
        return reply
        
    def do_abort(self):
        """ Send abort request """
        self.gcs.abort()
        return 'ok'
        
    def do_pause(self):
        """ Send pause request """
        self.gcs.pause()
        return 'ok'
        
    def do_resume(self):
        """ Send resume request """
        self.gcs.resume()
        return 'ok'
        
    def do_reset(self):
        """ Send reset request """
        self.gcs.reset()
        return 'ok'

    def set_z_modify(self, value):
        self.gcs.z_modify(float(value))
        return 'ok'
        
    def set_speed(self, value):
        return self.gcs.send('M220 S{0}\r\n'.format(value))
        
    def set_fan(self, value):
        return self.gcs.send('M106 S{0}\r\n'.format(value))
        
    def set_flow_rate(self, value):
        return self.gcs.send('M221 S{0}\r\n'.format(value))
        
    def set_auto_shutdown(self, value): # !shutdown:<on|off>
        self.gcs.push('config:shutdown', value)
        return 'ok'


# Setup arguments
parser = argparse.ArgumentParser()
parser.add_argument("-L", "--log", help="Use logfile to store log messages.",   default='<stdout>')
parser.add_argument("-p", "--pidfile", help="File to store process pid.",       default=os.path.join(RUN_PATH, 'xmlrpcserver.pid') )

# Get arguments
args = parser.parse_args()
pidfile = args.pidfile

with open(pidfile, 'w') as f:
    f.write( str(os.getpid()) )

# Setup logger
logger = logging.getLogger('XML-RPC')
logger.setLevel(logging.DEBUG)

ch = logging.StreamHandler()

formatter = logging.Formatter("%(levelname)s : %(message)s")
ch.setFormatter(formatter)
ch.setLevel(logging.DEBUG)
logger.addHandler(ch)

time.sleep(2)

gcs = GCodeServiceClient()

config = ConfigService()

SOCKET_HOST         = config.get('xmlrpc', 'xmlrpc_host')
SOCKET_PORT         = config.get('xmlrpc', 'xmlrpc_port')

log_trace           = config.get('general', 'trace')

rpc = ServerContainer(SOCKET_HOST, int(SOCKET_PORT), ExposeCommands(gcs, config, log_trace), logger)
rpc.start()

# Ensure CTRL+C detection to gracefully stop the server.
signal.signal(signal.SIGINT, signal_handler)

rpc.loop()


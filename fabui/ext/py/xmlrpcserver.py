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
from fabtotum.utils.translation import _, setLanguage
from fabtotum.os.paths     import RUN_PATH
from fabtotum.fabui.config import ConfigService
from fabtotum.utils.gcodefile import GCodeFile, GCodeInfo
from fabtotum.utils.pyro.gcodeclient import GCodeServiceClient
from fabtotum.database      import Database, timestamp2datetime, TableItem
from fabtotum.database.task import Task
from fabtotum.database.file import File
from fabtotum.database.object  import Object
from fabtotum.database.obj_file import ObjFile

from fabtotum.utils.gmacro import GMacroHandler

from fabtotum.utils.xmlrpc.servercontainer import ServerContainer

def signal_handler(signal, frame):
    print "You pressed Ctrl+C!"
    logger.debug("Shutting down services. Please wait...")
    rpc.stop()

class ExposeCommands:
    
    def __init__(self, gcs, config, log_trace, logger):
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
        
        self.log = logger
        
        self.gmacro = GMacroHandler(self.gcs, self.config, self.trace, self.__resetTrace)
    
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
    
    def send(self, code, block = True, timeout = None, async = False):
        """
        Send GCode and receive it's reply.
        """
        return self.gcs.send(code, block=block, timeout=timeout, async=async)
    
    def do_macro(self, preset, args = None, atomic = True, lang='en_US.UTF-8'):
        """
        Execute macro command.
        """
        self.gmacro.setLanguage(lang)
        
        self.log.debug("Macro START: " + preset)
        
        result = json.dumps( self.gmacro.run(preset, args, atomic) )
        
        self.log.debug("Macro END: " + preset)
        self.log.debug("Macro: " + result) 
        
        return result
        
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
    
    def set_rpm(self, value):
        return self.gcs.send('M3 S{0}\r\n'.format(value))
        
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

if args.log == '<stdout>':
    ch = logging.StreamHandler()
else:
    ch = logging.FileHandler(args.log)

formatter = logging.Formatter("%(levelname)s : %(message)s")
ch.setFormatter(formatter)
ch.setLevel(logging.DEBUG)
logger.addHandler(ch)

time.sleep(2)

gcs = GCodeServiceClient()

config = ConfigService()

SOCKET_HOST         = config.get('xmlrpc', 'xmlrpc_host')
SOCKET_PORT         = config.get('xmlrpc', 'xmlrpc_port')

log_trace = config.get('general', 'trace')

rpc = ServerContainer(SOCKET_HOST, int(SOCKET_PORT), ExposeCommands(gcs, config, log_trace, logger), logger)
rpc.start()

# Ensure CTRL+C detection to gracefully stop the server.
signal.signal(signal.SIGINT, signal_handler)

rpc.loop()


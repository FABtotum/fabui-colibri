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
import sys
import re
import json
import argparse
import time
import logging
import gettext
from threading import Event, Thread
try:
    import queue
except ImportError:
    import Queue as queue

# Import external modules

# Import internal modules
from fabtotum.fabui.config import ConfigService
#~ from fabtotum.utils.gcodefile import GCodeFile, GCodeInfo
from fabtotum.utils.pyro.gcodeclient import GCodeServiceClient

# Set up message catalog access
tr = gettext.translation('gpusher', 'locale', fallback=True)
_ = tr.ugettext

class Jog:
    
    def __init__(self, jog_response_file, gcs = None, config = None):
        if not config:
            self.config = ConfigService()
        else:
            self.config = config
        
        if not gcs:
            self.gcs = GCodeServiceClient()
        else:
            self.gcs = gcs
            
        self.jog_response_file = jog_response_file
        self.cq = queue.Queue()
        self.running = False
        
        self.send_thread = None
        
    def __send_thread(self):
        while self.running:
            
            cmd = self.cq.get()
            
            if cmd:
                token = [0]
                gcode = cmd[1]
                
                reply = self.gcs.send(gcode, group = 'jog:{0}'.format(token) )
                print "jog:", reply
    
    ### API ###
    
    def is_running(self):
        """ Returns `True` if the service is running """
        return self.running
    
    def start(self):
        """ Start the service. """
        self.send_thread = Thread( name = "Jog-send", target = self.__send_thread )
        
    def stop(self):
        """ Stop the service. """
        self.running = False
        # Used to wake up send_thread so it can detect the condition
        self.cq.put( None )
        
    def loop(self):
        """ Loop until the service is stopped. """
        if self.send_thread:
            self.send_thread.join()
    
    def send(self, token, gcode):
        """
        Send a gcode command and write the reply to jog_response_file.
        
        :param token: ID used to pair gcode command and reply
        :param gcode: Gcode to send
        :type token: string
        :type gcode: string
        """
        self.cq.put( [token, gcode] )

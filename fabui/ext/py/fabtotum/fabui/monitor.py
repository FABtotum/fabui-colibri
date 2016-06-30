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
import time
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

class StatsMonitor:
    def __init__(self, stats_file, gcs = None, backtrack = 20, period = 5.0):

        if not gcs:
            self.gcs = GCodeServiceClient()
        else:
            self.gcs = gcs
    
        self.stats_file = stats_file
        self.running = False
        
        self.monitor_thread = None
        self.ev_update = Event()
        self.backtrack = backtrack
        self.update_period = period
        
        ## Monitor variables, fill arrays with zeros
        self.ext_temp           = [0.0] * self.backtrack
        self.ext_temp_target    = [0.0] * self.backtrack
        self.bed_temp           = [0.0] * self.backtrack
        self.bed_temp_target    = [0.0] * self.backtrack
        self.fan                = [0.0] * self.backtrack
        self.flow_rate          = [0.0] * self.backtrack
        self.speed              = [0.0] * self.backtrack
        self.rpm                = [0.0] * self.backtrack
        
        # re
        self.re_temp = re.compile('ok\sT:(?P<T>[0-9]+\.[0-9]+)\s\/(?P<TT>[0-9]+\.[0-9]+)\sB:(?P<B>[0-9]+\.[0-9]+)\s\/(?P<BT>[0-9]+\.[0-9]+)\s')
    
    def __parse_temperature(self, line):
        match = self.re_temp.search(line)
        if match:
            return ( match.group('T'), match.group('TT'), match.group('B'), match.group('BT') )
    
    @staticmethod
    def __rotate_values(value_list, new_value):
        return value_list[1:] + [new_value]
    
    def __monitor_thread(self):
        print "StatsMonitor thread: started"
        while self.running:
            
            # Get temperature
            # Timeout is to prevent waiting for too long when there is a long running
            # command like M109,M190,G29 or G28
            reply = self.gcs.send('M105', timeout = 2) 
            if reply: # No timeout occured
                try:
                    a, b, c, d = self.__parse_temperature(reply[0])
                    self.ext_temp = self.__rotate_values(self.ext_temp, a)
                    self.ext_temp_target = self.__rotate_values(self.ext_temp_target, b)
                    self.bed_temp = self.__rotate_values(self.bed_temp, c)
                    self.bed_temp_target = self.__rotate_values(self.bed_temp_target, d)
                    
                    self.__write_stats()
                except Exception:
                    pass
                    
            # Used both as a delay and event trigger
            if self.ev_update.wait(self.update_period):
                # There was an update event, so write the new data
                self.__write_stats()    
                self.ev_update.clear()
            
        print "StatsMonitor thread: stopped"
    
    
    
    def __callback_handler(self, action, data):
        print "Monitor: callback_handler", action, data
        
        
        if action.startswith('temp_change'):
            self.__temp_change_callback(action.split(':')[1], data)
        elif action.startswith('gcode_action'):
            self.__gcode_action_callback(action.split(':')[1], data)
    
    def __write_stats(self):
        stats = {
            'ext_temp'          : self.ext_temp,
            'ext_temp_target'   : self.ext_temp_target,
            'bed_temp'          : self.bed_temp,
            'bed_temp_target'   : self.bed_temp_target,
            'fan'               : self.fan,
            'flow_rate'         : self.flow_rate,
            'speed'             : self.speed,
            'rpm'               : self.rpm
        }
        
        with open(self.stats_file, 'w') as f:
            f.write( json.dumps(stats) )
    
    ### API ###
    
    def set_backtrack_size(self, size):
        # TODO:
        if self.backtrack != size:
            # resize
            pass
        self.backtrack = size
    
    def start(self):
        self.running = True
        
        selg.gcs.register_callback(self.__callback_handler)
        
        self.monitor_thread = Thread( name = "Monitor", target = self.__monitor_thread )
        self.monitor_thread.start()
        
    def loop(self):
        if self.monitor_thread:
            self.monitor_thread.join()
        
    def stop(self):
        self.running = False
        self.ev_update.set()

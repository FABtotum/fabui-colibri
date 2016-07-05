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
import logging
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
    def __init__(self, stats_file, gcs = None, logger = None, backtrack = 20, period = 5.0):

        if not gcs:
            self.gcs = GCodeServiceClient()
        else:
            self.gcs = gcs
    
        if logger:
            self.log = logger
        else:
            self.log = logging.getLogger('StatsMonitor')
            ch = logging.StreamHandler()
            ch.setLevel(logging.DEBUG)
            formatter = logging.Formatter("%(levelname)s : %(message)s")
            ch.setFormatter(formatter)
            self.log.addHandler(ch)
    
        self.stats_file = stats_file
        self.running = False
        
        self.monitor_thread = None
        self.monitor_write_thread = None
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
    
    def __write_thread(self):
        self.log.debug("StatsMonitor write thread: started")
        while self.running:
            # Used both as a delay and event trigger
            if self.ev_update.wait(self.update_period):
                # There was an update event, so write the new data
                self.__write_stats()    
                self.ev_update.clear()
                
                self.__write_stats()
                
        self.log.debug("StatsMonitor write thread: stopped")
    
    def __monitor_thread(self):
        self.log.debug("StatsMonitor thread: started")
        while self.running:
            
            # Get temperature
            # Timeout is to prevent waiting for too long when there is a long running
            # command like M109,M190,G29 or G28
            #~ reply = self.gcs.send('M105', group = 'monitor', timeout = 2) 
            reply = self.gcs.send('M105', group = 'monitor')
            if reply: # No timeout occured
                try:
                    a, b, c, d = self.__parse_temperature(reply[0])
                    self.ext_temp = self.__rotate_values(self.ext_temp, a)
                    self.ext_temp_target = self.__rotate_values(self.ext_temp_target, b)
                    self.bed_temp = self.__rotate_values(self.bed_temp, c)
                    self.bed_temp_target = self.__rotate_values(self.bed_temp_target, d)
                    
                    #self.__trigger_callback('temp_change:all', [T,B])
                    self.gcs.push("temp_change:all", [a, b, c, d])
                    
                    self.ev_update.set()
                    #self.__write_stats()
                except Exception:
                    pass
                    
            time.sleep(self.update_period)
            #~ # Used both as a delay and event trigger
            #~ if self.ev_update.wait(self.update_period):
                #~ # There was an update event, so write the new data
                #~ self.__write_stats()    
                #~ self.ev_update.clear()
            
        self.log.debug("StatsMonitor thread: stopped")
    
    def __temp_change_callback(self, action, data):
        if action == 'ext_bed':
            self.log.debug("Ext: %f, Bed: %f", float(data[0]), float(data[1]) )
            self.ext_temp = self.__rotate_values(self.ext_temp, float(data[0]))
            self.bed_temp = self.__rotate_values(self.bed_temp, float(data[1]))
            self.ev_update.set()
        elif action == 'bed':
            self.log.debug("Bed: %f", float(data[0]) )
            self.bed_temp = self.__rotate_values(self.bed_temp, float(data[0]))
            self.ev_update.set()
        elif action == 'ext':
            self.log.debug("Ext: %f", float(data[0]) )
            self.ext_temp = self.__rotate_values(self.ext_temp, float(data[0]))
            self.ev_update.set()
        
    def __gcode_action_callback(self, action, data):
        if action == 'heating':

            if data[0] == 'M109':
                pass
                #self.trace( _("Wait for nozzle temperature to reach {0}&deg;C").format(data[1]) )
                #~ self.ext_temp_target = self.__rotate_values(self.ext_temp_target, float(data[1]))
                #~ self.ev_update.set()
            elif data[0] == 'M190':
                pass
                #self.trace( _("Wait for bed temperature to reach {0}&deg;C").format(data[1]) )
                #~ self.bed_temp_target = self.__rotate_values(self.bed_temp_target, float(data[1]))
                #~ self.ev_update.set()
            elif data[0] == 'M104':
                #self.trace( _("Nozzle temperature set to {0}&deg;C").format(data[1]) )
                self.ext_temp_target = self.__rotate_values(self.ext_temp_target, float(data[1]))
                self.ev_update.set()
            elif data[0] == 'M140':
                #self.trace( _("Bed temperature set to {0}&deg;C").format(data[1]) )
                self.bed_temp_target = self.__rotate_values(self.bed_temp_target, float(data[1]))
                self.ev_update.set()
            
        elif action == 'cooling':
            
            if data[0] == 'M106':
                value = int((float( data[1] ) / 255) * 100)
                self.fan = self.__rotate_values(self.fan, value)
                #self.trace( _("Fan value set to {0}%").format(value) )
                self.ev_update.set()
            elif data[0] == 'M107':
                #self.trace( _("Fan off") )
                self.fan = self.__rotate_values(self.fan, 0)
                self.ev_update.set()
                
        elif action == 'printing':
            
            if data[0] == 'M220': # Speed factor
                value = float( data[1] )
                self.speed = self.__rotate_values(self.speed, value)
                #self.trace( _("Speed factor set to {0}%").format(value) )
            elif data[0] == 'M221': # Extruder flow
                value = float( data[1] )
                self.flow_rate = self.__rotate_values(self.flow_rate, value)
                #self.trace( _("Extruder flow set to {0}%").format(value) )
                
        elif action == 'milling':
            if data[0] == 'M0':
                """ .. todo: do something with it """
                pass
            elif data[0] == 'M1':
                """ .. todo: do something with it """
                pass
            elif data[0] == 'M3':
                #self.trace( _("Milling motor RPM set to {0}%").format(value) )
                pass
            elif data[0] == 'M4':
                #self.trace( _("Milling motor RPM set to {0}%").format(value) )
                pass
            elif data[0] == 'M6':
                #""" .. todo: Check whether laser power should be scaled from 0-255 to 0.0-100.0 """
                pass
    
    def __callback_handler(self, action, data):
        #self.log.debug("Monitor: callback_handler %s %s", action, str(data))
        
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
        
        self.gcs.register_callback(self.__callback_handler)
        
        self.monitor_thread = Thread( name = "Monitor", target = self.__monitor_thread )
        self.monitor_thread.start()
        
        self.monitor_write_thread = Thread( name = "Monitor_write", target = self.__write_thread )
        self.monitor_write_thread.start()
        
    def loop(self):
        if self.monitor_thread:
            self.monitor_thread.join()
        
    def stop(self):
        self.running = False
        self.ev_update.set()

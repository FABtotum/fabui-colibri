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
import json
import ConfigParser

# Import external modules
from watchdog.observers import Observer
from watchdog.events import PatternMatchingEventHandler
from watchdog.events import FileSystemEventHandler

# Import internal modules
from fabtotum.os.paths        import CONFIG_INI, SERIAL_INI
from fabtotum.utils.singleton import Singleton

#####################################################

class ConfigService:
    __metaclass__ = Singleton
        
    def __init__(self):
        """ Load config files """
        self.config = ConfigParser.ConfigParser()
        self.config.read(CONFIG_INI)

        self.serialconfig = ConfigParser.ConfigParser()
        self.serialconfig.read(SERIAL_INI)
        
        self.HW_DEFAULT_SETTINGS = self.config.get('hardware', 'default_settings')
        self.HW_CUSTOM_SETTINGS  = self.config.get('hardware', 'custom_settings')
        
        json_f = open(self.HW_DEFAULT_SETTINGS)
        self.settings = json.load(json_f)
        
        if 'settings_type' in self.settings and self.settings['settings_type'] == 'custom':
            json_f = open(self.HW_CUSTOM_SETTINGS)
            self.settings = json.load(json_f)
            
        self.reload_callback = []
    
    def register_callback(self, handler):
        if handler not in self.reload_callback:
            self.reload_callback.append(handler)
        
    def unregister_callback(self, handler):
        self.reload_callback.remove(handler)
    
    def reload(self):
        """ Reload config files """
        
        self.config.read(CONFIG_INI)
        self.serialconfig.read(SERIAL_INI)

        self.HW_DEFAULT_SETTINGS = self.config.get('hardware', 'default_settings')
        self.HW_CUSTOM_SETTINGS  = self.config.get('hardware', 'custom_settings')
        
        json_f = open(self.HW_DEFAULT_SETTINGS)
        self.settings = json.load(json_f)
        
        if 'settings_type' in self.settings and self.settings['settings_type'] == 'custom':
            json_f = open(self.HW_CUSTOM_SETTINGS)
            self.settings = json.load(json_f)
        
        for cb in self.reload_callback:
            cb()
        
    def get(self, section, key, default = None):        
        value = ''
        
        try:
            if section == 'serial':
                value = self.serialconfig.get('serial', key)
            elif section == 'settings':
                value = self.settings[key]
            else:
                value = self.config.get(section, key)
        except KeyError:
            if default != None:
                return default
            else:
                raise KeyError
                
        return value

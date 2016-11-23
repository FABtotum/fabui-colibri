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
    
    def save(self, section):
        if section == 'settings':
            if 'settings_type' in self.settings and self.settings['settings_type'] == 'custom':
                with open(self.HW_CUSTOM_SETTINGS, 'w') as outfile:
                    json.dump(self.settings, outfile, sort_keys=True, indent=4)
            else:
                with open(self.HW_DEFAULT_SETTINGS, 'w') as outfile:
                    json.dump(self.settings, outfile, sort_keys=True, indent=4)                
            return True
            
        return False
    
    def __get_dict_value(self, data, key, default = None):
        try:
            kl = key.split('.')
            if len(kl) == 1 and kl[0]:
                return data[kl[0]]
                
            elif len(kl) == 2 and kl[0] and kl[1]:
                return data[kl[0]][kl[1]]
                
            elif len(kl) == 3 and kl[0] and kl[1] and kl[2]:
                return data[kl[0]][kl[1]][kl[2]]
                
            elif len(kl) == 4  and kl[0] and kl[1] and kl[2] and kl[3]:
                return data[kl[0]][kl[1]][kl[2]][kl[3]]
                
            else:
                return data
        except:
            return default
            
    def __set_dict_value(self, data, key, value):
        try:
            kl = key.split('.')
            if len(kl) == 1 and kl[0]:
                data[kl[0]] = value
                return True
                
            elif len(kl) == 2 and kl[0] and kl[1]:
                data[kl[0]][kl[1]] = value
                return True
                
            elif len(kl) == 3 and kl[0] and kl[1] and kl[2]:
                data[kl[0]][kl[1]][kl[2]] = value
                return True
                
            elif len(kl) == 4  and kl[0] and kl[1] and kl[2] and kl[3]:
                data[kl[0]][kl[1]][kl[2]][kl[3]] = value
                return True
                
            else:
                return False
        except:
            return False
            
    def get(self, section, key, default = None):        
        value = ''
        
        try:
            if section == 'serial':
                value = self.serialconfig.get('serial', key)
            elif section == 'settings':
                value = self.__get_dict_value(self.settings, key, default)
            else:
                value = self.config.get(section, key)
        except Exception:
            if default != None:
                return default
            else:
                raise KeyError
                
        return value

    def set(self, section, key, value):
        """ Set settings value """
        
        if section == 'settings':
            return self.__set_dict_value(self.settings, key, value)
            
        return False

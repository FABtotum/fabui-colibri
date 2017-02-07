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

__authors__ = "Krios Mane, Daniel Kesler"
__license__ = "GPL - https://opensource.org/licenses/GPL-3.0"
__version__ = "1.0"

# Import standard python module
import json
import gettext
import ConfigParser

# Import external modules
from watchdog.events import PatternMatchingEventHandler
from watchdog.events import FileSystemEventHandler

# Import internal modules
from fabtotum.fabui.config import ConfigService
from fabtotum.os.paths        import CONFIG_INI, SERIAL_INI

# Set up message catalog access
tr = gettext.translation('config_monitor', 'locale', fallback=True)
_ = tr.ugettext

###################################################################################################################
## Event Listener for the most used files
###################################################################################################################
class ConfigMonitor(PatternMatchingEventHandler):
    
    patterns = []
    ignore_directories = None
    ignore_patterns = None 
    case_sensitive = None
    ws = None #web socket, used to notify UI (future use)

    def __init__(self, gcs, config, logger):
        
        self.gcs = gcs
        self.log = logger
        self.config = config
        self.HW_DEFAULT_SETTINGS = self.config.get('hardware', 'settings')
        #self.HW_CUSTOM_SETTINGS  = self.config.get('hardware', 'custom_settings')
        
        self.patterns = [CONFIG_INI, SERIAL_INI, self.HW_DEFAULT_SETTINGS]
        self.ignore_directories = None
        self._ignore_patterns = None
        self.case_sensitive = None
        
    def on_modified(self, event):
        """
        Watchdog callback triggered when file is modified.
        """
                
        if (  event.src_path == CONFIG_INI 
           or event.src_path == SERIAL_INI
           or event.src_path == self.HW_DEFAULT_SETTINGS
           ):

            self.HW_DEFAULT_SETTINGS = self.config.get('hardware', 'settings')
            #self.HW_CUSTOM_SETTINGS  = self.config.get('hardware', 'custom_settings')
            
            self.patterns = [CONFIG_INI, SERIAL_INI, self.HW_DEFAULT_SETTINGS]
            
            self.log.debug('Reloading config files')
            self.config.reload()
            #~ self.gcs.push('config:reload', event.src_path)




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

# Import external modules
from watchdog.events import PatternMatchingEventHandler
from watchdog.events import FileSystemEventHandler

# Import internal modules
from fabtotum.fabui.config import ConfigService

# Set up message catalog access
tr = gettext.translation('usbdrive_monitor', 'locale', fallback=True)
_ = tr.ugettext

################################################################################################################### 
## Notify UI when an USB DISK is inserted or removed
###################################################################################################################   
class UsbMonitor(FileSystemEventHandler):
    
    patterns = []
    ws = None
    USB_FILE = None
     
    def __init__(self, notifyservice, logger, usb_file):
        self.ns = notifyservice
        self.USB_FILE = usb_file
        self.Empty = None
        self.log = logger
        
    def on_created(self, event):
        if(event.src_path == self.USB_FILE):
            self.log.debug("USB_FILE: created %s", event.src_path)
            self.ns.notify('usb', {'status':'inserted', 'alert':True, 'device':event.src_path})
    
    def on_deleted(self, event):
        self.log.debug("USB_FILE: deleted %s", event.src_path)
        if(event.src_path == self.USB_FILE):
            self.ns.notify('usb', {'status':'removed', 'alert':True, 'device':event.src_path})

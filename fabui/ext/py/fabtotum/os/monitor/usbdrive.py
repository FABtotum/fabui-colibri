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
     
    def __init__(self, WebSocket, gcs, usb_file ):
        self.ws = WebSocket
        self.USB_FILE = usb_file
        self.Empty = None
        
    def on_created(self, event):
        if(event.src_path == self.USB_FILE):
            print "USB_FILE: created", event.src_path
            self.sendMessage(True)
    
    def on_deleted(self, event):
        print "USB_FILE: deleted", event.src_path
        if(event.src_path == self.USB_FILE):
            self.sendMessage(False)
    
    def sendMessage(self, status):
        message={'type':'usb', 'data':{'status': status, 'alert':True}}
        self.ws.send(json.dumps(message))

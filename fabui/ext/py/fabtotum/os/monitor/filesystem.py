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
import time
import gettext

# Import external modules
from watchdog.events import PatternMatchingEventHandler
from watchdog.events import FileSystemEventHandler

# Import internal modules
from fabtotum.fabui.config import ConfigService
#~ from fabtotum.fabui.command_parser import CommandParser

# Set up message catalog access
tr = gettext.translation('filesystem_monitor', 'locale', fallback=True)
_ = tr.ugettext

###################################################################################################################
## Event Listener for the most used files
###################################################################################################################
class FolderTempMonitor(PatternMatchingEventHandler):
    
    patterns = []
    ignore_directories = None
    ignore_patterns = None 
    case_sensitive = None
    ws = None #web socket, used to notify UI
    TRACE = None
    MACRO_RESPONSE = None
    TASK_MONITOR = None
    COMMAND = None
    
    def __init__(self, notifyservice, gcs, logger, trace_file, monitor_file):
        
        self.TRACE = trace_file
        self.TASK_MONITOR = monitor_file
        self.gcs = gcs
        
        self.log = logger
        
        self.patterns = [self.TRACE, self.TASK_MONITOR]
        self.ignore_directories = None
        self._ignore_patterns = None
        self.case_sensitive = None
        self.ns = notifyservice
        
    def on_modified(self, event):
        """
        Watchdog callback triggered when file is modified.
        """
        
        messageType = ''
        messageData = ''
        
        #print "Monitor:", event.src_path
                
        if event.src_path == self.TRACE:
            messageData = {'content': str(self.getFileContent(self.TRACE))}
            messageType = "trace"
            self.ns.notify(messageType, messageData)
        
        elif event.src_path == self.TASK_MONITOR:
            tmp = str(self.getFileContent(self.TASK_MONITOR))
            if tmp:
                messageData = {'type': 'monitor', 'content': json.loads(tmp)}
                messageType = 'task'
                self.ns.notify(messageType, messageData)
        
    def on_created(self, event):
        #self.process(event)
        self.log.debug("CRAETED: " + event.src_path)
        #self.ws.send("CRAETED")
    
    def on_deleted(self, event):
        #self.process(event)
        self.log.debug("DELETED: " + event.src_path)
        #self.ws.send("DELETED")
                
    def getFileContent(self, file_path):
        file = open(file_path, 'r')
        content= file.read()
        file.close()
        return content



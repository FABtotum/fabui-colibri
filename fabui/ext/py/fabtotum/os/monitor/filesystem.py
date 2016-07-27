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
from fabtotum.fabui.command_parser import CommandParser

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
    
    def __init__(self, notifyservice, gcs, logger, trace_file, monitor_file, response_file, jog_response_file, command_file):
        
        self.TRACE = trace_file
        self.COMMAND = command_file
        self.TASK_MONITOR = monitor_file
        self.MACRO_RESPONSE = response_file
        self.JOG_RESPONSE = jog_response_file
        self.gcs = gcs
        
        self.log = logger
        
        # Erase the file(s)
        open(command_file, 'w').close()
        
        self.parser = CommandParser(gcs, jog_response_file, logger = logger)
        
        self.patterns = [self.TRACE, self.COMMAND, self.TASK_MONITOR, self.MACRO_RESPONSE, self.JOG_RESPONSE]
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
            messageData = {'type': 'trace', 'content': str(self.getFileContent(self.TRACE))}
            messageType = "macro"
            #~ self.sendMessage(messageType, messageData)
            self.ns.notify(messageType, messageData)
            
        elif event.src_path == self.COMMAND:
            self.parser.parse_file(self.COMMAND)
        
        elif event.src_path == self.TASK_MONITOR:
            messageData = {'type': 'monitor', 'content': json.loads(str(self.getFileContent(self.TASK_MONITOR)))}
            messageType = 'task'
            #self.sendMessage(messageType, messageData)
            self.ns.notify(messageType, messageData)
            
        elif event.src_path == self.JOG_RESPONSE:
            #time.sleep(0.5)
            
            retry = 5
            
            while retry:
                tmp = str(self.getFileContent(self.JOG_RESPONSE))
                if tmp:
                    messageData = {'content': json.loads(tmp)}
                    messageType = 'jog'
                    #self.sendMessage(messageType, messageData)
                    self.ns.notify(messageType, messageData)
                    break
                else:
                    print "---------------- EMPTY FILE -----------------", retry
                    retry -= 1
   
        
    def on_created(self, event):
        #self.process(event)
        self.log.debug("CRAETED: " + event.src_path)
        #self.ws.send("CRAETED")
    
    def on_deleted(self, event):
        #self.process(event)
        self.log.debug("DELETED: " + event.src_path)
        #self.ws.send("CRAETED")
                
    def getFileContent(self, file_path):
        file = open(file_path, 'r')
        content= file.read()
        file.close()
        return content



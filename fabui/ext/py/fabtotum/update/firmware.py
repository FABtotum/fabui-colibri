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

from fabtotum.update.file  import File

class Firmware:
    
    def __init__(self, data):
        self.name          = 'firmware'
        self.status        = ''
        self.message       = '' 
        self.date_uploaded = data['date-uploaded']
        self.version       = data['version']
        self.md5File       = File(data['files']['md5sum'])
        self.hex           = File(data['files']['firmware'])
        self.gcodes        = File(data['files']['gcodes'])
        
        print "init: ", self.name
        
    def getName(self):
        return self.name
    
    def getLatest(self):
        return self.latest
    
    def getVersion(self):
        return self.version
    
    def getPriority(self):
        return self.priority
    
    def getMd5File(self):
        return self.md5File
    
    def getHexFile(self):
        return self.hex
    
    def getStatus(self):
        return self.status
    
    def setStatus(self, status):
        self.status = status
        
    def getBundleFile(self):
        return self.bundleFile
    
    def getFile(self, type):
        if(type == 'bundle'):
            return self.getBundleFile()
        else:
            return self.getMd5File()
    
    def updateFile(self, file, type):
        if(type == 'bundle'):
            self.bundleFile = file
        else:
            self.md5File = file
            
    def setMessage(self, message):
        self.message = message
    
    def getMessage(self):
        return self.message
        
    def serialize(self):
        data = {
            'status'   : self.getStatus(),
            'message'  : self.getMessage(),
            'version'  : self.getVersion(),
            'files'    : {
                'hex' : self.getHexFile().serialize(),
                'md5' : self.getMd5File().serialize()
            }
        }
        return data
        
        
class File:
    def __init__(self, file_url):
        self.name = self.setFileName(file_url)
        self.endpoint = file_url
        self.size = 0
        self.progress = 0
        self.status = ''
        
    def setFileName(self, url):
        filename = url.split('/')
        return filename[-1]
    
    def getName(self):
        return self.name
    
    def setProgress(self, progress):
        self.progress = progress
        
    def getEndpoint(self):
        return self.endpoint
    
    def setSize(self, size):
        self.size = size
    
    def setStatus(self, status):
        self.status = status
    
    def getStatus(self):
        return self.status
    
    def serialize(self):
        data = {
            'name' : self.name,
            'endpoint' : self.endpoint,
            'size' : self.size,
            'progress' : self.progress,
            'status' : self.status
        }
        return data
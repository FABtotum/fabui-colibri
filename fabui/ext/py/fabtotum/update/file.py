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

import os

class File:
    def __init__(self, file_url, filename='', use_endpoint=True):
        self.name = os.path.basename(file_url)
        self.endpoint = file_url
        self.use_endpoint = use_endpoint
        if filename:
            self.name = filename
        self.size = 0
        self.progress = 0
        self.status = ''
        self.local = ''
    
    def getLocal(self):
        return self.local
        
    def setLocal(self, local):
        self.local = local
    
    def getName(self):
        return self.name
    
    def setProgress(self, progress):
        self.progress = progress
    
    def getUseDirectURL(self):
        return not self.use_endpoint
    
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

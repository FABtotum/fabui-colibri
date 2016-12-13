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


class Factory():
    def __init__(self):
        self.task_id = 0
        self.pid = 0
        self.type = 'update'
        self.controller = 'update'
        self.status = ''
        self.bundles = {}
        self.current = Current()
        self.stop = False
    
    def setTaskId(self, id):
        self.task_id = id
    
    def setPid(self, pid):
        self.pid = pid
    
    def setStatus(self, status):
        self.status = status
    
    def setCurrentBundle(self, currentBundle):
        self.current.setBundle(currentBundle)
        
    def getCurrentBundle(self):
        return self.current.getBundle()
    
    def setCurrentFileType(self, currentFileType):
        self.current.setFileType(currentFileType)
    
    def getCurrentFileType(self):
        return self.current.getFileType()
    
    def setCurrentStatus(self, status):
        self.current.setStatus(status)
    
    def addBundle(self, bundle):
        self.bundles[bundle.getName()] = bundle
        
    def getBundle(self, bundle_name):
        return self.bundles[bundle_name]
        
    def serialize(self):
        task_data = {
            'id' : self.task_id,
            'pid' : self.pid,
            'status' : self.status,
            'type' : self.type,
            'controller' : self.controller
        }
        return {'task' : task_data, 'update' : self.serializeBundles()}
    
    def serializeBundles(self):
        data = {}
        for bundle_name in self.bundles:
            bundle = self.getBundle(bundle_name)
            data[bundle_name] = bundle.serialize()
        return {
            'bundles': data,
            'current': self.current.serialize()
        }
    
    def updateBundle(self, bundle):
        self.bundles[bundle.getName()] = bundle
        
    def getStop(self):
        return self.stop
    
    def do_stop(self):
        self.stop = True
        


class Current:
    def __init__(self):
        self.bundle = ''
        self.file_type = ''
        self.status = ''
        
    def setBundle(self, bundle):
        self.bundle = bundle
    
    def setFileType(self, file_type):
        self.file_type = file_type
    
    def getBundle(self):
        return self.bundle
    
    def getFileType(self):
        return self.file_type
    
    def setStatus(self, status):
        self.status = status
    
    def getStatus(self):
        return self.status
    
    def serialize(self):
        return {
            'bundle' : self.bundle,
            'file_type' : self.file_type,
            'status' : self.status
        }

            
        
        
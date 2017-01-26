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
import time

from fabtotum.database      import Database, timestamp2datetime, TableItem
from fabtotum.database.task import Task

class UpdateFactory():
    def __init__(self, config):
        self.task_id = 0
        self.pid = 0
        self.type = 'update'
        self.controller = 'update'
        self.status = ''
        self.error = False
        self.message = ''
        self.bundles = {}
        self.firmware = None
        self.current = Current()
        self.stop = False  
        self.updated_count = 0
        self.db = Database(config)
    
    def setTaskId(self, id):
        self.task_id = id
    
    def setPid(self, pid):
        self.pid = pid
    
    def setStatus(self, status):
        self.status = status
        
    def getStatus(self):
        return self.status
    
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
        
    def addFirmware(self, firmware):
        self.firmware = firmware 
        
    def getBundle(self, bundle_name):
        return self.bundles[bundle_name]
    
    def getFirmware(self):
        return self.firmware
    
    def incraeseUpdatedCount(self):
        self.updated_count += 1
    
    def getUpdatedCount(self):
        return self.updated_count
    
    def setMessage(self, message):
        self.message = message
        
    def getMessage(self):
        return self.message
    
    def setError(self, bool):
        self.error = bool
    
    def getError(self):
        return self.error
        
    def serialize(self):
        
        task_data = {
            'id' : self.task_id,
            'pid' : self.pid,
            'status' : self.status,
            'type' : self.type,
            'controller' : self.controller,
            'message' : self.message,
            'error' : self.error
        }
        return {'task' : task_data, 'update' : self.serializeBundles()}
    
    def serializeBundles(self):
        data = {}
        
        for bundle_name in self.bundles:
            bundle = self.getBundle(bundle_name)
            data[bundle_name] = bundle.serialize()
        return {
            'bundles': data,
            'firmware' : self.serializeFirmware(),
            'current': self.current.serialize(),
            'to_update' : len(data),
            'updated' :  self.getUpdatedCount()
        }
    
    def serializeFirmware(self):
        if(self.firmware != None):
            return self.firmware.serialize()
        else:
            return {}
    
    def updateBundle(self, bundle):
        self.bundles[bundle.getName()] = bundle
        
    def getStop(self):
        return self.stop
    
    def do_stop(self):
        self.stop = True
    
    def update_task_db(self):
        if self.task_id == 0:
            return
        task_db = Task(self.db, self.task_id)
        
        if(self.getStatus() == 'running'):
            task_db['status'] = 'running'
        elif(self.getStatus() == 'completed' or self.getStatus() == 'aborted' ):
            task_db['status'] = self.getStatus()
            task_db['finish_date'] = timestamp2datetime( time.time() )
        tid = task_db.write()
        


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

            
        
        
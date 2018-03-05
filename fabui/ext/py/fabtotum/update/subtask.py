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


# Import standard python module
import os, sys

# Import external modules
import pycurl

# Import internal modules
from fabtotum.update.file  import File

class SubTask(object):
    
    def __init__(self, name, _type, factory = None):
        self.name          = name
        self.type          = _type
        self.status        = ''
        self.message       = ''
        self.files         = {}
        self.version       = ''
        self.factory       = factory
        self.downloaded    = False

        self.main_file     = ''
        self.current       = ''
        self.is_installable = False
    
    def setMainFile(self, tag):
        self.main_file = tag
    
    def isDownloaded(self):
        return self.downloaded
    
    def isInstallable(self):
        return self.is_installable
    
    def setInstallable(self, bool):
        self.is_installable = bool
    
    def getFactory(self):
        return self.factory
        
    def setFactory(self, factory):
        self.factory = factory
    
    def setCurrent(self, tag):
        self.current = tag
        
    def getCurrent(self):
        return self.current
    
    def getName(self):
        return self.name
    
    def getType(self):
        return self.type
    
    def getVersion(self):
        return self.version
    
    def getPriority(self):
        return self.priority
    
    def getStatus(self):
        return self.status
    
    def setStatus(self, status):
        self.status = status
        self.factory.update()
            
    def setMessage(self, message):
        self.message = message
        self.factory.update()
    
    def getMessage(self):
        return self.message
        
    def addFile(self, tag, file_url, filename='', use_endpoint=True):
        self.files[tag] = File(file_url, filename, use_endpoint)
        
    def getFile(self, tag):
        return self.files[tag]
    
    def getFilesSize(self):
        size = 0
        for tag in self.files:
            size += self.getFile(tag).getSize()
        return size

    def install(self):
        raise NotImplementedError('"install" function must be implemented !!!')

    def download(self):
        self.setStatus('downloading')
        self.downloaded = False
        
        for tag in self.files:
            if(self.download_file(tag) == False):
                self.downloaded = False
                return False
            
        self.downloaded = True
        self.setStatus('downloaded')
        return True
        
    def download_file(self, tag):
        
        self.setCurrent(tag)
        self.factory.update()
        
        file = self.getFile(tag)
        file_endpoint = file.getEndpoint()
        file_name = file.getName()
        
        """
        if file.getUseDirectURL():
            url = file_endpoint
        else:
            url = os.path.join(self.factory.getEndpoint(self.getType()), file_endpoint)
        
        """
        url = file_endpoint
        
        curl = pycurl.Curl()
        curl.setopt(pycurl.URL, url )
        curl.setopt(pycurl.FOLLOWLOCATION, 1)
        curl.setopt(pycurl.MAXREDIRS, 5)
        
        curl.setopt(pycurl.SSL_VERIFYPEER, 0)
        curl.setopt(pycurl.SSL_VERIFYHOST, 0)
        
        fn = os.path.join(self.factory.getTempFolder(), 'fabui', file_name)
        file.setLocal(fn)
        
        try:
            file_to_write = open( fn , "wb")
        except Exception as e:
            self.setStatus('error')
            self.setMessage(str(e))
            return False
        
        curl.setopt(pycurl.WRITEDATA, file_to_write)
        curl.setopt(pycurl.NOPROGRESS, 0)
        curl.setopt(pycurl.PROGRESSFUNCTION, self.download_progress)
        
        try:
            curl.perform()
        except pycurl.error, error:
            errno, errstr = error
            self.setStatus('error')
            self.setMessage(errstr)
            return False
        
        file.setStatus('downloaded')
        self.setCurrent("")
        self.factory.update()
        return True

    def download_progress(self, file_size, downloaded, upload_t, upload_d):
        
        try:
            tag = self.getCurrent()
            file = self.getFile(tag)
            
            if file_size:
                progress = ( downloaded / file_size ) * 100
            else:
                progress = 0
            
            file.setSize(file_size)
            file.setProgress(progress)
            file.setStatus('downloading')
        
            self.factory.update()
        
        except Exception as e:
            print "download_progress", e

        
    def serialize(self):
        data = {
            'name'     : self.getName(),
            'type'     : self.getType(),
            'status'   : self.getStatus(),
            'message'  : self.getMessage(),
            'version'  : self.getVersion(),
            'files'    : {}
        }
        
        for ftag in self.files:
            data['files'][ftag] = self.files[ftag].serialize()
        
        if self.main_file:
            data['files']['main_file'] = data['files'][self.main_file]
        else:
            data['files']['main_file'] = "none"
        
        return data


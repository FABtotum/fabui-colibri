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

from StringIO import StringIO as BytesIO
import pycurl, json
from fabtotum.fabui.config  import ConfigService

config = ConfigService()

class RemoteVersion:
    
    def __init__(self):
        self.colibri_endpoint = config.get('updates', 'colibri_endpoint')
        self.firmware_endpoint = config.get('updates', 'firmware_endpoint')
        self.colibri = None
        self.firmware = None
        self.setColibri()
        self.setFirmware()
    
    def getRemoteData(self, endpoint):
        curl = pycurl.Curl()
        buffer = BytesIO()
        curl.setopt(pycurl.URL, endpoint)
        curl.setopt(pycurl.TIMEOUT, 10)
        curl.setopt(pycurl.FOLLOWLOCATION, 1)
        curl.setopt(pycurl.MAXREDIRS, 5)
        curl.setopt(curl.WRITEDATA, buffer)
        curl.perform()
        
        return buffer.getvalue()
        
    def setColibri(self):
        self.colibri = json.loads(self.getRemoteData(self.colibri_endpoint + 'armhf/version.json'))
        
    def setFirmware(self):
        self.firmware = json.loads(self.getRemoteData(self.firmware_endpoint + 'fablin/atmega1280/version.json'))
    
    def getColibri(self):
        return self.colibri
    
    def getBundles(self):
        return self.colibri['bundles']
    
    def getFirmware(self):
        return self.firmware
    
    def getColibriEndpoint(self):
        return self.colibri_endpoint
        
        
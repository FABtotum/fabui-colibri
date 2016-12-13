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


class RemoteVersion:
    
    def __init__(self, endpoint):
        self.endpoint = endpoint
        self.data = None
        self.setData()
        
    def setData(self):
        curl = pycurl.Curl()
        buffer = BytesIO()
        curl.setopt(pycurl.URL, self.endpoint + 'armhf/version.json' )
        curl.setopt(pycurl.FOLLOWLOCATION, 1)
        curl.setopt(pycurl.MAXREDIRS, 5)
        curl.setopt(curl.WRITEDATA, buffer)
        curl.perform()
        self.data = json.loads(buffer.getvalue())
    
    def getData(self, key = ''):
        if(key == ''):
            return self.data
        else:
            return self.data[key]
        
        
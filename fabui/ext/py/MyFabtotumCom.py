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

__author__ = "Krios Mane"
__license__ = "GPL - https://opensource.org/licenses/GPL-3.0"
__version__ = "1.0"

# Import standard python module
import json
import re
import gettext
import time
import requests

from fabtotum.utils.pyro.gcodeclient import GCodeServiceClient
from fabtotum.fabui.config import ConfigService
from fabtotum.os.paths     import RUN_PATH
import logging
import argparse
import os, sys

# Set up message catalog access
tr = gettext.translation('my_fabtotum_com', 'locale', fallback=True)
_ = tr.ugettext

class MyFabtotumCom:
    
    def __init__(self, gcs):
        config = ConfigService()
        
        self.url = config.get('my.fabtotum.com', 'myfabtotum_url')
        self.api_version = config.get('my.fabtotum.com', 'myfabtotum_api_version')
        self.running = False
        self.polling_interval = 5
        self.gcs = gcs
        self.id_counter = 0
        
    
    def call(self, method, params):
        
        self.id_counter += 1
        
        headers = {'content-type': 'application/json'}
        payload = {
            "method" : method,
            "params" : params,
            "jsonrpc": "2.0",
            "id": self.id_counter,
        }
        
        response = requests.post( self.url, data=json.dumps(payload), headers=headers).json()
        return response['result']
    
    
    def identify(self):
        """ identify printer"""
        self.gcs.send("M728", group='gcode')
      
    def fab_polling(self):
        
        ''' polling '''
        params = {
            "serialno": "aaaaa-aaa-aaaaa",
            "mac": "b8:27:eb:c0:30:d8",
            "state": "",
            "apiversion": self.api_version,
        }
        result =  self.call('fab_polling', params)
        print result
        
        if result["status_code"] == 200:
            
            if "command" in result:
                try:
                    getattr(self, result["command"].lower())()
                except AttributeError:
                    print "Command not valid"
                except:
                    print "Unexpected error:", sys.exc_info()[0]
        
    def start(self):
        """ start server """
        self.running = True
    
    def stop(self):
        self.running = False
    
    def join(self):
        """ Place holder """
        pass
        
    def loop(self):
        """ run """
        while self.running:
            self.fab_polling()
            time.sleep(self.polling_interval)
        

def main():
    
    gcs = GCodeServiceClient()
    myFabototumCom = MyFabtotumCom(gcs)
    
    myFabototumCom.start()
    myFabototumCom.loop()

if __name__ == "__main__":
    main()

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

from fabtotum.database import Database
from fabtotum.database.sysconfig import SysConfig

# Set up message catalog access
tr = gettext.translation('my_fabtotum_com', 'locale', fallback=True)
_ = tr.ugettext

class MyFabtotumCom:
    
    
    SERVICE_SUCCESS            = 200
    SERVICE_UNAUTHORIZED       = 401
    SERVICE_FORBIDDEN          = 403
    SERVICE_SERVER_ERROR       = 500
    SERVICE_INVALID_PARAMETER  = 1001
    SERVICE_ALREADY_REGISTERED = 1002
    SERVICE_PRINTER_UNKNOWN    = 1003
    
    RESPONSE_CODES_DESCRIPTION = {
        SERVICE_SUCCESS            : 'SERVICE_SUCCESS',
        SERVICE_UNAUTHORIZED       : 'SERVICE_UNAUTHORIZED',
        SERVICE_FORBIDDEN          : 'SERVICE_FORBIDDEN',
        SERVICE_SERVER_ERROR       : 'SERVICE_SERVER_ERROR',
        SERVICE_INVALID_PARAMETER  : 'SERVICE_INVALID_PARAMETER',
        SERVICE_ALREADY_REGISTERED : 'SERVICE_ALREADY_REGISTERED',
        SERVICE_PRINTER_UNKNOWN    : 'SERVICE_PRINTER_UNKNOWN'
    }
    
    
    def __init__(self, gcs, config, logger):
        
        self.config = config
        self.gcs    = gcs
        self.db     = Database(self.config)
        self.log    = logger
        
        self.url              = self.config.get('my.fabtotum.com', 'myfabtotum_url')
        self.api_version      = self.config.get('my.fabtotum.com', 'myfabtotum_api_version')
        self.running          = False
        self.jsonrpc_version  = "2.0"
        self.request_timeout  = 5
        self.polling_interval = 5
        self.id_counter       = 0
        self.mac_address      = self.getMACAddres()
        self.serial_number    = self.getSerialNumber()
        
    
    def call(self, method, params):
        
        try:
            self.id_counter += 1
            
            headers = {
                'content-type' : 'application/json',
                'user-agent'   : 'fabtotum'
            }
            
            payload = {
                "method"  : method,
                "params"  : params,
                "jsonrpc" : self.jsonrpc_version,
                "id"      : self.id_counter,
            }
            response = requests.post( self.url, data=json.dumps(payload), headers=headers, timeout=self.request_timeout).json()
            
            return response['result']
        
        except requests.exceptions.RequestException as e:
            self.log.debug(e)
            return False
    
    def getMACAddres(self):
        """ get mac address """
        network_json_info = open(self.config.get('general', 'network_info_file'))
        network_info = json.load(network_json_info)
        return network_info["interfaces"]["eth0"]["mac_address"]
        
    
    def getSerialNumber(self):
        """ get printer serial number """
        sysconfig = SysConfig(self.db)
        sysconfig.query_by('key', 'serial_number')
        return sysconfig['text']
    
    def identify(self):
        """ identify printer"""
        self.log.info("identify printer")
        self.gcs.send("M728", group='gcode')
        
    def reboot(self):
        """ reboot unit """
        os.system('reboot')
        
    def lock(self):
        """ lock printer """
        self.log.info("Lock printer")
        
    def unlock(self):
        """ unlock printer """
        self.log.info("Unlock printer")
    
    def switch_off(self):
        """ switch off """
        os.system("poweroff")
    
    def end_job(self):
        """ end active task """
        self.log.info("abort task")
        self.gcs.abort()
      
    def fab_polling(self):
        
        ''' polling '''
        params = {
            "serialno"   : self.serial_number,
            "mac"        : self.mac_address,
            "state"      : "",
            "apiversion" : self.api_version,
        }
        result =  self.call('fab_polling', params)
        
        if result:
            if result["status_code"] == self.SERVICE_SUCCESS:     
                if "command" in result:
                    try:
                        getattr(self, result["command"].lower())()
                    except AttributeError:
                        self.log.debug("{0} : command not valid".format(result["command"]))
                    except:
                        self.log.debug("Unexpected error:", sys.exc_info()[0])
                        
                if "pollinterval" in result:
                    self.polling_interval = result['pollinterval']
            else:
                self.log.debug(self.RESPONSE_CODES_DESCRIPTION[result["status_code"]])
        
    def start(self):
        """ start server """
        self.log.info("Service started")
        self.running = True
    
    def stop(self):
        self.running = False
    
    def reload(self):
        """ reload settings """
        self.mac_address   = self.getMACAddres()
        self.serial_number = self.getSerialNumber()
    
    def join(self):
        """ Place holder """
        pass
        
    def loop(self):
        """ run """
        while self.running:
            self.fab_polling()
            time.sleep(self.polling_interval)
        

def main():
    
    # Setup arguments
    parser = argparse.ArgumentParser()
    parser.add_argument("-L", "--log",     help="Use logfile to store log messages.", default='/var/log/fabui/myfabtotumcom.log')
    parser.add_argument("-p", "--pidfile", help="File to store process pid.",         default=os.path.join(RUN_PATH, 'myfabtotumcom.pid') )
    
    args = parser.parse_args()
    pidfile = args.pidfile
    log = args.log
    
    with open(pidfile, 'w') as f:
        f.write( str(os.getpid()) )
    
    config = ConfigService()
    
    #load configurations
    LOG_LEVEL = config.get('general', 'log_level', 'INFO')
    
    #setup logger
    if LOG_LEVEL == "INFO":
        LOG_LEVEL = logging.INFO
    elif LOG_LEVEL == 'DEBUG':
        LOG_LEVEL = logging.DEBUG
    
    logger = logging.getLogger('MyFabtotumCom')
    logger.setLevel(LOG_LEVEL)
    fh = logging.FileHandler(log, mode='w')
    
    formatter = logging.Formatter("%(asctime)s - %(levelname)s : %(message)s")
    fh.setFormatter(formatter)
    fh.setLevel(LOG_LEVEL)
    logger.addHandler(fh)
    
    gcs = GCodeServiceClient()
    myFabototumCom = MyFabtotumCom(gcs, config, logger)
    
    myFabototumCom.start()
    myFabototumCom.loop()

if __name__ == "__main__":
    main()

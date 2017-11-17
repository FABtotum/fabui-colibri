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
import logging
import os, sys

from threading import Event, Thread

#import internal modules
from fabtotum.database import Database
from fabtotum.database.sysconfig import SysConfig
from fabtotum.database.user import User
from fabtotum.utils.common import shell_exec, fabtotum_model
from fabtotum.os.paths import TEMP_PATH, BASH_PATH
from fabtotum.fabui.config  import ConfigService
from fabtotum.fabui.constants import SERVICE_SUCCESS, SERVICE_UNAUTHORIZED, SERVICE_FORBIDDEN,\
        SERVICE_SERVER_ERROR, SERVICE_INVALID_PARAMETER, SERVICE_ALREADY_REGISTERED, SERVICE_PRINTER_UNKNOWN



# Set up message catalog access
tr = gettext.translation('my_fabtotum_com', 'locale', fallback=True)
_ = tr.ugettext

class MyFabtotumCom:
    
    RESPONSE_CODES_DESCRIPTION = {
        SERVICE_SUCCESS            : 'SERVICE_SUCCESS',
        SERVICE_UNAUTHORIZED       : 'SERVICE_UNAUTHORIZED',
        SERVICE_FORBIDDEN          : 'SERVICE_FORBIDDEN',
        SERVICE_SERVER_ERROR       : 'SERVICE_SERVER_ERROR',
        SERVICE_INVALID_PARAMETER  : 'SERVICE_INVALID_PARAMETER',
        SERVICE_ALREADY_REGISTERED : 'SERVICE_ALREADY_REGISTERED',
        SERVICE_PRINTER_UNKNOWN    : 'SERVICE_PRINTER_UNKNOWN'
    }
    
    STATUS = {
        0 : 'AVAILABLE',
        1 : 'BUSY',
        2 : 'BUSY',
        3 : 'PAUSED',
        4 : 'PAUSED',
        
    }
    
    def __init__(self, gcs, logger, config=None ):
            
        if config:
            self.config = config
        else:
            self.config = ConfigService()
        
        self.gcs    = gcs
        self.db     = Database(self.config)
        self.log    = logger
        
        self.url              = self.config.get('my.fabtotum.com', 'myfabtotum_url')
        self.api_version      = self.config.get('my.fabtotum.com', 'myfabtotum_api_version')
        self.thread_polling   = None
        self.thread_update    = None
        self.running          = False
        self.jsonrpc_version  = "2.0"
        self.request_timeout  = 5
        self.polling_interval = 5
        self.info_interval    = (60*30) #30 minutes
        self.id_counter       = 0
        self.leds_colors      = {}
        #self.mac_address      = None
        #self.serial_number    = None
        #self.fab_id           = None
        #self.unit_name        = None
        #self.batch_number     = None
        #self.fw_version       = None
        
        self.__init_vars()
    
    
    def __init_vars(self):
        """ init vars """
        self.mac_address   = self.getMACAddres()
        self.ip_lan        = self.getIPLan()
        self.serial_number = self.getSerialNumber()
        self.fab_id        = self.getFabID()
        self.unit_name     = self.getUnitName()
        #self.batch_number  = self.getBatchNumer()
        #self.fw_version    = self.getFwVersion()
        self.unit_color    = self.getUnitColor()
        self.leds_colors   = self.getLedsColors()
        
    
    def call(self, method, params):
        """ make jsonrpc call to my.fabtotum.com remote server """
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
            response = requests.post( self.url, data=json.dumps(payload), headers=headers, timeout=self.request_timeout, verify=False).json()
            
            if "result" in response:
                return response['result']
            elif "error" in response:
                self.log.debug("MyFabtotumCom - {0} : {1} : {2}".format(response['error']['message'], response['error']['code'], response['error']['data']))
                return False
            
        except requests.exceptions.RequestException as e:
            self.log.debug("MyFabtotumCom - {0}".format(e))
            self.log.debug("MyFabtotumCom - {0}".format(payload))
            return False
        
    
    def __getSystemConfig(self, value):
        """ get value from system config table """
        sysconfig = SysConfig(self.db)
        sysconfig.query_by('key', value)
        return sysconfig['text']
    
    
    def __getInterfaces(self):
        shell_exec('sh {0} > {1}'.format(os.path.join(BASH_PATH, 'get_net_interfaces.sh'), os.path.join(TEMP_PATH, 'interfaces.json')))
        interfaces = {}
        with open(os.path.join(TEMP_PATH, 'interfaces.json')) as data_file:    
            interfaces = json.load(data_file)
        return interfaces
        
    def getFabID(self):
        """ """
        user = User(self.db)
        user.query_by('role', 'administrator')
        settings = json.loads(user['settings'])
        if "fabid" in settings :
            return settings['fabid']['email']
        else:
            return False
    
    def getMACAddres(self):
        """ get printer mac address """
        interfaces = self.__getInterfaces()
        return interfaces["eth0"]["mac_address"]
        
    
    def getSerialNumber(self):
        """ get printer serial number """
        return self.__getSystemConfig('serial_number')
    
    def getUnitName(self):
        """ get unit name """
        return self.__getSystemConfig('unit_name')
    
    def getUnitColor(self):
        """ get unit color """
        return self.__getSystemConfig('unit_color')
    
    def getLedsColors(self):
        """ get ambient leds lights """
        try:
            return self.config.get('settings', 'color')
        except:
            return {'r': 255, 'g':255, 'b':255}
    
    def getBatchNumer(self):
        """ get batch number """
        try:
            reply = self.gcs.send("M763", group='gcode')
            return reply[0].strip()
        except:
            return 0
    
    def getModel(self):
        """ get fabtotum model """
        batch_number = self.getBatchNumer()
        model  = fabtotum_model(batch_number)
        return "{0} ({1})".format(model, batch_number)
    
    def getFwVersion(self):
        """ get firmware version """
        try:
            reply = self.gcs.send("M765", group='gcode')
            return reply[0].strip()
        except:
            return "N.D."
    
    def getIPLan(self):
        """ return valid ip for local network """
        interfaces = self.__getInterfaces()
        if "wlan0" in interfaces:
            return interfaces['wlan0']['ipv4_address'].split('/')[0]
        elif "wlan1" in interfaces:
            return interfaces['wlan1']['ipv4_address'].split('/')[0]
        else:
            return interfaces['eth0']['ipv4_address'].split('/')[0]
    
    def getState(self):
        """ printer's state """
        return self.STATUS[self.gcs.getState()]
    
    def identify(self):
        """ remote command identify printer"""
        self.log.info("MyFabtotumCom - Identify printer")
        
        #send info to remote server
        self.fab_info_update()
        
        self.gcs.send("M300", group='gcode')
        self.gcs.send("M150 R0 U255 B255 S50", group='gcode')
        time.sleep(3)
        self.gcs.send("M701 S{0}".format(self.leds_colors['r']), group='gcode')
        self.gcs.send("M702 S{0}".format(self.leds_colors['g']), group='gcode')
        self.gcs.send("M703 S{0}".format(self.leds_colors['b']), group='gcode')
        self.gcs.send("M300", group='gcode')
        
    def reboot(self):
        """ remote command reboot unit """
        os.system('reboot')
        
    def lock(self):
        """ remote command lock printer """
        self.log.info("MyFabtotumCom - Lock printer")
        
    def unlock(self):
        """ remote command unlock printer """
        self.log.info("MyFabtotumCom - Unlock printer")
    
    def switch_off(self):
        """ remote commnad switch off """
        os.system("poweroff")
    
    def end_job(self):
        """ remote command  end active task """
        self.log.info("MyFabtotumCom - Abort task")
        self.gcs.abort()
        
        
    def fab_polling(self):
        """ ping my.fabtotum.com remote server for new commands """
        params = {
            "serialno"   : self.serial_number,
            "mac"        : self.mac_address,
            "state"      : self.getState(),
            "apiversion" : self.api_version
        }
        result =  self.call('fab_polling', params)
        if result: 
            if result["status_code"] == SERVICE_SUCCESS:     
                if "command" in result:
                    try:
                        getattr(self, result["command"].lower())()
                    except AttributeError:
                        self.log.debug("MyFabtotumCom - {0} : command not valid".format(result["command"]))
                    except:
                        self.log.debug("MyFabtotumCom - Unexpected error: {0}".format(sys.exc_info()[0]))
                        
                if "pollinterval" in result:
                    self.polling_interval = result['pollinterval']
            else:
                self.log.debug("MyFabtotumCom - {0}".format(self.RESPONSE_CODES_DESCRIPTION[result["status_code"]]))
    
    def fab_info_update(self):
        """ update data on my.fabtotum.com remote server """
        
        head = self.config.get_current_head_info()
        params = {
            "serialno"   : self.serial_number,
            "mac"        : self.mac_address,
            "data"       : {
                "name"      : self.unit_name,
                "model"     : self.getModel(),
                "head"      : head["name"],
                "fwversion" : self.getFwVersion(),
                "iplan"     : self.ip_lan,
                'color'     : self.unit_color
            },
            "apiversion" : self.api_version,
        }
        
        result =  self.call('fab_info_update', params)
        
        if result:
            self.log.debug("MyFabtotumCom - fab_info_update: {0} - {1}".format(self.RESPONSE_CODES_DESCRIPTION[result["status_code"]], params))
       
    def start(self):
        """ start server """
        self.running = True
        
        self.thread_polling = Thread( name = "MyFabtotumCom_Polling", target = self.__thread_polling )
        self.thread_polling.start()
        
        self.thread_update = Thread( name = "MyFabtotumCom_InfoUpdate", target = self.__thread_update )
        self.thread_update.start()
    
    def stop(self):
        self.running = False
    
    def reload(self):
        """ reload settings """
        self.mac_address   = self.getMACAddres()
        self.ip_lan        = self.getIPLan()
        self.serial_number = self.getSerialNumber()
        self.fab_id        = self.getFabID()
        self.unit_name     = self.getUnitName()
        #self.batch_number  = self.getBatchNumer()
        #self.fw_version    = self.getFwVersion()
        self.unit_color    = self.getUnitColor()
        self.leds_colors   = self.getLedsColors()
        #### update info 
        self.fab_info_update()
        self.log.debug("MyFabtotumCom - Settings reloaded")
    
    def __thread_polling(self):
        """ polling thread """
        self.log.debug("MyFabtotumCom Polling_thread: started")
        while self.running:
            if self.fab_id:
                self.fab_polling()
            time.sleep(self.polling_interval)
            
    def __thread_update(self):
        """ info update thread """
        self.log.debug("MyFabtotumCom InfoUpdate_thread: started")
        while self.running:
            self.fab_info_update()
            time.sleep(self.info_interval)
        
        
    def loop(self):
        if self.thread_polling:
            self.thread_polling.join()
        
        if self.thread_update:
            self.thread_update.join()
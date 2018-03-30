#!/bin/env python
# -*- coding: utf-8; -*-
#
# (c) 2017 FABtotum, http://www.fabtotum.com
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

__authors__ = "Daniel Kesler"
__license__ = "GPL - https://opensource.org/licenses/GPL-3.0"
__version__ = "1.0"

# Import standard python module
import os
import argparse
import subprocess
import time
import json
from struct import pack

# Import external modules
import bluetooth
import dbus
import dbus.service
import dbus.mainloop.glib
try:
    from gi.repository import GObject
except ImportError:
    import gobject as GObject

# Import internal modules
#~ import bluezutils

from fabtotum.bluetooth.adapter import Adapter
from fabtotum.bluetooth.agent import Agent
from fabtotum.bluetooth.common import bluetooth_status, enable_bluetooth, disable_bletooth, scan
from prism_manager import send_command

from fabtotum.fabui.config  import ConfigService
from fabtotum.utils.common import get_ip_address


class BTFactory():
    
    def __init__(self, verbose=False):
        
        ## init attr
        self.verbose            = verbose
        self._adapter           = None
        self.controller_address = None
        self.config             = ConfigService()
        self.status             = None
        
        ## init adapater
        self._init_adapter()
        
    #######################
    ## INIT BLUETOOTH ADAPTER
    #######################
    def _init_adapter(self):
        dbus.mainloop.glib.DBusGMainLoop(set_as_default=True)
        
        ## get bluetooth status
        self.status = bluetooth_status()
        
        ## enable bluetooth if is necessary
        if(self.status['powered'] == False):
            self.enable_bluetooth()
        
        self._adapter = Adapter()
        
        ## power adapter if necessary
        if not self._adapter.Powered:
            self._adapter.Powered = True
        
        self.controller_address = self._adapter.Address
             
    #######################
    ## ENABLE BLUETOOTH
    #######################
    def enable_bluetooth(self):
        if(self.verbose):
            print "Enabling bluetooth"
        enable_bluetooth()
        ## give time to bluetooth
        time.sleep(2)
        self.status = bluetooth_status()
    
    #######################
    ## PAIR 
    #######################
    def pair(self, mac_address=None, look_for_name="PRISM"):
            
        if(self.status['paired'] != False):
            if(self.status['paired']['mac_address'] == mac_address or self.status['paired']['name'] == look_for_name):
                if(self.verbose):
                    print "Already paired, skip pairing"
                return {'paired': True, 'mac_address': self.status['paired']['mac_address'], 'name': self.status['paired']['name']}
        
        if(self.verbose):
            print "trying to pair with: {0} [{1}]".format(look_for_name, mac_address)
        
        if(self.status['powered'] == False):
            self.enable_bluetooth()
        
        paired = False
            
        ## start pairing 
        devices = self._adapter.discoverDevices(look_for_name=look_for_name, timeout=30, verbose=self.verbose)
        
        for addrees in devices:
            device = devices[addrees]
            paired = True
            # mac_address = addrees
            if(self.verbose):
                print addrees, device.Name, device.Paired, device.Trusted, device.Adapter
            # mac_address = addrees
            if not device.Paired:
                if(self.verbose):
                    print "Pairing.."
                device.Pair()
                device.Trusted = True
        
        if paired:
            if(self.verbose):
                print "Paired with {0} [{1}]".format(look_for_name, mac_address)
            
            ## save to config file
            self.config.set('bluetooth', 'prism_bt_address', str(mac_address) )
            self.config.set('bluetooth', 'prism_bt_name', str(look_for_name) )
            self.config.save('bluetooth')
            
            ### trust us
            ## give time to bluetooth
            time.sleep(2)
            send_command('trust', [self.controller_address], mac_address, verbose=self.verbose)
            
        return {'paired': paired, 'mac_address': mac_address, 'name': look_for_name}
                
    #######################
    ## UNPAIR 
    #######################
    def unpair(self, mac_address):
        try:
            self._adapter.RemoveDevice(mac_address)
            ### tell also device to unpair
            send_command('disconnect', [self.controller_address], mac_address, verbose=self.verbose)
            # send_command('unpair', [self.controller_address], mac_address, verbose=self.verbose)
            
            ## save to config file
            self.config.set('bluetooth', 'prism_bt_address', '')
            self.config.set('bluetooth', 'prism_bt_name', '' )
            self.config.set('bluetooth', 'prism_bt_network_address', '')
            self.config.save('bluetooth')
            
        except Exception as e:
            pass
        return True
    
    #######################
    ## SCAN 
    #######################
    def scan(self):
        if(self.verbose):
            print "Scan for devices..."
        
        return scan('list')
            
    ###################################
    ## AUTO PAIR AND CONNECT TO PRISM 
    ###################################
    def auto_connection(self, name="PRISM", mac_address=None):
         
        if(self.status['paired'] == False):
            if(mac_address == None or mac_address == ""):
                devices = self.scan()
                for device in devices:
                    if(device['name'] == name):
                        mac_address = device['mac']
        
        if(self.verbose):
            print "Autoconnection to {0} [{1}]".format(name, mac_address) 
        
        pairing_result = self.pair(mac_address=mac_address, look_for_name=name)
        mac_address    = pairing_result['mac_address']
        paired         = pairing_result['paired']
        connection     = None
        
        if(paired):
            connection = self.send_command('connect', [], mac_address)
                            
        return {'connection': connection, 'paired': paired}
    
    ###################################
    ## SEN COMMAND TO PRISM 
    ###################################
    def send_command(self, command, args=[], mac_address=None,  port=0x1001):
        if(mac_address == None or mac_address == ""):
            if "mac_address" in self.status['paired']:
                mac_address = self.status['paired']['mac_address']
            else:
                return {'error': _("No connected device")}
            
        
        
        socket = bluetooth.BluetoothSocket(bluetooth.L2CAP)
        socket.settimeout(3)
        
        if(self.verbose):
            print "Trying to connect to {0} on port {1}".format(mac_address, port)
            
        try:
            socket.connect((mac_address, port))
            #bluetooth.set_packet_timeout(mac_address, 0)
            
            if command in [ 'connect', 'disconnect', 'trust', 'untrust']:
                args.append( self._adapter.Address )
                
            if command in ['connect', 'rpc']:
                tether_address = get_ip_address('tether')
                if(tether_address != 'n.a'):
                    args.append( tether_address )
                    args.append(self.config.get('xmlrpc', 'xmlrpc_port'))
                    
            
            data = json.dumps({ 'cmd': command, 'args': args })
            
            if self.verbose:
                print "Data sent {0}".format(data)
             
            ## send data
            socket.send(data)
            
            # get data
            reply = socket.recv(1024)
            
            # close socket
            socket.close()
            
            if self.verbose:
                print "Data received:".format(reply)
            
            response = json.loads(reply)
                    
            ## save network address
            if(command in ['connect', 'network-address']):
                self.config.set('bluetooth', 'prism_bt_network_address', response['reply'])
                self.config.save('bluetooth')
            
            if(command == 'disconnect'):
                self.config.set('bluetooth', 'prism_bt_network_address', '')
                self.config.save('bluetooth')
                
            return {'command': command, 'response': response['reply']}
            
        except Exception as e:
            if self.verbose:
                print "Error: {0}".format(e)
            
            return {'error': str(e)}
            
def main():
    
    # SETTING EXPECTED ARGUMENTS
    parser = argparse.ArgumentParser(formatter_class=argparse.ArgumentDefaultsHelpFormatter)
    parser.add_argument("-n", "--name",    help="Look for name",   default="PRISM")
    parser.add_argument("-m", "--mac",     help="Mac address",     default="")
    parser.add_argument("-a", "--action",  help="Action",          default="")
    parser.add_argument("-v", "--verbose", action="store_true",    help="Show verbose" )
    parser.add_argument("-c", "--command", help="Command to send", default="")
    parser.add_argument("--arg-list",      help="Comma separated argument list.", default=[])
    parser.add_argument("-P", "--port",    help="L2C port",  default=0x1001)
    
    
    # GET ARGUMENTS
    args              = parser.parse_args()
    action            = args.action
    name              = args.name
    mac               = args.mac
    verbose           = args.verbose
    command           = args.command
    command_args_list = args.arg_list
    bt_port           = args.port
    
    if(len(command_args_list) > 0):
        command_args_list = command_args_list.split(",")
    
    bt_factory = BTFactory(verbose=verbose)
    action_result = None
    
    if(action == 'pair'):
        action_result = bt_factory.pair(mac, name)
    elif(action == 'unpair'):
        action_result = bt_factory.unpair(mac)
    elif(action == 'auto_connect'):
        action_result = bt_factory.auto_connection(name=name, mac_address=mac)
    elif(action == 'send-command'):
        action_result = bt_factory.send_command(command, command_args_list)
        
    output = {'action': action, 'response': action_result}
    
    print json.dumps(output)
     
if __name__ == '__main__':
    main()
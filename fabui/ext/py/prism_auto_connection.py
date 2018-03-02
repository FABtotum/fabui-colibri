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

__authors__ = "Krios Mane"
__license__ = "GPL - https://opensource.org/licenses/GPL-3.0"
__version__ = "1.0"


# Import external modules
import bluetooth
import dbus
import dbus.service
import dbus.mainloop.glib

from fabtotum.bluetooth.adapter import Adapter
from fabtotum.bluetooth.agent import Agent
from prism_manager import send_command

# Import internal modules
from fabtotum.bluetooth.common import bluetooth_status, enable_bletooth, scan, pair

def main():
    
    dbus.mainloop.glib.DBusGMainLoop(set_as_default=True)
    
    adapter = Adapter()
    master_bt_address = adapter.Address
    
    print master_bt_address
    
    bt_status = bluetooth_status()
    
    print bt_status
    
    if(bt_status["powered"] == False):
        enable_bletooth()
    
    
    if(bt_status['paired'] == False):
        devices = scan(output="list")
        prism = None
        for device in devices:
            if device["name"] == 'PRISM':
                prism = device
        
        #print prism
        print pair(adapter)
            
        
        
    
if __name__ == "__main__":
    main()
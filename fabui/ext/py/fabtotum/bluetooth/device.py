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

# Import external modules
import dbus
import dbus.service
import dbus.mainloop.glib
try:
  from gi.repository import GObject
except ImportError:
  import gobject as GObject

# Import internal modules
from common import SERVICE_NAME, DEVICE_INTERFACE, find_device

################################################################################

class Device(object):
    
    def __init__(self, address, bus=None):
        if bus:
            self.__bus = bus
        else:
            self.__bus = dbus.SystemBus()
            
        self.__device = find_device(address)
        self.__props = dbus.Interface(self.__bus.get_object(SERVICE_NAME, self.__device.object_path), "org.freedesktop.DBus.Properties") 

    def test(self):
        pass

    def Pair(self):
        """
        This method will connect to the remote device,
        initiate pairing and then retrieve all SDP records
        (or GATT primary services).
        """
        self.__device.Pair()

    def CancelPairing(self):
        self.__device.CancelPairing()

    def Connect(self):
        self.__device.Connect()
        
    def Disconnect(self):
        self.__device.Disconnect()
    
    @property
    def object_path(self):
        return self.__device.object_path
    
    @property
    def Adapter(self):
        return self.__props.Get(DEVICE_INTERFACE, "Adapter")
        
    @property
    def Address(self):
        return self.__props.Get(DEVICE_INTERFACE, "Address")
        
    @property
    def Name(self):
        return self.__props.Get(DEVICE_INTERFACE, "Name")
        
    @property
    def Icon(self):
        return self.__props.Get(DEVICE_INTERFACE, "Icon")
        
    @property
    def Class(self):
        return self.__props.Get(DEVICE_INTERFACE, "Class")
        
    @property
    def RSSI(self):
        """
        Received Signal Strength Indicator of the remote
        device (inquiry or advertising).
        """
        return self.__props.Get(DEVICE_INTERFACE, "RSSI")
        
    @property
    def Paired(self):
        return self.__props.Get(DEVICE_INTERFACE, "Paired")
        
    @property
    def Trusted(self):
        return self.__props.Get(DEVICE_INTERFACE, "Trusted")
        
    @Trusted.setter
    def Trusted(self, value):
        self.__props.Set(DEVICE_INTERFACE, "Trusted", value)
        
    @property
    def Blocked(self):
        return self.__props.Get(DEVICE_INTERFACE, "Blocked")

    @Blocked.setter
    def Blocked(self, value):
        self.__props.Set(DEVICE_INTERFACE, "Blocked", value)

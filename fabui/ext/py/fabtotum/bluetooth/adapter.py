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
import fnmatch

# Import external modules
import dbus
try:
  from gi.repository import GObject
except ImportError:
  import gobject as GObject
  
# Import internal modules
from common import SERVICE_NAME, ADAPTER_INTERFACE, DEVICE_INTERFACE, find_adapter, find_device
from device import Device

class Adapter(dbus.service.Object):
    
    def __init__(self, pattern=None):
        """
        Bluetooth adapter class constructor.
        
        @param pattern if None then automatically detect one
        """
        self.__mainloop = None
        self.__bus = dbus.SystemBus()
        self.__adapter = find_adapter(pattern)
        self.__props = dbus.Interface(self.__bus.get_object(SERVICE_NAME, self.__adapter.object_path), "org.freedesktop.DBus.Properties") 
        
        self.__bus.add_signal_receiver(self.__PropertiesChanged,
                dbus_interface = "org.freedesktop.DBus.Properties",
                signal_name = "PropertiesChanged",
                arg0 = DEVICE_INTERFACE,
                path_keyword = "path")
        
        self.__devices = {}
        
        om = dbus.Interface(self.__bus.get_object("org.bluez", "/"),
                    "org.freedesktop.DBus.ObjectManager")
        objects = om.GetManagedObjects()
        for path, interfaces in objects.iteritems():
            if DEVICE_INTERFACE in interfaces:
                self.__devices[path] = interfaces[DEVICE_INTERFACE]
                
        self.__look_for_name = None
        self.__discovery = {}

    def discoverDevices(self, look_for_name=None, timeout=10, scan_filter={}):
        """
        Start BT discovery process.
        
        Return discovered devices.
        """
        self.__look_for_name = look_for_name
        self.__discovery = {}
        
        timeout *= 1000
        GObject.timeout_add(timeout, self.__timeout_handler)
        
        self.__adapter.SetDiscoveryFilter(scan_filter)
        self.__adapter.StartDiscovery()
        
        self.__mainloop = GObject.MainLoop()
        self.__mainloop.run()
        
        self.__adapter.StopDiscovery()
        
        return self.__discovery

    def __timeout_handler(self):
        """
        Timeout handler.
        Stops main loop when timeout has expired.
        """
        if self.__mainloop is not None:
            self.__mainloop.quit()

    def __add_device(self, address, properties):
        """
        New device discovered.
        """
        print("[ " + address + " ]")

        for key in properties.keys():
            value = properties[key]
            if type(value) is dbus.String:
                value = unicode(value).encode('ascii', 'replace')
            
            if self.__look_for_name:
                if key == "Name":
                    print ">> name:", value
                if key == "Name" and value == self.__look_for_name and address != "<unknown>":
                    self.__discovery[address] = Device(address, bus=self.__bus)
                    self.__timeout_handler()

        properties["Logged"] = True

    @dbus.service.signal("org.freedesktop.DBus.ObjectManager")
    def InterfacesAdded(self, path, interfaces):
        """
        """
        properties = interfaces[DEVICE_INTERFACE]
        if not properties:
            return

        if path in self.__devices:
            dev = self.__devices[path]

            self.__devices[path] = dict(self.__devices[path].items() + properties.items())
        else:
            self.__devices[path] = properties

        if "Address" in self.__devices[path]:
            address = properties["Address"]
        else:
            address = "<unknown>"

        self.__add_device(address, self.__devices[path])
        
    def __PropertiesChanged(self, interface, changed, invalidated, path):
        """
        """
        if interface != DEVICE_INTERFACE:
            return

        if path in self.__devices:
            dev = self.__devices[path]

            self.__devices[path] = dict(self.__devices[path].items() + changed.items())
        else:
            self.__devices[path] = changed

        if "Address" in self.__devices[path]:
            address = self.__devices[path]["Address"]
        else:
            address = "<unknown>"

        self.__add_device(address, self.__devices[path])

    def RemoveDevice(self, address):
        self.__adapter.RemoveDevice(address)

    @property
    def object_path(self):
        return self.__device.object_path
    
    @property
    def Address(self):
        return self.__props.Get(ADAPTER_INTERFACE, "Address")
        
    @property
    def Name(self):
        return self.__props.Get(ADAPTER_INTERFACE, "Name")
        
    @property
    def Class(self):
        return self.__props.Get(ADAPTER_INTERFACE, "Class")
        
    @property
    def Alias(self):
        return self.__props.Get(ADAPTER_INTERFACE, "Alias")
        
    @Alias.setter
    def Alias(self, value):
        self.__props.Set(ADAPTER_INTERFACE, "Alias", value)
        
    @property
    def Powered(self):
        return self.__props.Get(ADAPTER_INTERFACE, "Powered")
        
    @Powered.setter
    def Powered(self, value):
        self.__props.Set(ADAPTER_INTERFACE, "Powered", value)
        
    @property
    def Discovering(self):
        return self.__props.Get(ADAPTER_INTERFACE, "Discovering")
        
    @property
    def Discoverable(self):
        return self.__props.Get(ADAPTER_INTERFACE, "Discoverable")
        
    @Discoverable.setter
    def Discoverable(self, value):
        self.__props.Set(ADAPTER_INTERFACE, "Discoverable", value)
        
    @property
    def DiscoverableTimeout(self):
        return self.__props.Get(ADAPTER_INTERFACE, "DiscoverableTimeout")
        
    @DiscoverableTimeout.setter
    def DiscoverableTimeout(self, value):
        self.__props.Set(ADAPTER_INTERFACE, "DiscoverableTimeout", value)
        
    @property
    def Pairable(self):
        return self.__props.Get(ADAPTER_INTERFACE, "Pairable")
        
    @Pairable.setter
    def Pairable(self, value):
        self.__props.Set(ADAPTER_INTERFACE, "Pairable", value)
        
    @property
    def PairableTimeout(self):
        return self.__props.Get(ADAPTER_INTERFACE, "PairableTimeout")
        
    @PairableTimeout.setter
    def PairableTimeout(self, value):
        self.__props.Set(ADAPTER_INTERFACE, "PairableTimeout", value)
        
    

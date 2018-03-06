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

# Import external modules
import dbus
import json

import bluetooth

# Import internal modules
from fabtotum.os.paths import *
from fabtotum.utils.common import shell_exec

################################################################################

SERVICE_NAME                = 'org.bluez'
AGENT_INTERFACE             = SERVICE_NAME + '.Agent1'
AGENT_MANAGE_INTERFACE      = SERVICE_NAME + '.AgentManager1'
AGENT_PATH                  = "/test/agent"
DEVICE_INTERFACE            = SERVICE_NAME + '.Device1'
ADAPTER_INTERFACE           = SERVICE_NAME + '.Adapter1'

def get_managed_objects():
    bus = dbus.SystemBus()
    manager = dbus.Interface(bus.get_object("org.bluez", "/"),
                "org.freedesktop.DBus.ObjectManager")
    return manager.GetManagedObjects()

def find_adapter(pattern=None):
    return find_adapter_in_objects(get_managed_objects(), pattern)

def find_adapter_in_objects(objects, pattern=None):
    bus = dbus.SystemBus()
    for path, ifaces in objects.iteritems():
        adapter = ifaces.get(ADAPTER_INTERFACE)
        if adapter is None:
            continue
        if not pattern or pattern == adapter["Address"] or \
                            path.endswith(pattern):
            obj = bus.get_object(SERVICE_NAME, path)
            return dbus.Interface(obj, ADAPTER_INTERFACE)
    raise Exception("Bluetooth adapter not found")

def find_device(device_address, adapter_pattern=None):
    return find_device_in_objects(get_managed_objects(), device_address,
                                adapter_pattern)

def find_device_in_objects(objects, device_address, adapter_pattern=None):
    bus = dbus.SystemBus()
    path_prefix = ""
    if adapter_pattern:
        adapter = find_adapter_in_objects(objects, adapter_pattern)
        path_prefix = adapter.object_path
    for path, ifaces in objects.iteritems():
        device = ifaces.get(DEVICE_INTERFACE)
        if device is None:
            continue
        if (device["Address"] == device_address and
                        path.startswith(path_prefix)):
            obj = bus.get_object(SERVICE_NAME, path)
            return dbus.Interface(obj, DEVICE_INTERFACE)

    raise Exception("Bluetooth device not found")


########################################################################
# get bluetooth status
########################################################################
def bluetooth_status():
    result = shell_exec('sudo sh ' + BASH_PATH + 'bluetooth.sh -a "status"')
    return json.loads(''.join(result))

########################################################################
# enable bluetooth
########################################################################
def enable_bluetooth():
    result = shell_exec('sudo sh ' + BASH_PATH + 'bluetooth.sh -a "enable"')
    return json.loads(''.join(result))

########################################################################
# disable bluetooth
########################################################################
def disable_bletooth():
    result = shell_exec('sudo sh ' + BASH_PATH + 'bluetooth.sh -a "disable"')
    return json.loads(''.join(result))

########################################################################
# Scan for bluetooth devices
########################################################################
def scan(output='json', flush=True):
    
    devices = []
    for address, name in bluetooth.discover_devices(flush_cache=flush, lookup_names = True):
        devices.append({'mac': address, 'name': name})
            
    if (output == 'json'):
        return json.dumps(devices)
    
    
    return devices

########################################################################
# Do pair
########################################################################
def pair(adapter, name="PRISM"):
    devices = adapter.discoverDevices(look_for_name=name, timeout=60, verbose=True)
    paired = False
    already_paired = False
    mac = None
    
    for addr in devices:
        dev = devices[addr]
        
        print addr
        if not dev.Paired :
            dev.Pair()
            dev.Trusted = True
            paired = True
            mac = addr
        else:
            paired = True
            mac = addr
            already_paired = True
    return {'paired': paired, 'already_paired': already_paired, 'mac': mac}
    
    
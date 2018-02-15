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

# Import external modules
import bluetooth
import dbus
import dbus.service
import dbus.mainloop.glib
try:
    from gi.repository import GObject
except ImportError:
    import gobject as GObject

try:
    import ConfigParser
except ImportError:
    import configparser as ConfigParser

# Import internal modules
#~ import bluezutils

from fabtotum.bluetooth.adapter import Adapter
from fabtotum.bluetooth.agent import Agent
from prism_manager import send_command
################################################################################

def main():
    from fabtotum.fabui.config  import ConfigService
    dbus.mainloop.glib.DBusGMainLoop(set_as_default=True)
    
    
    # SETTING EXPECTED ARGUMENTS
    parser = argparse.ArgumentParser(formatter_class=argparse.ArgumentDefaultsHelpFormatter)
    parser.add_argument("-n", "--name", help="Look for name", default="PRISM")
    parser.add_argument("-m", "--mac",  help="Mac address",   default="")
    parser.add_argument("-v", "--verbose", action="store_true", help="Show verbose" )
    
    
    # GET ARGUMENTS
    args    = parser.parse_args()
    name    = args.name
    mac     = args.mac
    verbose = args.verbose
    
    config    = ConfigService()

    # Ensure bluetooth is enabled
    p = subprocess.Popen(['connmanctl', 'enable', 'bluetooth'],
        stdout=subprocess.PIPE,
        stderr=subprocess.PIPE)
    out, err = p.communicate()

    if 'Enabled bluetooth' == out.strip():
        # Give bluetoothd some time to bring up the hci device
        time.sleep(3)
        if(verbose):
            print "Bluetooth enabled"

    adapter = Adapter()
    if not adapter.Powered:
        if(verbose):
            print "Powering up bluetooth..."
        adapter.Powered = True

    master_bt_address = adapter.Address
    devices = adapter.discoverDevices(look_for_name="PRISM", timeout=30, verbose=verbose)
    
    paired = False
    already_paired = False
    
    for addr in devices:
        dev = devices[addr]
        if(verbose):
            print addr, dev.Name, dev.Paired, dev.Trusted, dev.Adapter

        if not dev.Paired:
            if(verbose):
                print "Pairing..."
            dev.Pair()
            dev.Trusted = True
            paired = True
            mac = addr
            if(verbose):
                print "Paired"
            # Store PRISM bt mac address
            config.set('bluetooth', 'prism_bt_address', str(addr) )
            config.save('bluetooth')
            # Make PRISM trust us ;)
            send_command('trust', [master_bt_address], addr, verbose=verbose)
        else:
            paired = True
            mac = addr
            already_paired = True
            send_command('trust', [master_bt_address], addr, verbose=verbose)
            if(verbose):
                print "Already paired"
    
    response = {'name': name, 'mac': mac, 'paired': paired, 'already_paired': already_paired }
    print json.dumps(response)

if __name__ == '__main__':
    main()

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

__authors__ = "Daniel Kesler"
__license__ = "GPL - https://opensource.org/licenses/GPL-3.0"
__version__ = "1.0"

# Import standard python module
import sys
import json
import os
import argparse
import subprocess
import time
from threading import Event, Thread

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
from fabtotum.os.paths          import RUN_PATH
from fabtotum.bluetooth.adapter import Adapter

################################################################################

if sys.version < '3':
    input = raw_input

def main():
    from fabtotum.fabui.config  import ConfigService
    dbus.mainloop.glib.DBusGMainLoop(set_as_default=True)

    config  = ConfigService()

    # Get PRISM BT address and use it as default value if it exists
    bt_addr = config.get('bluetooth', 'prism_bt_address', '')
    bt_addr_required = True
    if bt_addr:
        bt_addr_required = False

    # Setup arguments
    parser = argparse.ArgumentParser()
    parser.add_argument("command",          help="Prism management command." )
    parser.add_argument("-v", dest='verbose', action='store_true',  help="Verbose.")
    parser.add_argument("--arg-list",       help="Comma separated argument list.", default=[] )
    parser.add_argument("-a", "--address",  help="Bluetooth address.",
            # In case there is no prism_bt_address preconfigured, fallback to asking for the address
            required=bt_addr_required,
            default=bt_addr
    )
    parser.add_argument("-P", "--port",     help="L2C port",  default=0x1001)

    args     = parser.parse_args()
    cmd      = args.command
    arg_list = args.arg_list
    bt_port  = args.port
    bt_addr  = args.address
    verbose  = args.verbose

    if arg_list:
        arg_list = arg_list.split(',')

    # Ensure bluetooth is enabled
    p = subprocess.Popen(['connmanctl', 'enable', 'bluetooth'],
        stdout=subprocess.PIPE,
        stderr=subprocess.PIPE)
    out, err = p.communicate()

    if 'Enabled bluetooth' == out.strip():
        # Give bluetoothd some time to bring up the hci device
        time.sleep(3)
        if verbose:
            print "Bluetooth enabled"

    sock=bluetooth.BluetoothSocket(bluetooth.L2CAP)

    sock.settimeout(5)

    if verbose:
        print("trying to connect to %s on port 0x%X" % (bt_addr, bt_port))

    try:

        sock.connect((bt_addr, bt_port))

        if cmd == 'connect' or cmd == 'disconnect':
            adapter = Adapter()
            arg_list.append( adapter.Address )

        data = json.dumps( {
            'cmd': cmd,
            'args': arg_list
        } )

        if verbose:
            print "Data sent", str(data)

        sock.send(data)
        reply = sock.recv(1024)
        if verbose:
            print "Data received:", str(reply)
        else:
            print str(reply)

    except Exception as e:
        if verbose:
            print "Error:", str(e)

    sock.close()


if __name__ == '__main__':
    main()


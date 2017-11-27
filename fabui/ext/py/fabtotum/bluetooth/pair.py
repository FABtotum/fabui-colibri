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

from adapter import Adapter
from agent import Agent
################################################################################

dbus.mainloop.glib.DBusGMainLoop(set_as_default=True)

#~ agent = Agent()
#~ agent.register()

adapter = Adapter()
devices = adapter.discoverDevices(look_for_name="PRISM", timeout=30)


for addr in devices:
    dev = devices[addr]
    print addr, dev.Name, dev.Paired, dev.Trusted, dev.Adapter
    
    if not dev.Paired:
        print "Pairing..."
        dev.Pair()
        dev.Trusted = True
        print "Paired"
    else:
        print "Already paired"
    

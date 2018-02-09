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
import logging
import argparse

# Import external modules
import dbus
import dbus.service
import dbus.mainloop.glib
try:
  from gi.repository import GObject
except ImportError:
  import gobject as GObject

# Import internal modules
from fabtotum.bluetooth.common  import SERVICE_NAME, AGENT_INTERFACE, \
                                       AGENT_MANAGE_INTERFACE, AGENT_PATH
from fabtotum.os.paths          import RUN_PATH

################################################################################

class Rejected(dbus.DBusException):
    _dbus_error_name = "org.bluez.Error.Rejected"

class Agent(dbus.service.Object):
    exit_on_release = True

    def __init__(self, passkey="1234", bus=None, log=None, path=AGENT_PATH, capability="KeyboardDisplay"):

        if bus:
            self.__bus = bus
        else:
            self.__bus = dbus.SystemBus()

        self.log = log

        super(Agent, self).__init__(self.__bus, path)

        obj = self.__bus.get_object(SERVICE_NAME, "/org/bluez");

        self.__passkey = passkey
        self.__manager = dbus.Interface(obj, AGENT_MANAGE_INTERFACE)
        self.__path = path
        self.__capability = capability

    def set_exit_on_release(self, exit_on_release):
        self.exit_on_release = exit_on_release

    def register(self):
        self.__manager.RegisterAgent(self.__path, self.__capability)
        self.__manager.RequestDefaultAgent(self.__path)
        self.log.debug("BT agent registered")

    @dbus.service.method(AGENT_INTERFACE,
                    in_signature="", out_signature="")
    def Release(self):
        self.log.debug("Released")
        if self.exit_on_release:
            mainloop.quit()

    @dbus.service.method(AGENT_INTERFACE,
                    in_signature="os", out_signature="")
    def AuthorizeService(self, device, uuid):
        self.log.debug("AuthorizeService (%s, %s)" % (device, uuid))
        return
        #~ authorize = ask("Authorize connection (yes/no): ")
        #~ if (authorize == "yes"):
            #~ return
        #~ raise Rejected("Connection rejected by user")

    @dbus.service.method(AGENT_INTERFACE,
                    in_signature="o", out_signature="s")
    def RequestPinCode(self, device):
        self.log.debug("RequestPinCode (%s) : %s" % (device, self.__passkey))
        #~ set_trusted(device)
        return self.__passkey

    @dbus.service.method(AGENT_INTERFACE,
                    in_signature="o", out_signature="u")
    def RequestPasskey(self, device):
        self.log.debug("RequestPasskey (%s): %s" % (device, self.__passkey))
        #~ set_trusted(device)
        return dbus.UInt32(self.__passkey)

    @dbus.service.method(AGENT_INTERFACE,
                    in_signature="ouq", out_signature="")
    def DisplayPasskey(self, device, passkey, entered):
        self.log.debug("DisplayPasskey (%s, %06u entered %u)" %
                        (device, passkey, entered))

    @dbus.service.method(AGENT_INTERFACE,
                    in_signature="os", out_signature="")
    def DisplayPinCode(self, device, pincode):
        self.log.debug("DisplayPinCode (%s, %s)" % (device, pincode))

    @dbus.service.method(AGENT_INTERFACE,
                    in_signature="ou", out_signature="")
    def RequestConfirmation(self, device, passkey):
        self.log.debug("RequestConfirmation (%s, %06d): Yes" % (device, passkey))
        #~ confirm = ask("Confirm passkey (yes/no): ")
        #~ confirm = "yes"
        #~ if (confirm == "yes"):
            #~ #set_trusted(device)
            #~ return
        return
        #~ raise Rejected("Passkey doesn't match")

    @dbus.service.method(AGENT_INTERFACE,
                    in_signature="o", out_signature="")
    def RequestAuthorization(self, device):
        self.log.debug("RequestAuthorization (%s): Yes" % (device))
        #~ auth = ask("Authorize? (yes/no): ")
        #~ auth = "yes"
        #~ if (auth == "yes"):
            #~ return
        return
        #~ raise Rejected("Pairing rejected")

    @dbus.service.method("org.bluez.Agent",
                         in_signature="s",
                         out_signature="")
    def ConfirmModeChange(self, mode):
        print("ConfirmModeChange ({})".format(mode))
        return

    @dbus.service.method(AGENT_INTERFACE,
                    in_signature="", out_signature="")
    def Cancel(self):
        print("Cancel")

if __name__ == '__main__':
    from fabtotum.fabui.config  import ConfigService
    dbus.mainloop.glib.DBusGMainLoop(set_as_default=True)

    # Setup arguments
    parser = argparse.ArgumentParser()
    parser.add_argument("-L", "--log",              help="Use logfile to store log messages.",  default='<stdout>')
    parser.add_argument("-p", "--pidfile",          help="File to store process pid.",          default=os.path.join(RUN_PATH,'btagent.pid') )

    # Get arguments
    args = parser.parse_args()
    pidfile = args.pidfile
    logging_facility = args.log

    with open(pidfile, 'w') as f:
        f.write( str(os.getpid()) )

    config = ConfigService()
    PASSKEY = config.get('bluetooth', 'passkey', '1234')
    LOG_LEVEL = 'DEBUG'

    # Setup logger
    if LOG_LEVEL == 'INFO':
        LOG_LEVEL = logging.INFO
    elif LOG_LEVEL == 'DEBUG':
        LOG_LEVEL = logging.DEBUG

    logger = logging.getLogger('FabtotumService')
    logger.setLevel(LOG_LEVEL)

    if logging_facility == '<stdout>':
        ch = logging.StreamHandler()
    elif logging_facility == '<syslog>':
        # Not supported at this point
        ch = logging.StreamHandler()
    else:
        ch = logging.FileHandler(logging_facility)

    formatter = logging.Formatter("[%(asctime)s]: %(message)s")
    ch.setFormatter(formatter)
    ch.setLevel(LOG_LEVEL)
    logger.addHandler(ch)

    mainloop = GObject.MainLoop()

    agent = Agent(passkey=PASSKEY, log=logger)
    agent.register()

    mainloop.run()

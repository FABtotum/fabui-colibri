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

# Import standard python module
import sys
import gettext
import signal

# Import external modules
from watchdog.observers import Observer
from ws4py.client.threadedclient import WebSocketClient

# Import internal modules
from fabtotum.os.paths                  import TEMP_PATH
from fabtotum.fabui.config              import ConfigService
from fabtotum.totumduino.gcode          import GCodeService
from fabtotum.utils.pyro.gcodeserver    import GCodeServiceServer
from fabtotum.os.monitor.filesystem     import FolderTempMonitor
from fabtotum.os.monitor.usbdrive       import UsbMonitor
from fabtotum.os.monitor.gpiomonitor    import GPIOMonitor

def signal_handler(signal, frame):
    print "You pressed Ctrl+C!"
    print "Shutting down services. Please wait..."
    ws.close()
    gcserver.stop()
    gcservice.stop()
    observer.stop()
    usbMonitor.stop()
    gpioMonitor.stop()

config = ConfigService()

# Load configuration
LOCK_FILE           = config.get('general', 'lock')
TRACE               = config.get('general', 'trace')
COMMAND             = config.get('general', 'command')
MACRO_RESPONSE      = config.get('general', 'macro_response')
TASK_MONITOR        = config.get('general', 'task_monitor')
EMERGENCY_FILE      = config.get('general', 'emergency_file')
##################################################################
SOCKET_HOST         = config.get('socket', 'host')
SOCKET_PORT         = config.get('socket', 'port')
##################################################################
HW_DEFAULT_SETTINGS = config.get('hardware', 'default_settings')
HW_CUSTOM_SETTINGS  = config.get('hardware', 'custom_settings')
##################################################################
USB_DISK_FOLDER     = config.get('usb', 'usb_disk_folder')
USB_FILE            = config.get('usb', 'usb_file')
##################################################################
SERIAL_PORT = config.get('serial', 'PORT')
SERIAL_BAUD = config.get('serial', 'BAUD')
GPIO_PIN    = config.get('gpio', 'pin')

# Start gcode service
gcservice = GCodeService(SERIAL_PORT, SERIAL_BAUD)
gcservice.start()

# Pyro GCodeService wrapper
gcserver = GCodeServiceServer(gcservice)

ws = WebSocketClient('ws://'+SOCKET_HOST +':'+SOCKET_PORT+'/')
ws.connect();

## Folder temp monitor
ftm = FolderTempMonitor(ws, gcservice, TRACE, MACRO_RESPONSE, TASK_MONITOR, COMMAND)
observer = Observer()
observer.schedule(ftm, TEMP_PATH, recursive=False)
observer.start()

## usb disk monitor
um = UsbMonitor(ws, gcservice, USB_FILE)
usbMonitor =  Observer()
usbMonitor.schedule(um, '/dev/', recursive=False)
usbMonitor.start()

## Safety monitor
gpioMonitor = GPIOMonitor(ws, gcservice, GPIO_PIN, EMERGENCY_FILE)
gpioMonitor.start()

# Ensure CTRL+C detection to gracefully stop the server.
signal.signal(signal.SIGINT, signal_handler)

# Wait for all threads to finish
gcserver.loop()
print "GCodeService stopped."
gcservice.loop()
print "Server stopped."
observer.join()
usbMonitor.join()
gpioMonitor.join()

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
import os
import sys
import gettext
import signal
import argparse
import logging
import time

# Import external modules
from watchdog.observers import Observer
from ws4py.client.threadedclient import WebSocketClient

# Import internal modules
from fabtotum.utils.translation         import _, setLanguage
from fabtotum.os.paths                  import TEMP_PATH, RUN_PATH, PYTHON_PATH
from fabtotum.fabui.config              import ConfigService
from fabtotum.fabui.bootstrap           import hardwareBootstrap
from fabtotum.fabui.monitor             import StatsMonitor
from fabtotum.fabui.notify              import NotifyService
from fabtotum.totumduino.gcode          import GCodeService
from fabtotum.totumduino.hardware       import reset as totumduino_reset
from fabtotum.utils.pyro.gcodeserver    import GCodeServiceServer
from fabtotum.os.monitor.filesystem     import FolderTempMonitor
from fabtotum.os.monitor.usbdrive       import UsbMonitor
from fabtotum.os.monitor.gpiomonitor    import GPIOMonitor
from fabtotum.os.monitor.configmonitor  import ConfigMonitor

def create_file(filename, content=''):
    with open(filename,'w') as f:
        f.write(content)
    os.chmod(filename, 0o660)
    # www-data uid/gid are 33/33
    os.chown(filename, 33, 33)

def signal_handler(signal, frame):
    print "You pressed Ctrl+C!"
    logger.debug("Shutting down services. Please wait...")
    ws.close()
    gcserver.stop()
    gcservice.stop()
    observer.stop()
    gpioMonitor.stop()
    statsMonitor.stop()

def shell_exec(cmd):
	stdin,stdout = os.popen2(cmd)
	stdin.close()
	lines = stdout.readlines(); 
	stdout.close()
	return lines

# Setup arguments
parser = argparse.ArgumentParser()
parser.add_argument("-B", "--bootstrap", action='store_true',  help="Execute bootstrape commands on startup.")
parser.add_argument("-R", "--reset", action='store_true',  help="Reset totumduino on startup.")
parser.add_argument("-L", "--log", help="Use logfile to store log messages.",               default='<stdout>')
parser.add_argument("-p", "--pidfile", help="File to store process pid.",                   default=os.path.join(RUN_PATH,'fabtotumservices.pid') )
parser.add_argument("-x", "--xmlrpc_pidfile", help="File to store xmlrpc process pid.",     default=os.path.join(RUN_PATH,'xmlrpcserver.pid') )

# Get arguments
args = parser.parse_args()

do_bootstrap        = args.bootstrap
do_reset            = args.reset
logging_facility    = args.log
pidfile             = args.pidfile
xmlrpc_pidfile      = args.xmlrpc_pidfile

with open(pidfile, 'w') as f:
    f.write( str(os.getpid()) )

config = ConfigService()

# Load configuration
LOCK_FILE           = config.get('general', 'lock')
TRACE               = config.get('general', 'trace')
TASK_MONITOR        = config.get('general', 'task_monitor')
TEMP_MONITOR_FILE   = config.get('general', 'temperature')
NOTIFY_FILE         = config.get('general', 'notify_file')
##################################################################
SOCKET_HOST         = config.get('socket', 'host')
SOCKET_PORT         = config.get('socket', 'port')
##################################################################
HW_DEFAULT_SETTINGS = config.get('hardware', 'settings')
#HW_CUSTOM_SETTINGS  = config.get('hardware', 'custom_settings')
##################################################################
USB_DISK_FOLDER     = config.get('usb', 'usb_disk_folder')
USB_FILE            = config.get('usb', 'usb_file')
##################################################################
SERIAL_PORT = config.get('serial', 'PORT')
SERIAL_BAUD = config.get('serial', 'BAUD')
GPIO_PIN    = config.get('gpio', 'pin')


# Prepare files with correct permissions
create_file(TRACE)
create_file(TASK_MONITOR, '{"task":{"status":"completed"}}')
create_file(TEMP_MONITOR_FILE, '{}')
create_file(NOTIFY_FILE, '{}')

# Setup logger
logger = logging.getLogger('FabtotumService')
logger.setLevel(logging.DEBUG)

if logging_facility == '<stdout>':
    ch = logging.StreamHandler()
elif logging_facility == '<syslog>':
    # Not supported at this point
    ch = logging.StreamHandler()
else:
    ch = logging.FileHandler(logging_facility)

#~ formatter = logging.Formatter("%(name)s - %(levelname)s : %(message)s")
formatter = logging.Formatter("%(levelname)s : %(message)s")
ch.setFormatter(formatter)
ch.setLevel(logging.DEBUG)
logger.addHandler(ch)

if do_reset:
    totumduino_reset()
    time.sleep(1)

# Clear unfinished tasks
from fabtotum.database import Database
from fabtotum.database.task import Task

db = Database()
conn = db.get_connection()
cursor = conn.execute("SELECT * from sys_tasks where status!='completed' and status!='aborted' and status!='terminated' ")
for row in cursor:
   id = row[0]
   t = Task(db, id)
   t['status'] = 'terminated'
   t.write()

# Start gcode service
gcservice = GCodeService(SERIAL_PORT, SERIAL_BAUD, logger=logger)
gcservice.start()

# Pyro GCodeService wrapper
gcserver = GCodeServiceServer(gcservice)

ws = WebSocketClient('ws://'+SOCKET_HOST +':'+SOCKET_PORT+'/')
ws.connect();

# Notification service
ns = NotifyService(ws, NOTIFY_FILE, config)

## Folder temp monitor
ftm = FolderTempMonitor(ns, gcservice, logger, TRACE, TASK_MONITOR)
## usb disk monitor
um = UsbMonitor(ns, logger, USB_FILE)
## Configuration monitor
cm = ConfigMonitor(gcservice, config, logger)

## The Observer ;)
observer = Observer()
observer.schedule(um, '/dev/', recursive=False)
observer.schedule(cm, '/var/lib/fabui', recursive=True)
observer.schedule(ftm, TEMP_PATH, recursive=False)
observer.start()

if do_bootstrap:
    time.sleep(1)
    hardwareBootstrap(gcservice, config, logger=logger)

## Safety monitor

# Setup logger
logger2 = logging.getLogger('GPIOMonitor')
logger2.setLevel(logging.DEBUG)
fh = logging.FileHandler('/var/log/fabui/gpiomonitor.log', mode='w')

#~ formatter = logging.Formatter("%(name)s - %(levelname)s : %(message)s")
formatter = logging.Formatter("%(levelname)s : %(message)s")
fh.setFormatter(formatter)
fh.setLevel(logging.DEBUG)
logger2.addHandler(fh)

gpioMonitor = GPIOMonitor(ns, gcservice, logger2, GPIO_PIN)
gpioMonitor.start()

## Stats monitor
statsMonitor = StatsMonitor(TEMP_MONITOR_FILE, gcservice, config, logger=logger)
statsMonitor.start()

# Ensure CTRL+C detection to gracefully stop the server.
signal.signal(signal.SIGINT, signal_handler)

# Start XMLRPC server

soc_id = shell_exec('</proc/cpuinfo grep Hardware | awk \'{print $3}\'')[0].strip()
rpc = None

if soc_id == 'BCM2709':
    xmlrpc_exe = os.path.join(PYTHON_PATH, 'fabtotum/utils/xmlrpc/xmlrpcserver.py')
    os.system('python {0} -p {1} -L /var/log/fabui/xmlrpc.log &'.format(xmlrpc_exe, xmlrpc_pidfile) )
else:
    from fabtotum.utils.xmlrpc.xmlrpcserver import create as rpc_create
    rpc = rpc_create(gcservice, config)
    rpc.start()

# Wait for all threads to finish
gcserver.loop()
gcservice.loop()
logger.info("Server stopped.")
statsMonitor.loop()
observer.join()
#usbMonitor.join()
gpioMonitor.join()
if rpc:
    rpc.loop()

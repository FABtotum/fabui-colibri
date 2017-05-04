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

__author__ = "Krios Mane, Daniel Kesler"
__license__ = "GPL - https://opensource.org/licenses/GPL-3.0"
__version__ = "1.0"

# Import standard python module
import json
import re
import gettext
import time

# Import external modules
try:
    import RPi.GPIO as GPIO
except:
    pass

# Import internal modules


# Set up message catalog access
tr = gettext.translation('gpio_monitor', 'locale', fallback=True)
_ = tr.ugettext

class GPIOMonitor:
    
    ACTION_PIN = None
    EMERGENCY_FILE = None
    
    def __init__(self, notifyservice, gcs, logger, action_pin):
        self.ns = notifyservice
        self.gcs = gcs
        self.log = logger
        self.ACTION_PIN = int(action_pin)
        self.running = False
        
    def gpioEventListener(self, chanel):
        """
        Triggered when a level change on a pin is detected.
        """
        try:
            self.log.debug("====== START ============")
            GPIO_STATUS = GPIO.input(self.ACTION_PIN)
            self.log.debug('GPIO STATUS: %s', str(GPIO_STATUS))
            
            #if GPIO_STATUS == 0:
            self.gcs.atomic_begin(group='emergency')
            reply = self.gcs.send("M730", group='emergency')
            self.gcs.atomic_end()
            
            if reply:
                if len(reply) > 1:
                    #~ self.log.debug('M730: reply[-2] is ', reply[-2])
                    search = re.search('ERROR\s:\s(\d+)', reply[-2])
                    if search != None:
                        errorNumber = int(search.group(1))
                        self.log.warning("Totumduino error no.: %s", errorNumber)
                        self.manageErrorNumber(errorNumber)
                    else:
                        self.log.error("Totumduino unrecognized error: %s", reply[0])

            #GPIO_STATUS = GPIO.HIGH
            GPIO_STATUS = GPIO.input(self.ACTION_PIN)
            self.log.debug('GPIO STATUS on EXIT: %s', str(GPIO_STATUS))
            self.log.debug("======= EXIT ============")
        except Exception as e:
            self.log.error('GPIOMonitor ERROR: %s', str(e) )

    def manageErrorNumber(self, error):
        alertErrors = [110, 111]
        shutdownErrors = [120, 121]
        #~ terminateErrors = [100, 101, 102, 106, 107, 108, 109]
        terminateErrors = [100, 101, 102]
        errorType = 'emergency'
        
        if error in shutdownErrors:
            self.log.info("shutdown")
            # TODO: trigger shutdown
            return None
        elif error in alertErrors:
            self.log.info("alert")
            errorType = 'alert'
            self.gcs.send('M999', block=False, group='*')
        elif error in terminateErrors:
            self.log.info("terminate")
            self.ns.notify(errorType, {'code': error} )
            self.gcs.terminate()
            return None
        
        self.gcs.trigger('error', [error])
        self.ns.notify(errorType, {'code': error} )

    def start(self):
        """ Start gpio event detection """
        try:
            # Setup BCM GPIO numbering
            GPIO.setmode(GPIO.BCM)
            GPIO.setwarnings(False)
            # Set GPIO as input (button)
            #~ GPIO.setup(self.ACTION_PIN, GPIO.IN, pull_up_down = GPIO.PUD_DOWN)
            GPIO.setup(self.ACTION_PIN, GPIO.IN)
            # Register callback function for gpio event, callbacks are handled from a separate thread
            GPIO.add_event_detect(self.ACTION_PIN, GPIO.BOTH, callback=self.gpioEventListener, bouncetime=300)
            
            self.log.debug("GPIOMonitor: Started")
            GPIO_STATUS = GPIO.input(self.ACTION_PIN)
            self.log.debug('GPIO STATUS on STARTUP: %s', str(GPIO_STATUS))
            
            #if GPIO_STATUS == 0:
            #~ self.log.debug('M730 check started')
            #~ reply = self.gcs.send("M730", group='*')
            #~ self.log.debug('M730 on startup:', reply)
            #~ if reply:
                #~ if len(reply) > 1:
                    #~ search = re.search('ERROR\s:\s(\d+)', reply[-2])
                    #~ if search != None:
                        #~ errorNumber = int(search.group(1))
                        #~ self.log.warning("Totumduino error no.: %s", errorNumber)
                        #~ self.manageErrorNumber(errorNumber)
                    #~ else:
                        #~ self.log.error("Totumduino unrecognized error: %s", reply[0])
        except:
            pass
            
        self.running = True
            
    def stop(self):
        """ Place holder """
        self.running = False
        
    def join(self):
        """ Place holder """
        pass
        
    def loop(self):
        while self.running:
            time.sleep(2)

def main():
    from ws4py.client.threadedclient import WebSocketClient
    from fabtotum.fabui.notify       import NotifyService
    from fabtotum.utils.pyro.gcodeclient import GCodeServiceClient
    from fabtotum.fabui.config import ConfigService
    from fabtotum.os.paths     import RUN_PATH
    import logging
    import argparse
    import os
    
    
    # Setup arguments
    parser = argparse.ArgumentParser()
    parser.add_argument("-L", "--log", help="Use logfile to store log messages.",   default='/var/log/fabui/gpiomonitor.log')
    parser.add_argument("-p", "--pidfile", help="File to store process pid.",       default=os.path.join(RUN_PATH, 'gpiomonitor.pid') )

    # Get arguments
    args = parser.parse_args()
    pidfile = args.pidfile
    
    with open(pidfile, 'w') as f:
        f.write( str(os.getpid()) )
    
    config = ConfigService()

    # Load configuration
    NOTIFY_FILE         = config.get('general', 'notify_file')
    ##################################################################
    SOCKET_HOST         = config.get('socket', 'host')
    SOCKET_PORT         = config.get('socket', 'port')
    ##################################################################
    GPIO_PIN    = config.get('gpio', 'pin')
    
    # Pyro GCodeService wrapper
    gcs = GCodeServiceClient()
    
    ws = WebSocketClient('ws://'+SOCKET_HOST +':'+SOCKET_PORT+'/')
    ws.connect();

    # Notification service
    ns = NotifyService(ws, NOTIFY_FILE, config)
    
    # Setup logger
    logger2 = logging.getLogger('GPIOMonitor')
    logger2.setLevel(logging.DEBUG)
    fh = logging.FileHandler(args.log, mode='w')

    #~ formatter = logging.Formatter("%(name)s - %(levelname)s : %(message)s")
    formatter = logging.Formatter("%(levelname)s : %(message)s")
    fh.setFormatter(formatter)
    fh.setLevel(logging.DEBUG)
    logger2.addHandler(fh)
    
    gpioMonitor = GPIOMonitor(ns, gcs, logger2, GPIO_PIN)
    gpioMonitor.start()
    gpioMonitor.loop()
    

if __name__ == "__main__":
    main()

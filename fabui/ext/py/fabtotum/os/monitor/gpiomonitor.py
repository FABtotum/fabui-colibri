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
from fabtotum.fabui.config import ConfigService

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
        
    def gpioEventListener(self, chanel):
        """
        Triggered when a level change on a pin is detected.
        """
        try:
            self.log.debug("====== START ============")
            GPIO_STATUS = GPIO.input(self.ACTION_PIN)
            self.log.debug('GPIO STATUS: %s', str(GPIO_STATUS))
            
            if GPIO_STATUS == 0:
                reply = self.gcs.send("M730", group='*')
                #print 'M730:', reply
                print "Checking"
                if reply:
                    if len(reply) > 1:
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
        except:
            pass

    def manageErrorNumber(self, error):
        alertErrors = [110]
        shutdownErrors = [120, 121]
        #~ terminateErrors = [100, 101, 102, 106, 107, 108, 109]
        terminateErrors = [100, 101, 102]
        errorType = 'emergency'
        
        if error in shutdownErrors:
            self.log.info("shutdown")
            # TODO: trigger shutdown
            return None
        elif error in alertErrors:
            errorType = 'alert'
            self.gcs.send('M999', block=False, group='*')
        elif error in terminateErrors:
            self.ns.notify(errorType, {'code': error} )
            self.gcs.terminate()
        
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
            
            if GPIO_STATUS == 0:
                reply = self.gcs.send("M730", group='*')
                if reply:
                    if len(reply) > 1:
                        search = re.search('ERROR\s:\s(\d+)', reply[-2])
                        if search != None:
                            errorNumber = int(search.group(1))
                            self.log.warning("Totumduino error no.: %s", errorNumber)
                            self.manageErrorNumber(errorNumber)
                        else:
                            self.log.error("Totumduino unrecognized error: %s", reply[0])
        except:
            pass
            
    def stop(self):
        """ Place holder """
        pass
        
    def join(self):
        """ Place holder """
        pass

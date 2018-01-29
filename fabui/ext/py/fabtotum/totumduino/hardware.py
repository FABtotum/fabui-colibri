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
import time
    
# Import external modules
import serial
try:
    import RPi.GPIO as GPIO
except:
    pass

# Import internal modules
from fabtotum.fabui.config import ConfigService

def reset():
    
    print "TOTUMDUINO: reset"
    
    config = ConfigService()

    reset_pin_s = config.get('totumduino', 'reset_pin', '17')
    reset_pins = reset_pin_s.split(',')

    try:
        #GPIO.setmode(GPIO.BOARD)
        GPIO.setmode(GPIO.BCM)
        GPIO.setwarnings(False)

        for pin in reset_pins:
            reset_pin = int(pin)

            GPIO.setup(reset_pin, GPIO.OUT)
            GPIO.output(reset_pin, GPIO.HIGH)
            time.sleep(0.5)
            GPIO.output(reset_pin, GPIO.LOW)

        time.sleep(0.5)

        for pin in reset_pins:
            reset_pin = int(pin)

            GPIO.output(reset_pin, GPIO.HIGH)

        GPIO.cleanup()

    except Exception as e:
        print e
        print "No GPIO support in QEMU simulation"
        
    time.sleep(1)

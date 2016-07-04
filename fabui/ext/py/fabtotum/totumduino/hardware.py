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
import RPi.GPIO as GPIO

# Import internal modules
from fabtotum.fabui.config import ConfigService

def reset():
    #GPIO.setmode(GPIO.BOARD)
    GPIO.setmode(GPIO.BCM)
    GPIO.setwarnings(False)

    #~ pin = 11
    pin = 17
    GPIO.setup(pin, GPIO.OUT)
    GPIO.output(pin, GPIO.HIGH)
    time.sleep(0.15)
    GPIO.output(pin, GPIO.LOW)
    time.sleep(0.15)
    GPIO.output(pin, GPIO.HIGH)

    GPIO.cleanup()

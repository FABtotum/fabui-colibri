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
    GPIO.setmode(GPIO.BOARD)
    GPIO.setwarnings(False)

    pin = 11
    GPIO.setup(pin, GPIO.OUT)
    GPIO.output(pin, GPIO.HIGH)
    time.sleep(0.15)
    GPIO.output(pin, GPIO.LOW)
    time.sleep(0.15)
    GPIO.output(pin, GPIO.HIGH)

    GPIO.cleanup()

def startup(gcs):
    config = ConfigService()
    
    try:
        color = config.get('units', 'color')
    except KeyError:
        color = {
            'r' : 255,
            'g' : 255,
            'b' : 255,
        }
    
    try:
        safety_door = config.get('units', 'safety')['door']
    except KeyError:
        safety_door = 0
    
    try:
        switch = config.get('units', 'switch')
    except KeyError:
        switch = 0
    
    try:
        collision_warning = config.get('units', 'safety')['collision-warning']
    except KeyError:
        collision_warning = 0
    
    gcs.send("M728")
    gcs.send("M402")
    gcs.send("M701 S"+str(color['r']))
    gcs.send("M702 S"+str(color['g']))
    gcs.send("M703 S"+str(color['b']))
        
    gcs.send("M732 S"+str(safety_door))
    gcs.send("M714 S"+str(switch))
        
    gcs.send("M734 S"+str(collision_warning))

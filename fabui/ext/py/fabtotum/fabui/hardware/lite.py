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

__authors__ = "Krios Mane, Daniel Kesler"
__license__ = "GPL - https://opensource.org/licenses/GPL-3.0"
__version__ = "1.0"

# Import standard python module
import re

# Import external modules

# Import internal modules
from fabtotum.fabui.hardware.common import loadFactoryFeeder, updateFactoryFeeder
from fabtotum.utils.translation import _, setLanguage


def hardware1000(gcodeSender, config, log, eeprom, factory):
    """
    Rev1000 CORE LITE: APRIL 2017 - xxx
    - RPi3
    """
    log.info("Rev1000 - Lite")
    
    #invert x endstop logic
    gcodeSender.send("M747 X1", group='bootstrap')
    #set maximum feedrate
    gcodeSender.send("M203 X550.00 Y550.00 Z15.00", group='bootstrap')
    
    config.set('settings', 'hardware.id', 1000)
    config.set('settings', 'feeder.show', False)
    config.set('settings', 'hardware.camera.available', False)
    config.save('settings')
    
    feeder = loadFactoryFeeder(config)
    steps_per_unit = float(feeder['steps_per_unit'])
    steps_per_angle = float(feeder['steps_per_angle'])
    feeder['max_feedrate'] = 23.00
    
    if config.is_firstboot():
        if factory:
            steps_per_unit = float(factory['feeder']['steps_per_unit'])
            steps_per_angle = float(factory['feeder']['steps_per_angle'])
        else:
            steps_per_unit = 1524
            steps_per_angle = 88.888889
            
        feeder['steps_per_unit'] = steps_per_unit
        feeder['steps_per_angle'] = steps_per_angle
        
        updateFactoryFeeder(config, feeder)
        config.save_feeder_info('built_in_feeder', feeder)

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

def customHardware(gcodeSender, config, log, eeprom, factory):
    """
    Revision for customs edits
    """
    log.info("Custom Hardware")
    
    logic = 1 if int(config.get('settings', 'custom.invert_x_endstop_logic')) else 0
    
    config.set('settings', 'hardware.id', eeprom['batch_number'])
    #set x endstop logic
    gcodeSender.send("M747 X{0}".format(logic), group='bootstrap')
    
    # custom overrides
    custom_overrides = config.get('settings', 'custom.overrides').strip().split('\n')
     
    for line in custom_overrides:
        if line != "" :
            log.info("Custom override: {0}".format(line))
            gcodeSender.send(line, group='bootstrap')
    
    config.save('settings')

            
def hardware1(gcodeSender, config, log, eeprom, factory):
    """
    Rev1: September 2014 - May 2015
    - Original FABtotum
    """
    gcodeSender.send("M203 X250.00 Y250.00 Z15.00", group='bootstrap')
    config.set('settings', 'hardware.id', 1)
    config.set('settings', 'feeder.engage', True)
    config.set('settings', 'feeder.available', True)
    config.set('settings', 'scan.available', True)
    config.set('settings', 'hardware.camera.available', True)
    config.save('settings')
    
    
    if config.is_firstboot():
        feeder = loadFactoryFeeder(config)
        updateFactoryFeeder(config, feeder)
        config.save_feeder_info('built_in_feeder', feeder)
    
    log.info("Rev1")
    

def hardware2(gcodeSender, config, log, eeprom, factory):
    """
    Rev2: June 2015 - August 2015
    - Simplified Feeder (Removed the disengagement and engagement procedure), if you want you can update it easily following this Tutorial: Feeder update.
    - Bowden tube improvement (Added a protection external sleeve to avoid the bowden tube get stuck in the back panel).
    - Endstops logic inverted.
    """
    #invert x endstop logic
    gcodeSender.send("M747 X1", group='bootstrap')
    #set maximum feedrate
    gcodeSender.send("M203 X250.00 Y250.00 Z15.00", group='bootstrap')
    #save settings
    #gcodeSender.send("M500", group='bootstrap')
    
    #~ eeprom = read_eeprom(gcodeSender)
    
    config.set('settings', 'hardware.id', 2)
    config.set('settings', 'feeder.engage', True)
    config.set('settings', 'feeder.available', True)
    config.set('settings', 'hardware.camera.available', True)
    config.set('settings', 'scan.available', True)
    config.save('settings')
    
    if config.is_firstboot():
        feeder = loadFactoryFeeder(config)
        updateFactoryFeeder(config, feeder)
        config.save_feeder_info('built_in_feeder', feeder)
        
    log.info("Rev2")


def hardware3(gcodeSender, config, log, eeprom, factory):
    """
    Rev3: Aug 2015 - Jan 2016
    - Back panel modified to minimize bowden tube collisions
    - Hotplate V2 as standard duty hotplate
    - Reed sensor (Contactless sensor for the frontal door)
    - Head V1 (hybrid) discontinued
    - Milling Head V2 (store.fabtotum.com/eu/store/milling-head-v2.html).
    - Print head V2 (store.fabtotum.com/eu/store/printing-head-v2.html).
    """
    #invert x endstop logic
    gcodeSender.send("M747 X1", group='bootstrap')
    #set maximum feedrate
    gcodeSender.send("M203 X250.00 Y250.00 Z15.00", group='bootstrap')
    #save settings
    #gcodeSender.send("M500", group='bootstrap')
    
    #eeprom = read_eeprom(gcodeSender)
    
    config.set('settings', 'hardware.id', 3)
    config.set('settings', 'feeder.engage', False)
    config.set('settings', 'feeder.available', True)
    config.set('settings', 'hardware.camera.available', True)
    config.set('settings', 'scan.available', True)
    config.save('settings')       
    
    if config.is_firstboot():
        feeder = loadFactoryFeeder(config)
        updateFactoryFeeder(config, feeder)
        config.save_feeder_info('built_in_feeder', feeder)
    
    log.info("Rev3")

def hardware4(gcodeSender, config, log, eeprom, factory):
    """
    Rev4(CORE): Jan 2016 - xxx
    """
    #invert x endstop logic
    gcodeSender.send("M747 X1", group='bootstrap')
    #set maximum feedrate
    gcodeSender.send("M203 X250.00 Y250.00 Z15.00", group='bootstrap')
    #save settings
    #gcodeSender.send("M500", group='bootstrap')
    
    #eeprom = read_eeprom(gcodeSender)
    
    config.set('settings', 'hardware.id', 4)
    config.set('settings', 'feeder.engage', False)
    config.set('settings', 'feeder.available', True)
    config.set('settings', 'hardware.camera.available', True)
    config.set('settings', 'scan.available', True)
    config.save('settings')
    if config.is_firstboot():
        feeder = loadFactoryFeeder(config)
        updateFactoryFeeder(config, feeder)
        config.save_feeder_info('built_in_feeder', feeder)
    log.info("Rev4")


def hardware5(gcodeSender, config, log, eeprom, factory):
    """
    Rev5(CORE): Oct 2016 - xxx
    - RPi3
    """
    #invert x endstop logic
    gcodeSender.send("M747 X1", group='bootstrap')
    #set maximum feedrate
    gcodeSender.send("M203 X250.00 Y250.00 Z15.00", group='bootstrap')
    #save settings
    config.set('settings', 'hardware.id', 5)
    config.set('settings', 'feeder.engage', False)
    config.set('settings', 'feeder.available', True)
    config.set('settings', 'hardware.camera.available', True)
    config.set('settings', 'scan.available', True)
    config.save('settings')
    
    if config.is_firstboot():
        feeder = loadFactoryFeeder(config)
        updateFactoryFeeder(config, feeder)
        config.save_feeder_info('built_in_feeder', feeder)
        
    log.info("Rev5")

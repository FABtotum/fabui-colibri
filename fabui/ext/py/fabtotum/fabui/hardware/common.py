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
import os
import re
import json

from fabtotum.utils.translation import _, setLanguage

def loadFactoryFeeder(config):
    fabui_path = config.get('general', 'fabui_path')
    
    try:
        feeder_file = os.path.join(fabui_path, 'feeders', 'built_in_feeder.json')
        
        with open(feeder_file) as json_f:
            info = json.load(json_f)
            
            if config.is_firstboot() and os.path.exists('/mnt/live/mnt/boot/factory/feeders/built_in_feeder.json') == False: ### ONLY At FIRST BOOT CHECK FOR  UNIT STEPS
                hw_id =  int(config.get('settings', 'hardware.id'))
                if(hw_id < 4):
                    steps_per_unit = 3048.16
                    steps_per_angle = 177.777778
                    max_feedrate = 12
                else:
                    steps_per_unit = 1524
                    steps_per_angle = 88.888889
                    max_feedrate = 23
                    
                info['steps_per_unit'] = steps_per_unit
                info['steps_per_angle'] = steps_per_angle
                info['max_feedrate'] = max_feedrate
            
            return info
    except:
        
        hw_id =  int(config.get('settings', 'hardware.id'))
        
        if(hw_id < 4):
            steps_per_unit = 3048.16
            steps_per_angle = 177.777778
            max_feedrate = 12
        else:
            steps_per_unit = 1524
            steps_per_angle = 88.888889
            max_feedrate = 23
            
        return {
                "name": "Built-in feeder",
                "description": "Built-in feeder (4th axis)",
                "link": "",
                "custom_gcode": "",
                "tube_length": 770,
                "steps_per_unit": steps_per_unit,
                "steps_per_angle": steps_per_angle,
                "max_acceleration": 100,
                "max_feedrate": max_feedrate,
                "max_jerk": 1,
                "retract_acceleration": 100,
                "retract_feedrate": 12,
                "retract_amount": 4,
                "factory": 1
            }

def updateFactoryFeeder(config, info):
    fabui_path = config.get('general', 'fabui_path')
    
    feeder_file = os.path.join(fabui_path, 'feeders', 'built_in_feeder.json')
    with open(feeder_file, 'w') as json_f:
        json.dump(info, json_f, sort_keys=True, indent=4)
        
""" CORE Default settings """
def defaultCoreSettings(gcodeSender, config, log, eeprom, factory):
    log.info("Applying default settings for CORE version")
    #invert x endstop logic
    gcodeSender.send("M747 X1", group='bootstrap')
    #set maximum feedrate
    gcodeSender.send("M203 X250.00 Y250.00 Z15.00", group='bootstrap')
    config.set('settings', 'feeder.engage', False)
    config.set('settings', 'feeder.available', True)
    config.set('settings', 'hardware.camera.available', False)
    config.set('settings', 'scan.available', False)
    
    if config.is_firstboot():
        feeder = loadFactoryFeeder(config)
        updateFactoryFeeder(config, feeder)
        config.save_feeder_info('built_in_feeder', feeder)
    
    config.save('settings')
    
""" CORE PRO Default settings """
def defaultProSettings(gcodeSender, config, log, eeprom, factory):
    log.info("Applying default settings for CORE PRO version")
    #invert x endstop logic
    gcodeSender.send("M747 X1", group='bootstrap')
    #set maximum feedrate
    gcodeSender.send("M203 X250.00 Y250.00 Z15.00", group='bootstrap')
    config.set('settings', 'feeder.engage', False)
    config.set('settings', 'feeder.available', False)
    config.set('settings', 'hardware.camera.available', False)
    config.set('settings', 'scan.available', False)
    config.save('settings')

""" CORE HYDRA Default settings """
def defaultHydraSettings(gcodeSender, config, log, eeprom, factory):
    log.info("Applying default settings for CORE HYDRA version")
    config.set('settings', 'feeder.engage', False)
    config.set('settings', 'feeder.available', False)
    config.set('settings', 'hardware.camera.available', False)
    config.set('settings', 'hardware.bed.enable', False)
    config.set('settings', 'scan.available', False)
    config.save('settings')

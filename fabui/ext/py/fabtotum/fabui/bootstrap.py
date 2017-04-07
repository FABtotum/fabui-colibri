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

__authors__ = "Daniel Kesler"
__license__ = "GPL - https://opensource.org/licenses/GPL-3.0"
__version__ = "1.0"

# Import standard python module
import gettext
import json
import os, re

# Import external modules

# Import internal modules
from fabtotum.utils.translation import _, setLanguage
from fabtotum.fabui.config import ConfigService


def read_eeprom(gcodeSender):
        
    def serialize(string_source, regex_to_serach, keys):
        match = re.search(regex_to_serach, string_source, re.IGNORECASE)
        if match != None:
            string = match.group(1)
            object = {}
            object.update({'string':string})
            for index in keys:
                match_temp = re.search(index+'([0-9.]+)', string, re.IGNORECASE)
                if match_temp != None:
                    val = match_temp.group(1)
                    object.update({index:val})
            return object
            
    def getServoEndstopValues(string_source):
        match = re.search('Servo\sEndstop\ssettings:\sR:\s([0-9.]+)\sE:\s([0-9.]+)', string_source, re.IGNORECASE)
        if match != None:
            object = {'r': match.group(1), 'e': match.group(2)}
            return object
    
    def getBatchNumber(string_source):
        match = re.search('Batch\sNumber:\s([0-9.]+)', string_source, re.IGNORECASE)
        if match != None:
            value = match.group(1)
            return value
    #reply = app.macro('M503', None, 1, _("Reading settings from eeprom"), verbose=False)
    reply = gcodeSender.send('M503', group='bootstrap')

    eeprom = {}

    for line in reply:
        line = line.strip()
        
        if line.startswith('M92 '):
            eeprom["steps_per_unit"] = serialize(line, '(M92\sX[0-9.]+\sY[0-9.]+\sZ[0-9.]+\sE[0-9.]+)', ['x', 'y', 'z', 'e'])
        elif line.startswith('M203'):
            eeprom["maximum_feedrates"] = serialize(line, '(M203\sX[0-9.]+\sY[0-9.]+\sZ[0-9.]+\sE[0-9.]+)', ['x', 'y', 'z', 'e'])
        elif line.startswith('M201'):
            eeprom["maximum_accelaration"] = serialize(line, '(M201\sX[0-9.]+\sY[0-9.]+\sZ[0-9.]+\sE[0-9.]+)', ['x', 'y', 'z', 'e'])
        elif line.startswith('M204'):
            eeprom["acceleration"] = serialize(reply[9], '(M204\sS[0-9.]+\sT1[0-9.]+)', ['s', 't1'])
        elif line.startswith('M205'):
           eeprom["advanced_variables"] = serialize(line,'(M205\sS[0-9.]+\sT0[0-9.]+\sB[0-9.]+\sX[0-9.]+\sZ[0-9.]+\sE[0-9.]+)', ['s', 't', 'b', 'x', 'z', 'e'])
        elif line.startswith('M206'):
            eeprom["home_offset"] = serialize(line,'(M206\sX[0-9.]+\sY[0-9.]+\sZ[0-9.]+)', ['x', 'y', 'z'])
        elif line.startswith('M31'):
            eeprom["pid"] = serialize(line,'(M301\sP[0-9.]+\sI[0-9.]+\sD[0-9.]+)', ['p', 'i', 'd'])
        elif line.startswith('Z Probe Length') or line.startswith('Probe Length'):
            eeprom["probe_length"] = line.split(':')[1].strip()
        elif line.startswith('Servo Endstop'):
            eeprom["servo_endstop"] = getServoEndstopValues(line)
        elif line.startswith('Batch Number'):
            eeprom['batch_number'] = getBatchNumber(line)
    
    return eeprom

def updateFactoryFeeder(config, info):
    fabui_path = config.get('general', 'fabui_path')
    
    feeder_file = os.path.join(fabui_path, 'feeders', 'built_in_feeder.json')
    with open(feeder_file, 'w') as json_f:
        json.dump(info, json_f, sort_keys=True, indent=4)

def loadFctoryFeeder(config):
    fabui_path = config.get('general', 'fabui_path')
    
    try:
        feeder_file = os.path.join(fabui_path, 'feeders', 'built_in_feeder.json')
        with open(feeder_file) as json_f:
            info = json.load(json_f)
            return info
    except:
        return {
                "name": "Built-in feeder",
                "description": "Built-in feeder (4th axis)",
                "link": "",
                "custom_gcode": "",
                "tube_length": 770,
                "steps_per_unit": 3048.16,
                "steps_per_angle": 177.777778,
                "max_acceleration": 100,
                "max_feedrate": 12,
                "max_jerk": 1,
                "retract_acceleration": 100,
                "retract_feedrate": 12,
                "retract_amount": 4,
                "factory": 1
            }

def customHardware(gcodeSender, config, log, eeprom):
    """
    Revision for customs edits
    """
    log.info("Custom Hardware")
    
    logic = 1 if int(config.get('settings', 'custom.invert_x_endstop_logic')) else 0

    #set x endstop logic
    gcodeSender.send("M747 X{0}".format(logic), group='bootstrap')
    
    # custom overrides
    custom_overrides = config.get('settings', 'custom.overrides').strip().split('\n')
     
    for line in custom_overrides:
        if line != "" :
            log.info("Custom override: {0}".format(line))
            gcodeSender.send(line, group='bootstrap')

def hardware1(gcodeSender, config, log, eeprom):
    """
    Rev1: September 2014 - May 2015
    - Original FABtotum
    """
    
    #eeprom = read_eeprom(gcodeSender)
    
    config.set('settings', 'hardware.id', 1)
    config.set('settings', 'feeder.show', True)
    config.save('settings')
    
    feeder = loadFctoryFeeder(config)
    steps_per_unit = float(feeder['steps_per_unit'])
    if steps_per_unit != 3048.16:
        steps_per_unit = 3048.16
        steps_per_angle = 177.777778
        
        feeder['steps_per_unit'] = steps_per_unit
        feeder['steps_per_angle'] = steps_per_angle
        
        updateFactoryFeeder(config, feeder)
        config.save_feeder_info('built_in_feeder', feeder)
    
    log.info("Rev1")
    
def hardware2(gcodeSender, config, log, eeprom):
    """
    Rev2: June 2015 - August 2015
    - Simplified Feeder (Removed the disengagement and engagement procedure), if you want you can update it easily following this Tutorial: Feeder update.
    - Bowden tube improvement (Added a protection external sleeve to avoid the bowden tube get stuck in the back panel).
    - Endstops logic inverted.
    """
    #invert x endstop logic
    gcodeSender.send("M747 X1", group='bootstrap')
    #set maximum feedrate
    gcodeSender.send("M203 X550.00 Y550.00 Z15.00", group='bootstrap')
    #save settings
    #gcodeSender.send("M500", group='bootstrap')
    
    #~ eeprom = read_eeprom(gcodeSender)
    
    config.set('settings', 'hardware.id', 2)
    config.set('settings', 'feeder.show', True)
    config.save('settings')
    
    feeder = loadFctoryFeeder(config)
    feeder['max_feedrate'] = 12.00
    steps_per_unit = float(feeder['steps_per_unit'])
    if steps_per_unit != 3048.16:
        steps_per_unit = 3048.16
        steps_per_angle = 177.777778
        
        feeder['steps_per_unit'] = steps_per_unit
        feeder['steps_per_angle'] = steps_per_angle
    
    updateFactoryFeeder(config, feeder)
    config.save_feeder_info('built_in_feeder', feeder)
    
    log.info("Rev2")
    
def hardware3(gcodeSender, config, log, eeprom):
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
    gcodeSender.send("M203 X550.00 Y550.00 Z15.00", group='bootstrap')
    #save settings
    #gcodeSender.send("M500", group='bootstrap')
    
    #eeprom = read_eeprom(gcodeSender)
    
    config.set('settings', 'hardware.id', 3)
    config.set('settings', 'feeder.show', False)
    config.save('settings')
    
    feeder = loadFctoryFeeder(config)
    feeder['max_feedrate'] = 12.00
    steps_per_unit = float(feeder['steps_per_unit'])
    if steps_per_unit != 3048.16:
        steps_per_unit = 3048.16
        steps_per_angle = 177.777778
        
        feeder['steps_per_unit'] = steps_per_unit
        feeder['steps_per_angle'] = steps_per_angle
    
    updateFactoryFeeder(config, feeder)
    config.save_feeder_info('built_in_feeder', feeder)
    
    log.info("Rev3")
    
    
def hardware4(gcodeSender, config, log, eeprom):
    """
    Rev4(CORE): Jan 2016 - xxx
    - TBA
    """
    #invert x endstop logic
    gcodeSender.send("M747 X1", group='bootstrap')
    #set maximum feedrate
    gcodeSender.send("M203 X550.00 Y550.00 Z15.00", group='bootstrap')
    #save settings
    #gcodeSender.send("M500", group='bootstrap')
    
    #eeprom = read_eeprom(gcodeSender)
    
    config.set('settings', 'hardware.id', 4)
    config.set('settings', 'feeder.show', False)
    config.save('settings')
    
    feeder = loadFctoryFeeder(config)
    steps_per_unit = float(feeder['steps_per_unit'])
    feeder['max_feedrate'] = 12.00
    if steps_per_unit != 1524:
        steps_per_unit = 1524
        steps_per_angle = 88.888889
        
        feeder['steps_per_unit'] = steps_per_unit
        feeder['steps_per_angle'] = steps_per_angle
    
    updateFactoryFeeder(config, feeder)
    config.save_feeder_info('built_in_feeder', feeder)
    log.info("Rev4")
    
def hardware5(gcodeSender, config, log, eeprom):
    """
    Rev5(CORE): Oct 2016 - xxx
    - RPi3
    """
    #invert x endstop logic
    gcodeSender.send("M747 X1", group='bootstrap')
    #set maximum feedrate
    gcodeSender.send("M203 X550.00 Y550.00 Z15.00 E23.00", group='bootstrap')
    #save settings
    #gcodeSender.send("M500", group='bootstrap')
    
    #eeprom = read_eeprom(gcodeSender)
    
    config.set('settings', 'hardware.id', 5)
    config.set('settings', 'feeder.show', False)
    config.save('settings')
    
    feeder = loadFctoryFeeder(config)
    steps_per_unit = float(feeder['steps_per_unit'])
    feeder['max_feedrate'] = 23.00
    if steps_per_unit != 1524:
        steps_per_unit = 1524
        steps_per_angle = 88.888889
        
        feeder['steps_per_unit'] = steps_per_unit
        feeder['steps_per_angle'] = steps_per_angle
    
    updateFactoryFeeder(config, feeder)
    config.save_feeder_info('built_in_feeder', feeder)
    log.info("Rev5")
    
def hardware6(gcodeSender, config, log, eeprom):
    """
    Rev6(CORE Lite): April 2017
    """
    log.info("Rev6 - Lite")
 

def configure_head(gcs, config, log):

    try:
        log.info("Initializing HEAD")
            
        head = config.get_current_head_info()
        if head == None:
            log.error("Failed to read head configuration")
            return
            
        pid          = head.get('pid', '')
        th_idx       = int(head.get('thermistor_index', 0))
        mode         = int(head.get('working_mode', 0))
        offset       = float(head.get('nozzle_offset', 0))
        fw_id        = int(head.get('fw_id',0))
        max_temp     = int(head.get('max_temp',0)) + 15
        custom_gcode = head.get('custom_gcode','')
        tool         = head.get('tool', '')
        
        probe_length  = float(config.get('settings', 'zprobe.length', 0))
        
        # Set installed head
        if fw_id is not None:
            gcs.send( "M793 S{0}".format( fw_id ), group='bootstrap' )
        
        # Set head PID
        if pid != "":
            gcs.send( head['pid'], group='bootstrap' )
        
        # Set Thermistor index
        gcs.send( "M800 S{0}".format( th_idx ), group='bootstrap' )
        
        # Set max_temp
        if max_temp > 25:
            gcs.send( "M801 S{0}".format( max_temp ), group='bootstrap' )
        
        # Set nozzle offset
        #~ if offset:
            #~ app.macro( "M206 Z-{0}".format( offset ),   "ok", 2, _("Configuring nozzle offset"))
        
        # Set probe offset
        if probe_length:
            gcs.send( "M710 S{0}".format( probe_length ), group='bootstrap' )
        
        # Working mode
        gcs.send( "M450 S{0}".format( mode ), group='bootstrap' )
        
        #Set tool
        if tool != "" :
            gcs.send( head['tool'], group='bootstrap' )
        
        for line in custom_gcode.split('\n'):
            if line:
                code = line.split(';')[0]
                if code:
                    gcs.send( code, group='bootstrap' )
        
    except Exception as e:
        print log.error("head configuration failed: {0}".format(str(e)))
    
def configure_feeder(gcs, config, log):
    try:
        
        log.info("Initializing FEEDER")
        
        feeder = config.get_current_feeder_info()
        if feeder == None:
            log.error("Failed to read feeder configuration")
            return
            
        steps_per_unit       = float(feeder['steps_per_unit'])
        max_feedrate         = float(feeder['max_feedrate'])
        max_acceleration     = float(feeder['max_acceleration'])
        max_jerk             = float(feeder['max_jerk'])
        retract_acceleration = float(feeder['retract_acceleration'])

        gcs.send("M92 E{0}".format(steps_per_unit),        group='bootstrap' )
        gcs.send("G92 E0".format(steps_per_unit),          group='bootstrap' )
        gcs.send("M201 E{0}".format(max_acceleration),     group='bootstrap' )
        gcs.send("M203 E{0}".format(max_feedrate),         group='bootstrap' )
        gcs.send("M205 E{0}".format(max_jerk),             group='bootstrap' )
        gcs.send("M204 T{0}".format(retract_acceleration), group='bootstrap' )
            
    except Exception as e:
        print log.error("Feeder configuration failed: {0}".format(str(e)))

def hardwareBootstrap(gcs, config = None, logger = None):
    if not config:
        config = ConfigService()
    
    if logger:
       log = logger
    else:
        log = logging.getLogger('GCodeService')
        ch = logging.StreamHandler()
        ch.setLevel(logging.DEBUG)
        formatter = logging.Formatter("%(levelname)s : %(message)s")
        ch.setFormatter(formatter)
        log.addHandler(ch)

    config.reload()
    
        
    try:
        color = config.get('settings', 'color')
    except KeyError:
        color = {
            'r' : 255,
            'g' : 255,
            'b' : 255,
        }
    
    try:
        safety_door = config.get('settings', 'safety.door')
    except KeyError:
        safety_door = 0
    
    try:
        switch = config.get('settings', 'switch')
    except KeyError:
        switch = 0
    
    try:
        collision_warning = config.get('settings', 'safety.collision_warning')
    except KeyError:
        collision_warning = 0

    gcs.atomic_begin(group='bootstrap')

    # clean output
    #reply = gcs.send('G1', group='bootstrap')
    
    # read EEPROM
    eeprom = read_eeprom(gcs)
    
    try:
        hardwareID = eeprom['batch_number']
    except Exception as e:
        log.error("cannot read batch number")
        log.error("batch number set to 1")
        hardwareID = 1
    
    # Raise probe
    gcs.send('M402', group='bootstrap')
    # Send ALIVE
    gcs.send('M728', group='bootstrap')

    # Set ambient colors
    gcs.send("M701 S{0}".format(color['r']), group='bootstrap')
    gcs.send("M702 S{0}".format(color['g']), group='bootstrap')
    gcs.send("M703 S{0}".format(color['b']), group='bootstrap')
    
    # Set safety door open warnings: enabled/disabled
    gcs.send("M732 S{0}".format(safety_door), group='bootstrap')
    # Set collision warning: enabled/disabled
    gcs.send("M734 S{0}".format(collision_warning), group='bootstrap')
    # Set homing preferences
    gcs.send("M714 S{0}".format(switch), group='bootstrap')
                
    # Execute version specific intructions
    HW_VERSION_CMDS = {
        'custom' : customHardware,
        '1'      : hardware1,
        '2'      : hardware2,
        '3'      : hardware3,
        '4'      : hardware4,
        '5'      : hardware5,
        '6'      : hardware6,
    }
    
    if config.get('settings', 'settings_type') == 'custom':
        customHardware(gcs, config, log, eeprom)
    elif hardwareID in HW_VERSION_CMDS:
        HW_VERSION_CMDS[hardwareID](gcs, config, log, eeprom)
    else:
        log.error("Unsupported hardware version: %s", hardwareID)
        log.error("Forced to hardware1")
        hardware1(gcs, config, log, eeprom)
    
    configure_head(gcs, config, log)
    configure_feeder(gcs, config, log)

    gcs.atomic_end()

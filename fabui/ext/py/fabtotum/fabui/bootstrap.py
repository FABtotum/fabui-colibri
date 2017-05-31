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
from fabtotum.fabui.hardware.all import PRESET_MAP

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
        min_temp     = int(head.get('min_temp', 0))
        custom_gcode = head.get('custom_gcode','')
        tool         = head.get('tool', '')
        
        probe_length  = float(config.get('settings', 'probe.length', 0))
        
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
            
        # Set min_temp
        if min_temp > 0:
            gcs.send( "M302 S{0}".format( min_temp ), group='bootstrap' )
        
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
    
      
    try:
        wire_end = config.get('settings', 'wire_end', 0)
    except KeyError:
        wire_end = 0
    
    probe = {}  
    probe['extend']  = config.get('settings', 'probe.e', 127)
    probe['retract'] = config.get('settings', 'probe.r', 25)
        
    ## MANDATORY - AFTER THAT LINE YOU CAN SEND COMMANDS
    gcs.atomic_begin(group='bootstrap')

    # read EEPROM
    eeprom = read_eeprom(gcs)
    
    # reset EEPROM (to prevent any mysterious bug)
    log.info("Reset EEPROM")
    gcs.send('M502', group='bootstrap')
    
    # read Factory settings
    factory = None
    if os.path.exists('/mnt/live/mnt/boot/factory.json'):
        try:
            with open('/mnt/live/mnt/boot/factory.json') as json_f:
                factory = json.load(json_f)
        except:
            # Continue if the file is not there
            pass
        
    try:
        hardwareID = eeprom['batch_number']
    except Exception as e:
        log.error("cannot read batch number")
        hardwareID = config.get('settings', 'hardware.id', 1)
        log.error("batch number set to {0}".format(hardwareID))
    
    
    if config.is_firstboot():
        log.info("First Boot")
        
        if factory:
            
            probe['extend']  = factory['probe']['e']
            probe['retract'] = factory['probe']['r']
            probe['length']  = factory['probe']['length']
            hardwareID = factory['hardware']['id']
            
            log.info("Factory settings applied")
            
            config.set('settings', 'probe.e', probe['extend'])
            config.set('settings', 'probe.r', probe['retract'])
            config.set('settings', 'probe.length', probe['length'])
            config.set('settings', 'hardware.id', hardwareID)
            config.save('settings')
    
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
    #set wire_end enabled/disabled
    gcs.send("M805 S{0}".format(wire_end), group='bootstrap')
    #set probe extend angle
    gcs.send("M711 S{0}".format(probe['extend']), group='bootstrap')
    #set probe retract angle
    gcs.send("M712 S{0}".format(probe['retract']), group='bootstrap')
    
    # Execute version specific intructions
    if config.get('settings', 'settings_type') == 'custom':
        PRESET_MAP["custom"](gcs, config, log, eeprom, factory)
    elif hardwareID in PRESET_MAP:
        PRESET_MAP[hardwareID](gcs, config, log, eeprom, factory)
    else:
        log.error("Unsupported hardware version: %s", hardwareID)
        log.error("Forced to hardware1")
        PRESET_MAP["1"](gcs, config, log, eeprom, factory)
    
    configure_head(gcs, config, log)
    configure_feeder(gcs, config, log)
    
    gcs.atomic_end()

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

def zProbe(app, lang='en_US.UTF-8'):
    _ = setLanguage(lang)
    app.macro("M401", "ok", 5, _("Open probe"), verbose=False)
    data = app.macro("G30", "ok", 120, _("Probe position"), verbose=False)
    reply = data[0]
    
    print reply
    
    match = re.search("Feedrate: ([-|+0-9.]+)\sBed\sX:\s([-|+0-9.]+)\sY:\s([-|+0-9.]+)\sZ:\s([-|+0-9.]+)", reply, re.IGNORECASE)
    #  Feedrate: 200.00 Bed X: 10.00 Y: 10.00 Z: 38.53
    
    app.macro("G91",                "ok", 2,    _("Setting rel position"), verbose=False)
    app.macro("G0 Z5 F1000",        "ok", 20,   _("Moving bed away from the probe"), verbose=False)
    app.macro("M402", "ok", 5, _("Retract probe"), verbose=False)

    probe = {
        "x" : match.group(2),
        "y" : match.group(3),
        "z" : match.group(4)
        }    
    return probe
    

def getPosition(app, lang='en_US.UTF-8'):
    _ = setLanguage(lang)
    
    data = app.macro("M114", "ok", 2, _("Get position"), verbose=False)
    reply = data[0]
    position = None
    match = re.search('X:([-|+0-9.]+)\sY:([-|+0-9.]+)\sZ:([-|+0-9.]+)\sE:([-|+0-9.]+)\sCount\sX:\s([-|+0-9.]+)\sY:([-|+0-9.]+)\sZ:([-|+0-9.]+)', reply, re.IGNORECASE)
    
    if match != None:
        position = {
            "x" : match.group(1),
            "y" : match.group(2),
            "z" : match.group(3),
            "e" : match.group(4),
            "count": {
                "x" : match.group(5),
                "y" : match.group(6),
                "z" : match.group(7),
            }
        }
    
    return position

def getEeprom(app, lang='en_US.UTF-8'):
    _ = setLanguage(lang)
    
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
        
    def getProbeLength(string_source):
        match = re.search('Z\sProbe\sLength:\s([-|+][0-9.]+)', string_source, re.IGNORECASE)
        if match != None:
            value = match.group(1)
            return value
    
    def getBaudrate(string_source):
        match = re.search('Baudrate:\s([0-9.]+)', string_source, re.IGNORECASE)
        if match != None:
            value = match.group(1)
            return value
    
    def getInstalledHead(string_source):
        match = re.search('M793\sS([0-9.]+)', string_source, re.IGNORECASE)
        if match != None:
            value = match.group(1)
            return value
        
    def getBatchNumber(string_source):
        match = re.search('Batch\sNumber:\s([0-9.]+)', string_source, re.IGNORECASE)
        if match != None:
            value = match.group(1)
            return value
        
    def getFablinVersion(string_source):
        match = re.search('Version:\sV\s(\w.+)', string_source, re.IGNORECASE)
        if match != None:
            value = match.group(1)
            return value
        
    reply = app.macro('M503', '*', 1, _("Reading settings from eeprom"), verbose=False)
    
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
            eeprom["acceleration"] = serialize(line, '(M204\sS[0-9.]+\sT[0-9.]+)', ['s', 't'])
        elif line.startswith('M205'):
           eeprom["advanced_variables"] = serialize(line,'(M205\sS[0-9.]+\sT0[0-9.]+\sB[0-9.]+\sX[0-9.]+\sZ[0-9.]+\sE[0-9.]+)', ['s', 't', 'b', 'x', 'z', 'e'])
        elif line.startswith('M206'):
            eeprom["home_offset"] = serialize(line,'(M206\sX[0-9.]+\sY[0-9.]+\sZ[0-9.]+)', ['x', 'y', 'z'])
        elif line.startswith('M301'):
            eeprom["pid"] = serialize(line,'(M301\sP[0-9.]+\sI[0-9.]+\sD[0-9.]+)', ['p', 'i', 'd'])
        elif line.startswith('Z Probe Length') or line.startswith('Probe Length'):
            eeprom["probe_length"] = getProbeLength(line)
        elif line.startswith('Servo Endstop'):
            eeprom["servo_endstop"] = getServoEndstopValues(line)
        elif line.startswith('Baudrate'):
            eeprom["baudrate"] = getBaudrate(line)
        elif line.startswith('M793'):
            eeprom['installed_head'] = getInstalledHead(line)
        elif line.startswith('Batch Number'):
            eeprom['batch_number'] = getBatchNumber(line)
        elif line.startswith('Version'):
            eeprom['fablin_version'] = getFablinVersion(line)
    
    return eeprom

def get_versions(app, lang='en_US.UTF-8'):
    _ = setLanguage(lang)
    
    ### controller serail ID
    retr = app.macro("M760",   "ok", 1, _("Controller serial ID"), verbose=False)
    controller_serial_id = retr[0]
    ### controller control code
    retr = app.macro("M761",   "ok", 1, _("Controller control code"), verbose=False)
    controller_control_code = retr[0]
    ### board version
    retr = app.macro("M762",   "ok", 1, _("Board version"), verbose=False)
    board_version = retr[0]
    ### Production batch (hardware version)
    retr = app.macro("M763",   "ok", 1, _("Production batch"), verbose=False)
    production_batch = retr[0]
    ### Production batch control code
    retr = app.macro("M764",   "ok", 1, _("Production batch control code"), verbose=False)
    production_batch_control_code = retr[0]
    ### firmware version
    retr = app.macro("M765",   "ok", 1, _("Firmware version"), verbose=False)
    firmware_version = retr[0]
    try:
        firmware_version = firmware_version.split()[1]
    except:
        pass
    ### Firmware build date
    retr = app.macro("M766",   "ok", 1, _("Firmware build date"), verbose=False)
    firmware_build_date = retr[0]
    ### firmware author
    retr = app.macro("M767",   "ok", 1, _("Firmware author"), verbose=False)
    firmware_author = retr[0]
    
    return {
        'firmware' : {
            'version' : firmware_version,
            'build_date' : firmware_build_date,
            'author' : firmware_author
        },
        'production' : {
            'batch': production_batch,
            'control_code' : production_batch_control_code
        },
        'controller' : {
            'serial_id' : controller_serial_id,
            'control_code' : controller_control_code
        },
        'board' : {
            'version' : board_version
        }
    }

def configure_feeder(app, feeder_name, lang='en_US.UTF-8'):
    """
    """
    _ = setLanguage(lang)
    
    feeder = app.config.get_feeder_info(feeder_name)
    
    if not feeder:
        return False
    
    steps_per_unit = float(feeder['steps_per_unit'])
    max_feedrate = float(feeder['max_feedrate'])
    max_acceleration = float(feeder['max_acceleration'])
    max_jerk = float(feeder['max_jerk'])
    retract_acceleration = float(feeder['retract_acceleration'])

    app.macro("M92 E{0}".format(steps_per_unit),            "ok", 1,   _("Setting E steps_per_unit") )
    app.macro("G92 E0".format(steps_per_unit),              "ok", 1,   _("Setting E position to 0"), verbose=False )
    app.macro("M201 E{0}".format(max_acceleration),         "ok", 1,   _("Setting E acceleration") )
    app.macro("M203 E{0}".format(max_feedrate),             "ok", 1,   _("Setting E feedrate") )
    app.macro("M205 E{0}".format(max_jerk),                 "ok", 1,   _("Setting E jerk") )
    app.macro("M204 T{0}".format(retract_acceleration),     "ok", 1,   _("Setting retract acceleration") )
    
    return True
    
def configure_head(app, head_name, lang='en_US.UTF-8'):
    _ = setLanguage(lang)
    
    # Load Head
    head = app.config.get_head_info(head_name)
    if not head:
        return False
    #~ try:
        #~ head_file = os.path.join( app.config.get('hardware', 'heads'), head_name + '.json');
    
        #~ with open(head_file) as json_f:
            #~ head = json.load(json_f)
    #~ except Exception as e:
        #~ app.trace( str(e) )
        #~ return False
        
    pid     = head.get('pid', '')
    th_idx  = int(head.get('thermistor_index', 0))
    mode    = int(head.get('working_mode', 0))
    offset  = float(head.get('nozzle_offset', 0))
    fw_id   = int(head.get('fw_id',0))
    max_temp= int(head.get('max_temp',230))
    probe_length  = float(app.config.get('settings', 'zprobe.length', 0))
    
    # Set installed head ID
    if fw_id is not None:
        #~ gcs.send( "M793 S{0}".format( fw_id ), group='bootstrap' )
        app.macro( "M793 S{0}".format( fw_id ),   "ok", 2, _("Setting soft ID to {0}").format(fw_id) )
    
    # Working mode
    app.macro( "M450 S{0}".format( mode ),   "ok", 2, _("Configuring working mode"))
    
    # Set head PID
    if pid != "":
        app.macro(head['pid'],   "ok *", 2, _("Configuring PID"))
        
    # Set Thermistor index
    app.macro( "M800 S{0}".format( th_idx ),   "ok", 2, _("Setting thermistor index to {0}").format(th_idx) )
    
    # Set max_temp
    if max_temp > 25:
        #~ gcs.send( "M801 S{0}".format( max_temp ), group='bootstrap' )
        app.macro( "M801 S{0}".format( max_temp ),   "ok", 2, _("Setting MAX temperature to {0}".format(max_temp)) )

    # Set nozzle offset
    #~ if offset:
        #~ app.macro( "M206 Z-{0}".format( offset ),   "ok", 2, _("Configuring nozzle offset"))
    
    # Set probe offset
    if probe_length:
        app.macro( "M710 S{0}".format( probe_length ),   "ok", 2, _("Configuring probe offset"))
    
    # Custom initialization code
    app.trace( _("Custom initialization") )
    for line in head.get('custom_gcode', '').split('\n'):
        if line:
            code = line.split(';')[0]
            app.macro( code, "ok*", 50, "hidden message", verbose=False)
    
    # Save settings
    #~ gcs.send( "M500", group='bootstrap' )
    return True

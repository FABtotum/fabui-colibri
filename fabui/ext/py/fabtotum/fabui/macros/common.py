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
import os, time
import re
import json

from fabtotum.utils.translation import _, setLanguage
from fabtotum.utils.plugin import activate_plugin, get_active_plugins, get_installed_plugins
from fabtotum.totumduino.format import parseG30, parseM114

def zProbe(app, lang='en_US.UTF-8'):
    _ = setLanguage(lang)
    app.macro("M401", "ok", 5, _("Open probe"), verbose=False)
    reply = app.macro("G30", "ok", 120, _("Probe position"), verbose=False)
    probe = parseG30(reply)
    
    app.macro("G91",                "ok", 2,    _("Setting rel position"), verbose=False)
    app.macro("G0 Z5 F1000",        "ok", 20,   _("Moving bed away from the probe"), verbose=False)
    app.macro("M402", "ok", 5, _("Retract probe"), verbose=False)

    return probe
    

def getPosition(app, lang='en_US.UTF-8'):
    _ = setLanguage(lang)
    
    reply = app.macro("M114", "ok", 120, _("Get position"), verbose=False)
    result = parseM114(reply)
    if result:
        return result

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
        match = re.search('Z\sProbe\sLength:\s([-|+]?[0-9.]+)', string_source, re.IGNORECASE)
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
    
    if feeder == None:
        return False
    
    steps_per_unit       = float(feeder['steps_per_unit'])
    max_feedrate         = float(feeder['max_feedrate'])
    max_acceleration     = float(feeder['max_acceleration'])
    max_jerk             = float(feeder['max_jerk'])
    retract_acceleration = float(feeder['retract_acceleration'])
    custom_gcode         = feeder.get('custom_gcode','')
    
    app.trace( _("Setting feeder values..."))
    
    app.macro("M92 E{0}".format(steps_per_unit),            "ok", 1,   _("Setting E steps per unit to {0}").format(steps_per_unit), verbose=False)
    app.macro("G92 E0",              						"ok", 1,   _("Setting E position to 0"), verbose=False )
    app.macro("M201 E{0}".format(max_acceleration),         "ok", 1,   _("Setting E acceleration to {0}").format(max_acceleration), verbose=False)
    app.macro("M203 E{0}".format(max_feedrate),             "ok", 1,   _("Setting E max feedrate to {0}").format(max_feedrate), verbose=False)
    app.macro("M205 E{0}".format(max_jerk),                 "ok", 1,   _("Setting E max jerk to {0}").format(max_jerk), verbose=False)
    app.macro("M204 T{0}".format(retract_acceleration),     "ok", 1,   _("Setting retract acceleration {0}").format(retract_acceleration), verbose=False)
    
    for line in custom_gcode.split('\n'):
            if line:
                code = line.split(';')[0]
                if code:
                    app.macro( code, "ok*", 50, "hidden message", verbose=False)
    
    return True

def configure_4thaxis(app, feeder_name, lang='en_US.UTF-8'):
    """
    """
    _ = setLanguage(lang)
    
    feeder = app.config.get_feeder_info(feeder_name)
    
    if feeder == None:
        return False
    
    steps_per_angle      = float(feeder['steps_per_angle'])
    max_feedrate         = float(feeder['max_feedrate'])
    max_acceleration     = float(feeder['max_acceleration'])
    max_jerk             = float(feeder['max_jerk'])
    retract_acceleration = float(feeder['retract_acceleration'])
    
    app.trace( _("Setting 4th-axis values..."))
    
    app.macro("M92 E{0}".format(steps_per_angle),   "ok", 1,  _("Setting A steps per degree to {0}").format(steps_per_angle), verbose=False)
    app.macro("G92 E0",              				"ok", 1,  _("Setting A position to 0"), verbose=False )
    app.macro("M201 E{0}".format(max_acceleration), "ok", 1,  _("Setting A acceleration to {0}").format(max_acceleration), verbose=False)
    app.macro("M203 E{0}".format(max_feedrate),     "ok", 1,  _("Setting A max feedrate to {0}").format(max_feedrate), verbose=False)
    app.macro("M205 E{0}".format(max_jerk),         "ok", 1,  _("Setting A max jerk to {0}").format(max_jerk), verbose=False)
    
    return True
    
def configure_head(app, head_name, lang='en_US.UTF-8'):
    _ = setLanguage(lang)
    
    # Load Head
    head = app.config.get_head_info(head_name)
    if head == None:
        return False
        
    pid          = head.get('pid', '')
    th_idx       = int(head.get('thermistor_index', 0))
    mode         = int(head.get('working_mode', 0))
    offset       = float(head.get('nozzle_offset', 0))
    fw_id        = int(head.get('fw_id',0))
    max_temp     = int(head.get('max_temp',230)) + 15
    min_temp     = int(head.get('min_temp', 0))
    # probe_length = float(app.config.get('settings', 'probe.length', 0))
    tool         = head.get('tool', '')
    plugins      = head.get('plugins', False)
    capabilities = head.get('capabilities', False)
    
    app.trace( _("Setting head values..."))
    
    
    # disable head
    app.macro( "M793 S0",   "ok", 2, _("Disabling previous installed head's settings"), verbose=False)
    
    # Working mode
    app.macro( "M450 S{0}".format( mode ),   "ok", 5, _("Configuring working mode"), verbose=False)
    
    # Set installed head ID
    if fw_id is not None:
        #~ gcs.send( "M793 S{0}".format( fw_id ), group='bootstrap' )
        app.macro( "M793 S{0}".format( fw_id ),   "ok", 2, _("Setting soft ID to {0}").format(fw_id), verbose=False)
     
    # Set head PID
    if pid != "":
        app.macro(head['pid'],   "ok *", 2, _("Configuring PID"), verbose=False)
        
    # Set Thermistor index
    app.macro( "M800 S{0}".format( th_idx ),   "ok", 2, _("Setting thermistor index to {0}").format(th_idx), verbose=False)
    
    # Set max_temp
    if max_temp > 25:
        app.macro( "M801 S{0}".format( max_temp ),   "ok", 2, _("Setting MAX temperature to {0}".format(max_temp)), verbose=False)
    
    # Set min_temp
    if min_temp > 0:
        app.macro(  "M302 S{0}".format( min_temp ),   "ok", 2, _("Setting MIN temperature to {0}".format(min_temp)), verbose=False)

    # Set nozzle offset
    #~ if offset:
        #~ app.macro( "M206 Z-{0}".format( offset ),   "ok", 2, _("Configuring nozzle offset"))
        
    # Set TOOL
    if tool != "":
        app.macro(head['tool'],   "ok", 2, _("Configuring tool"), verbose=False)
    
    # Set probe offset
    #if "print" in capabilities:
    #    if probe_length:
    #        app.macro( "M710 S{0}".format( probe_length ),   "ok", 2, _("Configuring probe offset"), verbose=False)
    
    # enable bed thermistor - disable serial port
    if "laser" in capabilities:
        app.macro("M563 P0 H4:5 S0",   "ok", 2, _("Configuring tool"), verbose=False)
    
    # Custom initialization code
    app.trace( _("Custom initialization"))
    for line in head.get('custom_gcode', '').split('\n'):
        if line:
            code = line.split(';')[0]
            app.macro( code, "ok*", 50, "hidden message", verbose=False)
    
    # Save settings
    #~ gcs.send( "M500", group='bootstrap' )
    if plugins:
        activated_plugins = get_active_plugins()
        installed_plugins = get_installed_plugins()
        app.trace( _("Check for plugins..") )
        for plugin in plugins:
            
            if (plugin not in installed_plugins):
                app.trace( _("Please install <strong>{0}</strong> plugin".format(plugin)) )
            elif (plugin not in activated_plugins):
                app.trace( _("Activating <strong>{0}</strong> plugin".format(plugin)) )
                activate_plugin(plugin)
            
    return True

def go_to_focal_point(app, head):
    """ go to laser focal point """
    
    is_laser_pro = app.config.is_laser_pro_head(head['fw_id'])
    
    try:
        laser_focus_offset = head['focus']
    except:
        laser_focus_offset = 2
    
    if(is_laser_pro == True):
        
        # offset are relative to miscroswitch
        laser_point_offset = head['offset']['laser_point']
        laser_cross_offset = head['offset']['laser_cross']
        
        app.macro("G92 X0 Y0", "ok", 2, _("Setting Cross position"), verbose=False)
        app.macro("G91", "ok", 2,   _("Set relative mode"), verbose=False)
        app.macro("G0 X{0} Y{1} F1000".format(laser_cross_offset['x'], laser_cross_offset['y']), "ok", 2, _("Going to laser cross point"), verbose=True)
        app.macro("M733 S0",    "ok", 2,   _("disable homeing check"), verbose=False)
        app.macro("G92 Z241.5", "ok", 2,   _("set z max"), verbose=False)
        app.macro("M746 S2",    "ok", 2,   _("enable external probe"), verbose=False)
        app.macro("G38",        "ok", 120, _("G38"), verbose=False)
        app.macro("G0 Z{0} F1000".format(laser_focus_offset), "ok", 2,   _("Going to focus point"), verbose=True)
        app.macro("M746 S0",    "ok", 2,   _("disable external probe"), verbose=False)
        app.macro("M733 S1",    "ok", 2,   _("enable homeing check"), verbose=False)
        
        app.macro("G90", "ok", 2,   _("Set absolute mode"), verbose=False)
        app.macro("G0 X{0} Y{1} F1000".format(laser_point_offset['x'], laser_point_offset['y']), "ok", 2, _("Moving to start position"), verbose=True)
        
    else:
        app.macro("G91",                                      "ok", 2, _("Set relative mode"),    verbose=False)
        app.macro("G0 Z{0} F1000".format(laser_focus_offset), "ok", 2, _("Going to focus point"), verbose=True)


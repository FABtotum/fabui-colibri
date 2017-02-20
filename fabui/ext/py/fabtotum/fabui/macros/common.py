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

from fabtotum.utils.translation import _, setLanguage

def getPosition(app, lang='en_US.UTF-8'):
    _ = setLanguage(lang)
    
    data = app.macro("M114", "ok", 2, _("Get Position"), verbose=False)
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
        
    reply = app.macro('M503', None, 1, _("Reading settings from eeprom"), verbose=False)
    
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
    
    return eeprom

def version(app, lang='en_US.UTF-8'):
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

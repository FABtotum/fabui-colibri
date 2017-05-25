#!/bin/env python
# -*- coding: utf-8; -*-
#
# (c) 2017 FABtotum, http://www.fabtotum.com
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
import re
import os
    
# Import external modules

# Import internal modules

#############################################


def parseG30(reply):
    """ 
        Parse reply of G30 / G38
        >> Feedrate: 200.00 Bed X: 10.00 Y: 10.00 Z: 38.53 
        [new]>> X: 10.00 Y: 10.00 Z: 38.53 
        >> ok
    """
    
    try:
        match = re.search("Feedrate: ([-|+0-9.]+)\sBed\sX:\s([-|+0-9.]+)\sY:\s([-|+0-9.]+)\sZ:\s([-|+0-9.]+)", reply[0], re.IGNORECASE)
        return {
            "x" : float(match.group(2)),
            "y" : float(match.group(3)),
            "z" : float(match.group(4))
            }
    except:
        return {}

def parseM114(reply):
    """
        Parse reply of M114
        >> X:0.00 Y:0.00 Z:0.00 E:0.00 Count X: 0.00 Y:0.00 Z:0.00
        [new] >> X: 0.00 Y: 0.00 Z: 0.00 E: 0.00
        >> ok
    """
    position = {}    
    try:
        parts = reply[-2].split()
        
        return {
            "x" : float(parts[1]),
            "y" : float(parts[3]),
            "z" : float(parts[5]),
            "e" : float(parts[7])
        }
    except:
        return {}

def parseM503(reply):
    """
        Parse reply of M503
        ...
        >> ok
    """
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

    try:
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
    except:
        return {}

def parseM730(reply):
    """
        Parse M730, error code
        >> ERROR: 102
        [new] >> 102
    """
    try:
        return {
            'error_num' : float(reply[-2])
        }
    except:
        return {}
    
def partialM109(line):
    """
        Parse M109 partial reply, wait for Nozzle Temp
        >> T:27.4 E:0 W:?
        [new]>> T: 200.57 B: 26
    """
    return partialTemp(line)
    
def partialM190(line):
    """
        Parse M109 partial reply, wait for Bed Temp
        >> T:27.38 E:0 B:54.9
        [new]>> T: 200.57 B: 26
    """
    return partialTemp(line)
    
def partialM303(line):
    """
        Parse M303 partial reply, PID tune
        >> ok T:200.57 @:26
        [new]>> T: 200.57 B: 26
    """
    return partialTemp(line)

def parseM303(reply):
    """
        Parse M303 result
        >> bias: 150 d: 104 min: 198.88 max: 201.61
        >> Ku: 96.99 Tu: 18.09
        >> Classic PID
        >> Kp: 58.19
        >> Ki: 6.43
        >> Kd: 131.57
        >> PID Autotune finished! Put the last Kp, Ki and Kd constants from above into Configuration.h
        
        [new]>> ok Kp: 58.19 Ki: 6.43 Kd: 131.57
    """
    try:
        #Kp = reply[-4].split(':')[1].strip()
        #Ki = reply[-3].split(':')[1].strip()
        #Kd = reply[-2].split(':')[1].strip()
        
        values = reply[-1].split()
        Kp = values[2]
        Ki = values[4]
        Kd = values[6]

        return {
            'Kp' : float(Kp),
            'Ki' : float(Ki),
            'Kd' : float(Kd)
        }
    except:
        return {}

def parseM105(reply):
    """
        Parse M105 reply
        >> ok T:27.2 /0.0 B:27.8 /0.0 T0:27.2 /0.0 A:25 @:0 B@:0
        [new]>> ok T: 27.2/0.0 B: 27.8/0.0 T0: 27.2/0.0 A: 25 @: 0 B@: 0
    """
    try:
        temps = reply[0].split()
        T = temps[2].split('/')
        B = temps[4].split('/')
        T0 = temps[6].split('/')
        PT = temps[8]
        PB = temps[10]

        return {
            'T' : T[0],
            'B' : B[0],
            'target' : {
                'T' : T[1],
                'B' : B[1]
            },
            'power' : {
                'T' : PT,
                'B' : PB
            }
        }
    except:
        return {}

def partialTemp(line):
    """
        Short temperature format appended to regular 'ok' responses
        >> T: 100.0 B: 40.0
    """
    try:
        temps = line.split()
        if temps[0] == 'ok':
            return {
                'T': float(temps[2]),
                'B': float(temps[4])
            }
        else:
            return {
                'T': float(temps[1]),
                'B': float(temps[3])
            }
    except:
        return {}

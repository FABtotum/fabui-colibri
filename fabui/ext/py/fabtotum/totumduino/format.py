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
        >> ok
    """
    match = re.search("Feedrate: ([-|+0-9.]+)\sBed\sX:\s([-|+0-9.]+)\sY:\s([-|+0-9.]+)\sZ:\s([-|+0-9.]+)", reply, re.IGNORECASE)
    
    try:
        return {
            "x" : match.group(2),
            "y" : match.group(3),
            "z" : match.group(4)
            }
    except:
        return {}

def parseM114(reply):
    """
        Parse reply of M114
        >> X:0.00 Y:0.00 Z:0.00 E:0.00 Count X: 0.00 Y:0.00 Z:0.00
        >> ok
    """
    position = {}
    match = re.search('X:([-|+0-9.]+)\sY:([-|+0-9.]+)\sZ:([-|+0-9.]+)\sE:([-|+0-9.]+)\sCount\sX:\s([-|+0-9.]+)\sY:([-|+0-9.]+)\sZ:([-|+0-9.]+)', reply, re.IGNORECASE)
    
    try:
        return {
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
    except:
        return {}

def parseM503(reply):
    """
        Parse reply of M503
        
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

def partialM109(line):
    """
        Parse M109 partial reply, wait for Nozzle Temp
        >> T:27.4 E:0 W:?
    """
    try:
        temps = line.split()
        T = temps[0].replace("T:","").strip()
        
        return {
            'T' : T
        }
    except:
        return {}
    
def partialM190(line):
    """
        Parse M109 partial reply, wait for Bed Temp
        >> T:27.38 E:0 B:54.9
    """
    try:
        temps = line.split()
        T = temps[0].replace("T:","").strip()
        B = temps[2].replace("B:","").strip()
        return {
            'T' : T,
            'B' : B
        }
    except:
        return {}
    
def partialM303(line):
    """
        Parse M303 partial reply, PID tune
        >> ok T:200.57 @:26
    """
    temps = line.split()
    try:
        if temps[0][:2] == 'ok':
            T = temps[1].replace("T:","").strip()
            
            return {
                'T' : T
            }
    except:
        return {}
        
    return {}

def parseM105(reply):
    """
        Parse M105 reply
        >> ok T:27.2 /0.0 B:27.8 /0.0 T0:27.2 /0.0 A:25 @:0 B@:0
    """
    try:
        line = reply[0]
        match = re.search('ok\sT:(?P<T>[0-9]+\.[0-9]+)\s\/(?P<TT>[0-9]+\.[0-9]+)\sB:(?P<B>[0-9]+\.[0-9]+)\s\/(?P<BT>[0-9]+\.[0-9]+)\s', line)
        match.group('T'), match.group('TT'), match.group('B'), match.group('BT') )
        return {
            'T' : match.group('T'),
            'B' : match.group('B'),
            'TT': match.group('TT'),
            'BT': match.group('BT')
        }
    except:
        return {}

def parseShortTemp(reply):
    pass

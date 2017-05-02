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

def parse_gcode(raw_code):
    """
    Parse raw gcode command into it's code and fields.
    
    :param raw_code: Full GCode string.
    :type raw_code: string
    :returns:   A tuple of (code, fields) where fields is a dict with field 
                names as keys and values as their parsed values.
                If the field was not found or is not supported then those values
                are stored in the dict as indexed numerical keys fields[0]...
    :rtype: tuple(string, dict)
    """
    code = None
    fields = {}
    index = 0
    
    tmp = raw_code.split(';')
    tags = tmp[0].split()    
    for tag in tags:
        try:
            if tag[0] in "XYZEIJFRSTQN":
                fields[ tag[0] ] = tag[1:]
            elif tag[0] in "GM":
                code = tag
            else:
                fields[index] = tag
                index += 1
        except:
            pass
    
    return code, fields

def process_command(gcs, line):
    """
    Process command line and decide whether a trigger action should be taken.
    
    :param line: Line to be processed.
    :type line: string
    """
    
    trigger = False
    callback_name = 'gcode_action'
    callback_data = line
    code = None
    
    code, fields = parse_gcode(line)
    
    if code is None:
        return False, '', []
    
    callback_data = [code]
    
    if (code == 'G0' or 
        code == 'G1'):
        # @TODO: monitor feedrate
        pass
        
    elif code == 'G90': # Abs XYZ mode
        gcs.gcode_state['axis_relative_mode']['x'] = False
        gcs.gcode_state['axis_relative_mode']['y'] = False
        gcs.gcode_state['axis_relative_mode']['z'] = False
        
    elif code == 'G91': # Rel XYZ mode
        gcs.gcode_state['axis_relative_mode']['x'] = True
        gcs.gcode_state['axis_relative_mode']['y'] = True
        gcs.gcode_state['axis_relative_mode']['z'] = True
    
    elif code == 'M82': # Abs E mode
        gcs.gcode_state['axis_relative_mode']['e'] = False
        
    elif code == 'M83': # Rel E mode
        gcs.gcode_state['axis_relative_mode']['e'] = True
    
    elif (code == 'M0' or # Unconditional stop 
        code == 'M1' or # Same as M0
        code == 'M3' or # Spindle CounterClocwise
        code == 'M4' or # Spindle Clocwise
        code == 'M6' ): # Laser
        """ Milling action """
        callback_name += ':milling'
        if 'S' in fields:
            callback_data.append(fields['S'])
        trigger = True
        
    elif (code == 'M104' or # Set extruder temp
          code == 'M109' or # Wait for extruder temp
          code == 'M140' or # Set bed temp
          code == 'M190' ): # wait for bed temp
        """ Heating action """
        callback_name += ':heating'
        if 'S' in fields:
            callback_data.append(fields['S'])
        trigger = True
         
    elif (code == 'M106' or # Fan ON
          code == 'M107' ): # Fan OFF
        """ Cooling action """
        callback_name += ':cooling'
        if 'S' in fields:
            callback_data.append(fields['S'])
        trigger = True
         
    elif (code == 'M220' or # Set speed factor
          code == 'M221' ): # Set extruder factor
        """ Printing action """
        callback_name += ':printing'
        if 'S' in fields:
            callback_data.append(fields['S'])
        trigger = True
         
    elif (code == 'M240' or # Trigger camera
          code == 'M401' or # Lower probe
          #code == 'M700' or # Scanning laser
          code == 'M402' ): # Raise probe
        """ Scanning action """
        callback_name += ':scanning'
        if 'S' in fields:
            callback_data.append(fields['S'])
        trigger = True
        
    elif (code == 'M117'):  # Display message
        """ UI action """
        callback_name += ':message'
        callback_data = line.split("M117")[1].strip()
        trigger = True
        
    return trigger, callback_name, callback_data

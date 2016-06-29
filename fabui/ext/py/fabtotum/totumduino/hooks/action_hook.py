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


def process_command(line):
    """
    Process command line and decide whether a trigger action should be taken.
    
    :param line: Line to be processed.
    :type line: string
    """
    
    trigger = False
    callback_name = 'gcode_action'
    callback_data = line
    code = '<none>'
    
    tags = line.split()
    if tags:
        code = tags[0]
    
    callback_data = tags
    
    if (code == 'M0' or # Unconditional stop 
        code == 'M1' or # Same as M0
        code == 'M3' or # Spindle CounterClocwise
        code == 'M4' or # Spindle Clocwise
        code == 'M6' ): # Laser
        """ Milling action """    
        callback_name += ':milling'
        if len(tags) > 1:
            callback_data[1] = float(tags[1].replace("S","").strip())
        trigger = True
        
    elif (code == 'M104' or # Set extruder temp
          code == 'M109' or # Wait for extruder temp
          code == 'M140' or # Set bed temp
          code == 'M190' ): # wait for bed temp
        """ Heating action """
        callback_name += ':heating'
        if len(tags) > 1:
            callback_data[1] = float(tags[1].replace("S","").strip())
        trigger = True
         
    elif (code == 'M106' or # Fan ON
          code == 'M107' ): # Fan OFF
        """ Cooling action """
        callback_name += ':cooling'
        if len(tags) > 1:
            callback_data[1] = float(tags[1].replace("S","").strip())
        trigger = True
         
    elif (code == 'M220' or # Set speed factor
          code == 'M221' ): # Set extruder factor
        """ Printing action """
        callback_name += ':printing'
        if len(tags) > 1:
            callback_data[1] = float(tags[1].replace("S","").strip())
        trigger = True
         
    elif (code == 'M240' or # Trigger camera
          code == 'M700' or # Scanning laser
          code == 'M401' or # Lower probe
          code == 'M402' ): # Raise probe
        """ Scanning action """
        callback_name += ':scanning'
        if len(tags) > 1:
            callback_data[1] = float(tags[1].replace("S","").strip())
        trigger = True
        
    elif (code == 'M117'):  # Display message
        """ UI action """
        callback_name += ':message'
        callback_data = line.split("M117")[1].strip()
        trigger = True
        
    return trigger, callback_name, callback_data

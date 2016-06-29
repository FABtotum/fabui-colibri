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

# Import standard python module
import re

def _is_number(s):
    try:
        float(s)
        return True
    except ValueError:
        return False

def process_line(line):
    attrs = {}

    if re.search('CURA_PROFILE_STRING:', line):
       attrs['slicer'] = 'CURA'
    
    tags = line.split(';')
    
    if len(tags) == 2:
        if re.search('Layer count:', tags[1]):
            tag, value = tags[1].split(':')
            value = value.strip()
            if _is_number(value):
                attrs['layer_count'] = value
        elif re.search('LAYER:', tags[1]):
            tag, value = tags[1].split(':')
            attrs['layer'] = value.strip()
    
    
    return attrs

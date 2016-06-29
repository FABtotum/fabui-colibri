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

# Import external modules

# Import internal modules
from fabtotum.utils.slicer import cura_utils
from fabtotum.utils.slicer import slic3r_utils


EXTERNALS = {
    'CURA'      : cura_utils, 
    'SLIC3R'    : slic3r_utils
}

class GCodeFileIter:
    
    def __init__(self, filename, attr_parser = None):
        self.fd = open(filename, 'r+')
        self.parser = attr_parser
        
    def next(self):
        line = self.fd.readline()
        if line:
            
            if self.parser:
                attrs = self.parser.process_line(line)
            else:
                attrs = None
            
            tags = line.strip().split(';')
            if len(tags) > 0:
                return tags[0].strip(), attrs
            return '', attrs
        else:
            self.fd.close()
            raise StopIteration


class GCodeInfo:
    
    RAW      = 'raw'
    PRINT    = 'print'
    MILL     = 'mill'
    LASER    = 'lase'
    
    def __init__(self, filename):
        self.attribs = {
            'filename'  : filename,
            'type'      : GCodeInfo.RAW,
            'count'     : 0
            }
        
    def __contains__(self, key):
        return key in self.attribs
        
    def __setitem__(self, key, value):
        self.attribs[key] = value
        
    def __getitem__(self, key):
        return self.attribs[key]


class GCodeFile:
    
    def __init__(self, filename):
        self.info = GCodeInfo(filename)
        self.process_file(filename)
        
    def __iter__(self):
        """
        Return iterable object used to iterate though gcode.
        """
        parser = None
        if 'slicer' in self.info:
            if self.info['slicer'] in EXTERNALS:
                parser = EXTERNALS[ self.info['slicer'] ]
        return GCodeFileIter(self.info['filename'], parser)

    def process_file(self, filename):
        """
        Go threough the whole gcode file and extract usefull information about it.
        This information include gcode type, code count, layer count...
        """
        count = 0
        gcode_count = 0
        max_layer = 0
        layer_count = 0
        slicer = None
        gcode_type = None
        
        with open(filename, 'r+') as file:
            for line in file:
                count += 1
                
                head = line[:4]
                
                if head == 'M109':
                    gcode_type = self.info['type'] = GCodeInfo.PRINT
                elif head == 'M3 S' or head == 'M4 S':
                    gcode_type = self.info['type'] = GCodeInfo.MILL
                
                for external in EXTERNALS.values():
                    attrs = external.process_line(line)
                    
                    if 'type' in attrs:
                        gcode_type = self.info['type'] = attrs['type']
                    if 'slicer' in attrs:
                        slicer = self.info['slicer'] = attrs['slicer']
                    if 'layer_count' in attrs:
                        layer_count = self.info['layer_count'] = attrs['layer_count']
                    if 'layer' in attrs:
                        layer = int(attrs['layer'])
                        if layer > max_layer:
                            max_layer = layer
        
                tags = line.strip().split(';')
                if tags[0]:
                    gcode_count += 1
        
        if not layer_count and gcode_type == GCodeInfo.PRINT and max_layer > 0:
            self.info['layer_count'] = max_layer
        
        self.info['line_count'] = count
        self.info['gcode_count'] = gcode_count

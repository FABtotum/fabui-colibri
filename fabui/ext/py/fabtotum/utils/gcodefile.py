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
import os

# Import external modules

# Import internal modules
from fabtotum.utils.slicer.cura_utils import Parser as CuraParser
from fabtotum.utils.slicer.slic3r_utils import Parser as Slic3rParser
from fabtotum.utils.slicer.simplify_utils import Parser as SimplifyParser
from fabtotum.utils.common import rpi_version

class GCodeFileIter:
    
    def __init__(self, filename, attr_parser = None):
        self.fd = open(filename, 'r')
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
    DRILL    = 'drill'
    LASER    = 'laser'
    
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
    
    MAX_FILE_SIZE = 30 #MB - limit for lite_parsing to be triggered
    
    def __init__(self, filename, lite_parsing=False):
        self.info = GCodeInfo(filename)
        self.cura_p   = CuraParser()
        self.slic3r_p = Slic3rParser()
        self.simplify_p = SimplifyParser()
        self.process_file(filename, lite_parsing)
        
    def __iter__(self):
        """
        Return iterable object used to iterate though gcode.
        """
        parser = None
        if 'slicer' in self.info:
            slicer = self.info['slicer']
            if slicer == 'CURA':
                parser = self.cura_p
            elif slicer == 'SLIC3R':
                parser = self.slic3r_p
            elif slicer == 'SIMPLIFY3D':
                parser = self.simplify_p
        return GCodeFileIter(self.info['filename'], parser)

    @staticmethod
    def __tail(f, n):
        stdin,stdout = os.popen2('tail -n {0} "{1}"'.format(n,f))
        stdin.close()
        lines = stdout.readlines(); 
        stdout.close()
        return lines

    @staticmethod
    def __head(f, n):
        stdin,stdout = os.popen2('head -n {0} "{1}"'.format(n,f))
        stdin.close()
        lines = stdout.readlines(); 
        stdout.close()
        return lines
    
    @staticmethod
    def __size(f):
        stdin,stdout = os.popen2('wc -c < "{0}"'.format(f))
        stdin.close()
        size = stdout.readline()
        stdout.close()
        return size #return byte size
    

    def process_file(self, filename, lite_parsing):
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
        stop_parsing = False
        
        head_lines = self.__head(filename, 50)
        tail_lines = self.__tail(filename, 50)
        file_size  = float(self.__size(filename))/1000000
        rpi        = rpi_version()
        
        lines = head_lines + tail_lines
        
        if(rpi == "Raspberry Pi Model B" and file_size > self.MAX_FILE_SIZE):
            lite_parsing = True # On Rasperry Pi Model B it would take too much time to process the file
            
        # Check GCode profile (deduce slicer)
        for line in lines:
            if self.cura_p.is_cura(line):
                slicer = 'CURA'
                break
            elif self.slic3r_p.check_profile(line):
                slicer = 'SLIC3R'
                break
            elif self.simplify_p.is_simplify(line):
                slicer = 'SIMPLIFY3D'
                break
        if slicer:
            self.info['slicer'] = slicer
            
        #print slicer
        
        with open(filename, 'r+') as file:
            for line in file:
                count += 1
                
                if not gcode_type:
                    head = line[:4]
                    
                    if head == 'M109':
                        gcode_type = self.info['type'] = GCodeInfo.PRINT
                        if lite_parsing:
                            break
                    elif head == 'M3 S' or head == 'M4 S':
                        gcode_type = self.info['type'] = GCodeInfo.MILL
                        if lite_parsing:
                            break
                    elif head == 'M450':
                        gcode_type = self.info['type'] = GCodeInfo.LASER
                        if lite_parsing:
                            break
                            
                if not stop_parsing and not lite_parsing:
                    attrs = {}
                    
                    if slicer == 'CURA':
                        attrs = self.cura_p.process_line(line)
                    elif slicer == 'SLIC3R':
                        attrs = self.slic3r_p.process_line(line)
                    elif slicer == 'SIMPLIFY3D':
                        attrs = self.simplify_p.process_line(line)
                    
                    if attrs:
                        if 'type' in attrs:
                            gcode_type = self.info['type'] = attrs['type']
                            
                        if 'layer_count' in attrs:
                            layer_count = self.info['layer_count'] = attrs['layer_count']
                            stop_parsing = True
                            
                        if 'layer' in attrs:
                            layer = int(attrs['layer'])
                            if layer > max_layer:
                                max_layer = layer
                                
                if line[0] != ';':
                    gcode_count += 1
                        
        if not layer_count and gcode_type == GCodeInfo.PRINT and max_layer > 0:
            self.info['layer_count'] = max_layer
        
        self.info['line_count'] = count
        self.info['gcode_count'] = gcode_count

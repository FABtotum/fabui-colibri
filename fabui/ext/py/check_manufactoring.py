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
import argparse
import commands
import re
import os

MANUFACTORING_ADDITIVE     = 'additive'
MANUFACTORING_SUBTRACTIVE  = 'subtractive'
MANUFACTORING_LASER        = 'laser'
MANUFACTORING_UNKNOWN      = ''

def isSubtractive(line):
    match = re.search('(M3\s|M4\s|M03\s)', line)
    return match != None

def isLaser(line):
    match = re.search('(M60\s|M61\s|M62\s)', line)
    return match != None

def isPrint(line):
    match = re.search('(M109\s|M104\s)', line)
    return match != None

def checkGCodeManufactoring(filename, num_of_lines = 500):
    """
    Check what type of manufactoring a file is.
    
    @param filename
    @param num_of_lines Number of lines to check
    
    @retursn additive|subtractive|lase
    """
    
    file_total_num_lines =  int(commands.getoutput('wc -l < "{0}"'.format(filename)))

    if(file_total_num_lines < num_of_lines):
        num_of_lines = file_total_num_lines

    ''' READ FIRST NUM_LINES '''
    with open(filename) as myfile:
        lines = [next(myfile) for x in xrange(num_of_lines)]
        
    manufactoring = MANUFACTORING_UNKNOWN

    for line in lines:
        if(line.startswith(';') == False):
            
            if(isSubtractive(line)): ##ignore comments
                manufactoring = MANUFACTORING_SUBTRACTIVE
                break
            elif(isLaser(line)):
                manufactoring = MANUFACTORING_LASER
                break
            elif(isPrint(line)):
                manufactoring = MANUFACTORING_ADDITIVE
                break

    return manufactoring

def main():
    parser = argparse.ArgumentParser()
    parser.add_argument("-f", help="file to read")
    parser.add_argument("-n", help="Num lines to check",  default=500, nargs='?', type=int)
    parser.add_argument("-d", "--debug",    help="Debug: print console",   action="store_true")
    parser.add_argument("--lang",           help="Output language",     default='en_US.UTF-8' )

    args = parser.parse_args()

    filename        = args.f
    num_of_lines    = int(args.n)
    debug       = bool(args.debug)

    ################################################################################
    ext = os.path.splitext(filename)[1].lower()
    if ext == '.gcode' or ext == '.nc':
        print checkGCodeManufactoring(filename, num_of_lines)
    else:
        print MANUFACTORING_UNKNOWN
    
if __name__ == "__main__":
    main()

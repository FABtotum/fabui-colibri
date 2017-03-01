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
import argparse
import commands
import re

MANUFACTORING_ADDITIVE     = 'additive'
MANUFACTORING_SUBTRACTIVE  = 'subtractive'
MANUFACTORING_LASER        = 'laser'

parser = argparse.ArgumentParser()
parser.add_argument("-f", help="file to read")
parser.add_argument("-n", help="Num lines to check",  default=500, nargs='?', type=int)
parser.add_argument("-d", "--debug",    help="Debug: print console",   action="store_true")

args = parser.parse_args()

file=args.f
num_lines=args.n
debug = args.debug


def isSubtractive(line):
    match = re.search('(M3\s|M4\s|M03\s)', line)
    return match != None

def isLaser(line):
    match = re.search('(M60\s|M61\s|M62\s)', line)
    return match != None

def isPrint(line):
    return False



file_total_num_lines =  int(commands.getoutput('wc -l < "{0}"'.format(file)))

if(file_total_num_lines < num_lines):
    num_lines = file_total_num_lines

''' READ FIRST NUM_LINES '''
with open(file) as myfile:
    lines = [next(myfile) for x in xrange(num_lines)]
    
manufactoring = MANUFACTORING_ADDITIVE

for line in lines:
    
    if(isSubtractive(line)):
        manufactoring = MANUFACTORING_SUBTRACTIVE
        break
    elif(isLaser(line)):
        manufactoring = MANUFACTORING_LASER
        break
    else:
        manufactoring = MANUFACTORING_ADDITIVE

print manufactoring
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
import os

#######################################################################
# Execute command via shell and return the complete output as a string
########################################################################
def shell_exec(cmd):
    stdin,stdout = os.popen2(cmd)
    stdin.close()
    lines = stdout.readlines(); 
    stdout.close()
    return lines

#######################################################################
# Define on wich Raspberry im running on
########################################################################
def rpi_version():
    soc_id = shell_exec('</proc/cpuinfo grep Hardware | awk \'{print $3}\'')[0].strip()
    name_id = ''
    soc_name = {'BCM2708' : 'Raspberry Pi Model B', 'BCM2709' : 'Raspberry Pi 3 Model B' }
    if soc_id in soc_name:
        return soc_name[soc_id]
    else:
        return soc_id
#######################################################################
# Define model depending on batch number
########################################################################
def fabtotum_model(batch_number):
    
    try:
        batch_number = int(batch_number)
    except:
        batch_number = 1
        
    model = ''
    
    if(batch_number >= 3000 and batch_number < 4000 ):
        model = 'FABtotum Hydra'
    elif(batch_number >= 2000 and batch_number < 3000):
        model = 'FABtotum CORE PRO'
    elif(batch_number >= 1000 and batch_number < 2000):
        model = "FABtotum Core"
    else:
        model = 'FABtotum Personal Fabricator'
    
    return model
    
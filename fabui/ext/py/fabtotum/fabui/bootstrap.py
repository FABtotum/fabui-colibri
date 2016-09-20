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

__authors__ = "Daniel Kesler"
__license__ = "GPL - https://opensource.org/licenses/GPL-3.0"
__version__ = "1.0"

# Import standard python module
import gettext
import json
import os

# Import external modules

# Import internal modules
from fabtotum.fabui.config import ConfigService

# Set up message catalog access
tr = gettext.translation('gpusher', 'locale', fallback=True)
_ = tr.ugettext

def customHardware(gcodeSender,config,log):
    """
    Revision for customs edits
    """
    #set x endstop logic
    gcodeSender.send("M747 X{0}".format(config.get('settings', 'invert_x_endstop_logic')))
    #save settings
    gcodeSender.send("M500")
    log.debug("Custom Hardware")

def hardware1(gcodeSender,log):
    """
    Rev1: September 2014 - May 2015
    - Original FABtotum
    """
    log.debug("Rev1")
    
def hardware2(gcodeSender,log):
    """
    Rev2: June 2015 - August 2015
    - Simplified Feeder (Removed the disengagement and engagement procedure), if you want you can update it easily following this Tutorial: Feeder update.
    - Bowden tube improvement (Added a protection external sleeve to avoid the bowden tube get stuck in the back panel).
    - Endstops logic inverted.
    """
    #invert x endstop logic
    gcodeSender.send("M747 X1")
    #set maximum feedrate
    gcodeSender.send("M203 X550.00 Y550.00 Z15.00 E12.00")
    #save settings
    gcodeSender.send("M500")
    log.debug("Rev2")
    
def hardware3(gcodeSender,log):
    """
    Rev3: Aug 2015 - Jan 2016
    - Back panel modified to minimize bowden tube collisions
    - Hotplate V2 as standard duty hotplate
    - Reed sensor (Contactless sensor for the frontal door)
    - Head V1 (hybrid) discontinued
    - Milling Head V2 (store.fabtotum.com/eu/store/milling-head-v2.html).
    - Print head V2 (store.fabtotum.com/eu/store/printing-head-v2.html).
    """
    #invert x endstop logic
    gcodeSender.send("M747 X1")
    #set maximum feedrate
    gcodeSender.send("M203 X550.00 Y550.00 Z15.00 E12.00")
    #save settings
    gcodeSender.send("M500")
    log.debug("Rev3")
    
    
def hardware4(log):
    """
    Rev4(CORE): Jan 2016 - xxx
    - TBA
    """
    log.debug("Rev4")

def hardwareBootstrap(gcs, config = None, logger = None):
    if not config:
        config = ConfigService()
        
    if logger:
       log = logger
    else:
        log = logging.getLogger('GCodeService')
        ch = logging.StreamHandler()
        ch.setLevel(logging.DEBUG)
        formatter = logging.Formatter("%(levelname)s : %(message)s")
        ch.setFormatter(formatter)
        log.addHandler(ch)
        
        
    try:
        color = config.get('settings', 'color')
    except KeyError:
        color = {
            'r' : 255,
            'g' : 255,
            'b' : 255,
        }
    
    try:
        safety_door = config.get('settings', 'safety')['door']
    except KeyError:
        safety_door = 0
    
    try:
        switch = config.get('settings', 'switch')
    except KeyError:
        switch = 0
    
    try:
        collision_warning = config.get('settings', 'safety')['collision-warning']
    except KeyError:
        collision_warning = 0

    # Get hardware id (version)
    reply = gcs.send('M763')
    try:
        hardwareID = reply[0].strip()
    except Exception as e:
        print "ERROR", e
        hardwareID = 0
    
    # Raise probe
    gcs.send('M402')
    # Send ALIVE
    gcs.send('M728')

    # Set ambient colors
    gcs.send("M701 S{0}".format(color['r']))
    gcs.send("M702 S{0}".format(color['g']))
    gcs.send("M703 S{0}".format(color['b']))
    
    # Set safety door open warnings: enabled/disabled
    gcs.send("M732 S{0}".format(safety_door))
    # Set collision warning: enabled/disabled
    gcs.send("M734 S{0}".format(collision_warning))
    # Set homing preferences
    gcs.send("M714 S{0}".format(switch))
    
    # Load Head
    #~ try:
    head_file = os.path.join( config.get('hardware', 'heads'), config.get('settings', 'hardware')['head'] + '.json');
    with open(head_file) as json_f:
        head = json.load(json_f)
    # Set head PID
    gcs.send( head['pid'] )
    # Set installed head
    gcs.send( "M793 S{0}".format( head['fw_id'] ) )
    # Save settings
    gcs.send( "M500" )
    #~ except Exception as e:
        #~ print "ERROR", e
        
    # Execute version specific intructions
    HW_VERSION_CMDS = {
        'custom' : customHardware,
        '1'      : hardware1,
        '2'      : hardware2,
        '3'      : hardware3,
        '4'      : hardware4
    }
    if config.get('settings', 'settings_type') == 'custom':
        customHardware(gcs,config,log)
    elif hardwareID in HW_VERSION_CMDS:
        HW_VERSION_CMDS[hardwareID](gcs,log)
    else:
        log.error("Unsupported hardware version: %s", hardwareID)

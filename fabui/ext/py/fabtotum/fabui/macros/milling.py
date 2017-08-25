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

__authors__ = "Marco Rizzuto, Daniel Kesler"
__license__ = "GPL - https://opensource.org/licenses/GPL-3.0"
__version__ = "1.0"

# Import standard python module
import os
import json

# Import external modules

# Import internal modules
from fabtotum.utils.translation import _, setLanguage
from fabtotum.fabui.macros.common import getPosition, configure_head

def pause_subtractive(app, args=None, lang='en_US.UTF-8'):
    app.macro("M400",   "ok", 240,    _("Waiting for all moves to finish"), verbose=False)
    position = getPosition(app, lang)
    
    with open('/var/lib/fabui/settings/stored_task.json', 'w') as f:
        f.write( json.dumps({
            'position':position
            }) )
    
    current_z = float(position['z'])
    safe_z = current_z + 50.0
    
    max_z = app.config.get('settings', 'z_max_offset', 241.5) - 5
    if safe_z > max_z:
        safe_z = max_z
    
    app.macro("G90",                "ok", 2,    _("Setting absolute position"), verbose=False )
    app.macro("G0 Z{0} F5000".format(safe_z),        "ok", 100,  _("Moving to Z safe zone"), verbose=False )
    
    app.macro("G0 X210 Y210 F6000", "ok", 100,  _("Moving to safe zone"), verbose=False )
    #block stepper motor for 5min => 60*5=300
    app.macro("M84 S300", "ok", 2, _("Block stepper motor"), verbose=False)
    app.macro("M732 S0", "ok", 2, _("Disabling door safety"))

def resume_subtractive(app, args=None, lang='en_US.UTF-8'):
    app.macro("M84", "ok", 2, _("Unlock stepper motor"), verbose=False)
    safety_door = app.config.get('settings', 'safety.door', 0)
    
    app.macro("M732 S{0}".format(safety_door), "ok", 2, _("Set door safety"))
    
    # restore position
    if os.path.exists('/var/lib/fabui/settings/stored_task.json'):
        content = {}
        with open('/var/lib/fabui/settings/stored_task.json') as f:
            content = json.load(f)
        
        if "position" in content:
            x = float(content['position']['x'])
            y = float(content['position']['y'])
            z = float(content['position']['z'])
            
            app.macro("G90",                            "ok", 2,  _("Setting absolute position"), verbose=False )
            app.macro("G0 X{0} Y{1} F6000".format(x,y), "ok", 60,  _("Restore XY position"), verbose=False )
            app.macro("G0 Z{0} F1000".format(z),        "ok", 60,  _("Restore Z position"), verbose=False )
            app.macro("M400",                           "ok", 120,  _("Waiting for all moves to finish"), verbose=False)

def check_subtractive(app, args = None, lang='en_US.UTF-8'):
    _ = setLanguage(lang)
    try:
        safety_door = app.config.get('settings', 'safety')['door']
    except KeyError:
        safety_door = 0
        
    try:
        bed_enabled = app.config.get('settings', 'hardware')['bed']['enable']
    except KeyError:
        bed_enabled = True
        
    app.trace( _("Checking safety measures") )
    if safety_door == 1:
        app.macro("M741",   "TRIGGERED", 2, _("Front panel door opened") )
    
    if bed_enabled == True:
        app.macro("M744",       "open", 1, _("Building plane inserted correctly"))

def start_subtractive(app, args = None, lang='en_US.UTF-8'):
    _ = setLanguage(lang)
    
    feeder = app.config.get_feeder_info('built_in_feeder')
    units_a = feeder['steps_per_angle']
    
    configure_head(app, app.config.get('settings', 'hardware.head'))
    
    app.macro("G92 X0 Y0 Z0 E0", "ok", 1,       _("Setting Origin Point"), verbose=False)
    app.macro("M92 E"+str(units_a), "ok", 1,    _("Setting 4th Axis mode"), verbose=False)
    
    
def end_subtractive(app, args = None, lang='en_US.UTF-8'):
    _ = setLanguage(lang)

    app.trace("Terminating...")
    
    # Deinitialize and restore settings
    end_subtractive_aborted(app, args, lang)


def end_subtractive_aborted(app, args = None, lang='en_US.UTF-8'):
    feeder = app.config.get_current_feeder_info()
    units_e = feeder['steps_per_unit']
    
    try:
        color = app.config.get('settings', 'color')
    except KeyError:
        color = {
            'r' : 255,
            'g' : 255,
            'b' : 255,
        }
        
    app.macro("M400",       "ok", 200,   _("Waiting for all moves to finish") )
    app.macro("M5",         "ok", 100,   _("Shutting Down Milling Motor") ) #should be moved to firmware       
    app.macro("M220 S100",  "ok", 50,    _("Reset Speed factor override") )
    app.macro("M221 S100",  "ok", 5,     _("Reset Extruder factor override") )
    app.macro("M107",       "ok", 50,    _("Turning Fan off") ) # moved to firmware
    app.macro("M18",        "ok", 50,    _("Motor Off") )
    app.macro("M92 E"+str(units_e), "ok", 1,    _("Setting extruder mode"), verbose=False)
    #go back to user-defined colors
    app.macro("M701 S"+str(color['r']), "ok", 2,  _("Turning on lights"), verbose=False)
    app.macro("M702 S"+str(color['g']), "ok", 2,  _("Turning on lights"), verbose=False)
    app.macro("M703 S"+str(color['b']), "ok", 2,  _("Turning on lights"), verbose=False)
    app.macro("M300",                   "ok", 10, _("Milling completed!") ) #should be moved to firmware

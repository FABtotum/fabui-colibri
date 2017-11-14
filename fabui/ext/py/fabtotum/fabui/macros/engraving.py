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

# Import external modules

# Import internal modules
from fabtotum.utils.translation import _, setLanguage
from fabtotum.fabui.macros.common import configure_head

def check_engraving(app, args = None, lang='en_US.UTF-8'):
    _ = setLanguage(lang)
    try:
        safety_door = app.config.get('settings', 'safety')['door']
    except KeyError:
        safety_door = 0
        
    #~ app.trace( _("Checking safety measures") )
    if safety_door == 1:
        app.macro("M741",   "TRIGGERED", 2, _("Front panel door control"))
    
    app.trace( _("Checking building plate") )
    app.macro("M744",       "open", 1, _("Build plate needs to be flipped to the milling side"), verbose=False)
    app.trace( _("Building plate inserted correctly") )
    
def start_engraving(app, args = None, lang='en_US.UTF-8'):
    _ = setLanguage(lang)
    
    feeder       = app.config.get_feeder_info('built_in_feeder')
    units_a      = feeder['steps_per_angle']
    head         = app.config.get_current_head_info()
    is_laser_pro = app.config.is_laser_pro_head(head['fw_id'])
    
    try:
        laser_focus_offset = head['focus']
    except:
        laser_focus_offset = 2
    
    configure_head(app, app.config.get('settings', 'hardware.head'))
    
    go_to_focus = int(args[0])
    
    try:
        fan_on = int(args[1]) == 1
    except:
        fan_on = True
           
        
    if(go_to_focus == 1):
        if(is_laser_pro == True):
            app.trace( _("Calibrating Z axis heigth") )
            
            x = head['offset']['microswitch']['x']
            y = head['offset']['microswitch']['y']
            
            app.macro("G91",       "ok", 2,   _("Set relative mode"), verbose=False)
            app.macro("G0 X-{0} Y-{1} F1000".format(x, y), "ok", 2,   _("Going to laser cross point"), verbose=True)
            app.macro("M733 S0",    "ok", 2,   _("disable homeing check"), verbose=False)
            app.macro("G92 Z241.5", "ok", 2,   _("set z max"), verbose=False)
            app.macro("M746 S2",    "ok", 2,   _("enable external probe"), verbose=False)
            app.macro("G38",        "ok", 120, _("G38"), verbose=False)
            app.macro("G0 Z{0} F1000".format(laser_focus_offset), "ok", 2,   _("Going to focus point"), verbose=True)
            app.macro("M746 S0",    "ok", 2,   _("disable external probe"), verbose=False)
            app.macro("M733 S1",    "ok", 2,   _("enable homeing check"), verbose=False)
            app.macro("G0 X+{0} F1000".format(x), "ok", 2,   _("Going to laser cross point"), verbose=True)
        else:
            app.macro("G91",       "ok", 2,   _("Set relative mode"), verbose=False)
            app.macro("G0 Z{0} F1000".format(laser_focus_offset), "ok", 2,   _("Going to focus point"), verbose=True)
    
    if(is_laser_pro == True):
        app.trace("G0 X-{0} Y{1} F1000".format(head['offset']['laser_cross']['x'], head['offset']['laser_cross']['y']))
        app.macro("G0 X-{0} Y{1} F1000".format(head['offset']['laser_cross']['x'], head['offset']['laser_cross']['y']), "ok", 2,   _("Going to laser point"), verbose=True)
    
    app.macro("G92 X0 Y0 Z0 E0", "ok", 1,    _("Setting Origin Point"),  verbose=False)
    app.macro("M92 E"+str(units_a), "ok", 1, _("Setting 4th Axis mode"), verbose=False)
    app.macro("M701 S0",  "ok", 2,           _("Turning off lights"),    verbose=False)
    app.macro("M702 S0",  "ok", 2,           _("Turning off lights"),    verbose=False)
    app.macro("M703 S0",  "ok", 2,           _("Turning off lights"),    verbose=False)
    
    if(fan_on == True):
        app.macro("M106 S255", "ok", 1,          _("Turning fan on"), verbose=True)
    
    
def end_engraving(app, args = None, lang='en_US.UTF-8'):
    _ = setLanguage(lang)
    
    app.trace("Terminating...")    
    app.macro("G0 X0 Y0 Z0 E0 F2000", "ok", 1, _("Go back to Origin Point"), verbose=False)
    
    # Deinitialize and restore settings
    end_engraving_aborted(app, args, lang)

def end_engraving_aborted(app, args = None, lang='en_US.UTF-8'):
    
    feeder = app.config.get_current_feeder_info();
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
    app.macro("M62",        "ok", 10,    _("Shutting down the laser") ) #should be moved to firmware       
    app.macro("M220 S100",  "ok", 50,    _("Reset Speed factor override") )
    app.macro("M221 S100",  "ok", 5,     _("Reset Extruder factor override") )
    app.macro("M107",       "ok", 50,    _("Turning Fan off") ) # moved to firmware
    app.macro("M18",        "ok", 50,    _("Motor Off") )
    app.macro("M92 E"+str(units_e), "ok", 1,    _("Setting extruder mode"), verbose=False)
    #go back to user-defined colors
    app.macro("M701 S"+str(color['r']), "ok", 2,  _("Turning on lights"), verbose=False)
    app.macro("M702 S"+str(color['g']), "ok", 2,  _("Turning on lights"), verbose=False)
    app.macro("M703 S"+str(color['b']), "ok", 2,  _("Turning on lights"), verbose=False)
    app.macro("M300",                   "ok", 10, _("Laser engraving completed!") ) #should be moved to firmware

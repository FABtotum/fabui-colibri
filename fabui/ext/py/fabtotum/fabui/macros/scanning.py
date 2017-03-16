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

def check_pre_scan(app, args = None, lang='en_US.UTF-8'):
    _ = setLanguage(lang)
    units_a = app.config.get('settings', 'a')
    
    try:
        feeder_disengage_offset = app.config.get('settings', 'feeder')['disengage_offset']
    except KeyError:
        feeder_disengage_offset = 2
        
    try:
        safety_door = app.config.get('settings', 'safety')['door']
    except KeyError:
        safety_door = 0
    
    app.trace( _("Preparing the FABtotum to scan") )
    if(safety_door == 1):
        app.macro("M741",   "TRIGGERED", 2,     _("Front panel door control") )
    # macro("M744","open",1,"Building plane control",0.1)
    app.macro("M742",       "TRIGGERED",1,      _("Spool panel control"), warning=True, verbose=False)
    app.macro("G90",        "ok", 2,            _("Setting absolute positioning mode"), verbose=False)
    #app.macro("G28 X Y",    "ok", 15,           _("Zeroing Z axis"), verbose=False)
    app.macro("G27",      "ok", 120,          _("Zeroing Z axis") )
    # Disable feeder
    app.macro("M120",               "ok", 1,    _("Disable Endstop checking") )
    app.macro("G91",                "ok", 2,    _("Setting rel position"), verbose=False)
    app.macro("G0 Z-"+str(feeder_disengage_offset)+" F300",   "ok", 100, _("Engaging 4th Axis Motion") )
    #app.macro("M400",                "ok", 100,   _("Wait for moves to finish"), verbose=False)
    app.macro("M121",               "ok", 1,    _("Enable Endstop checking") )
    app.macro("G90",                "ok", 2,    _("Setting Absolute position"), verbose=False)
    app.macro("M92 E"+str(units_a), "ok", 1,    _("Setting 4th axis mode"), verbose=True)
    # Move to collimation
    app.macro("G0 Z135 F1000",  "ok", 100,        _("Moving to pre-scan position") )
    #app.macro("M400",           "ok", 55,       _("Wait for all moves to finish"), verbose=False)
    # macro("M18","ok",1,"Motor Off",1) #should be moved to firmware
    
def rotary_scan(app, args = None, lang='en_US.UTF-8'):
    _ = setLanguage(lang)
    units_a = app.config.get('settings', 'a')
    
    try:
        safety_door = app.config.get('settings', 'safety')['door']
    except KeyError:
        safety_door = 0
    
    app.trace( _("Initializing Rotative Laser scanner") )
    app.trace( _("Checking panel door status and bed inserted") )
    if(safety_door == 1):
        app.macro("M741",   "TRIGGERED", 2,     _("Front panel door control"), verbose=False)
    app.macro("M744",       "open", 1,          _("Building plane (must be removed)") )
    app.macro("M742",       "TRIGGERED", 1,     _("Spool panel closed"), warning=True, verbose=False)
    app.macro("M701 S0",  "ok", 2,            _("Turning off lights"), verbose=False)
    app.macro("M702 S0",  "ok", 2,            _("Turning off lights"), verbose=False)
    app.macro("M703 S0",  "ok", 2,            _("Turning off lights"), verbose=False)
    app.macro("G90",        "ok", 2,            _("Setting Absolute position"), verbose=False)
    app.macro("G0 X96 Y175 Z135 E0 F10000", "ok", 100,   _("Moving to collimation position") )
    app.macro("M302 S0",    "ok", 2,            _("Enabling cold extrusion"), verbose=False)
    app.macro("M92 E"+str(units_a), "ok", 1,    _("Setting 4th axis mode"), verbose=True)
    #app.macro("M400",        "ok", 55,          _("Wait for all moves to finish"), verbose=False)
    #macro("M92 E"+str(units['a']),"ok",1,"Setting 4th axis mode",0)
    
def photogrammetry_scan(app, args = None, lang='en_US.UTF-8'):
    _ = setLanguage(lang)
    units_a = app.config.get('settings', 'a')
    
    try:
        safety_door = app.config.get('settings', 'safety')['door']
    except KeyError:
        safety_door = 0
        
    app.trace( _("Initializing Photogrammetry scanner") )
    app.trace( _("Checking panel door status and bed inserted") )
    if(safety_door == 1):
        app.macro("M741",   "TRIGGERED", 2,     _("Front panel door control"), verbose=False)
    app.macro("M744",       "open", 1,          _("Building plane (must be removed)") )
    app.macro("M742",        "TRIGGERED", 1,    _("Spool panel closed"), warning=True)
    app.macro("M701 S255",  "ok", 2,            _("Turning on lights"), verbose=False)
    app.macro("M702 S255",  "ok", 2,            _("Turning on lights"), verbose=False)
    app.macro("M703 S255",  "ok", 2,            _("Turning on lights"), verbose=False)
    app.macro("G90",        "ok", 2,            _("Setting Absloute position"), verbose=False)
    app.macro("G0 X96 Y175 Z135 E0 F10000", "ok", 100,   _("Moving to collimation position") )
    app.macro("M302 S0",    "ok",2,             _("Enabling cold extrusion"), verbose=False)
    app.macro("M92 E"+str(units_a), "ok", 1,    _("Setting 4th axis mode"), verbose=True)
    #app.macro("M400",       "ok", 55,           _("Wait for all moves to finish"), verbose=False)

def sweep_scan(app, args = None, lang='en_US.UTF-8'):
    _ = setLanguage(lang)
    try:
        safety_door = app.config.get('settings', 'safety')['door']
    except KeyError:
        safety_door = 0
    
    app.trace( _("Initializing Sweeping Laserscanner") )
    app.trace( _("checking panel door status and bed inserted") )
    if(safety_door == 1):
        app.macro("M741",       "TRIGGERED", 2, _("Front panel door control") )
    app.macro("M744",           "TRIGGERED", 2,      _("Building plane removed!"), warning=True)
    app.macro("M742",           "TRIGGERED", 1, _("Spool panel is not closed!"), warning=True, verbose=False)
    app.macro("M701 S0",        "ok", 2,        _("Turning off lights") )
    app.macro("M702 S0",        "ok", 2,        _("Turning off lights"), verbose=False)
    app.macro("M703 S0",        "ok", 2,        _("Turning off lights"), verbose=False)
    # macro("M744","open",2,"Working plane absent/tilted",0.1)
    #~ app.macro("G28 X0 Y0",      "ok", 90,       _("Homing all axis"), 1)
    #~ app.macro("G27",            "ok", 90,       _("Homing all axis") )
    app.macro("G90",            "ok", 2,        _("Setting Absolute position"), verbose=False)
    # macro("M92 E"+str(units['a']),"ok",1,"Setting 4th axis mode",0)
    app.macro("G0 Z145 F1000",  "ok", 100,       _("Lowering the plane"), verbose=False)
    #app.macro("M400",            "ok", 55,      _("Wait for all moves to finish"), verbose=False)

def probe_scan(app, args = None, lang='en_US.UTF-8'):
    _ = setLanguage(lang)
    units_a = app.config.get('settings', 'a')
    try:
        safety_door = app.config.get('settings', 'safety')['door']
    except KeyError:
        safety_door = 0
    
    app.trace( _("Initializing Probing procedure") )
    if(safety_door == 1):
        app.macro("M741",           "TRIGGERED", 2, _("Front panel door control"), warning=True)
    app.macro("M402",               "ok", 2,        _("Raising Probe") )
    app.macro("M744",               "TRIGGERED", 2, _("Building plane is absent"), warning=True)
    app.macro("M742",               "TRIGGERED", 2, _("Spool panel"), warning=False)
    app.macro("G90",                "ok", 2,        _("Setting Absolute position"), verbose=True)
    app.macro("M302 S0",            "ok", 2,        _("Disabling cold extrusion prevention"), verbose=True)
    app.macro("M92 E"+str(units_a), "ok", 2,        _("Setting 4th axis mode"), verbose=True)

def end_scan(app, args = None, lang='en_US.UTF-8'):
    _ = setLanguage(lang)
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
    app.trace( _("Terminating digitalization procedure") )
    app.macro("M402",                   "ok", 10,   _("Retracting Probe") )
    app.macro("M700 S0",                "ok", 3,    _("Shutting Down Laser") )
    app.macro("G90",                    "ok", 10,   _("Setting Absolute position"), verbose=False)
    app.macro("G0 E0 F5000",            "ok", 100,  _("Rasing Probe"), verbose=False)
    app.macro("G0 Z140 F5000",          "ok", 100,  _("Rasing Probe"), verbose=False)
    app.macro("M92 E"+str(units_e),     "ok", 1,    _("Setting extruder mode"), verbose=True)
    #go back to user-defined colors
    app.macro("M701 S"+str(color['r']), "ok", 2,    _("Turning on lights"), verbose=False)
    app.macro("M702 S"+str(color['g']), "ok", 2,    _("Turning on lights"), verbose=False)
    app.macro("M703 S"+str(color['b']), "ok", 2,    _("Turning on lights"), verbose=False)
    #app.macro("M400",                   "ok", 55,   _("Wait for all moves to finish"), verbose=False)
    app.macro("M300",                   "ok", 1,    _("Scan completed!"), verbose=False)

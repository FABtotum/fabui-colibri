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
import gettext

# Import external modules

# Import internal modules


# Set up message catalog access
tr = gettext.translation('gmacro', 'locale', fallback=True)
_ = tr.ugettext

def start_additive(app, args = None):
    units_e = app.config.get('settings', 'e')
    
    ext_temp = args[0];
    bed_temp = args[1];
    
    app.trace( _("Preparing the FABtotum Personal Fabricator") )
    app.macro("G90",                    "ok", 2,    _("Setting absolute position"), verbose=False)
    app.macro("G0 X5 Y5 Z60 F1500",     "ok", 3,    _("Moving to oozing point") )
    #~ # Pre-heating (dismissed)
    app.macro("M104 S"+str(ext_temp),   "ok", 3,    _("Pre Heating Nozzle ({0}&deg;) (fast)").format(str(ext_temp)))
    app.macro("M140 S"+str(bed_temp),   "ok", 3,    _("Pre Heating Bed ({0}&deg;) (fast)").format(str(bed_temp)))
    app.macro("M220 S100",              "ok", 1,    _("Reset Speed factor override"),     verbose=False)
    app.macro("M221 S100",              "ok", 1,    _("Reset Extruder factor override"),  verbose=False)
    app.macro("M92 E"+str(units_e),     "ok", 1,    _("Setting extruder mode"),           verbose=False)
    app.macro("M400",                   "ok", 60,   _("Waiting for all moves to finish"), verbose=False)

def end_additive(app, args = None):
    try:
        color = app.config.get('settings', 'color')
    except KeyError:
        color = {
            'r' : 255,
            'g' : 255,
            'b' : 255,
        }
    
    #note: movement here is done so it works with AUTO positioning (additive mode).
    app.trace( _("Terminating...") )
    #macro("G90","ok",100,"Set Absolute movement",0.1,verbose=False)
    #macro("G90","ok",2,"Set Absolute movement",1)
    #macro("G0 X210 Y210 Z200 F10000","ok",100,"Moving to safe zone",0.1,verbose=False) #right top, normally Z=240mm
    app.macro("M400",       "ok", 100,  _("Waiting for all moves to finish") )
    app.macro("M104 S0",    "ok", 50,   _("Shutting down Extruder") )
    app.macro("M140 S0",    "ok", 50,   _("Shutting down Heated Bed") )
    app.macro("M220 S100",  "ok", 20,   _("Reset Speed factor override") )
    app.macro("M221 S100",  "ok", 20,   _("Reset Extruder factor override"), verbose=False)
    app.macro("M107",       "ok", 50,   _("Turning Fan off"), 0)       #should be moved to firmware
    app.macro("M18",        "ok", 10,   _("Motor Off"), 0)             #should be moved to firmware
    #go back to user-defined colors
    app.macro("M701 S"+str(color['r']), "ok", 2,    _("Turning on lights"), verbose=False)
    app.macro("M702 S"+str(color['g']), "ok", 2,    _("Turning on lights"), verbose=False)
    app.macro("M703 S"+str(color['b']), "ok", 2,    _("Turning on lights"), verbose=False)
    app.macro("M300",                   "ok", 1,    _("Printing completed!"), verbose=False)  #end print signal

def end_additive_safe_zone(app, args = None):
    app.macro("G90",                        "ok", 2,    _("Setting Absolute position") )
    app.macro("G0 X210 Y210 Z100 F10000",   "ok", 100,  _("Moving to safe zone") )
    app.macro("M400",       "ok", 200,    _("Waiting for all moves to finish") )

def check_pre_print(app, args = None):
    try:
        safety_door = app.config.get('settings', 'safety')['door']
    except KeyError:
        safety_door = 0
    
    app.trace( _("Checking safety measures") )
    if safety_door == 1:
        app.macro("M741",   "TRIGGERED", 2, _("Front panel door control") )
    app.macro("M744",       "TRIGGERED", 1, _("Building plane inserted correctly"), warning=True)
    app.macro("M742",       "TRIGGERED", 1, _("Spool panel control"), warning=True)

def engage_feeder(app, args = None):
    try:
        safety_door = app.config.get('settings', 'safety')['door']
    except KeyError:
        safety_door = 0
        
    units_e = app.config.get('settings', 'e')

    app.trace( _("Engaging 3D-Printer Feeder") )
    if safety_door == 1:
        app.macro("M741",           "TRIGGERED", 2, _("Front panel door control") )
    app.macro("M742",               "TRIGGERED", 1, _("Spool panel control"), warning=True, verbose=False)
    app.macro("G27",                "ok", 100,      _("Zeroing Z axis") )
    app.macro("G91",                "ok", 1,        _("Set rel movement"), verbose=False)
    app.macro("G0 Z-4 F1000",       "ok", 5,        _("Setting Z position") )
    app.macro("M400",               "ok", 5,        _("Waiting for all moves to finish"), verbose=False)
    app.macro("G90",                "ok", 1,        _("Set absolute movement") )
    app.macro("M92 E"+str(units_e), "ok", 1,        _("Setting extruder mode") )
    app.macro("M18",                "ok", 3,        _("Stopping motors"), verbose=False)
    app.macro("M300",               "ok", 3,        _("Play beep sound"), verbose=False)   

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


def prepare_additive(app, args=None, lang='en_US.UTF-8'):
    _ = setLanguage(lang)
    
    ext_temp = args[0];
    bed_temp = args[1];

    app.macro("M104 S"+str(ext_temp),   "ok", 3,    _("Pre Heating Nozzle ({0}&deg;) (fast)").format(str(ext_temp)))
    app.macro("M140 S"+str(bed_temp),   "ok", 3,    _("Pre Heating Bed ({0}&deg;) (fast)").format(str(bed_temp)))
    
    app.macro("M402", "ok", 2,    _("Retract Probe"), verbose=False)

def start_additive(app, args = None, lang='en_US.UTF-8'):
    _ = setLanguage(lang)
    
    units_e = app.config.get('settings', 'e')
    
    head_file = os.path.join( app.config.get('hardware', 'heads'), app.config.get('settings', 'hardware.head') + '.json');

    with open(head_file) as json_f:
        head = json.load(json_f)
    
    offset  = float(head.get('nozzle_offset', 0))

    try:
        switch = app.config.get('settings', 'switch')
    except KeyError:
        switch = 0
    
    app.trace( _("Preparing the FABtotum Personal Fabricator") )
    app.macro("G90",                    "ok", 2,    _("Setting absolute position"), verbose=False)
    
    oozing_z = 60.0
    oozing_z_adjusted = 60.0 + offset
    
    if switch == 0:
        app.macro("G0 X5 Y5 Z{0} F1500".format(oozing_z_adjusted),     "ok", 10,    _("Moving to oozing point") )
        app.macro("G92 Z{0}".format(oozing_z),  "ok" , 10, _("Adjusting nozzle offset"), verbose=False );
    else:
        app.macro("G0 X209 Y5 Z{0} F1500".format(oozing_z_adjusted),     "ok", 10,    _("Moving to oozing point") )
        app.macro("G92 Z{0}".format(oozing_z),  "ok" , 10, _("Adjusting nozzle offset"), verbose=False );
    
    #~ # Pre-heating (dismissed)
    app.macro("M220 S100",              "ok", 1,    _("Reset Speed factor override"),     verbose=False)
    app.macro("M221 S100",              "ok", 1,    _("Reset Extruder factor override"),  verbose=False)
    app.macro("M92 E"+str(units_e),     "ok", 1,    _("Setting extruder mode"),           verbose=False)
    app.macro("M400",                   "ok", 60,   _("Waiting for all moves to finish"), verbose=False)

def end_additive(app, args=None, lang='en_US.UTF-8'):
    _ = setLanguage(lang)
    
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
    app.macro("M107",       "ok", 50,   _("Turning Fan off"))       #should be moved to firmware
    app.macro("M18",        "ok", 10,   _("Motor Off"))             #should be moved to firmware
    #go back to user-defined colors
    app.macro("M701 S"+str(color['r']), "ok", 2,    _("Turning on lights"), verbose=False)
    app.macro("M702 S"+str(color['g']), "ok", 2,    _("Turning on lights"), verbose=False)
    app.macro("M703 S"+str(color['b']), "ok", 2,    _("Turning on lights"), verbose=False)
    app.macro("M300",                   "ok", 1,    _("Printing completed!"), verbose=False)  #end print signal

def end_additive_safe_zone(app, args = None, lang='en_US.UTF-8'):
    _ = setLanguage(lang)
    app.macro("M121",                     "ok", 2,    _("Force endstops"), verbose=False )
    app.macro("G90",                      "ok", 2,    _("Setting Absolute position") )
    app.macro("G0 X210 Y210 Z240 F10000", "ok", 100,  _("Moving to safe zone") )
    app.macro("G27 Z0",                   "ok", 100,  _("Zeroing Z axis") )
    app.macro("M400",       "ok", 200,   _("Waiting for all moves to finish") )
    
def end_additive_aborted(app, args = None, lang='en_US.UTF-8'):
    _ = setLanguage(lang)
    app.macro("G91",                        "ok", 2,    _("Setting Relative position") )
    app.macro("G0 Z5 F10000",   "ok", 100,  _("Moving to safe zone") )
    app.macro("M400",       "ok", 200,    _("Waiting for all moves to finish") )

def check_additive(app, args = None, lang='en_US.UTF-8'):
    _ = setLanguage(lang)
    try:
        safety_door = app.config.get('settings', 'safety')['door']
    except KeyError:
        safety_door = 0
    
    app.trace( _("Checking safety measures") )
    if safety_door == 1:
        app.macro("M741",   "TRIGGERED", 2, _("Front panel door control") )
        
    app.trace( _("Checking building plate") )
    app.macro("M744",       "TRIGGERED", 1, _("Build plate needs to be flipped to the printing side"), verbose=False )
    app.macro("M742",       "TRIGGERED", 1, _("Spool panel control"), verbose=False, warning=True)

def engage_feeder(app, args = None, lang='en_US.UTF-8'):
    _ = setLanguage(lang)
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

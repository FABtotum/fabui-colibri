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

__authors__ = "Marco Rizzuto, Daniel Kesler, Krios Mane"
__license__ = "GPL - https://opensource.org/licenses/GPL-3.0"
__version__ = "1.0"

# Import standard python module
import os
import json

# Import external modules

# Import internal modules
from fabtotum.utils.translation import _, setLanguage
from fabtotum.fabui.macros.common import getPosition, configure_head
from fabtotum.fabui.constants import *

def pause_additive(app, args=None, lang='en_US.UTF-8'):
    app.macro("M999",   "ok", 5,    _("Clearing error state"), verbose=False)
    #app.macro("M756",   "ok", 1,    _("Clearing error state"))
    app.macro("M400",   "ok", 240,    _("Waiting for all moves to finish"), verbose=False)
    position = getPosition(app, lang)
    
    with open('/var/lib/fabui/settings/stored_task.json', 'w') as f:
        f.write( json.dumps({
            'position':position
            }) )
    
    current_z = float(position['z'])
    safe_z = current_z + 50.0
    
    max_z = app.config.get('settings', 'z_max_offset', Z_MAX_OFFSET) - 5
    if safe_z > max_z:
        safe_z = max_z
    
    feeder = app.config.get_current_feeder_info()
    #app.macro("M82",                "ok", 2,    _("E relative position mode"), verbose=False )
    #app.macro("G0 E-{0} F{1}".format(feeder['retract_amount'], feeder['retract_feedrate']),  "ok", 20,    _("Retract fillament") )
    
    app.macro("G90",                "ok", 2,    _("Setting abs position"), verbose=False )
    app.macro("G0 Z{0} F5000".format(safe_z),        "ok", 100,  _("Moving to Z safe zone"), verbose=False )
    app.macro("G0 X210 Y210 F6000", "ok", 100,  _("Moving to safe zone"), verbose=False )
    #block stepper motor for 5min => 60*5=300
    app.macro("M84 S300", "ok", 2, _("Block stepper motor"), verbose=False)
    app.macro("M732 S0", "ok", 2, _("Disabling door safety"))
    
def resume_additive(app, args=None, lang='en_US.UTF-8'):
    
    
    ext_temp = args[0]
    bed_temp = args[1]
    
    try:
        wire_end = app.config.get('settings', 'wire_end', 0)
    except KeyError:
        wire_end = 0
    
    
    safety_door = app.config.get('settings', 'safety.door', 0)
    
    head = app.config.get_current_head_info()
    is_pro_head = app.config.is_pro_head(head['fw_id'])
    
    #if(is_pro_head == True and wire_end == 1):
        #app.macro("M805 S1", "ok", 1, _("Enable wire end check"), verbose=False)
        #app.macro("M740", "TRIGGERED", 1, _("Filament not inserted"), verbose=False)
        
    #block stepper motor for 1min => 60*1=60
    app.macro("M84 S60",                     "ok", 2,  _("Block stepper motor"), verbose=False)
    app.macro("M82",                         "ok", 1,  _(" Set extruder to absolute mode"),  verbose=False)
    app.macro("M104 S{0}".format(ext_temp),  "ok", 5,  _("Heating Nozzle"), verbose=False)
    app.macro("M140 S{0}".format(bed_temp),  "ok", 5,  _("Heating Bed"), verbose=False)
    app.macro("M109 S{0}".format(ext_temp),  "*", 400, _("Waiting for nozzle to reach temperature {0}&deg;".format(ext_temp)) ) #heating and waiting.
    app.macro("M190 S{0}".format(bed_temp),  "*", 400, _("Waiting for bed to reach temperature {0}&deg;".format(bed_temp)) ) #heating and waiting.
    app.macro("M84", "ok", 2, _("Unlock stepper motor"), verbose=False)
    app.macro("M732 S{0}".format(safety_door), "ok", 2, _("Set door safety"))
    
    if(is_pro_head == True and wire_end == 1):
        app.macro("M805 S1",   "ok", 1,    _("Enable wire endstop"), verbose=False)
    # restore position
    if os.path.exists('/var/lib/fabui/settings/stored_task.json'):
        content = {}
        with open('/var/lib/fabui/settings/stored_task.json') as f:
            content = json.load(f)
        
        os.remove('/var/lib/fabui/settings/stored_task.json')
        
        if "position" in content:
            x = float(content['position']['x'])
            y = float(content['position']['y'])
            z = float(content['position']['z'])
            e = float(content['position']['e'])
            
            app.macro("G28 XY",                         "ok", 20,   _("Homing"), verbose=False)
            app.macro("G92 E{0:.16f}".format(e),        "ok", 2,    _("Set extuder length"), verbose=False)
            app.macro("G90",                            "ok", 2,    _("Setting abs position"), verbose=False )
            app.macro("G0 X{0} Y{1} F6000".format(x,y), "ok", 60,   _("Restore XY position"), verbose=False )
            app.macro("G0 Z{0} F5000".format(z),        "ok", 60,   _("Restore Z position"), verbose=False )
            app.macro("M400",                           "ok", 120,  _("Waiting for all moves to finish"), verbose=False)
            #app.macro("G0 E{0} F{1}".format(feeder['retract_amount'], feeder['retract_feedrate']),  "ok", 20,    _("Restore fillament") )

def prepare_additive(app, args=None, lang='en_US.UTF-8'):
    _ = setLanguage(lang)
    
    ext_temp = args[0];
    bed_temp = args[1];

    app.macro("M104 S"+str(ext_temp),   "ok", 3,    _("Pre Heating Nozzle ({0}&deg;) (fast)").format(str(ext_temp)))
    app.macro("M140 S"+str(bed_temp),   "ok", 3,    _("Pre Heating Bed ({0}&deg;) (fast)").format(str(bed_temp)))
    
    app.macro("M402", "ok", 2,    _("Retract Probe"), verbose=False)

def start_additive(app, args = None, lang='en_US.UTF-8'):
    _ = setLanguage(lang)
    
    feeder = app.config.get_current_feeder_info();
    units_e = feeder['steps_per_unit']
    
    head_file = os.path.join( app.config.get('hardware', 'heads'), app.config.get('settings', 'hardware.head') + '.json');
    
    configure_head(app, app.config.get('settings', 'hardware.head'))

    with open(head_file) as json_f:
        head = json.load(json_f)
    
    offset  = float(head.get('nozzle_offset', 0))

    # homing direction (left= 0, right=1)
    try:
        switch = app.config.get('settings', 'switch')
    except KeyError:
        switch = 0
    
    app.trace( _("Preparing the FABtotum Personal Fabricator") )
    app.macro("G90",                    "ok", 2,    _("Setting absolute position"), verbose=False)
    
    oozing_z = 60.0
    oozing_z_adjusted = 60.0 + offset
        
    if switch == 0:
        app.macro("G0 X5 Y5 Z{0:.16f} F1500".format(oozing_z_adjusted),     "ok", 10,    _("Moving to oozing point") )
        app.macro("G92 Z{0}".format(oozing_z),  "ok" , 10, _("Adjusting nozzle offset"), verbose=False );
    else:
        app.macro("G0 X209 Y5 Z{0:.16f} F1500".format(oozing_z_adjusted),     "ok", 10,    _("Moving to oozing point") )
        app.macro("G92 Z{0}".format(oozing_z),  "ok" , 10, _("Adjusting nozzle offset"), verbose=False );
        
    #~ # Pre-heating (dismissed)
    app.macro("M220 S100",              "ok", 1,  _("Reset Speed factor override"),     verbose=False)
    app.macro("M221 S100",              "ok", 1,  _("Reset Extruder factor override"),  verbose=False)
    app.macro("M92 E"+str(units_e),     "ok", 1,  _("Setting extruder mode"),           verbose=False)
    app.macro("M82",                    "ok", 1,  _("Set extruder to absolute mode"),   verbose=False)
    app.macro("G92 E0",                 "ok", 1,  _("Zero the extruder length"),        verbose=False)
    app.macro("M400",                   "ok", 60, _("Waiting for all moves to finish"), verbose=False)

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
    
    try:
        wire_end = app.config.get('settings', 'wire_end', 0)
    except KeyError:
        wire_end = 0
        
    #note: movement here is done so it works with AUTO positioning (additive mode).
    app.trace( _("Terminating...") )
    
    if(wire_end == 1):
        app.macro("M805 S1",   "ok", 1,    _("Enable wire endstop"), verbose=False)
    #macro("G90","ok",100,"Set Absolute movement",0.1,verbose=False)
    #macro("G90","ok",2,"Set Absolute movement",1)
    #macro("G0 X210 Y210 Z200 F10000","ok",100,"Moving to safe zone",0.1,verbose=False) #right top, normally Z=240mm
    app.macro("M400",       "ok", 100,  _("Waiting for all moves to finish"), verbose=False )
    app.macro("M104 S0",    "ok", 50,   _("Shutting down Extruder"), verbose=False)
    app.macro("M140 S0",    "ok", 50,   _("Shutting down Heated Bed"), verbose=False)
    app.macro("M220 S100",  "ok", 20,   _("Reset Speed factor override"), verbose=False)
    app.macro("M221 S100",  "ok", 20,   _("Reset Extruder factor override"), verbose=False)
    app.macro("M107",       "ok", 50,   _("Turning Fan off"))       #should be moved to firmware
    app.macro("M18",        "ok", 10,   _("Motor Off"))             #should be moved to firmware
    #go back to user-defined colors
    app.macro("M701 S"+str(color['r']), "ok", 2,    _("Turning on lights"), verbose=False)
    app.macro("M702 S"+str(color['g']), "ok", 2,    _("Turning on lights"), verbose=False)
    app.macro("M703 S"+str(color['b']), "ok", 2,    _("Turning on lights"), verbose=False)
    app.macro("M300",                   "ok", 1,    _("Printing completed!"), verbose=False)  #end print signal
    app.macro("M18",                    "ok", 2,    _("Motors off"), verbose=False    )

def end_additive_safe_zone(app, args = None, lang='en_US.UTF-8'):
    _ = setLanguage(lang)
    app.macro("M121",                     "ok", 2,    _("Force endstops"), verbose=False )
    app.macro("G90",                      "ok", 2,    _("Setting abs position"), verbose=False)
    app.macro("G0 X210 Y210 Z240 F10000", "ok", 100,  _("Moving to safe zone"), verbose=False)
    app.macro("G27 Z0",                   "ok", 100,  _("Zeroing Z axis"), verbose=False)
    app.macro("M400",                     "ok", 200,   _("Waiting for all moves to finish") )
    app.macro("M18",                      "ok", 2,    _("Motors off"), verbose=False    )
        
def end_additive_aborted(app, args = None, lang='en_US.UTF-8'):
    _ = setLanguage(lang)
    #app.macro("G91",                        "ok", 2,    _("Setting rel position") )
    #app.macro("G0 Z5 F10000",   "ok", 100,  _("Moving to safe zone") )
    app.macro("G27 Z0", "ok", 100,  _("Moving to safe zone") )
    app.macro("G28 XY", "ok", 100,  _("Zeroing Z axis"), verbose=False )
    app.macro("M400",   "ok", 200,  _("Waiting for all moves to finish") )
    app.macro("M18",    "ok", 2,    _("Motors off"), verbose=False    )

def check_additive(app, args = None, lang='en_US.UTF-8'):
    _ = setLanguage(lang)
    try:
        safety_door = app.config.get('settings', 'safety')['door']
    except KeyError:
        safety_door = 0
    
    try:
        bed_enabled = app.config.get('settings', 'hardware')['bed']['enable']
    except KeyError:
        bed_enabled = True
    
    try:
        wire_end = app.config.get('settings', 'wire_end', 0)
    except KeyError:
        wire_end = 0


    head = app.config.get_current_head_info()
    is_pro_head = app.config.is_pro_head(head['fw_id'])
    
    app.trace( _("Checking safety measures") )
    
    if(is_pro_head == True and wire_end == 1):
        app.macro("M805 S1", "ok", 1, _("Enable wire end check"), verbose=False)
        app.macro("M740", "TRIGGERED", 1, _("Filament not inserted"), verbose=False)
    else:
        app.macro("M805 S0", "ok", 1, _("Disable wire end check"), verbose=False)
    
    if safety_door == 1:
        app.macro("M741",   "TRIGGERED", 2, _("Front panel door opened") )
        
    app.trace( _("Checking building plate") )
    if bed_enabled == True:
        app.macro("M744",       "TRIGGERED", 1, _("Build plate needs to be flipped to the printing side"), verbose=False )
    
    app.macro("M742",       "TRIGGERED", 1, _("Spool panel control"), verbose=False, warning=True)
    

def engage_feeder(app, args = None, lang='en_US.UTF-8'):
    _ = setLanguage(lang)
    try:
        safety_door = app.config.get('settings', 'safety')['door']
    except KeyError:
        safety_door = 0
        
    feeder = app.config.get_current_feeder_info();
    units_e = feeder['steps_per_unit']

    app.trace( _("Engaging 3D-Printer Feeder") )
    if safety_door == 1:
        app.macro("M741",           "TRIGGERED", 2, _("Front panel door opened"), verbose=False )
        
    app.macro("M742",               "TRIGGERED", 1, _("Spool panel control"), warning=True, verbose=False)
    app.macro("G27",                "ok", 100,      _("Zeroing Z axis") )
    app.macro("G91",                "ok", 1,        _("Set rel position"), verbose=False)
    app.macro("G0 Z-4 F1000",       "ok", 5,        _("Setting Z position") )
    app.macro("M400",               "ok", 5,        _("Waiting for all moves to finish"), verbose=False)
    app.macro("G90",                "ok", 1,        _("Set abs position") )
    app.macro("M92 E"+str(units_e), "ok", 1,        _("Setting extruder mode") )
    app.macro("M18",                "ok", 3,        _("Stopping motors"), verbose=False)
    app.macro("M300",               "ok", 3,        _("Play beep sound"), verbose=False)   

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
import re
import gettext

# Import external modules

# Import internal modules


# Set up message catalog access
tr = gettext.translation('gmacro', 'locale', fallback=True)
_ = tr.ugettext

def home_all(app, args = None):
    
    try:
        zprobe = app.config.get('settings', 'zprobe')
        zprobe_disabled = (zprobe['disable'] == 1)
        zmax_home_pos   = float(zprobe['zmax'])
    except KeyError:
        zmax_home_pos = 206.0
        zprobe_disabled = False
    
    app.trace( _("Now homing all axes") )
    app.macro("G90", "ok", 2, _("Set abs position"), 0, verbose=False)
    
    #macro("G28","ok",100,"homing all axes",1,verbose=False)
    if zprobe_disabled:
        app.macro("G27 X0 Y0 Z" + str(zmax_home_pos),   "ok", 100,  _("Homing all axes"), 0.1)
        app.macro("G0 Z50 F10000",                      "ok", 15,   _("Raising"), 0.1, verbose=False)
    else:
        app.macro("G28",                                "ok", 100,  _("Homing all axes"), 1, verbose=False)

def start_up(app, args = None):
    
    try:
        color = app.config.get('settings', 'color')
    except KeyError:
        color = {
            'r' : 255,
            'g' : 255,
            'b' : 255,
        }
    
    try:
        safety_door = app.config.get('settings', 'safety')['door']
    except KeyError:
        safety_door = 0
    
    try:
        switch = app.config.get('settings', 'switch')
    except KeyError:
        switch = 0
    
    try:
        collision_warning = app.config.get('settings', 'safety')['collision-warning']
    except KeyError:
        collision_warning = 0
    
    app.trace( _("Starting up") )
    app.macro("M728",                   "ok", 2, _("Alive!"), 1,verbose=False)
    app.macro("M402",                   "ok", 1, _("Probe Up"), 0)
    app.macro("M701 S"+str(color['r']), "ok", 2, _("turning on lights"), 0.1, verbose=False)
    app.macro("M702 S"+str(color['g']), "ok", 2, _("turning on lights"), 0.1, verbose=False)
    app.macro("M703 S"+str(color['b']), "ok", 2, _("turning on lights"), 0.1, verbose=False)
    
    app.macro("M732 S"+str(safety_door),"ok", 2, _("Safety Settings"), 0.1, verbose=False)
    app.macro("M714 S"+str(switch),     "ok", 2, _("Homing direction"), 0.1, verbose=False)
    

    app.macro("M734 S"+str(collision_warning),  "ok", 2, _("Machine Limits Collision warning") ,0.1,verbose=False)

def shutdown(app, args = None):
    app.trace( _("Shutting down...") ) 
    app.macro("M300",   "ok", 5, _("Play alert sound!"), 1, verbose=False)
    app.macro("M729",   "ok", 2, _("Asleep!"), 1, verbose=False)
    
def raise_bed(app, args = None):
    """
    For homing procedure before probe calibration and print without homing.
    """
    try:
        zprobe = app.config.get('settings', 'zprobe')
        zprobe_disabled = (zprobe['disable'] == 1)
        zmax_home_pos   = float(zprobe['zmax'])
    except KeyError:
        zmax_home_pos = 206.0
        zprobe_disabled = False
    
    app.macro("M402",   "ok", 4,    _("Raising probe"), 0.1, verbose=True)
    app.macro("G90",    "ok", 2,    _("Setting absolute position"), 1)
    
    #macro("G27","ok",100,"Homing all axes",0.1)
    #macro("G0 Z10 F10000","ok",15,"raising",0.1)
    #macro("G28","ok",100,"homing all axes",0.1)
    if zprobe_disabled:
        app.macro("G27 X0 Y0 Z" + str(zmax_home_pos),   "ok", 100,  _("Homing all axes"), 0.1)
        app.macro("G0 Z50 F10000",                      "ok", 15,   _("Raising"), 0.1)
    else:
        app.macro("G27",            "ok", 100,  _("Homing all axes"), 0.1)
        app.macro("G0 Z10 F10000",  "ok", 15,   _("Raising"), 0.1)
        app.macro("G28",            "ok", 100,  _("Homing all axes"), 0.1, verbose=False)

def auto_bed_leveling(app, args = None):
    app.trace( _("Auto Bed leveling Initialized") )
    app.macro("G91",                "ok", 2,    _("Setting relative position"), 1, verbose=False)
    app.macro("G0 Z25 F1000",       "ok", 2,    _("Moving away from the plane"), 1,verbose=False)
    app.macro("G90",                "ok", 2,    _("Setting abs position"), 1, verbose=False)
    app.macro("G28",                "ok", 90,   _("Homing all axis"), 1)
    app.macro("G29",                "ok", 140,  _("Auto bed leveling procedure"), 1)
    app.macro("G0 X5 Y5 Z60 F2000", "ok", 100,  _("Getting to idle position"), 1)

def probe_down(app, args = None):
    app.macro("M401",   "ok", 1, _("Probe Down"), 0)
    
def probe_up(app, args = None):
    app.macro("M402",   "ok", 1, _("Probe Up"), 0)

def safe_zone(app, args = None):
    """ .. todo: turn these into macroes """
    app.send("G91")
    app.send("G0 E-5 F1000")
    app.send("G0 Z+1 F1000")
    app.send("G90")
    app.send("G27 Z0")
    app.send("G0 X210 Y210")

def engage_4axis(app, args = None):
    units_a = app.config.get('settings', 'a')
    try:
        feeder_disengage_offset = app.config.get('settings', 'feeder')['disengage-offset']
    except KeyError:
        feeder_disengage_offset = 2
    
    app.trace( _("Engaging 4th Axis") )
    app.macro("G27",                "ok", 100,  _("Zeroing Z axis"), 0.1)
    app.macro("G91",                "ok", 1,    _("Setting Relative position"), 0.1,verbose=False)
    app.macro("M120",               "ok", 1,    _("Disable Endstop checking"), 0.1)
    app.macro("G0 Z+"+str(feeder_disengage_offset)+" F300", "ok", 5,    _("Engaging 4th Axis Motion"), 0.1)
    app.macro("M400",               "ok", 5,    _("Waiting for all moves to finish"), 0.1, verbose=False)
    app.macro("M121",               "ok", 1,    _("Enable Endstop checking"), 0.1)
    app.macro("M92 E"+str(units_a), "ok", 1,    _("Setting 4th axis mode"), 0)
    app.macro("G92 Z241",           "ok", 1,    _("Setting position"), 0.1, verbose=False)
    app.macro("G90",                "ok", 1,    _("Setting Absolute position"), 0.1, verbose=False)
    app.macro("G0 Z234",            "ok", 1,    _("Check position"), 0.1, verbose=False)
    app.macro("M300",               "ok", 3,    _("Play beep sound"), 1, verbose=False)
    
def do_4th_axis_mode(app, args = None):
    units_a = app.config.get('settings', 'a')
    app.macro("M92 E"+str(units_a), "ok", 1,    _("Setting 4th axis mode"), 0, verbose=False)

def version(app, args = None):
    pass

def read_eeprom(app, args = None):
    
    def serialize(string_source, regex_to_serach, keys):
        match = re.search(regex_to_serach, string_source, re.IGNORECASE)
        if match != None:
            string = match.group(1)
            object = {}
            object.update({'string':string})
            for index in keys:
                match_temp = re.search(index+'([0-9.]+)', string, re.IGNORECASE)
                if match_temp != None:
                    val = match_temp.group(1)
                    object.update({index:val})
            return object
            
    def getServoEndstopValues(string_source):
        match = re.search('Servo\sEndstop\ssettings:\sR:\s([0-9.]+)\sE:\s([0-9.]+)', string_source, re.IGNORECASE)
        if match != None:
            object = {'r': match.group(1), 'e': match.group(2)}
            return object
    
    reply = app.macro('M503', None, 1, _("Reading settings from eeprom"), 1, verbose=False)
    
    #echo:Z Probe Length: -32.05
    probe_length = reply[17].split('Probe Length:')[1].strip()
    
    eeprom = {
        "steps_per_unit"        : serialize(reply[3], '(M92\sX[0-9.]+\sY[0-9.]+\sZ[0-9.]+\sE[0-9.]+)', ['x', 'y', 'z', 'e']),
        "maximum_feedrates"     : serialize(reply[5], '(M203\sX[0-9.]+\sY[0-9.]+\sZ[0-9.]+\sE[0-9.]+)', ['x', 'y', 'z', 'e']),
        "maximum_accelaration"  : serialize(reply[7], '(M201\sX[0-9.]+\sY[0-9.]+\sZ[0-9.]+\sE[0-9.]+)', ['x', 'y', 'z', 'e']),
        "acceleration"          : serialize(reply[9], '(M204\sS[0-9.]+\sT1[0-9.]+)', ['s', 't1']),
        "advanced_variables"    : serialize(reply[11],'(M205\sS[0-9.]+\sT0[0-9.]+\sB[0-9.]+\sX[0-9.]+\sZ[0-9.]+\sE[0-9.]+)', ['s', 't', 'b', 'x', 'z', 'e']),
        "home_offset"           : serialize(reply[13],'(M206\sX[0-9.]+\sY[0-9.]+\sZ[0-9.]+)', ['x', 'y', 'z']),
        "pid"                   : serialize(reply[15],'(M301\sP[0-9.]+\sI[0-9.]+\sD[0-9.]+)', ['p', 'i', 'd']),
        "probe_length"          : probe_length,
        "servo_endstop"         : getServoEndstopValues(reply[16])
    }
    
    return eeprom

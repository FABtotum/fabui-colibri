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


def pre_unload_spool(app, args = None):
    reply = app.send("M105")
    serial_reply = reply[0].rstrip()
    
    ext_temp = serial_reply.split()[1]
    ext_temp = ext_temp.split(":")[1]
    
    if(float(ext_temp) < 100):
        time_to_wait = 60
    elif(float(ext_temp) > 100 and float(ext_temp) < 150 ):
        time_to_wait = 30
    elif(float(ext_temp) > 150 and float(ext_temp) < 190 ):
        time_to_wait = 10
    elif(float(ext_temp) > 190):
        time_to_wait = 0
        
    app.macro("M104 S190",  "ok", 5,    _("Heating Nozzle..."), time_to_wait, verbose=True) #heating and waiting.
    
def unload_spool(app, args = None):
    units_e = app.config.get('units', 'e')
    
    app.trace( _("Unloading Spool : Procedure Started.") )
    app.macro("G90",                "ok", 10,   _("Set abs position"), 0, verbose=False)
    app.macro("M302 S0",            "ok", 10,   _("Extrusion prevention disabled"), 0.1,verbose=False)
    app.macro("G27",                "ok", 100,  _("Zeroing Z axis"), 1, verbose=False)
    app.macro("G0 Z150 F10000",     "ok", 10,   _("Moving to safe zone"), 0.1, verbose=False) #right top corner Z=150mm
    app.macro("G91",                "ok", 2,    _("Set rel position"), 0, verbose=False)
    app.macro("G92 E0",             "ok", 5,    _("Set extruder to zero"), 0.1, verbose=False)
    app.macro("M92 E"+str(units_e), "ok", 30,   _("Setting extruder mode"), 0.1, verbose=True)

    app.macro("M300",               "ok", 2,    _("<b>Start Pulling!</b>"), 3)
    app.macro("G0 E-800 F550",      "ok", 10,   _("Expelling filament"), 1)
    app.macro("G0 E-200 F550",      "ok", 10,   _("Expelling filament"), 1, verbose=False)

    app.macro("M104 S0",            "ok", 1,    _("Disabling Extruder"), 1)
    app.macro("M302 S170",          "ok", 10,   _("Extrusion prevention enabled"), 0.1, verbose=False)
    app.trace( _("Done!") )
    
def load_spool(app, args = None):
    units_e = app.config.get('units', 'e')
    
    app.trace( _("Loading Spool : Procedure Started.") )
    app.macro("G90",                "ok", 2,    _("Set abs position"), 0, verbose=False)
    app.macro("G27",                "ok", 100,  _("Zeroing Z axis"), 0.1, verbose=False)
    app.macro("G0 Z150 F10000",     "ok", 10,   _("Moving to Safe Zone"), 0.1, verbose=False)
    app.macro("M302 S0",            "ok", 5,    _("Enabling Cold extrusion"), 0.1, verbose=False)
    app.macro("G91",                "ok", 2,    _("Set relative position"), 0, verbose=False)
    app.macro("G92 E0",             "ok", 5,    _("Setting extruder position to 0"), 0.1, verbose=False)
    app.macro("M92 E"+str(units_e), "ok", 5,    _("Setting extruder mode"), 0.1, verbose=True)
    app.macro("M104 S190",          "ok", 5,    _("Heating Nozzle. Get ready to push..."), 0.1) #heating and waiting.
    app.macro("M300",               "ok", 5,    _("<b>Start pushing!</b>"), 3)

    app.macro("G0 E110 F500",       "ok", 1,    _("Loading filament"), 15)
    app.macro("G0 E660 F700",       "ok", 1,    _("Loading filament (fast)"), 20,verbose=False)
    app.macro("M109 S210",          "ok", 200,  _("Waiting to get to temperature..."), 0.1) #heating and waiting.
    app.macro("G0 E100 F200",       "ok", 1,    _("Entering the hotend (slow)"), 0.1)

    app.macro("M104 S0",            "ok", 1,    _("Turning off heat"), 0.1)
    app.macro("M302 S170",          "ok", 1,    _("Disabling Cold Extrusion Prevention"), 0.1,verbose=False)

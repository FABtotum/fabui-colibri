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
    
    units_a = app.config.get('settings', 'a')
    
    app.macro("G92 X0 Y0 Z0 E0", "ok", 1,       _("Setting Origin Point"), verbose=False)
    app.macro("M92 E"+str(units_a), "ok", 1,    _("Setting 4th Axis mode"), verbose=False)
    app.macro("M106 S255", "ok", 1,             _ ("Turning fan on"), verbose=False)
    
    
def end_engraving(app, args = None, lang='en_US.UTF-8'):
    _ = setLanguage(lang)
    

    app.trace("Terminating...")    
    app.macro("G0 X0 Y0 Z0 E0 F2000", "ok", 1, _("Go back to Origin Point"), verbose=False)
    
    # Deinitialize and restore settings
    end_engraving_aborted(app, args, lang)

def end_engraving_aborted(app, args = None, lang='en_US.UTF-8'):
    
    units_e = app.config.get('settings', 'e')
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

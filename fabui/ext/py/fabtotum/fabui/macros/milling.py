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

def start_subtractive(app, args = None, lang='en_US.UTF-8'):
    _ = setLanguage(lang)
    
    units_a = app.config.get('settings', 'a')
    
    app.macro("G92 X0 Y0 Z0 E0", "ok", 1,       _("Setting Origin Point"), verbose=False)
    app.macro("M92 E"+str(units_a), "ok", 1,    _("Setting 4th Axis mode"), verbose=False)
    
    
def end_subtractive(app, args = None, lang='en_US.UTF-8'):
    _ = setLanguage(lang)
    units_e = app.config.get('settings', 'e')
    
    try:
        color = app.config.get('settings', 'color')
    except KeyError:
        color = {
            'r' : 255,
            'g' : 255,
            'b' : 255,
        }

    app.trace("Terminating...")
    #macro("G27","ok",100,"Lowering the building platform",1,verbose=False) #normally Z=240mm
    #note: movement here is done so it works with manual positioning (subtractive mode).
    
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

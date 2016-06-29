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


def start_subtractive(app, args = None):
    units_a = app.config.get('units', 'a')
    
    #macro("G92 X0 Y0 Z0 E0","ok",3,"Setting Origin Point",1)
    #macro("G90","ok",3,"Setting Origin Point",0.1, verbose=False)
    app.macro("M92 E"+str(units_a), "ok", 1,    _("Setting 4th Axis mode"), 0.1, verbose=False)
    
    
def end_subtractive(app, args = None):

    try:
        color = app.config.get('units', 'color')
    except KeyError:
        color = {
            'r' : 255,
            'g' : 255,
            'b' : 255,
        }

    app.trace("Terminating...")
    #macro("G27","ok",100,"Lowering the building platform",1,verbose=False) #normally Z=240mm
    #note: movement here is done so it works with manual positioning (subtractive mode).
    
    app.macro("M400",       "ok", 1,    _("Waiting for all moves to finish"), 1)
    app.macro("M5",         "ok",100,   _("Shutting Down Milling Motor"), 1) #should be moved to firmware       
    app.macro("M220 S100",  "ok",50,    _("Reset Speed factor override"), 0.1)
    app.macro("M221 S100",  "ok",1,     _("Reset Extruder factor override"), 0.1)
    app.macro("M107",       "ok",50,    _("Turning Fan off"), 1) # moved to firmware
    app.macro("M18",        "ok",50,    _("Motor Off"), 1) 
    #go back to user-defined colors
    app.macro("M701 S"+str(color['r']), "ok", 2,    _("Turning on lights"), 0.1, verbose=False)
    app.macro("M702 S"+str(color['g']), "ok", 2,    _("Turning on lights"), 0.1, verbose=False)
    app.macro("M703 S"+str(color['b']), "ok", 2,    _("Turning on lights"), 0.1, verbose=False)
    app.macro("M300",                   "ok",10,    _("Milling completed!"), 0.1) #should be moved to firmware

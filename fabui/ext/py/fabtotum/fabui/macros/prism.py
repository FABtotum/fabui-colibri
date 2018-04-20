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

# Import internal modules
from fabtotum.utils.translation import _, setLanguage

from fabtotum.fabui.macros.common import set_lights, getPosition


def initial_prism_homing(app, args=None, lang='en_US.UTF-8'):
    
    setLanguage(lang)
    
    app.trace(_("Moving head to safe zone"))
    
    app.macro("G28 XY", "ok", 90, _("Homing XY"), verbose=False)
    
    app.macro("G90", "ok", 2, _("Absoulte mode"), verbose=False)
    
    app.macro("G0 X210 Y210 F10000", "ok", 90, _("Going to safe zone"), verbose=False)
    
    app.macro("M400", "ok", 120, _("Wait for all movements"), verbose=False)
    
    app.trace(_("Positioning the platform"))
    
    app.macro("G91", "ok", 1, _("Relative mode"), verbose=False)
    
    app.macro("G0 Z-200 F500", "ok", 60, _("Going up"), verbose=False)
    
    app.macro("M400", "ok", 120, _("Wait for all movements"), verbose=False)
    
    app.macro("G92 Z0", "ok", 1, _("Set Z 0"), verbose=False)
    
    app.macro("M564 Z175 S1", "ok", 1, _("Restrict Z movements"), verbose=False)
    
    app.macro("G90", "ok", 1, _("Absolute mode"), verbose=False)
    
    app.macro("G0 Z130 F1000", "ok", 60, _("Going to position (fast)"), verbose=False)
    
    app.macro("G0 Z150 F200", "ok", 60, _("Going to position (slow)"), verbose=False)
    
    app.macro("M400", "ok", 120, _("Wait for all movements"), verbose=False)
    
    
def prepare_prism(app, args=None, lang='en_US.UTF-8'):
    
    setLanguage(lang)
    
    app.trace( _("Turning off lights") )
    
    set_lights(app, [0, 0, 0])
    
    app.macro("M564 S0", "ok", 3, _("Disbale restricted movements"), verbose=False)
    
    app.macro("G92 Z0", "ok", 3, _("Set Z 0"), verbose=False)
    
    
def pause_prism(app, args=None, lang='en_US.UTF-8'):
    
    setLanguage(lang)
    
    try:
        
        max_height = args[0]
    
    except:
        
        max_height = 40.00
        
    try:
        
        z_offset = args[1]
    
    except:
        
        z_offset = 100.00
        
    app.macro("M300", "ok", 3, _("Play beep"), verbose=False)
    
    # turn lights to red
    set_lights(app, [25, 2, 0])
    
    # get position
    position = getPosition(app, lang)
    
    current_z = float(position['z'])
    
    # raise platform only if object's height is < max_height
    
    if current_z < max_height :
        
        # store position
        with open('/var/lib/fabui/settings/stored_task.json', 'w') as f:
            
            f.write( json.dumps({ 'position': position }) )
        
        safe_z = current_z + z_offset
        
        app.macro("G91", "ok", 1, _("Relative mode"), verbose=False)
        
        app.macro("G0 Z-{0} F300".format(safe_z), "ok", 100,  _("Raising platform"), verbose=False )
        
        app.macro("M400", "ok", 120, _("Wait for all movements"), verbose=False)
    
    
    
def resume_prism(app, args=None, lang='en_US.UTF-8'):
    
    setLanguage(lang)
    
    try:
        
        z_offset = args[0]
    
    except:
        
        z_offset = 100.00
    
    app.macro("M999",    "ok", 3, _("Reset all errors"),   verbose=False)
    
    app.trace( _("Turning off lights") )
    
    set_lights(app, [0, 0, 0])
    
    app.macro("M300", "ok", 3, _("Play beep"), verbose=False)
    
    # restore position
    if os.path.exists('/var/lib/fabui/settings/stored_task.json'):
        
        content = {}
        
        with open('/var/lib/fabui/settings/stored_task.json') as f:
            
            content = json.load(f)
        
        os.remove('/var/lib/fabui/settings/stored_task.json')
        
        if "position" in content:
            
            z = float(content['position']['z'])
            
            restored_z = z + z_offset
            
            app.macro("G91", "ok", 1, _("Relative mode"), verbose=False)
            
            app.macro("G0 Z{0} F300".format(restored_z), "ok", 100,  _("Return to position"), verbose=False )
            
            app.macro("M400", "ok", 120, _("Wait for all movements"), verbose=False)
            
            
            
    
def end_prism(app, args=None, lang='en_US.UTF-8'):
    
    setLanguage(lang)
    
    set_lights(app, [0, 0, 0])
    
    app.macro("G91", "ok", 1, _("Relative mode"), verbose=False)
    
    app.macro("G0 Z-200 F300", "ok", 60, _("Raising the platform"), verbose=True)
    
    app.macro("M400", "ok", 120, _("Wait for all movements"), verbose=False)
    
    
    
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
from fabtotum.fabui.macros.common import getPosition, getEeprom, zProbe

def check_measure_probe(app, args = None, lang='en_US.UTF-8'):
    _ = setLanguage(lang)
    try:
        safety_door = app.config.get('settings', 'safety')['door']
    except KeyError:
        safety_door = 0
    
    try:
        bed_enabled = app.config.get('settings', 'hardware')['bed']['enable']
    except KeyError:
        bed_enabled = True
    
    app.trace( _("Checking safety measures") )
    if safety_door == 1:
        app.macro("M741",   "TRIGGERED", 2, _("Front panel door opened") )
        
    app.trace( _("Checking building plate") )
    if bed_enabled == True:
        app.macro("M744",       "TRIGGERED", 1, _("Build plate needs to be flipped to the printing side"), verbose=False )
    
    app.macro("M742",       "TRIGGERED", 1, _("Spool panel control"), verbose=False, warning=True)

def measure_probe_offset(app, args = None, lang='en_US.UTF-8'):
    _ = setLanguage(lang)

    
    ext_temp = 200
    bed_temp = 45
    max_probe_length = 45
    default_probe_length = 38
    eeprom = getEeprom(app)
    curret_probe_length = abs(float(eeprom["probe_length"]))
    zprobe_disabled = int(app.config.get('settings', 'zprobe.enable')) == 0
    
    if(curret_probe_length > max_probe_length):
        app.macro("M710 S{0}".format(default_probe_length), "ok", 2, _("Write config to EEPROM"), verbose=False)
            
    app.macro("M104 S"+str(ext_temp),   "ok", 3,    _("Pre Heating Nozzle ({0}&deg;) (fast)").format(str(ext_temp)))
    app.macro("M140 S"+str(bed_temp),   "ok", 3,    _("Pre Heating Bed ({0}&deg;) (fast)").format(str(bed_temp)))
    
    # app.trace( _("Preparing Calibration procedure") )    
    # Get Z-Max
    app.trace( _("Measuring Z max offset") )
    app.macro("M206 X0 Y0 Z0",      "ok", 5,    _("Reset homing offset"), verbose=False )
    app.macro("G27",                "ok", 100,  _("Homing all axes"), verbose=False )
    zmax = getPosition(app, lang)

    # Get Probe-Length
    if not zprobe_disabled:
        app.trace( _("Measuring probe length") )
        app.macro("G90",                "ok", 2,    _("Setting abs position"), verbose=False)
        app.macro("G0 Z50 F1000",       "ok", 100,  _("Moving the bed 50mm away from nozzle"), verbose=False)
        app.macro("G0 X86 Y58 F6000",   "ok", 100,  _("Moving the probe to the center"), verbose=False)
        probe_length = 0.0
        for i in range(0,4):
            app.trace( _("Measurement ({0}/4)").format(i+1) )
            zprobe = zProbe(app, lang)
            probe_length = probe_length + float(zprobe['z'])
            
        probe_length = probe_length / 4
        
        app.macro("M710 S{0}".format(probe_length), "ok", 2, _("Write config to EEPROM"), verbose=False)
    else:
        probe_length = 38.0
    
    # Move closer to nozzle
    app.macro("G90",                "ok", 2,    _("Setting abs position"), verbose=False)
    app.macro("G0 X103 Y119.5 Z20 F1000",  "ok", 100,  _("Moving the bed 20mm away from nozzle"), verbose=False)
    
    app.config.set('settings', 'z_max_offset', float(zmax['z']))
    app.config.set('settings', 'zprobe.length', float(probe_length))
    app.config.save('settings')
    
    return {
        'z_max_offset' : float(zmax['z']),
        'z_probe_offset' : float(probe_length)
    }

def measure_nozzle_prepare(app, args = None, lang='en_US.UTF-8'):
    _ = setLanguage(lang)
    app.macro("M109 S200",          "ok", 200,  _("Waiting for extruder temperature (<span class='top-bar-nozzle-actual'>-</span> / 200&deg;)") )
    
def measure_nozzle_offset(app, args = None, lang='en_US.UTF-8'):
    _ = setLanguage(lang)
    
    app.macro("M104 S0",    "ok", 2,   _("Extruder heating off") )
    app.macro("M140 S0",    "ok", 2,   _("Bed heating off") )
    
    app.trace( _("Measuring nozzle offset") )
    zpos = getPosition(app, lang)
    
    # Paper width is 0.08mm
    nozzle = float(zpos['z']) - 0.08

    app.macro("G90",      "ok", 2,    _("Setting rel position"), verbose=False)
    app.macro("G0 Z+40",  "ok", 100,  _("Moving the bed 40mm away from measured height"), verbose=False)
    
    # Store offset to head config
    head_file = os.path.join( app.config.get('hardware', 'heads'), app.config.get('settings', 'hardware.head') + '.json');
    with open(head_file) as json_f:
        head_info = json.load(json_f)
        
    head_info['nozzle_offset'] = str(round(nozzle,2))
    
    with open(head_file, 'w') as outfile:
        json.dump(head_info, outfile, sort_keys=True, indent=4)
    
    return {
        'nozzle_z_offset' : nozzle
    }

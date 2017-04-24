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

__authors__ = "Krios Mane, Daniel Kesler"
__license__ = "GPL - https://opensource.org/licenses/GPL-3.0"
__version__ = "1.0"

# Import standard python module
import os
import re
import json

from fabtotum.utils.translation import _, setLanguage

def loadFactoryFeeder(config):
    fabui_path = config.get('general', 'fabui_path')
    
    try:
        feeder_file = os.path.join(fabui_path, 'feeders', 'built_in_feeder.json')
        with open(feeder_file) as json_f:
            info = json.load(json_f)
            return info
    except:
        return {
                "name": "Built-in feeder",
                "description": "Built-in feeder (4th axis)",
                "link": "",
                "custom_gcode": "",
                "tube_length": 770,
                "steps_per_unit": 3048.16,
                "steps_per_angle": 177.777778,
                "max_acceleration": 100,
                "max_feedrate": 12,
                "max_jerk": 1,
                "retract_acceleration": 100,
                "retract_feedrate": 12,
                "retract_amount": 4,
                "factory": 1
            }

def updateFactoryFeeder(config, info):
    fabui_path = config.get('general', 'fabui_path')
    
    feeder_file = os.path.join(fabui_path, 'feeders', 'built_in_feeder.json')
    with open(feeder_file, 'w') as json_f:
        json.dump(info, json_f, sort_keys=True, indent=4)

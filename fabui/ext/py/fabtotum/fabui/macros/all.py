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

__authors__ = "Daniel Kesler"
__license__ = "GPL - https://opensource.org/licenses/GPL-3.0"
__version__ = "1.0"

import fabtotum.fabui.macros.general     as general_macros
import fabtotum.fabui.macros.printing    as print_macros
import fabtotum.fabui.macros.milling     as mill_macros
import fabtotum.fabui.macros.scanning    as scan_macros
import fabtotum.fabui.macros.maintenance as maint_macros
import fabtotum.fabui.macros.calibration as calib_macros
import fabtotum.fabui.macros.testing     as test_macros

PRESET_MAP = {
    # General purpose
    "start_up"                      : general_macros.start_up,
    "shutdown"                      : general_macros.shutdown,
    "auto_bed_leveling"             : general_macros.auto_bed_leveling,
#    "jog_setup"                     : general_macros.jog_setup,
    "home_all"                      : general_macros.home_all,
    "probe_down"                    : general_macros.probe_down,
    "probe_up"                      : general_macros.probe_up,
    "raise_bed"                     : general_macros.raise_bed,
    "safe_zone"                     : general_macros.safe_zone,
    "engage_4axis"                  : general_macros.engage_4axis,
    "4th_axis_mode"                 : general_macros.do_4th_axis_mode,  
    # Print
    "check_pre_print"               : print_macros.check_pre_print,
    "engage_feeder"                 : print_macros.engage_feeder,
    "start_print"                   : print_macros.start_additive,
    "end_print_additive"            : print_macros.end_additive,
    "end_print_additive_safe_zone"  : print_macros.end_additive_safe_zone,
    # Maintenance
    "pre_unload_spool"              : maint_macros.pre_unload_spool,
    "unload_spool"                  : maint_macros.unload_spool,
    "load_spool"                    : maint_macros.load_spool,
    # Milling
    "start_subtractive_print"       : mill_macros.start_subtractive,
    "end_print_subtractive"         : mill_macros.end_subtractive,
    # Scanning    
    "check_pre_scan"                : scan_macros.check_pre_scan, 
    "r_scan"                        : scan_macros.rotary_scan,
    "pg_scan"                       : scan_macros.photogrammetry_scan,
    "s_scan"                        : scan_macros.sweep_scan,
    "p_scan"                        : scan_macros.probe_scan,
    "end_scan"                      : scan_macros.end_scan,
    # Calibration
    "probe_setup_prepare"           : calib_macros.probe_setup_prepare,
    "probe_setup_calibrate"         : calib_macros.probe_setup_calibrate,
    "raise_bed_no_g27"              : calib_macros.raise_bed_no_g27,
    # Test
    #~ "laser"                         : test_macros.laser,
    #~ "mill"                          : test_macros.mill,
    #~ "blower"                        : test_macros.blower,
    #~ "head_light"                    : test_macros.head_light,
    #~ "g28"                           : test_macros.g28,
    #~ "end_stop"                      : test_macros.end_stop,
    #~ "temperature"                   : test_macros.temperature
}

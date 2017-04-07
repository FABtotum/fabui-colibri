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
import fabtotum.fabui.macros.engraving   as laser_macros
import fabtotum.fabui.macros.scanning    as scan_macros
import fabtotum.fabui.macros.maintenance as maint_macros
import fabtotum.fabui.macros.calibration as calib_macros

PRESET_MAP = {
    # General purpose
    "start_up"                      : general_macros.start_up,
    "shutdown"                      : general_macros.shutdown,
    "auto_bed_leveling"             : general_macros.auto_bed_leveling,
    "home_all"                      : general_macros.home_all,
    "probe_down"                    : general_macros.probe_down,
    "probe_up"                      : general_macros.probe_up,
    "safe_zone"                     : general_macros.safe_zone,
    "engage_4axis"                  : general_macros.engage_4axis,
    "4th_axis_mode"                 : general_macros.do_4th_axis_mode,  
    "read_eeprom"                   : general_macros.read_eeprom,  
    "set_ambient_color"             : general_macros.set_ambient_color,  
    "version"                       : general_macros.version,  
    "install_head"                  : general_macros.install_head,
    "install_feeder"                : general_macros.install_feeder,
    "clear_errors"                  : general_macros.clear_errors,
    
    # Print
    "prepare_additive"              : print_macros.prepare_additive,
    "engage_feeder"                 : print_macros.engage_feeder,
    "check_additive"                : print_macros.check_additive,
    "start_additive"                : print_macros.start_additive,
    "end_additive"                  : print_macros.end_additive,
    "end_additive_safe_zone"        : print_macros.end_additive_safe_zone,
    "end_additive_aborted"          : print_macros.end_additive_aborted,
    "pause_additive"                : print_macros.pause_additive,
    "resume_additive"               : print_macros.resume_additive,
    
    # Milling
    "check_subtractive"             : mill_macros.check_subtractive,
    "start_subtractive"             : mill_macros.start_subtractive,
    "end_subtractive"               : mill_macros.end_subtractive,
    "end_subtractive_aborted"       : mill_macros.end_subtractive_aborted,
    "pause_subtractive"             : mill_macros.pause_subtractive,
    "resume_subtractive"            : mill_macros.resume_subtractive,
        
    # Laser
    "check_engraving"               : laser_macros.check_engraving,
    "start_engraving"               : laser_macros.start_engraving,
    "end_engraving"                 : laser_macros.end_engraving,
    "end_engraving_aborted"         : laser_macros.end_engraving_aborted,
    
    # Maintenance
    "pre_unload_spool"              : maint_macros.pre_unload_spool,
    "unload_spool"                  : maint_macros.unload_spool,
    "load_spool"                    : maint_macros.load_spool,
    "extrude"                       : maint_macros.extrude,
    "change_step"                   : maint_macros.change_step,
    "manual_bed_leveling"           : maint_macros.manual_bed_leveling,
    
    # Scanning    
    "check_pre_scan"                : scan_macros.check_pre_scan, 
    "start_rotary_scan"             : scan_macros.rotary_scan,
    "start_photogrammetry_scan"     : scan_macros.photogrammetry_scan,
    "start_sweep_scan"              : scan_macros.sweep_scan,
    "start_probe_scan"              : scan_macros.probe_scan,
    "end_scan"                      : scan_macros.end_scan,
    
    # Calibration
    "measure_probe_offset"          : calib_macros.measure_probe_offset,
    "measure_nozzle_prepare"        : calib_macros.measure_nozzle_prepare,
    "measure_nozzle_offset"         : calib_macros.measure_nozzle_offset,
    
}

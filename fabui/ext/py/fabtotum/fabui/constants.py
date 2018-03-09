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

__authors__ = "Daniel Kesler - Krios Mane"
__license__ = "GPL - https://opensource.org/licenses/GPL-3.0"
__version__ = "1.0"

############################
# HARDWARE SETTINGS        #
############################
Z_MAX_OFFSET = 241.5
############################
#      ERROR CODES         #
############################
ERROR_KILLED           = 100
ERROR_STOPPED          = 101
ERROR_DOOR_OPEN        = 102 
ERROR_MIN_TEMP         = 103
ERROR_MAX_TEMP         = 104
ERROR_MAX_BED_TEMP     = 105
ERROR_X_MAX_ENDSTOP    = 106
ERROR_X_MIN_ENDSTOP    = 107
ERROR_Y_MAX_ENDSTOP    = 108
ERROR_Y_MIN_ENDSTOP    = 109
ERROR_IDLE_SAFETY      = 110
ERROR_WIRE_END         = 111
ERROR_Y_BOTH_TRIGGERED = 120
ERROR_Z_BOTH_TRIGGERED = 121
ERROR_AMBIENT_TEMP     = 122
ERROR_EXTRUDE_MINTEMP  = 123
ERROR_LONG_EXTRUSION   = 124
ERROR_HEAD_ABSENT      = 125
ERROR_PWR_OFF          = 999
############################
#      GCODE               #
############################
FAN_MAX_VALUE          = 255
FAN_MIN_VAlUE          = 0
################################
# MY.FABTOTUM.COM
################################
SERVICE_SUCCESS            = 200
SERVICE_UNAUTHORIZED       = 401
SERVICE_FORBIDDEN          = 403
SERVICE_SERVER_ERROR       = 500
SERVICE_INVALID_PARAMETER  = 1001
SERVICE_ALREADY_REGISTERED = 1002
SERVICE_PRINTER_UNKNOWN    = 1003
################################
# HEADS & MODULES
################################
PRISM_MODULE_ID = 8
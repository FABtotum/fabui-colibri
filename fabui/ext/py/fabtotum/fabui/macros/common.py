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
import re
import gettext

tr = gettext.translation('gmacro', 'locale', fallback=True)
_ = tr.ugettext


def getPosition(app):
    
    data = app.macro("M114", "ok", 2, _("Get Position"), verbose=True)
    reply = data[0]
    position = None
    match = re.search('X:([-|+0-9.]+)\sY:([-|+0-9.]+)\sZ:([-|+0-9.]+)\sE:([-|+0-9.]+)\sCount\sX:\s([-|+0-9.]+)\sY:([-|+0-9.]+)\sZ:([-|+0-9.]+)', reply, re.IGNORECASE)
    
    if match != None:
        position = {
            "x" : match.group(1),
            "y" : match.group(2),
            "z" : match.group(3),
            "e" : match.group(4),
            "count": {
                "x" : match.group(5),
                "y" : match.group(6),
                "z" : match.group(7),
            }
        }
    
    return position

def doG30(app):
    data = app.macro("G30", "ok", 100, _("doing G30"), final_reply = 'echo:', verbose=True)
    app.trace( data )
    app.macro("G91", "ok", 2, _("G30"), verbose=False)
    app.macro("G0 Z+1", "ok:", 2, _("G30"), verbose=False)
    return ""
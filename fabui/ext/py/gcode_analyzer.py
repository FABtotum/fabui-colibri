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

# Import standard python module
import sys
import json
import gettext

# Import external modules

# Import internal modules
from fabtotum.database import Database, timestamp2datetime
from fabtotum.database.file import File
from fabtotum.fabui.config import ConfigService

from printrun.gcoder import GCode

# Set up message catalog access
tr = gettext.translation('gcode_analyzer', 'locale', fallback=True)
_ = tr.ugettext

################################################################################

def main():
    fileID = sys.argv[1]
    
    db = Database()
    
    # Load file info from database    
    f = File(db, fileID)
    
    # Analyze the gcode file
    gcode = GCode(open(f['full_path'], "rU"))
    
    result = {
        "dimensions" : {
            "x" : str(gcode.width),
            "y" : str(gcode.depth),
            "z" : str(gcode.height)
        },
        "number_of_layers" : str(gcode.layers_count),
        "filament": str(gcode.filament_length),
        "estimated_time": str(gcode.estimate_duration()[1])
    }
    
    # Write newly calculated attributes back to file
    f['attributes'] = json.dumps(result)
    f.write()       
    
if __name__ == "__main__":
    main()

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
import argparse
import time
import gettext

# Import external modules

# Import internal modules
from fabtotum.fabui.gpusher    import GCodePusher
from fabtotum.fabui.macros.all import PRESET_MAP

# Set up message catalog access
tr = gettext.translation('gmacro', 'locale', fallback=True)
_ = tr.ugettext

parser = argparse.ArgumentParser()
parser.add_argument("preset",       help="macro to execute")
parser.add_argument("log_trace",    help="log trace file")
parser.add_argument("log_response", help="log response file")
parser.add_argument("force",        help="avoid lock control",  default=0, nargs='?')

parser.add_argument("--ext_temp",   help="extruder target",     default=180, nargs='?', type=int)
parser.add_argument("--bed_temp",   help="bed target",          default=50,  nargs='?', type=int)

args = parser.parse_args()

preset          = args.preset
log_trace       = args.log_trace
log_response    = args.log_response
force           = args.force
ext_temp        = args.ext_temp
bed_temp        = args.bed_temp

################################################################################

class GMacroApplication(GCodePusher):
    
    def __init__(self, log_trace, log_response):
        super(GMacroApplication, self).__init__(log_trace, use_callback=False)
        self.log_trace = log_trace
        self.log_response = log_response
        self.response_file = self.config.get('general', 'macro_response')
        
        self.resetTrace()
        self.resetResponse()
    
    def resetResponse(self):
        open(self.response_file, 'w').close()
        open(self.log_response, 'w').close()
    
    def response(self, status):
        with open(self.response_file,"a+") as out_file:
            out_file.write( str(status) + "<br>" )
    
        if(self.log_response != self.response_file):
            with open(log_response,"a+") as out_file:
                out_file.write( str(status) + "<br>" )
    
    #~ def trace(self, msg):
        #~ print msg
            
    def run(self, preset, args):
        
        if preset in PRESET_MAP:
            PRESET_MAP[preset](self, args)
        else:
            print _("Preset '{0}' not found").format(preset)

        if self.macro_error > 0:
            self.response("false")
            self.trace( _("{0} Error(s) occurred").format(str(self.macro_error)) )
            self.trace( _("{0} operation(s) have been skipped due to errors.").format(str(self.skipped)) )
            self.trace( _("<b>Try Again!</b>") )
        else:
            self.response("true")
            self.trace( _("Done!") )

        self.stop()
    
app = GMacroApplication(log_trace, log_response)

app.run(preset, [ext_temp, bed_temp])
app.loop()

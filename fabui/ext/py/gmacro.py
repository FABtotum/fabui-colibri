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
import json
import gettext

# Import external modules

# Import internal modules
from fabtotum.fabui.config import ConfigService
from fabtotum.fabui.gpusher import GCodePusher
from fabtotum.fabui.macros.all import PRESET_MAP

# Set up message catalog access
tr = gettext.translation('gmacro', 'locale', fallback=True)
_ = tr.ugettext

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
            
    def run(self, preset, args):
        response = False
        reply = None
        
        if preset in PRESET_MAP:
            self.macro_start() # Ensure atomic macro execution
            reply = PRESET_MAP[preset](self, args)
            self.macro_end()
        else:
            self.trace( _("Preset '{0}' not found").format(preset) )

        if self.macro_error > 0:
            self.response("false")
            response = False
            self.trace( _("{0} Error(s) occurred").format(str(self.macro_error)) )
            self.trace( _("{0} operation(s) have been skipped due to errors.").format(str(self.macro_skipped)) )
            self.trace( _("<b>Try Again!</b>") )
        else:
            self.response("true")
            response = True
            self.trace( _("Done!") )
        
        result = {}
        result['response']  = response
        result['reply']     = reply
            
        print json.dumps(result)

        self.stop()
   
def main():
    config = ConfigService()

    parser = argparse.ArgumentParser()
    parser.add_argument("preset",       help=_("Macro to execute. To list all macros type 'list-macros'") )
    parser.add_argument("--log_trace",    help=_("log trace file"), default=config.get('general', 'trace'), nargs='?')
    parser.add_argument("--log_response", help=_("log response file"), default=config.get('general', 'macro_response'), nargs='?')

    parser.add_argument("arguments",   help=_("Macro arguments"), nargs='*')
    #~ parser.add_argument("--ext_temp",   help=_("extruder target"),     default=180, nargs='?', type=int)
    #~ parser.add_argument("--bed_temp",   help=_("bed target"),          default=50,  nargs='?', type=int)

    args = parser.parse_args()

    preset          = args.preset
    log_trace       = args.log_trace
    log_response    = args.log_response
    macro_args = args.arguments
    
    with open('/tmp/fabui/gmacro.log', 'w') as f:
        f.write('{0} : {1}'.format(preset, macro_args) )
    
    if not preset == 'list-macros':    
        app = GMacroApplication(log_trace, log_response)

        app.run(preset, macro_args)
        app.loop()
    else:
        for key in PRESET_MAP.keys():
            print key

if __name__ == "__main__":
    main()

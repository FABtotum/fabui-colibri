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

# Import external modules
from threading import Event, Thread

# Import internal modules
from fabtotum.utils.translation import _, setLanguage
from fabtotum.fabui.config  import ConfigService
from fabtotum.fabui.gpusher import GCodePusher

class ProbeArea(GCodePusher):
    
    def __init__(self, x1, x2, y1, y2, skip_homing=False, lang = 'en_US.UTF-8'):
        config = ConfigService()
        monitor_file = config.get('general', 'task_monitor')
        log_trace       = config.get('general', 'trace')
        super(ProbeArea, self).__init__(log_trace, monitor_file, config=config, use_stdout=False, lang=lang)
        
        self.probe_length = 50
        
        self.x1 = x1
        self.x2 = x2
        self.y1 = y1
        self.y2 = y2
        self.skip_homing = skip_homing
        
    def probe(self, x, y):
        self.send("G0 X{0} Y{1} F6000".format(x, y))
        reply = self.send('G30', timeout = 90)
        self.send("G0 Z{0} F5000".format(self.probe_length))
        self.send("M400")
        
    def run(self):
        
        self.resetTrace()
        eeprom = self.exec_macro('read_eeprom');
        try:
            self.probe_length = abs(float(eeprom['reply']['probe_length']))
            if(self.probe_length < 38):
                self.probe_length = 38
            self.probe_length += 5
        except KeyError:
            pass
        
        points = [[self.x1, self.y1], [self.x1, self.y2], [self.x2, self.y2], [self.x2, self.y1]]
        
        self.send("M402")
        self.send("M733 S0")
        if(self.skip_homing == False):
            self.trace(_("Homing all axes"))
            self.send("G27")
            self.send("M400")
            
        self.send("G90")
        self.send("M401")
        self.trace(_("Probing points"))
        
        for (x,y) in points:
            self.probe(x, y)
        self.send("M402")
        self.send("M733 S1")
        self.stop()
        
def main():
    parser = argparse.ArgumentParser(add_help=False, formatter_class=argparse.ArgumentDefaultsHelpFormatter)
    
    parser.add_argument("-x", "--x1", help="X1.", default=0)
    parser.add_argument("-y", "--y1", help="Y1.", default=0)
    parser.add_argument("-i", "--x2", help="X2.", default=10)
    parser.add_argument("-j", "--y2", help="Y2.", default=10)
    parser.add_argument("-s", "--skip_homing", action='store_true', help="Skip homing." )
    parser.add_argument("--lang", help="Output language", default='en_US.UTF-8' )
    
    # GET ARGUMENTS
    args = parser.parse_args()
    
    x1          = float(args.x1)
    y1          = float(args.y1)
    x2          = float(args.x2)
    y2          = float(args.y2)
    lang        = args.lang
    skip_homing = args.skip_homing
    
    app = ProbeArea(x1, x2, y1, y2, skip_homing = skip_homing,  lang=lang)
    
    app_thread = Thread( target = app.run )
    app_thread.start()
    
    app.loop() # app.loop() must be started to allow callbacks
    app_thread.join()
    
    
if __name__ == "__main__":
    main()
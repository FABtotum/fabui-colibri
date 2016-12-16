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
import time

# Import external modules
from threading import Event, Thread

# Import internal modules
from fabtotum.fabui.config  import ConfigService
from fabtotum.fabui.gpusher import GCodePusher


class Color():
    def __init__(self, r, g, b):
        self.red = r
        self.green = g
        self.blue = b
        
    def getRed(self):
        return self.red
    def getGreen(self):
        return self.green
    def getBlue(self):
        return self.blue
    def setRed(self, red):
        self.red = red
    def setGreen(self, green):
        self.green = green
    def setBlue(self, blue):
        self.blue = blue
    def setUnit(self, unit, value):
        if(unit == 'red'):
            self.setRed(value)
        elif(unit == 'green'):
            self.setGreen(value)
        elif(unit == 'blue'):
            self.setBlue(value)

class Blink(GCodePusher):
    
    MAX_VALUE = 255.0
    MIN_VALUE = 0.0
    
    RED   = Color(255, 0, 0)
    GREEN = Color(0, 255, 0)
    BLUE  = Color(0, 0, 255)
    
    def __init__(self, log_trace, monitor_file, sleep = 0.05, step = 10.0):
        super(Blink, self).__init__(log_trace, monitor_file, use_stdout=False)
            
        self.sleep = sleep
        self.step = step 
        self.stop_blinking = False
    
    def setColor(self, color):
        self.send('M701 S{0}'.format(color.red))
        self.send('M702 S{0}'.format(color.green))
        self.send('M703 S{0}'.format(color.blue))
    
    def lightsOff(self):
        self.setColor(Color(0, 0, 0))
    
    def lightsOn(self):
        self.setColor(Color(255, 255, 255))
        
    def scaleColor(self, color, color_unit, direction):
        
        offset = ( self.MAX_VALUE / self.step )
        
        if(direction == 'up'):
            step = self.MIN_VALUE
            max = self.MAX_VALUE
        else:
            step = self.MAX_VALUE
            max = self.MIN_VALUE
                
        counter = 0
        
        while(counter <= self.step):
            color.setUnit(color_unit, step)
            self.setColor(color)
            time.sleep(self.sleep)
            if(direction == 'up'):
                step += offset
            elif(direction == 'down'):
                step -= offset
            counter += 1
            
    
    def stopBlinking(self):
        self.stop_blinking = True
        
    
    def run(self, color_to_blink): 
        
        if(color_to_blink == 'blue'):
            color = self.BLUE
        elif(color_to_blink == 'red'):
            color = self.RED
        elif(color_to_blink == 'green'):
            color = self.GREEN
            
        self.lightsOn()

        
        while self.stop_blinking == False :
            self.scaleColor(color, color_to_blink, 'up')
            self.scaleColor(color, color_to_blink, 'down')
            time.sleep(self.sleep)
        self.lightsOn()
        self.stop()

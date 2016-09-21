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
import os
# Import external modules
import numpy as np

# Import internal modules

class ASCFile:
    def __init__(self, filename):
        self.filename = filename
        self.fd = open(filename, 'w')

    def write_points(self, points):
        if points is None:
            return 0
            
        if len(points)>4:
            for row in xrange(0,len(points)):
                p = np.float32(points[row].T)
                
                x = float(p[0])
                y = float(p[1])
                z = float(p[2])
                
                line = "{0}, {1}, {2}\n".format(x,y,z)
                self.fd.write(line)
            return len(points)
            
        return 0

    def get_size(self):
        return os.fstat(self.fd.fileno()).st_size

    def close(self):
        self.fd.close()

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

# Import standard python module
from collections import OrderedDict

# Import external modules

# Import internal modules
from fabtotum.database import TableItem

################################################################################

#~ CREATE TABLE sys_codes (
#~ id INTEGER PRIMARY KEY AUTOINCREMENT, 
#~ type varchar (5) NOT NULL, 
#~ code int (10) NOT NULL, 
#~ label varchar (255) NOT NULL, 
#~ description text NOT NULL);

class Code(TableItem):
    
    def __init__(self, database, code_id=TableItem.DEFAULT):
         
        attribs = OrderedDict()
        attribs['id'] = code_id     # Code ID
        attribs['type'] = ""        # G or M
        attribs['code'] = 0         # Code number
        attribs['label'] = ""       # Full code name (ex. G27)
        attribs['description'] = "" # Code description
        
        super(Code, self).__init__(database, table='sys_code', primary='id', primary_autoincrement=True, attribs=attribs)



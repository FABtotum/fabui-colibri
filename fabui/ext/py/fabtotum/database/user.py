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
from datetime import datetime
import time
# Import external modules

# Import internal modules
from fabtotum.database import TableItem

################################################################################

#~ CREATE TABLE sys_user (
#~ id INTEGER PRIMARY KEY AUTOINCREMENT, 
#~ email varchar (255) DEFAULT NULL, 
#~ password varchar (255) DEFAULT NULL, 
#~ first_name varchar (255) NOT NULL, 
#~ last_name varchar (255) NOT NULL, 
#~ last_login datetime DEFAULT '1970-01-01 00:00:00', 
#~ session_id varchar (255), 
#~ settings text NOT NULL);

class User(TableItem):
    
    def __init__(self, database, user_id = TableItem.DEFAULT):
         
        attribs = OrderedDict()
        attribs['id']           = user_id
        attribs['email']        = ""
        attribs['password']     = ""
        attribs['first_name']   = ""
        attribs['last_name']    = ""
        attribs['last_login']   = ""
        attribs['session_id']   = ""
        attribs['settings']     = "{}"
        attribs['role']         = ""
        
        # 1970-01-01 00:00:00
        # %Y-%m-%d %H:%M:%S
        # dt = datetime.fromtimestamp( time.time()  )
        # str = datetime.strftime(dt, "%Y-%m-%d %H:%M:%S")
        
        super(User, self).__init__(database, table='sys_user', primary='id', primary_autoincrement=True, attribs=attribs)


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
import time
from collections import OrderedDict

# Import external modules

# Import internal modules
from fabtotum.database import TableItem, timestamp2datetime
from fabtotum.database.file import File
from fabtotum.database.obj_file import ObjFile
from fabtotum.fabui.config import ConfigService

################################################################################

#~ CREATE TABLE sys_objects (
#~ id INTEGER PRIMARY KEY AUTOINCREMENT, 
#~ user int (11) NOT NULL, 
#~ name varchar (255) DEFAULT NULL, 
#~ description text, 
#~ date_insert datetime DEFAULT NULL, 
#~ date_update datetime DEFAULT NULL, 
#~ public int (1) NOT NULL DEFAULT '1');

class Object(TableItem):
    
    PUBLIC  = 1
    PRIVATE = 0
    
    def __init__(self, database, object_id=TableItem.DEFAULT, user_id=0, name="", desc="", public=1):
        """
        Table containing all objects. Files associated with objects are tracked
        in sys_obj_files table.
        """
        attribs = OrderedDict()
        attribs['id']               = object_id # Object ID
        attribs['user']             = user_id   # User ID of the user owning this object
        attribs['name']             = name  # Object name
        attribs['description']      = desc  # Object description
        now = timestamp2datetime( time.time() )
        attribs['date_insert']      = now       # Date/time when the object was inserted into the table
        attribs['date_update']      = now       # Date/time when the object was updated
        attribs['public']           = public   # If set to 1 object is only visible to it's owner
        
        super(Object, self).__init__(database, table='sys_objects', primary='id', primary_autoincrement=True, attribs=attribs)

    def add_file(self, filename, client_name=None, upload_dir=None):
        """
        """
        if not upload_dir:
            config = ConfigService()
            upload_dir = config.get('general', 'uploads')
            
        file = File(self._db, filename=filename, client_name=client_name, upload_dir=upload_dir)
        file_id = file.write()
        object_id = self['id']
        
        objfile = ObjFile(self._db, object_id, file_id)
        objfile.write()
        
        return file
        
    def remove_file(self):
        pass
        
    def get_files(self):
        pass

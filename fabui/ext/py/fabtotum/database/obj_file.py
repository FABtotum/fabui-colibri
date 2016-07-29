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

#~ CREATE TABLE sys_obj_files (
#~ id INTEGER PRIMARY KEY AUTOINCREMENT, 
#~ id_obj int (11) DEFAULT NULL, 
#~ id_file int (11) DEFAULT NULL);

class ObjFile(TableItem):
    
    def __init__(self, database, object_id=0, file_id=0, obj_file_id=TableItem.DEFAULT):
        """
        Table used to map files to objects.
        """
        attribs = OrderedDict()
        attribs['id']       = obj_file_id   # ObjFile map ID
        attribs['id_obj']   = object_id     # Object ID of the parent object
        attribs['id_file']  = file_id       # File ID
        
        super(ObjFile, self).__init__(database, table='sys_obj_files', primary='id', primary_autoincrement=True, attribs=attribs)

    def object_files(self, object_id):
        result = []
        with self._db.lock:
            conn = self._db.get_connection()
            
            cursor = conn.execute("SELECT {2} from {0} where {1}=?".format(self._table, 'id_obj', 'id_file'), (object_id,) )
            for row in cursor:
                result.append(row[0])
        return result
        
    def object_associations(self, object_id):
        result = []
        with self._db.lock:
            conn = self._db.get_connection()
            
            cursor = conn.execute("SELECT {2} from {0} where {1}=?".format(self._table, 'id_obj', 'id'), (object_id,) )
            for row in cursor:
                result.append(row[0])
        return result
        
    def file_associations(self, file_id):
        result = []
        with self._db.lock:
            conn = self._db.get_connection()
            
            cursor = conn.execute("SELECT {2} from {0} where {1}=?".format(self._table, 'id_file', 'id'), (file_id,) )
            for row in cursor:
                result.append(row[0])
        return result

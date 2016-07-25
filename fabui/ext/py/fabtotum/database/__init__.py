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
from datetime import datetime
from collections import OrderedDict

# Import external modules
import sqlite3

# Import internal modules
from fabtotum.fabui.config import ConfigService

################################################################################

def timestamp2datetime(ts):
    """ Covert python timestamp number to sqlite3 datetime format """
    dt = datetime.fromtimestamp(ts)
    return dt.strftime('%Y-%m-%d %H:%M:%S')

class Database(object):
    """
    """
    
    def __init__(self, config = None):
        if not config:
            self.config = ConfigService()
        else:
            self.config = config
            
        database_file = self.config.get('general', 'database')
        
        # Database connection
        self.conn = sqlite3.connect(database_file)

class TableItem(object):
    
    def __init__(self, database, table, primary, primary_value=0, attribs=OrderedDict() ):
        """
        TableItem contructor.
        
        :param databse: Database object
        :param table: Table name
        :param primary: Primary column used to query item
        :param primary_value: Primary column value
        :param attribs: OrderedDict containing item columns
        :type database: Database
        :type table: string
        :type primary: string
        :type attribs: OrderedDict
        """
        self._attribs = attribs
        
        if not attribs:
            self._attribs[primary] = 0
        
        self._exists = False
        self._fetched = False
        self._db = database
        self._primary = primary
        self._table = table
        # Do an initial read to get the values if the item already exists in the databases
        self.read()
        
    def __contains__(self, key):
        return key in self._attribs
        
    def __setitem__(self, key, value):
        self._attribs[key] = value
        
    def __getitem__(self, key):
        return self._attribs[key]
    
    def exists(self):
        """
        Returns whether the item exists in the database.
        """
        if not self._fetched:
            args = ( self[self._primary], )
            cursor = self._db.conn.execute("SELECT * from {0} where {1}=?".format(self._table, self._primary), args )
            raw =  cursor.fetchone()
            if raw:
                self._exists = True
            else:
                self._exists = False
            self._fatched = True
            
        return self._exists

    def read(self):
        """
        Get the full content from the database based on the `primary` key.
        """
        args = ( self[self._primary], )
        
        cursor = self._db.conn.execute("SELECT * from {0} where {1}=?".format(self._table, self._primary), args )
        self._fetched = True
        raw =  cursor.fetchone()
        if raw:
            idx = 0
            for k in self._attribs:
                self._attribs[k] = raw[idx]
                idx += 1
            
            self._exists = True
            return True
        return False
        
    def write(self):
        """
        Write the full content to the database based on the `primary` column.
        If the item does not exist yet, INSERT is used otherwise UPDATE is used.
        """
        if self.exists():
            args = None
            statement = "UPDATE {0} SET ".format(self._table)
            
            for k in self._attribs:
                if k != self._primary:
                    if not args:
                        args = ( self._attribs[k] ,)
                        statement += "{0}=?".format(k)
                    else:
                        args += ( self._attribs[k] ,)
                        statement += ", {0}=?".format(k)
            args += ( self[self._primary], )
            statement += " WHERE {0}=?".format(self._primary)
            cursor = self._db.conn.execute(statement, args )
        else:
            args = None
            statement = "INSERT INTO {0} VALUES (".format(self._table)
            
            for k in self._attribs:
                    if not args:
                        args = ( self._attribs[k] ,)
                        statement += "?"
                    else:
                        args += ( self._attribs[k] ,)
                        statement += ",?"
            statement += ")"
            self._exists = True
            print args
            print statement
            cursor = self._db.conn.execute(statement, args )
        self._db.conn.commit()
            
    def delete(self):
        args = ( self[self._primary], )
        
        cursor = self._db.conn.execute("DELETE from {0} where {1}=?".format(self._table, self._primary), args )
        self._db.conn.commit()

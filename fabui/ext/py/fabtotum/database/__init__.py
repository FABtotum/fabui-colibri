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
from threading import RLock

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
        
        self.lock = RLock()
        self.database_file = self.config.get('general', 'database')
        
    def get_connection(self):
        return sqlite3.connect(self.database_file)

class TableItem(object):
    
    DEFAULT = -1
    
    def __init__(self, database, table, primary, primary_value=0, primary_autoincrement=False, attribs=OrderedDict() ):
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
        self._autoincrement = primary_autoincrement
        self._table = table
        # Do an initial read to get the values if the item already exists in the databases
        self.read()
        
    def __contains__(self, key):
        return key in self._attribs
        
    def __setitem__(self, key, value):
        self._attribs[key] = value
        
    def __getitem__(self, key):
        return self._attribs[key]
    
    def query_by(self, key, value):
        """
        Query item from database by specific key and value.
        """
        result = False
        with self._db.lock:
            conn = self._db.get_connection()
            args = ( value, )
            cursor = conn.execute("SELECT * from {0} where {1}=?".format(self._table, key), args )
            raw =  cursor.fetchone()
            if raw:
                idx = 0
                for k in self._attribs:
                    self._attribs[k] = raw[idx]
                    idx += 1
                    result = True
                    
        return result
        
    def exists(self):
        """
        Returns whether the item exists in the database.
        """
        with self._db.lock:
            conn = self._db.get_connection()
            
            if not self._fetched:
                args = ( self[self._primary], )
                cursor = conn.execute("SELECT * from {0} where {1}=?".format(self._table, self._primary), args )
                raw =  cursor.fetchone()
                if raw:
                    self._exists = True
                else:
                    self._exists = False
                self._fatched = True
            
        return self._exists

    def query(self, query):
        pass

    def read(self):
        """
        Get the full content from the database based on the `primary` key.
        """
        args = ( self[self._primary], )
        result = False
        
        with self._db.lock:
            conn = self._db.get_connection()
        
            cursor = conn.execute("SELECT * from {0} where {1}=?".format(self._table, self._primary), args )
            self._fetched = True
            raw =  cursor.fetchone()
            if raw:
                idx = 0
                for k in self._attribs:
                    self._attribs[k] = raw[idx]
                    idx += 1
                
                self._exists = True
                result = True
        
        return result
        
    def write(self):
        """
        Write the full content to the database based on the `primary` column.
        If the item does not exist yet, INSERT is used otherwise UPDATE is used.
        """
        lastrowid = -1
        with self._db.lock:
            conn = self._db.get_connection()
            
            if self.exists():
                args = None
                arg_names = ''
                
                for k in self._attribs:
                    if k != self._primary:
                        if not args:
                            args = ( self._attribs[k] ,)
                            arg_names += "{0}=?".format(k)
                        else:
                            args += ( self._attribs[k] ,)
                            arg_names += ", {0}=?".format(k)
                args += ( self[self._primary], )            
                statement = "UPDATE {0} SET {1} WHERE {2}=?".format(self._table, arg_names, self._primary)
                #~ print args
                #~ print statement
                cursor = conn.execute(statement, args )
                lastrowid = self[self._primary]
            else:
                args = None
                arg_names = ''
                arg_questionmarks = ''
                
                for k in self._attribs:
                    if self._autoincrement and k == self._primary:
                        pass # skip
                    else:
                        if not args:
                            args = ( self._attribs[k] ,)
                            arg_questionmarks += "?"
                            arg_names += k
                        else:
                            args += ( self._attribs[k] ,)
                            arg_questionmarks += ",?"
                            arg_names += "," + k
                
                statement = "INSERT INTO {0} ({1}) VALUES ({2})".format(self._table, arg_names, arg_questionmarks)
                self._exists = True
                #~ print args
                #~ print statement
                cursor = conn.execute(statement, args )
                lastrowid = cursor.lastrowid
                self[self._primary] = lastrowid
            conn.commit()
        
        return lastrowid
            
    def delete(self, multiple = None):
        with self._db.lock:
            conn = self._db.get_connection()
            
            if multiple:
                for id in multiple:
                    args = ( id, )
                    cursor = conn.execute("DELETE from {0} where {1}=?".format(self._table, self._primary), args )
                conn.commit()
            else:
                args = ( self[self._primary], )
                cursor = conn.execute("DELETE from {0} where {1}=?".format(self._table, self._primary), args )
                conn.commit()

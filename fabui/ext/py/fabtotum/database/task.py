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

################################################################################

#~ CREATE TABLE "sys_tasks" (
#~ "id" INTEGER,
#~ "user" int(11) NOT NULL,
#~ "controller" varchar(255) NOT NULL,
#~ "type" varchar(255) DEFAULT NULL,
#~ "status" varchar(255) DEFAULT NULL,
#~ "attributes" text,
#~ "start_date" datetime NOT NULL,
#~ "finish_date" datetime,
#~ "id_object" INTEGER,
#~ "id_file" INTEGER,
#~ PRIMARY KEY ("id" ASC) );

class Task(TableItem):
    
    def __init__(self, database, task_id=TableItem.DEFAULT):
         #database, table, primary, primary_value=0, attribs=OrderedDict() ):
         
        attribs = OrderedDict()
        attribs['id']           = task_id   # Task ID
        attribs['user']         = 0         # User ID of the user that has started this task
        attribs['controller']   = ""        # Controller type. "create" for print, mill, scan
        attribs['type']         = ""        # Task type (print, mill, scan...TODO)
        attribs['status']       = ""        # Task status (running, aborting, aborted, completing, completed)
        attribs['attributes']   = "{}"
        now = timestamp2datetime(time.time())
        attribs['start_date']   = now       # Date/time when the task was started
        attribs['finish_date']  = 0         # Date/time when the task was finished (completed or aborted)
        attribs['id_object']  = 0           # ID of the object associated to this task
        attribs['id_file']  = 0             # ID of the file associated to this task
        
        super(Task, self).__init__(database, table='sys_tasks', primary='id', primary_autoincrement=True, attribs=attribs)

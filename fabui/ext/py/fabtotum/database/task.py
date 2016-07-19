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

#~ DROP TABLE IF EXISTS `sys_tasks`;
#~ CREATE TABLE `sys_tasks` (
  #~ `id` int(11) NOT NULL AUTO_INCREMENT,
  #~ `user` int(11) NOT NULL,
  #~ `controller` varchar(255) NOT NULL,
  #~ `type` varchar(255) DEFAULT NULL,
  #~ `id_object` int(11) NOT NULL,
  #~ `id_file` int(11) NOT NULL,
  #~ `status` varchar(255) DEFAULT NULL,
  #~ `attributes` text,
  #~ `start_date` datetime NOT NULL,
  #~ `finish_date` datetime NOT NULL,
  #~ PRIMARY KEY (`id`)
#~ ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

class Task(TableItem):
    
    def __init__(self, database, task_id):
         #database, table, primary, primary_value=0, attribs=OrderedDict() ):
         
        attribs = OrderedDict()
        attribs['id']           = task_id
        attribs['user']         = 0
        attribs['controller']   = ""
        attribs['type']         = ""
        attribs['id_object']    = 0
        attribs['id_file']      = 0
        attribs['status']       = ""
        attribs['attributes']   = "{}"
        attribs['start_date']   = 0
        attribs['finish_date']  = 0
        
        super(Task, self).__init__(database, table='sys_tasks', primary='id', attribs=attribs)

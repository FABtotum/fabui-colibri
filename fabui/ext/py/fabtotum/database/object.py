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

#~ DROP TABLE IF EXISTS `sys_objects`;
#~ CREATE TABLE IF NOT EXISTS `sys_objects` (
  #~ `id` int(11) NOT NULL AUTO_INCREMENT,
  #~ `user` int(11) NOT NULL,
  #~ `obj_name` varchar(255) DEFAULT NULL,
  #~ `obj_description` text,
  #~ `date_insert` datetime DEFAULT NULL,
  #~ `date_updated` datetime DEFAULT NULL,
  #~ `private` int(1) NOT NULL DEFAULT '1',
  #~ PRIMARY KEY (`id`)
#~ ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

class Object(TableItem):
    
    def __init__(self, database, object_id):         
        attribs = OrderedDict()
        attribs['id']               = object_id
        attribs['user']             = 0
        attribs['obj_name']         = ""
        attribs['obj_description']  = ""
        attribs['date_insert']      = ""
        attribs['date_updated']     = ""
        attribs['private']          = 0
        
        super(Object, self).__init__(database, table='sys_objects', primary='id', attribs=attribs)

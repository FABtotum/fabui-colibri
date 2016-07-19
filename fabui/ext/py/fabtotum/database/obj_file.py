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

#~ DROP TABLE IF EXISTS `sys_obj_files`;
#~ CREATE TABLE IF NOT EXISTS `sys_obj_files` (
  #~ `id` int(11) NOT NULL AUTO_INCREMENT,
  #~ `id_obj` int(11) DEFAULT NULL,
  #~ `id_file` int(11) DEFAULT NULL,
  #~ PRIMARY KEY (`id`)
#~ ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

class ObjFile(TableItem):
    
    def __init__(self, database, obj_file_id):
         #database, table, primary, primary_value=0, attribs=OrderedDict() ):
         
        attribs = OrderedDict()
        attribs['id']       = obj_file_id
        attribs['id_obj']   = 0
        attribs['id_file']  = 0
        
        super(ObjFile, self).__init__(database, table='sys_obj_files', primary='id', attribs=attribs)

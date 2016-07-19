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

#~ DROP TABLE IF EXISTS `sys_plugins`;
#~ CREATE TABLE IF NOT EXISTS `sys_plugins` (
  #~ `id` int(11) NOT NULL AUTO_INCREMENT,
  #~ `name` text NOT NULL,
  #~ `attributes` text NOT NULL,
  #~ PRIMARY KEY (`id`)
#~ ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

class Plugin(TableItem):
    
    def __init__(self, database, plugin_id):
         
        attribs = OrderedDict()
        attribs['id']           = plugin_id
        attribs['name']        = ""
        attribs['attributes']  = ""
        
        super(Plugin, self).__init__(database, table='sys_plugins', primary='id', attribs=attribs)



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

#~ DROP TABLE IF EXISTS `sys_files`;
#~ CREATE TABLE IF NOT EXISTS `sys_files` (
  #~ `id` int(11) NOT NULL AUTO_INCREMENT,
  #~ `file_name` varchar(255) DEFAULT NULL,
  #~ `file_type` varchar(255) DEFAULT NULL,
  #~ `file_path` varchar(255) DEFAULT NULL,
  #~ `full_path` varchar(255) DEFAULT NULL,
  #~ `raw_name` varchar(255) DEFAULT NULL,
  #~ `orig_name` varchar(255) DEFAULT NULL,
  #~ `client_name` varchar(255) DEFAULT NULL,
  #~ `file_ext` varchar(255) DEFAULT NULL,
  #~ `file_size` int(11) DEFAULT NULL,
  #~ `print_type` varchar(255) NOT NULL,
  #~ `is_image` int(1) DEFAULT NULL,
  #~ `image_width` int(11) DEFAULT NULL,
  #~ `image_height` int(11) DEFAULT NULL,
  #~ `image_type` int(255) DEFAULT NULL,
  #~ `image_size_str` varchar(255) DEFAULT NULL,
  #~ `insert_date` datetime NOT NULL,
  #~ `update_date` datetime NOT NULL,
  #~ `note` text NOT NULL,
  #~ `attributes` text NOT NULL,
  #~ PRIMARY KEY (`id`)
#~ ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

class File(TableItem):
    
    def __init__(self, database, file_id):         
        attribs = OrderedDict()
        attribs['id']               = file_id
        attribs['file_name']        = ""
        attribs['file_type']        = ""
        attribs['file_path']        = ""
        attribs['full_path']        = ""
        attribs['raw_name']         = ""
        attribs['orig_name']        = ""
        attribs['client_name']      = ""
        attribs['file_ext']         = ""
        attribs['file_size']        = ""
        attribs['print_type']       = ""
        attribs['is_image']         = 0
        attribs['image_width']      = None
        attribs['image_height']     = None
        attribs['image_type']       = 0
        attribs['image_size_str']   = "0"
        attribs['insert_date']      = ""
        attribs['update_date']      = ""
        attribs['note']             = ""
        attribs['attributes']       = "{}"
        
        
        super(File, self).__init__(database, table='sys_files', primary='id', attribs=attribs)

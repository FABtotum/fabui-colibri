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
import os
import shutil
import errno
import mimetypes
import time
from collections import OrderedDict

# Import external modules
import cv2

# Import internal modules
from fabtotum.database import TableItem, timestamp2datetime
from fabtotum.utils.gcodefile import GCodeFile, GCodeInfo
from fabtotum.os import USER_UID, USER_GID

################################################################################

# https://www.codeigniter.com/userguide3/libraries/file_uploading.html

#~ CREATE TABLE sys_files (
#~ id INTEGER PRIMARY KEY AUTOINCREMENT, 
#~ file_name varchar (255) NOT NULL, 
#~ file_type varchar (255) DEFAULT NULL, 
#~ file_path varchar (255) NOT NULL, 
#~ full_path varchar (255) NOT NULL, 
#~ raw_name varchar (255) DEFAULT NULL, 
#~ orig_name varchar (255) DEFAULT NULL, 
#~ client_name varchar (255) DEFAULT NULL, 
#~ file_ext varchar (255) DEFAULT NULL, 
#~ file_size int (11) DEFAULT NULL, 
#~ print_type varchar (255) DEFAULT NULL, 
#~ is_image int (1) DEFAULT NULL, 
#~ image_width int (11) DEFAULT NULL, 
#~ image_height int (11) DEFAULT NULL, 
#~ image_type int (255) DEFAULT NULL, 
#~ image_size_str varchar (255) DEFAULT NULL, 
#~ insert_date datetime NOT NULL, 
#~ update_date datetime DEFAULT NULL, 
#~ note text DEFAULT '', 
#~ attributes text NOT NULL DEFAULT '{}');

class File(TableItem):
    
    def __init__(self, database, file_id=TableItem.DEFAULT, filename = None, client_name = None, upload_dir = None):
        """
        Table containing all file description and paths.
        """
        attribs = OrderedDict()
        attribs['id']               = file_id   # File ID
        attribs['file_name']        = ""        # Name of the file that was uploaded, including the filename extension
        attribs['file_type']        = ""        # File MIME type identifier
        attribs['file_path']        = ""        # Absolute server path to the file
        attribs['full_path']        = ""        # Absolute server path, including the file name
        attribs['raw_name']         = ""        # File name, without the extension
        attribs['orig_name']        = ""        # Original file name. This is only useful if you use the encrypted name option.
        attribs['client_name']      = ""        # File name as supplied by the client user agent, prior to any file name preparation or incrementing
        attribs['file_ext']         = ""        # Filename extension, period included
        attribs['file_size']        = ""        # File size in kilobytes
        attribs['print_type']       = ""        # Print type. additive for print, substractive for mill and "" otherwise
        attribs['is_image']         = 0         # Whether the file is an image or not. 1 = image. 0 = not.
        attribs['image_width']      = None      # Image width
        attribs['image_height']     = None      # Image height
        attribs['image_type']       = 0         # Image type (usually the file name extension without the period)
        attribs['image_size_str']   = "0"       # A string containing the width and height (useful to put into an image tag)
        attribs['insert_date']      = ""        # Date and time the file was inserted in the table
        attribs['update_date']      = ""        # Date and time the file was updated
        attribs['note']             = ""        # User notes
        attribs['attributes']       = "{}"      # Attributes
        
        super(File, self).__init__(database, table='sys_files', primary='id', primary_autoincrement=True, attribs=attribs)
        
        if filename:
            self.from_file(filename, client_name, upload_dir)

# files should be sorted by extension

    def from_file(self, filename, client_name = None, upload_dir = None):
        """
        """
        
        if upload_dir:
            fname = os.path.basename(filename)
            ext   = 'other'
            tmp = fname.split('.')
            if len(tmp) > 1:
                ext = tmp[1].strip()
            
            dst_path = os.path.join(upload_dir, ext)
            dst_fname = os.path.join(dst_path, fname)
            try:
                os.makedirs(dst_path)
            except OSError as exc:  # Python >2.5
                if exc.errno == errno.EEXIST and os.path.isdir(dst_path):
                    pass
                else:
                    raise
                    
            shutil.copyfile(filename, dst_fname)
            os.chown(dst_fname, USER_UID, USER_GID)
            filename = dst_fname
                
        image_types = ['.jpg', '.jpeg', '.png', '.bmp', '.gif']
        
        mimetypes.init()
        fname = os.path.basename(filename)
        dname = os.path.dirname(filename)
        ext   = ''
        dext  = ''
        tmp = fname.split('.')
        if len(tmp) > 1:
            ext = tmp[1].strip()
            dext = '.'+ext
        size = os.path.getsize(filename)
        
        mimetypes.types_map['.gcode'] = 'text/plain'
        mimetypes.types_map['.gc'] = 'text/plain'
        mimetypes.types_map['.nc'] = 'text/plain'
        
        ftype = 'application/octet-stream'
        if dext in mimetypes.types_map:
            ftype = mimetypes.types_map[dext]
        
        if not client_name:
            client_name = fname
        
        # General file handling
        self['file_name']     = fname               #[file_name]     => mypic.jpg
        self['file_type']     = ftype               #[file_type]     => image/jpeg
        self['file_path']     = dname               #[file_path]     => /path/to/your/upload/
        self['full_path']     = filename            #[full_path]     => /path/to/your/upload/jpg.jpg
        self['raw_name']      = fname.split('.')[0].strip() #[raw_name]      => mypic
        self['orig_name']     = fname               #[orig_name]     => mypic.jpg
        self['client_name']   = client_name         #[client_name]   => mypic.jpg
        self['file_ext']      = '.'+ext             #[file_ext]      => .jpg
        self['file_size']     = size / 1000.0       #[file_size]     => 22.2
        now = timestamp2datetime( time.time() )
        self['insert_date']   = now        # Date and time the file was inserted in the table
        self['update_date']   = now        # Date and time the file was updated
        
        # Image handling
        if dext in image_types:
            try:
                image = cv2.imread(filename)
                h = image.shape[0]
                w = image.shape[1]
                self['is_image']      = 1
                self['image_width']   = w           #[image_width]   => 800
                self['image_height']  = h           #[image_height]  => 600
                self['image_type']    = ''          #[image_type]    => jpeg
                self['image_size_str']= 'width="{0}" height="{0}"'.format(w, h)
            except:
                # If the image read fails just ignore as it might not be an image after all
                #print e
                pass

        # GCode handling
        if dext == '.gcode' or dext == '.gc' or dext == '.nc':
            gc = GCodeFile(filename, lite_parsing=True)
            t  = gc.info['type']
            if t == GCodeInfo.PRINT:
                self['print_type'] = 'additive'
            elif t ==GCodeInfo.MILL:
                self['print_type'] = 'substractive' 

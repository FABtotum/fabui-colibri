#!/bin/env python
# -*- coding: utf-8; -*-
#
# (c) 2017 FABtotum, http://www.fabtotum.com
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

import os
import collections

CFG_FILE = "/var/lib/connman/settings"

class ConnmanCfg(object):
    """
    ConfigParser python package can read/write *.ini files. However it changes
    the content so that connman cannot understand it.
    ConfigParser on write ensures that keys and values are separated by ' = ' not
    by '=' without spaces and also converts the key names to lowercase.
    """

    def __init__(self):
        self.sections = collections.OrderedDict()

    def get(self, section, key, default):
        try:
            return self.sections[section][key]
        except Exception as e:
            return default

    def set(self, section, key, value):
        try:
            self.sections[section][key] = value
        except Exception as e:
            print e

    def read(self, filename):

        self.sections = collections.OrderedDict()

        with open(filename, 'r') as f:
            content = f.read()
            lines = content.split()

            sec_name = ''

            for line in lines:
                if line[0] == '[':
                    sec_name = line.strip('[]')
                    self.sections[sec_name] = collections.OrderedDict()
                else:
                    key, value = line.split('=', 1)
                    self.sections[sec_name][key] = value

    def has_section(self, section):
        return section in self.sections

    def add_section(self, section):
        if not self.has_section(section):
            self.sections[section] = {}

    def write(self, filename):
        with open(filename, 'w') as f:
            for section in self.sections:
                f.write( '[' + section + ']\n' )
                for key, value in self.sections[section].items():
                    f.write( key + '=' + value + '\n' )
                f.write('\n')

if os.path.exists(CFG_FILE):
    cfg = ConnmanCfg()
    cfg.read(CFG_FILE)
    if cfg.has_section('Bluetooth'):
        do_write = False

        enabled = cfg.get('Bluetooth', 'Enable', 'true') == 'true'
        tethering = cfg.get('Bluetooth', 'Tethering', 'false') == 'true'

        if enabled:
            do_write = True
            cfg.set('Bluetooth', 'Enable', 'false')

        if not tethering:
            do_write = True
            cfg.set('Bluetooth', 'Tethering', 'true')

        if do_write:
            cfg.write(CFG_FILE)
    else:
        cfg.add_section('Bluetooth')
        cfg.set('Bluetooth', 'Enable', 'false')
        cfg.set('Bluetooth', 'Tethering', 'true')


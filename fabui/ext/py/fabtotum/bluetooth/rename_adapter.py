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

# Import standard python module
import sys

# Import external modules
try:
    import ConfigParser
except ImportError:
    import configparser as ConfigParser

################################################################################

BLUEZ_MAIN_CONF='/etc/bluetooth/main.conf'

config = ConfigParser.ConfigParser()
config.read(sys.argv[1])

if not config.has_section('prism'):
    exit(0)

# Read new BlueZ name
new_name = config.get('prism','name', 'PRISM')

save_new = False
with open(BLUEZ_MAIN_CONF, 'r') as f:
  content = f.read()
  lines = content.split('\n')
  new_lines = []
  for line in lines:
    if line.startswith('Name ='):
        tmp = line.split('Name =',1)
        old_name = tmp[1].strip()
        if old_name != new_name:
            # Update Name parameter
            new_lines.append('Name = ' + new_name)
            save_new = True
        else:
            new_lines.append(line)
    else:
       new_lines.append(line)

if save_new:
    # Store new Bluez config file
    with open(BLUEZ_MAIN_CONF, 'wb') as f:
      content = '\n'.join(new_lines)
      f.write(content)



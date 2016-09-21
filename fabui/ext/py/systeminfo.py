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

# Import standard python module
import argparse
import time
import os
import datetime
import math
import json
import gettext

# Import external modules
from threading import Event, Thread

# Import internal modules
from fabtotum.fabui.config  import ConfigService
from fabtotum.utils.pyro.gcodeclient import GCodeServiceClient
from fabtotum.database      import Database, TableItem
from fabtotum.database.sysconfig import SysConfig

# Set up message catalog access
tr = gettext.translation('systeminfo', 'locale', fallback=True)
_ = tr.ugettext

################################################################################

def human_time(*args, **kwargs):
    secs  = float(datetime.timedelta(*args, **kwargs).total_seconds())
    units = [("day", 86400), ("hour", 3600), ("minute", 60), ("second", 1)]
    parts = []
    for unit, mul in units:
        if secs / mul >= 1 or mul == 1:
            if mul > 1:
                n = int(math.floor(secs / mul))
                secs -= n * mul
            else:
                n = secs if secs != int(secs) else int(secs)
            parts.append("%s %s%s" % (n, unit, "" if n == 1 else "s"))
    return " ".join(parts)

def shell_exec(cmd):
    stdin,stdout = os.popen2(cmd)
    stdin.close()
    lines = stdout.readlines(); 
    stdout.close()
    return lines

def get_rx_tx_bytes(iface):
    with open('/sys/class/net/{0}/statistics/rx_bytes'.format(iface), 'r') as f:
        rx = int(f.read())
    with open('/sys/class/net/{0}/statistics/tx_bytes'.format(iface), 'r') as f:
        tx = int(f.read())
       
    return [rx, tx]

def main():
    config  = ConfigService()
    gcs     = GCodeServiceClient()
    data = {}
    
    # Memory
    with open('/proc/meminfo', 'r') as f:
        meminfo = f.read().split()
        data['mem_total']   = int(meminfo[1])
        data['mem_free']    = int(meminfo[4])
        data['mem_used_percentage'] = int( (data['mem_free'] * 100.0) / data['mem_total'] )
    
    # Board Temperature
    with open('/sys/class/thermal/thermal_zone0/temp', 'r') as f:
        tmp = float(f.read())
        data['temp'] = tmp / 1000.0;
    
    # Uptime
    with open('/proc/uptime', 'r') as f:
        tmp = f.read().split()
        data['time_alive'] = human_time( seconds=round(float(tmp[0])) )
    
    # BCM2709 RPi2/RPi3
    # BCM2708 RPi1
    # Raspberry Pi version
    soc_id = shell_exec('</proc/cpuinfo grep Hardware | awk \'{print $3}\'')[0].strip()
    name_id = ''
    soc_name = {'BCM2708' : 'Raspberry Pi Model B', 'BCM2709' : 'Raspberry Pi 3 Model B' }
    if soc_id in soc_name:
        data['rpi_version'] = soc_name[soc_id]
    else:
        data['rpi_version'] = soc_id;
    
    # Storage
    tmp = shell_exec('df -Ph');
    table_header = tmp[0].split()[:-1]
    table_rows = []
    visible_partitions = ['/tmp', '/mnt/bigtemp', '/mnt/userdata', '/mnt/live/mnt/changes', '/mnt/live/mnt/bundles', '/mnt/live/mnt/boot']
    for row in tmp[1:]:
        tmp2 = row.split()
        if tmp2[5] in visible_partitions:
            table_rows.append(" ".join(tmp2) )
    
    data['table_header'] = table_header
    data['table_rows'] = table_rows

    # OS Info
    data['os_info'] = shell_exec('uname -a')[0].strip();
    
    # Fabtotum info
    reply = gcs.send('M765')
    fw = reply[0].split()[1]
    
    reply = gcs.send('M763')
    hw = reply[0]
    data['fabtotum_info'] = {'fw':fw, 'hw':hw}
    
    # FABUI version
    db = Database(config)
    fabui_version = SysConfig(db)
    fabui_version.query_by('key', 'fabui_version')
    data['fabui_version'] = fabui_version['text']
    
    data['unit_configs'] = config.settings
    
    data['eth_bytes'] = get_rx_tx_bytes('eth0')
    data['wlan_bytes'] = get_rx_tx_bytes('wlan0')

    print json.dumps(data)
    
if __name__ == "__main__":
    main()

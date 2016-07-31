#!/bin/bash
###########################################################################
##                                                                       ##
##                     Set time zone                                     ##
##                                                                       ##
## Creation:    29.07.2016                                               ##
## Last Update: 29.07.2016                                               ##
##                                                                       ##
##                                                                       ##
## This program is free software; you can redistribute it and/or modify  ##
## it under the terms of the GNU General Public License as published by  ##
## the Free Software Foundation; either version 2 of the License, or     ##
## (at your option) any later version.                                   ##
##                                                                       ##
###########################################################################

#essid is mandatory
#["$1"] || usage

TIMEZONE=${1}

sudo rm /etc/localtime
sudo cp /usr/share/zoneinfo/$TIMEZONE /etc/localtime
sudo cat <<EOF> /etc/timezone
$TIMEZONE
EOF

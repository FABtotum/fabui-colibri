#!/bin/bash
###########################################################################
##                                                                       ##
##                     Set wifi connection                               ##
##                                                                       ##
## Creation:    12.07.2016                                               ##
## Last Update: 12.07.2016                                               ##
##                                                                       ##
##                                                                       ##
## This program is free software; you can redistribute it and/or modify  ##
## it under the terms of the GNU General Public License as published by  ##
## the Free Software Foundation; either version 2 of the License, or     ##
## (at your option) any later version.                                   ##
##                                                                       ##
###########################################################################

usage() {
	echo "usage: <WIFI_ESSID> <WIFI_PASSWORD>"
	exit 1
}

#essid is mandatory
#["$1"] || usage

SSID=${1}
PASSWORD=${2}

if [ -z "$SSID"]; then
	
	cat <<EOF> /etc/wpa_supplicant.conf
	ctrl_interface=DIR=/run/wpa_supplicant GROUP=netdev
	update_config=1
EOF
else
	PWDLINE=""
	if [ -z "$PASSWORD" ] ; then
		PWDLINE="key_mgmt=NONE"
	else
		PWDLINE="psk=\"$PASSWORD\""
	fi

	cat <<EOF > /etc/wpa_supplicant.conf
	ctrl_interface=DIR=/run/wpa_supplicant GROUP=netdev
	update_config=1

	network={
	  ssid="$SSID"
	  $PWDLINE
	}
EOF
fi
/etc/init.d/network restart

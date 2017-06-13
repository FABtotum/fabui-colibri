#!/bin/bash

test -r /etc/default/network && source /etc/default/network
[ -z $NETWORK_MANAGER ] && NETWORK_MANAGER=ifupdown
test -r /usr/share/fabui/ext/bash/connman_functions.sh && source /usr/share/fabui/ext/bash/connman_functions.sh

usage()
{
cat << EOF
usage: $0 interface

This script disconnect a wifi interface.
EOF
}

if [ -z "$1" ]; then
    usage
    exit 1
fi

IFACE="$1"

if [ $NETWORK_MANAGER == "ifupdown" ]; then
	wpa_cli -p /run/wpa_supplicant -i$IFACE disconnect
	sh /usr/share/fabui/ext/bash/set_wifi.sh -i "${IFACE}" -M "default"
elif [ $NETWORK_MANAGER == "connman" ]; then
	disconnect_connman_wifi
else
	echo "error: Unsupported network manager \'$NETWORK_MANAGER\'"
	exit 1
fi

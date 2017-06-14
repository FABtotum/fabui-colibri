#!/bin/bash

test -r /etc/default/network && source /etc/default/network
[ -z $NETWORK_MANAGER ] && NETWORK_MANAGER=ifupdown
source /usr/share/fabui/ext/bash/${NETWORK_MANAGER}_nm_functions.sh

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

disconnect_wifi "$IFACE"
config_wifi_default "$IFACE"

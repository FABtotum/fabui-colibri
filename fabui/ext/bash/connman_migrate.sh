#!/bin/bash

test -r /etc/default/network && source /etc/default/network

if [ x"$NETWORK_MANAGER" == x"connman" ]; then

source /usr/share/fabui/ext/bash/${NETWORK_MANAGER}_nm_functions.sh

#~ echo $(connman_iface2service wlan0)
connman_migrate_settings

fi

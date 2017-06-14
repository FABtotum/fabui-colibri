#!/bin/bash

test -r /etc/default/network && source /etc/default/network
[ -z $NETWORK_MANAGER ] && NETWORK_MANAGER=ifupdown
source /usr/share/fabui/ext/bash/${NETWORK_MANAGER}_nm_functions.sh

IFACES=$1

get_interface_state "$IFACES"

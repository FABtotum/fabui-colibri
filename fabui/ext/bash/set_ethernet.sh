#!/bin/bash
###########################################################################
##                                                                       ##
##                     Set eth connection                                ##
##                                                                       ##
## This program is free software; you can redistribute it and/or modify  ##
## it under the terms of the GNU General Public License as published by  ##
## the Free Software Foundation; either version 2 of the License, or     ##
## (at your option) any later version.                                   ##
##                                                                       ##
###########################################################################

test -r /etc/default/network && source /etc/default/network
[ -z $NETWORK_MANAGER ] && NETWORK_MANAGER=ifupdown
source /usr/share/fabui/ext/bash/${NETWORK_MANAGER}_nm_functions.sh

usage()
{
cat << EOF
usage: $0 options

This script configures ethernet connection.

OPTIONS:
   -h      Show this message
   -i      Ethernet interface
   -D      DHCP address mode
   -S      STATIC address mode
   -a      IP address   (ex: 192.168.0.15)
   -n      Netmask      (ex: 255.255.255.0)
   -g      Gateway      (ex: 192.168.0.1)
EOF
}

IFACE=
MODE=
IP=
NETMASK=
GATEWAY=
while getopts “hDSAi:a:n:g:” OPTION
do
     case $OPTION in
         h)
             usage
             exit 1
             ;;
         D)
             MODE="dhcp"
             ;;
         S)
             MODE="static"
             ;;
         a)
             IP=$OPTARG
             ;;
         n)
             NETMASK=$OPTARG
             ;;
         g)
             GATEWAY=$OPTARG
             ;;
         i)
             IFACE=$OPTARG
             ;;
         ?)
             usage
             exit
             ;;
     esac
done

if [[ -z $MODE ]] || [[ -z $IFACE ]]
then
     usage
     exit 1
fi

if [[ $MODE == "static" ]]; then
    #~ if [[ -z $IP ]] || [[ -z $NETMASK ]] || [[ -z $GATEWAY ]]; then
    if [[ -z $IP ]] || [[ -z $NETMASK ]]; then
        echo "error: In STATIC mode you must provide ip, netmask and gateway"
        usage
        exit 1
    fi
fi

case $MODE in
    dhcp)
        config_ethernet_dhcp "$IFACE"
        ;;
    static)
        config_ethernet_static "$IFACE" "$IP" "$NETMASK" "$GATEWAY"
        ;;
    *)
        echo "error: unknown mode \'$MODE\'"
        usage
        ;;
esac

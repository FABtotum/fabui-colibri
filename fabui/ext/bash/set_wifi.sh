#!/bin/bash
##########################################################################
##                                                                      ##
##                     Set wifi connection                              ##
##                                                                      ##
##                                                                      ##
## This program is free software; you can redistribute it and/or modify ##
## it under the terms of the GNU General Public License as published by ##
## the Free Software Foundation; either version 2 of the License, or    ##
## (at your option) any later version.                                  ##
##                                                                      ##
##########################################################################

test -r /etc/default/network && source /etc/default/network
[ -z $NETWORK_MANAGER ] && NETWORK_MANAGER=ifupdown
source /usr/share/fabui/ext/bash/${NETWORK_MANAGER}_nm_functions.sh

usage()
{
cat << EOF
usage: $0 options

This script configures wifi connection.

OPTIONS:
   -h      Show this message
   -i      WiFi interface
   -M      Special mode (default|disabled)
   -D      DHCP address mode
   -S      STATIC address mode
   -A      Access Point mode
   -s      WiFi SSID
   -p      WiFi Password
   -k      WiFi PSK (optional, instead of password)
   -c      WiFi Channel,  only in AP mode (default: 1)
   -a      IP address   (ex: 192.168.8.1)
   -n      Netmask      (ex: 255.255.255.0)
   -g      Gateway      (ex: 192.168.0.1)
EOF
}

IFACE=
MODE=
SSID=
PASS=
PSK=
IP=
NETMASK=
GATEWAY=
CHANNEL=1

echo $@ > /tmp/args

while getopts “hDSAi:s:c:p:k:a:n:g:r:M:” OPTION
do
     case $OPTION in
         h)
             usage
             exit 1
             ;;
         M)
             MODE="$OPTARG"
             SSID="-"
             ;;
         D)
             MODE="dhcp"
             ;;
         S)
             MODE="static"
             ;;
         A)
             MODE="ap"
             ;;
         c)
             CHANNEL="$OPTARG"
             ;;
         s)
             SSID="$OPTARG"
             ;;
         p)
             PASS="$OPTARG"
             ;;
         k)
             PSK="$OPTARG"
             ;;
         a)
             IP="$OPTARG"
             ;;
         n)
             NETMASK="$OPTARG"
             ;;
         g)
             GATEWAY="$OPTARG"
             ;;
         i)
             IFACE="$OPTARG"
             ;;
         ?)
             usage
             exit
             ;;
     esac
done

echo "MODE: $MODE"

if [[ -z "$MODE" ]] || [[ -z "$SSID" ]] || [[ -z "$IFACE" ]]
then
     usage
     exit 1
fi

if [[ "$MODE" == "ap" ]]; then
    if [[ -z "$PASS" ]] || [[ -z "$IP" ]] || [[ -z "$NETMASK" ]]; then
        echo "error: AP mode must have a password, ip, netmask, ip range"
        usage
        exit 1
    fi
fi

if [[ "$MODE" == "static" ]]; then
    if [[ -z "$IP" ]] || [[ -z "$NETMASK" ]] || [[ -z "$GATEWAY" ]]; then
        echo "error: In STATIC mode you must provide ip, netmask and gateway"
        usage
        exit 1
    fi
fi

case $MODE in
    dhcp)
        config_wifi_dhcp "$IFACE" "$SSID" "$PASS"
        connect_wifi "$IFACE" "$SSID"
    ;;
    static)
        config_wifi_static "$IFACE" "$SSID" "$PASS" "$IP" "$NETMASK" "$GATEWAY"
        connect_wifi "$IFACE" "$SSID"
        ;;
    ap)
        config_wifi_ap "$IFACE" "$SSID" "$PASS" "$CHANNEL" "$IP" "$NETMASK"
        ;;
    default)
        config_wifi_default "$IFACE"
        ;;
    disabled)
        ;;
    *)
        echo "error: unknown mode \'$MODE\'"
        usage
        ;;
esac

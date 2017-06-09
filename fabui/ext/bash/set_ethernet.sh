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

INTERFACESD=/etc/network/interfaces.d

ifupdown_cleanup()
{
	IFACE=${1}
	ifdown --force $IFACE
	ip addr flush dev $IFACE
}

set_static_ifupdown()
{
	
    IFACE=${1}
    IP=${2}
    NETMASK=${3}
    GATEWAY=${4}
    
    ifupdown_cleanup $IFACE
    
cat <<EOF > $INTERFACESD/$IFACE
# Automatically generated, do not edit

allow-hotplug $IFACE
auto $IFACE
iface $IFACE inet static
  address  $IP
  netmask  $NETMASK
  gateway  $GATEWAY
EOF

	/etc/init.d/network restart
}

set_static_connman()
{
	IFACE=${1}
    IP=${2}
    NETMASK=${3}
    GATEWAY=${4}
    
	ETH_SRV=$(ip link show dev $IFACE | grep link/ether | awk '{print $2}' | sed -e s@:@@g )
	
	connmanctl config $ETH_SRV ipv4 manual $IP $NETMASK $GATEWAY
}

set_dhcp_ifupdown()
{
    IFACE=${1}
    
    ifupdown_cleanup $IFACE
    
cat <<EOF > $INTERFACESD/$IFACE
# Automatically generated, do not edit

allow-hotplug $IFACE
auto $IFACE
iface $IFACE inet dhcp
EOF

	/etc/init.d/network restart
}

set_dhcp_connman()
{
	IFACE=${1}
	
	ETH_SRV=$(ip link show dev $IFACE | grep link/ether | awk '{print $2}' | sed -e s@:@@g )
	
	connmanctl config $ETH_SRV ipv4 dhcp
}

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
    if [[ -z $IP ]] || [[ -z $NETMASK ]] || [[ -z $GATEWAY ]]; then
        echo "error: In STATIC mode you must provide ip, netmask and gateway"
        usage
        exit 1
    fi
fi

case $MODE in
    dhcp)
		if [ $NETWORK_MANAGER == "ifupdown" ]; then
			set_dhcp_ifupdown $IFACE
		elif [ $NETWORK_MANAGER == "connman" ]; then
			set_dhcp_connman $IFACE
		else
			echo "error: Unsupported network manager \'$NETWORK_MANAGER\'"
			exit 1
		fi
        ;;
    static)
		if [ $NETWORK_MANAGER == "ifupdown" ]; then
			set_static_ifupdown $IFACE $IP $NETMASK $GATEWAY
		elif [ $NETWORK_MANAGER == "connman" ]; then
			set_static_connman $IFACE $IP $NETMASK $GATEWAY
		else
			echo "error: Unsupported network manager \'$NETWORK_MANAGER\'"
			exit 1
		fi
        ;;
    *)
        echo "error: unknown mode \'$MODE\'"
        usage
        ;;
esac

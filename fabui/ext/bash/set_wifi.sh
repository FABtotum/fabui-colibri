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

test -r /usr/share/fabui/ext/bash/connman_functions.sh && source /usr/share/fabui/ext/bash/connman_functions.sh

INTERFACESD=/etc/network/interfaces.d
MAX_STATIONS=10

ifupdown_cleanup()
{
	IFACE=${1}
	ifdown --force $IFACE
	ip addr flush dev $IFACE
}

set_wpa_supplicant_default()
{
	cat <<EOF > $WPA_CONF
ctrl_interface=DIR=/run/wpa_supplicant GROUP=netdev
update_config=1
network={
}
EOF

}

set_wpa_supplicant_conf()
{
	SSID="${1}"
	PASSWORD="${2}"
    PSK="${3}"
        
	cat <<EOF > $WPA_CONF
# Automatically generated file, do not edit by hand.
ctrl_interface=DIR=/run/wpa_supplicant GROUP=netdev
update_config=1

EOF
	echo >> $WPA_CONF
	
	if [ -n "$SSID" ]; then
		PWDLINE=""
		if [ -z "$PASSWORD" ] ; then
	cat <<EOF >> $WPA_CONF
network={
  ssid="$SSID"
  key_mgmt=NONE
}
EOF
		else
			if [ x"$PASSWORD" == x"-" ]; then
	cat <<EOF >> $WPA_CONF
network={
  ssid="$SSID"
  psk="$PSK"
}
EOF
			else
				# Store password as psk instead of plain text
				wpa_passphrase "$1" "$2" | sed -e '/#.*/d' >> $WPA_CONF
			fi
		fi
	fi
}

set_hostapd_conf()
{
	IFACE="${1}"
	SSID="${2}"
	PASS="${3}"
	CHANNEL="${4}"
cat <<EOF > $HOSTAPD_CONF
# Automatically generated, do not edit
interface=$IFACE
driver=nl80211
ctrl_interface=/run/hostapd
ctrl_interface_group=0

max_num_sta=$MAX_STATIONS
ssid=$SSID
wpa=2                   # WPA2 only
auth_algs=1             # 1=wpa, 2=wep, 3=both
wpa_passphrase=$PASS
wpa_key_mgmt=WPA-PSK
rsn_pairwise=CCMP
channel=$CHANNEL

ieee80211d=1            # limit the frequencies used to those allowed in the country
country_code=IT         # the country code

hw_mode=g
ieee80211n=1            # 802.11n support
wmm_enabled=1           # QoS support
EOF
}

set_static()
{
    IFACE=${1}
    IP=${2}
    NETMASK=${3}
    GATEWAY=${4}
cat <<EOF > $INTERFACESD/$IFACE
# Automatically generated, do not edit \n

allow-hotplug $IFACE
auto $IFACE
iface $IFACE inet static
  wpa-conf $WPA_CONF
  address  $IP
  netmask  $NETMASK
  gateway  $GATEWAY
EOF
}

set_disabled()
{
cat <<EOF > $INTERFACESD/$IFACE
# Automatically generated, do not edit \n
# Interface is disabled
iface $IFACE inet manual
EOF

}

set_default()
{
    IFACE=${1}
    IDX=$(echo $IFACE | sed s/wlan//)
    let "IDX++"
    IP="172.17.0.$IDX"
    NETMASK="255.255.0.0"

cat <<EOF > $INTERFACESD/$IFACE
# Automatically generated, do not edit \n

allow-hotplug $IFACE
auto $IFACE
iface $IFACE inet static
  wpa-conf $WPA_CONF
  address  $IP
  netmask  $NETMASK

EOF
}

set_dhcp()
{
    IFACE="${1}"
cat <<EOF > $INTERFACESD/$IFACE
# Automatically generated, do not edit \n

allow-hotplug $IFACE
auto $IFACE
iface $IFACE inet dhcp
  wpa-conf $WPA_CONF
  wpa-timeout 30
EOF
}

set_static_ap()
{
    IFACE="${1}"
    IP="${2}"
    NETMASK="${3}"
    
    masked=$(mask_ip $IP $NETMASK)
    RANGE=$(make_range $masked)
    
cat <<EOF > $INTERFACESD/$IFACE
# Automatically generated, do not edit \n

allow-hotplug $IFACE
auto $IFACE
iface $IFACE inet static
  hostapd $HOSTAPD_CONF
  dnsmasq-range $RANGE
  address $IP
  netmask $NETMASK
EOF
}

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

mask_ip()
{
    IP="${1}"
    NETMASK="${2}"
IFS=. read -r i1 i2 i3 i4 << EOF
$IP
EOF

IFS=. read -r m1 m2 m3 m4 << EOF
$NETMASK
EOF

    printf "%d.%d.%d.%d\n" "$((i1 & m1))" "$((i2 & m2))" "$((i3 & m3))" "$((i4 & m4))"
}

make_range()
{
    IP="${1}"
IFS=. read -r i1 i2 i3 i4 << EOF
$IP
EOF
    printf "%d.%d.%d.%d,%d.%d.%d.%d\n" $i1 $i2 $i3 10 $i1 $i2 $i3 100
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

HOSTAPD_CONF=/etc/hostapd/${IFACE}.conf
WPA_CONF=/etc/wpa_supplicant/${IFACE}.conf

case $MODE in
    dhcp)
        if [ -n "$PSK" ]; then
            PASS="-"
        fi
        
        if [ $NETWORK_MANAGER == "ifupdown" ]; then	
			ifupdown_cleanup "$IFACE"
			set_wpa_supplicant_conf "$SSID" "$PASS" "$PSK"
			set_dhcp "$IFACE"
			/etc/init.d/network restart
		elif [ $NETWORK_MANAGER == "connman" ]; then
			create_connman_wifi_config dhcp "$SSID" "$PASS"
		else
			echo "error: Unsupported network manager \'$NETWORK_MANAGER\'"
			exit 1
		fi
        ;;
    static)
        if [ -n "$PSK" ]; then
            PASS="-"
        fi
        
        if [ $NETWORK_MANAGER == "ifupdown" ]; then	
			ifupdown_cleanup "$IFACE"
			set_wpa_supplicant_conf "$SSID" "$PASS" "$PSK"
			set_static "$IFACE" "$IP" "$NETMASK" "$GATEWAY"
			/etc/init.d/network restart
		elif [ $NETWORK_MANAGER == "connman" ]; then
			true
		else
			echo "error: Unsupported network manager \'$NETWORK_MANAGER\'"
			exit 1
		fi	
        ;;
    ap)
		if [ $NETWORK_MANAGER == "ifupdown" ]; then
			ifupdown_cleanup "$IFACE"
			set_hostapd_conf "$IFACE" "$SSID" "$PASS" "$CHANNEL"
			set_static_ap "$IFACE" "$IP" "$NETMASK"
			/etc/init.d/network restart
		elif [ $NETWORK_MANAGER == "connman" ]; then
			disconnect_connman_wifi
			cleanup_connman_wifi_config
			ap_mode_connman "$SSID" "$PASS"
		else
			echo "error: Unsupported network manager \'$NETWORK_MANAGER\'"
			exit 1
		fi
        ;;
    default)
		if [ $NETWORK_MANAGER == "ifupdown" ]; then
			ifupdown_cleanup "$IFACE"
			set_wpa_supplicant_default
			set_default "$IFACE"
			/etc/init.d/network restart
		elif [ $NETWORK_MANAGER == "connman" ]; then
			cleanup_connman_wifi_config
		else
			echo "error: Unsupported network manager \'$NETWORK_MANAGER\'"
			exit 1
        fi
        ;;
    disabled)
		if [ $NETWORK_MANAGER == "ifupdown" ]; then
			ifupdown_cleanup "$IFACE"
			set_disabled "$IFACE"
		elif [ $NETWORK_MANAGER == "connman" ]; then
			true
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

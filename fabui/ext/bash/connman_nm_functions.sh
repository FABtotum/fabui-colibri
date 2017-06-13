#!/bin/bash

CONNMAN_SERVICES_DIR="/var/lib/connman"
CONNMAN_WIFI_CONFIG_FILE="user_wifi.config"

##
# Remove current wifi configuration
#
connman_cleanup_wifi_config()
{
	rm -rf ${CONNMAN_SERVICES_DIR}/wifi_*
	rm ${CONNMAN_SERVICES_DIR}/${CONNMAN_WIFI_CONFIG_FILE}
}

##
# Return active wifi service
#
connman_active_wifi_service()
{
	WIFI_SRV=$(connmanctl services | grep "*A" | grep wifi | awk '{print $NF}')
	echo $WIFI_SRV
}

##
# Return wifi service for given SSID
#
connman_service_by_ssid()
{
	SSID="$1"
	WIFI_SRV=$(connmanctl services | grep wifi | grep $SSID | awk '{print $NF}')
	echo $WIFI_SRV
}

##
# Scan wifi network and return results
# 
# $1 - interface (ex: wlan0)
#
scan_wifi()
{
	return 0
}

##
# Configure wifi interface for STA mode and dhcp address
#
# $1 - interface (ex: wlan0)
# $2 - SSID
# $3 - Passphrase
# 
# returns 0 on success
#
config_wifi_dhcp()
{
	IFACE="$1"
	SSID="$2"
	PASS="$3"
	
	connman_cleanup_wifi_config
	
cat <<EOF > ${CONNMAN_SERVICES_DIR}/${CONNMAN_WIFI_CONFIG_FILE}
# Automatically generated, do not edit \n
[service_user_wifi]
Type = wifi
Name = ${SSID}
Passphrase = ${PASS}
IPv4 = dhcp
EOF
	
	return 0
}

##
# Configure wifi interface for STA mode and static IPv4 address
#
# $1 - interface (ex: wlan0)
# $2 - SSID
# $3 - Passphrase
# $4 - IPv4 address
# $5 - Netmask
# $6 - Gateway
#
# returns 0 on success
#
config_wifi_static()
{
	IFACE="$1"
	SSID="$2"
	PASS="$3"
	IP="$4"
	NETMASK="$5"
	GATEWAY="$6"
	
	connman_cleanup_wifi_config
	
cat <<EOF > ${CONNMAN_SERVICES_DIR}/${CONNMAN_WIFI_CONFIG_FILE}
# Automatically generated, do not edit \n
[service_user_wifi]
Type = wifi
Name = ${SSID}
Passphrase = ${PASS}
IPv4 = ${IP}/${NETMASK}/${GW}
EOF

	return 0
}

##
# Configure wifi interface for AP mode and static IPv4 address
#
# $1 - interface (ex: wlan0)
# $2 - SSID
# $3 - Passphrase
# $4 - Channel (1-13)
# $5 - IPv4 Base address (will be used as gateway for clients)
# $6 - Netmask
#
# returns 0 on success
#
config_wifi_ap()
{
	IFACE="$1"
	SSID="$2"
	PASS="$3"
	CHANNEL="$4" # Is not configurable
	IP="$5"      # Is not configurable
	NETMASK="$6" # Is not configurable
	
	disconnect_wifi "$IFACE"
	connman_cleanup_wifi_config
	connman tether wifi on "$SSID" "$PASS"
	
	return $?
}

##
# Confiture wifi interface to a default state
# $1 - interface (ex: wlan0)
#
# returns 0 on success
#
config_wifi_default()
{
	IFACE="$1"
	
	disconnect_wifi "$IFACE"
	connman_cleanup_wifi_config
	connman tether wifi off
	
	return 0
}

##
# Connect wifi interface to a network
#
# $1 - interface (ex: wlan0)
# $2 - SSID
#
# returns 0 on success
#
connect_wifi()
{
	IFACE="$1"
	SSID="$2"

	SRV=$(connmanctl services | grep "$SSID" | awk '{print $NF}')
	connmanctl scan
	connmanctl connect $SRV
	return 0
}

##
# Disconnect wifi from a network
# 
# $1 - interface (ex: wlan0)
#
# returns 0 on success
#
disconnect_wifi()
{
	IFACE="$1"
	connmanctl disconnect $(connman_active_wifi_service)
	return $?
}

##
# Configure ethernet interface for dhcp address
# 
# $1 - interface (ex: eth0)
#
# returns 0 on success
#
config_ethernet_dhcp()
{
	IFACE="$1"
	
	ETH_MAC=$(ip link show dev $IFACE | grep link/ether | awk '{print $2}' | sed -e s@:@@g )
	ETH_SRV="ethernet_${ETH_MAC}_cable"
	connmanctl config $ETH_SRV ipv4 dhcp
	
	return $?
}

## 
# Configure ethernet interface for static IPv4 address
#
# $1 - interface (ex: eth0)
# $2 - IPv4 address
# $3 - Netmask
# $4 - Gateway
#
# returns 0 on success
#
config_ethernet_static()
{
	IFACE="$1"
	IP="$2"
	NETMASK="$3"
	GATEWAY="$4"
	
	ETH_MAC=$(ip link show dev $IFACE | grep link/ether | awk '{print $2}' | sed -e s@:@@g )
	ETH_SRV="ethernet_${ETH_MAC}_cable"
	connmanctl config $ETH_SRV ipv4 manual $IP $NETMASK $GATEWAY
	
	return 0
}

##
# Get internet state
#
# returns "online" or "offline"
# 
get_internet_state()
{
	STATE=$(connmanctl state | grep State | awk '{print $NF}')
	if [ x"$STATE" == x"online" ]; then
		echo "online"
	else
		echo "offline"
	fi
}

##
# Returns state of all network interfaces.
#
# returns json with following format
#
#  {
#    "interface" : {
#        "driver"       : "Linux kernel driver used by this interface",
#		 "mac_address"  : "00:00:00:00:00:00",
#        "ipv4_address" : "0.0.0.0",
#        "ipv6_address" : "...",
#        "gateway"      : "0.0.0.0",
#        "address_mode" : "auto|static|unknown",
#        "wireless"     : {
#            "can_be_ap"  : "yes|no",
#            "mode"       : "accesspoint|station|auto",
#            "ssid"       : "SSID",
#            "passphrase" : "Passphrase",
#            "channel"    : "Chnnel only available in AP mode"
#        }
#    }
#  }
#
get_interface_state()
{
	echo "{"
	# for IFACE in list of interfaces
	# ...
	echo "}"
}

##
# Get DNS configuration
#
get_dns_config()
{
	echo ""
}

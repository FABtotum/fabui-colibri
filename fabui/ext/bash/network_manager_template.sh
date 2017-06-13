#!/bin/bash

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
	CHANNEL="$4"
	IP="$5"
	NETMASK="$6"
	return 0
}

##
# Confiture wifi interface to a default state
# $1 - interface (ex: wlan0)
#
# returns 0 on success
#
config_wifi_default()
{
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
	return 0
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
	return 0
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
	return 0
}

##
# Get internet state
#
# returns "online" or "offline"
# 
get_internet_state()
{
	echo "offline"
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

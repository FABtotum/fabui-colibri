#!/bin/bash

source /mnt/live/mnt/boot/earlyboot/earlyboot.conf
test -r /etc/default/network && source /etc/default/network
[ -z $NETWORK_MANAGER ] && NETWORK_MANAGER=ifupdown

########################################################################

INTERFACESD=/etc/network/interfaces.d
CONNMAN_UPGRADE_FILE="/var/lib/fabui/upgrade.d/001_connman.sh"
UPGRADE_TO_CONNMAN="no"

########################################################################

if [ "$NETWORK_MANAGER" == "ifupdown" ] && [ "$UPGRADE_TO_CONNMAN" == "yes" ]; then
	# We are using the old network manager, we need to prepare for porting
	# the settings to connman
	echo "ifupdown detected, upgrading to connman"
	
	mkdir -p $(dirname $CONNMAN_UPGRADE_FILE)
	
	ETH_IFACE=$NETWORK_IF
	ETH_IP=$(echo $NETWORK_IPV4 | awk -F / '{print $1}')
	EARLY_ETH_IP=$ETH_IP
	ETH_GATEWAY=$NETWORK_GW
	EARLY_ETH_GATEWAY=$ETH_GATEWAY
	export eval ETH_$(ipcalc -m $ETH_IP)

cat <<EOF > ${CONNMAN_UPGRADE_FILE}
# Automatically generated upgrade file, do not edit \n
source /usr/share/fabui/ext/bash/connman_nm_functions.sh

# Ethernet configuration upgrade
EOF

	chmod +x ${CONNMAN_UPGRADE_FILE}

	# Port eth0 config
	CFG="${INTERFACESD}/${ETH_IFACE}"
	if [ -e "$CFG" ]; then
		ETH_METHOD=$(cat $CFG | grep "inet" | awk '{print $NF}')
		ETH_IP=$(cat $CFG | grep "address" | awk '{print $NF}')
		ETH_NETMASK=$(cat $CFG | grep "netmask" | awk '{print $NF}')
		ETH_GATEWAY=$(cat $CFG | grep "gateway" | awk '{print $NF}')
		if [ -z "$ETH_GATEWAY" ] && [ x"$ETH_IP" == x"$EARLY_ETH_IP" ]; then
			ETH_GATEWAY=$EARLY_ETH_GATEWAY
		fi
	fi
	
	case $ETH_METHOD in
		static)
			echo "config_ethernet_static $ETH_IFACE $ETH_IP $ETH_NETMASK $ETH_GATEWAY" >> ${CONNMAN_UPGRADE_FILE}
			;;
		dhcp)
			echo "config_ethernet_dhcp \"$ETH_IFACE\"" >> ${CONNMAN_UPGRADE_FILE}
			;;
	esac
	
	WLAN_IFACE="wlan0"
	CFG="${INTERFACESD}/${WLAN_IFACE}"
	if [ -e "$CFG" ]; then
		echo  >> ${CONNMAN_UPGRADE_FILE}
		echo "# Wifi configuration upgrade" >> ${CONNMAN_UPGRADE_FILE}
		echo "connmanctl enable wifi" >> ${CONNMAN_UPGRADE_FILE}
		
		WLAN_METHOD=$(cat $CFG | grep "inet" | awk '{print $NF}')
		WLAN_IP=$(cat $CFG | grep "address" | awk '{print $NF}')
		WLAN_NETMASK=$(cat $CFG | grep "netmask" | awk '{print $NF}')
		WLAN_GATEWAY=$(cat $CFG | grep "gateway" | awk '{print $NF}')
		WLAN_WPA_CONF=$(cat $CFG | grep "wpa-conf" | awk '{print $NF}')
		WLAN_HOSTAPD_CONF=$(cat $CFG | grep "hostapd" | awk '{print $NF}')
		WLAN_SSID=""
		WLAN_PASSPHRASE=""
		
		if [ -e "$WLAN_WPA_CONF" ]; then
			WLAN_SSID=$(cat $WLAN_WPA_CONF | grep ssid | sed -e 's@.*ssid=@@')
			WLAN_PASSPHRASE=$(cat $WLAN_WPA_CONF | grep psk | sed -e 's@.*psk=@@')
		fi
		
		if [ -e "$WLAN_HOSTAPD_CONF" ]; then
			WLAN_SSID="\""$(cat $WLAN_HOSTAPD_CONF | grep ssid | sed -e 's@.*ssid=@@')"\""
			WLAN_PASSPHRASE="\""$(cat $WLAN_HOSTAPD_CONF | grep wpa_passphrase | sed -e 's@.*wpa_passphrase=@@')"\""
			WLAN_METHOD="ap"
		fi
		
		if [ x"$WLAN_METHOD" == x"static" ] && [ x"$WLAN_IP" == x"172.16.0.2" ] && [ -z "$WLAN_GATEWAY" ]; then
			echo "config_wifi_default $WLAN_IFACE" >> ${CONNMAN_UPGRADE_FILE}
		else
			if [ x"$WLAN_METHOD" == x"static" ] && [ -n "$WLAN_SSID" ]; then
				echo "config_wifi_static $WLAN_IFACE $WLAN_SSID $WLAN_PASSPHRASE $WLAN_IP $WLAN_NETMASK $WLAN_GATEWAY"  >> ${CONNMAN_UPGRADE_FILE}
				echo "connect_wifi $WLAN_IFACE $WLAN_SSID"  >> ${CONNMAN_UPGRADE_FILE}
			elif [ x"$WLAN_METHOD" == x"dhcp" ] && [ -n "$WLAN_SSID" ]; then
				echo "config_wifi_dhcp $WLAN_IFACE $WLAN_SSID $WLAN_PASSPHRASE"  >> ${CONNMAN_UPGRADE_FILE}
				echo "connect_wifi $WLAN_IFACE $WLAN_SSID"  >> ${CONNMAN_UPGRADE_FILE}
			elif [ x"$WLAN_METHOD" == x"ap" ]  && [ -n "$WLAN_SSID" ]; then
				echo "config_wifi_ap $WLAN_IFACE $WLAN_SSID $WLAN_PASSPHRASE"  >> ${CONNMAN_UPGRADE_FILE}
			fi
		fi
	fi
else
	# Already using connman or no upgrade configured
	echo "Skipping ifupdown config file porting"
fi


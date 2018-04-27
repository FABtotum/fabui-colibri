#!/bin/bash

CONNMAN_SERVICES_DIR="/var/lib/connman"
CONNMAN_WIFI_CONFIG_FILE="user_wifi.config"
CONNMAN_ETH_CONFIG_FILE="user_ethernet.config"

##
#
# $1 - filename
# $2 - section
# $3 - key
#
parse_ini()
{
	INI_FILE="$1"
	INI_SECTION="$2"
	INI_KEY="$3"
	
	CUR_SECTION=""
	
	for line in $(cat $INI_FILE); do
	
		SECTION=$(echo $line | awk -F'[\]\[]' '{print $2}')
	
		[ -n "$SECTION" ] && CUR_SECTION="$SECTION"
		
		if [ x"$CUR_SECTION" == x"$INI_SECTION" ]; then
			KEY=$(echo $line | awk -F= '{print $1}')
			VALUE=$(echo $line | awk -F= '{print $2}')
			
			echo "[$line]" $KEY  $VALUE
			
			if [ x"$KEY" == x"$INI_KEY" ]; then
				echo $VALUE
				return
			fi
		fi
	done
}

##
# Function calculates number of bit in a netmask
# 
# $1 - netmask
#
mask2cidr() {
    nbits=0
    IFS=.
    for dec in $1 ; do
        case $dec in
            255) let nbits+=8;;
            254) let nbits+=7;;
            252) let nbits+=6;;
            248) let nbits+=5;;
            240) let nbits+=4;;
            224) let nbits+=3;;
            192) let nbits+=2;;
            128) let nbits+=1;;
            0);;
            *) echo "Error: $dec is not recognised"; exit 1
        esac
    done
    echo "$nbits"
}

##
# Translate interface name to service name
#
# $1 - interface (ex: wlan0)
#
connman_iface2service()
{
    IFACE="$1"
    IF_MAC=$(ip link show dev $IFACE | grep link/ether | awk '{print $2}' | sed -e s@:@@g )
    IF_SRV=""
    
    if [ $(echo $iface | grep -e "eth[0-9]") ]; then
        IF_SRV="ethernet_${IF_MAC}_cable"
    elif [ $(echo $iface | grep -e "wlan[0-9]") ]; then
        IF_SRV=$(find ${CONNMAN_SERVICES_DIR} -name  wifi_${IF_MAC}_*)
        [ -n "$IF_SRV" ] && IF_SRV=$(basename $IF_SRV)
    fi
    
    echo "$IF_SRV"
}

##
# Remove current wifi configuration
#
connman_cleanup_wifi_config()
{
	rm -rf ${CONNMAN_SERVICES_DIR}/wifi_* &> /dev/null
	rm ${CONNMAN_SERVICES_DIR}/${CONNMAN_WIFI_CONFIG_FILE} &> /dev/null
}

##
# Remove current ethernet configuration
#
connman_cleanup_ethernet_config()
{
	rm -rf ${CONNMAN_SERVICES_DIR}/ethernet* &> /dev/null
	rm ${CONNMAN_SERVICES_DIR}/${CONNMAN_ETH_CONFIG_FILE} &> /dev/null
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
# Get nameservers from resolv.conf.tail and convert to connman format
#
connman_get_dns_tail()
{
	if [ -f /etc/resolv.conf.tail ]; then
		cat /etc/resolv.conf.tail | awk '{print $2}' | sed -n '1{x;d};${H;x;s/\n/,/g;p};{H}'
	fi
}

##
# Get nameservers from resolv.conf.head and convert to connman format
#
connman_get_dns_head()
{
	if [ -f /etc/resolv.conf.head ]; then
		cat /etc/resolv.conf.head | awk '{print $2}' | sed -n '1{x;d};${H;x;s/\n/,/g;p};{H}'
	fi
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
	connmanctl tether wifi off
	
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
	NS=$(connman_get_dns_tail)

	connman_cleanup_wifi_config
	
cat <<EOF > ${CONNMAN_SERVICES_DIR}/${CONNMAN_WIFI_CONFIG_FILE}
# Automatically generated, do not edit \n
[service_user_wifi]
Type = wifi
Name = ${SSID}
Passphrase = ${PASS}
IPv4 = ${IP}/${NETMASK}/${GATEWAY}
Nameservers = ${NS}
EOF
	connmanctl tether wifi off
	
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
	
	#~ disconnect_wifi "$IFACE"
	connman_cleanup_wifi_config
	connmanctl tether wifi on "$SSID" "$PASS"
	
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
	
	#~ disconnect_wifi "$IFACE"
	connman_cleanup_wifi_config
	connmanctl tether wifi off
	
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
	connmanctl scan wifi
	connmanctl connect $SRV
	connmanctl disable wifi
	sleep 2
	connmanctl enable wifi
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
# Disable wireless hardware
#
disable_wifi()
{
    connmanctl disable wifi
}

##
# Enable wireless hardware
enable_wifi()
{
    connmanctl enable wifi
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
	
	#ETH_MAC=$(ip link show dev $IFACE | grep link/ether | awk '{print $2}' | sed -e s@:@@g )
	#ETH_SRV="ethernet_${ETH_MAC}_cable"
	#connmanctl config $ETH_SRV ipv4 dhcp
cat <<EOF > ${CONNMAN_SERVICES_DIR}/${CONNMAN_ETH_CONFIG_FILE}
# Automatically generated, do not edit \n
[service_ethernet_wifi]
Type = ethernet
IPv4 = dhcp
IPv6 = Off
EOF
	
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
	NO_NAMESERVER="$5"
	NS="Nameservers = $(connman_get_dns_tail)"
	
	#~ ETH_MAC=$(ip link show dev $IFACE | grep link/ether | awk '{print $2}' | sed -e s@:@@g )
	#~ ETH_SRV="ethernet_${ETH_MAC}_cable"
	#~ connmanctl config $ETH_SRV ipv4 manual $IP $NETMASK $GATEWAY nameservers $NS
	
	if [ x"$NO_NAMESERVER" == x"yes" ]; then
		NS=""
	fi
	
	if [ -n "$GATEWAY" ]; then
	
cat <<EOF > ${CONNMAN_SERVICES_DIR}/${CONNMAN_ETH_CONFIG_FILE}
# Automatically generated, do not edit \n
[service_ethernet]
Type = ethernet
IPv4 = $IP/$NETMASK/$GATEWAY
$NS
IPv6 = Off
EOF

	else
	
cat <<EOF > ${CONNMAN_SERVICES_DIR}/${CONNMAN_ETH_CONFIG_FILE}
# Automatically generated, do not edit \n
[service_ethernet]
Type = ethernet
IPv4 = $IP/$NETMASK
$NS
IPv6 = Off
EOF
	
	fi
	
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
	
	if [ x"$STATE" == x"ready" ]; then
	    echo -e "GET http://google.com HTTP/1.0\n\n" | nc google.com 80 -w 5 > /dev/null 2>&1
		if [ $? -eq 0 ]; then
		    echo "online"
		else
		   echo "offline"
		fi
	elif [ x"$STATE" == x"online" ]; then
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
#        "mac_address"  : "00:00:00:00:00:00",
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
	if [ -z "$1" ]; then
		IFACES=$(ls /sys/class/net)
	else
		IFACES=$1
	fi
	BLUETOOTH=$(bluetooth_status)
	echo "{"
	echo "  \"hostname\":\"$HOSTNAME.local\"",
	echo "  \"bluetooth\": $BLUETOOTH, "
	PREV=
	# TETHER="no"
	for iface in $(echo $IFACES); do
		if [ "$iface" != "lo" ]; then
		
			if [ "$iface" == "tether" ]; then
				TETHER="yes"
				continue
			else
				TETHER="no"
			fi
			
			if [ $(echo $iface | grep -e "wlan[0-9]") ]; then
				TETHERING=$(connmanctl technologies | awk '/^$/{f=0} f{print} /\/net\/connman\/technology\/wifi/{f=1}' | grep -m 1 Tethering | awk  '{print $3}')
			elif [ $(echo $iface | grep -e "eth[0-9]") ]; then
				TETHERING=$(connmanctl technologies | awk '/^$/{f=0} f{print} /\/net\/connman\/technology\/ethernet/{f=1}' | grep -m 1 Tethering | awk  '{print $3}')
			fi
						
			if [ x"$TETHERING" == x"True" ]; then
				TETHER="yes"
			else
				TETHER="no"
			fi
			
			# Get driver used by this interface
			DRIVER_DIR="/sys/class/net/$iface/device/driver"
			DRIVER=""
			[ -e "$DRIVER_DIR" ] && DRIVER=$(basename $(readlink $DRIVER_DIR) )
			SERVICE=$(connman_iface2service $iface)
			
			if [ -n "$PREV" ]; then
				echo -e "  },"
			fi
			
			
			MAC=$(ip link show dev $iface | grep link/ether | awk '{print $2}')
			IPv4=""
			IPv6=""
			GATEWWAY=""
			NETMASK_PREFIX=24
			MODE="unknown"
			PASSPHRASE=""
			
			echo "  \"$iface\" : {"
			echo "    \"driver\" : \"$DRIVER\", "
			
			if [ $(echo $iface | grep -e "eth[0-9]") ]; then
				CABLE_PLUGGED_IN=$(ethtool $iface | grep "Link detected" | awk '{print $NF}')
				echo "    \"cable\" : \"$CABLE_PLUGGED_IN\","
			fi
			
			# Get interface addresses
			if [ -n "$SERVICE" ]; then
				SETTINGS_FILE="${CONNMAN_SERVICES_DIR}/${SERVICE}/settings"
			
				LIVE_SERVICE=$(connmanctl services | grep $SERVICE | awk '{print $NF}')
				
				if [ -n "$LIVE_SERVICE" ]; then
					A=$(connmanctl services $SERVICE | grep "IPv4 " | sed -e 's@IPv4 = \[@@g' -e 's@\]@@g' -e 's@,@@g')
					B=$(connmanctl services $SERVICE | grep IPv4.Configuration | sed -e 's@IPv4.Configuration = \[@@g' -e 's@\]@@g' -e 's@,@@g')
					for SEGMENT in $(echo $A $B); do
						KEY=$(echo $SEGMENT | awk -F= '{print $1}' | sed -e 's@\.@_@g' )
						VALUE=$(echo $SEGMENT | awk -F= '{print $2}')
						case $KEY in
							Method)
								MODE="$VALUE"
								;;
							Address)
								IPv4="$VALUE"
								;;
							Netmask)
								NETMASK="$VALUE"
								;;
							Gateway)
								GATEWAY="$VALUE"
								;;
						esac
					done
					NETMASK_PREFIX=$(mask2cidr ${NETMASK})
				else
					if [ $(echo $iface | grep -e "eth[0-9]") ]; then
						if [ -e "$SETTINGS_FILE" ]; then
							#echo "Reading settings from file"
							# fallback, read addresses from settings
							MODE=$(cat "$SETTINGS_FILE" |  grep "IPv4.method" | awk -F= '{print $2}')
							NETMASK_PREFIX=$(cat "$SETTINGS_FILE" |  grep "IPv4.netmask_prefixlen" | awk -F= '{print $2}')
							IPv4=$(cat "$SETTINGS_FILE" |  grep "IPv4.local_address" | awk -F= '{print $2}')
							eval $(ipcalc $IPv4/$NETMASK_PREFIX -m)
							
							GATEWAY=$(cat "$SETTINGS_FILE" |  grep "IPv4.gateway" | awk -F= '{print $2}')
						fi
					elif [ $(echo $iface | grep -e "wlan[0-9]") ]; then
						MODE="disabled"
						STATE=$(parse_ini "${CONNMAN_SERVICES_DIR}/settings" WiFi Enable)
						if [ x"$STATE" == x"false" ]; then
							MODE="manual"
						fi
					fi
				fi
				
				
				
				if [ -e "$SETTINGS_FILE" ]; then
					PASSPHRASE=$(cat "$SETTINGS_FILE" |  grep Passphrase | awk -F= '{print $2}')
				fi
			fi
			
			[ $TETHER == "yes" ] && MODE="static-ap"
			
			if [ $(echo $iface | grep -e "wlan[0-9]") ]; then
				POWERED=$(connmanctl technologies | awk '/^$/{f=0} f{print} /\/net\/connman\/technology\/wifi/{f=1}' | grep -m 1 Powered | awk  '{print $3}')
				if [ x"$POWERED" == x"False" ]; then
					MODE="disabled"
				fi
			fi
			
			echo "    \"address_mode\" : \"$MODE\","
			
			echo "    \"mac_address\" : \"$MAC\","
			if [ -n "$NETMASK" ]; then
				eval $(ipcalc -p $NETMASK)
				echo "    \"ipv4_address\" : \"$IPv4/${NETMASK_PREFIX}\","
			else
				echo "    \"ipv4_address\" : \"$IPv4\","
			fi
			echo "    \"ipv6_address\" : \"$IPv6\","
			echo -n "    \"gateway\" : \"$GATEWAY\""
			
			# Check if the interface has wireless capabilities
			if [ -e "/sys/class/net/$iface/wireless" ]; then
				echo ","
				echo "    \"wireless\" : {"
				#~ if [[ "$DRIVER" == "rtl8192cu" ]] || [[ "$DRIVER" == "brcmfmac_sdio" ]]; then
				if [ "$DRIVER" == "brcmfmac_sdio" ] || [ $TETHER == "yes" ] ; then
					echo "      \"can_be_ap\" : \"yes\","
				else
					echo "      \"can_be_ap\" : \"no\","
				fi
				echo "      \"support_ap_channel\" : \"no\","
				echo -n "      \"support_ap_custom_address\" : \"no\""
				
				if [ x"$MODE" != x"disabled" ] && [ x"$MODE" != x"unknown" ]; then
				
					MODE=$(iwconfig $iface | awk '/Mode/{print $1}')
					
					if [ x"$MODE" == x"$iface" ] && [ x"$MODE" != x"disabled" ]; then
						MODE=$(iwconfig $iface | awk '/Mode/{print $4}')
					fi
					
					if [ $MODE == "Mode:Master" ] || [ $TETHER == "yes" ]; then
						echo ","
						echo "      \"mode\" : \"accesspoint\","
						SSID=$(connmanctl technologies | grep TetheringIdentifier | awk '{print $NF}')
						PASSPHRASE=$(connmanctl technologies | grep TetheringPassphrase | awk '{print $NF}')
						
						echo "      \"ssid\" : \"$SSID\","
						echo "      \"passphrase\" : \"$PASSPHRASE\""
					elif [ $MODE == "Mode:Managed" ]; then
						echo ","
						echo "    \"passphrase\" : \"$PASSPHRASE\""
						POWERED=$(connmanctl technologies | awk '/^$/{f=0} f{print} /\/net\/connman\/technology\/wifi/{f=1}' | grep -m 1 Powered | awk  '{print $3}')
						if [ x"$POWERED" == x"True" ]; then
							echo ","
							a=$(wpa_cli -p /run/wpa_supplicant -i$iface status | sed -e 's@^@"@g; s@$@",@g; s@=@" : "@'; echo -n ",")
						fi
						echo $a | sed -e 's@, ,@@g'
						
					elif [ $MODE == "Mode:Auto" ]; then
						echo ","
						echo "      \"mode\" : \"auto\""
					else
						echo ""
					fi
				
				fi
				
				echo "    }"
			else
				echo ""
			fi
			
			#~ echo "  }"
			PREV=yes
		fi
	done
	echo "  }"
	echo "}"
}

##
# Get DNS configuration
#
get_dns_config()
{
	echo ""
}

connman_migrate_settings()
{
	ETH_SER=$(connman_iface2service eth0)
	echo "HW ETH: $ETH_SER"
	if [ -e "${CONNMAN_SERVICES_DIR}/$ETH_SER" ]; then
		echo "- configured: YES"
	else
		echo "- configured: NO"
		rm -rf ${CONNMAN_SERVICES_DIR}/ethernet_* &> /dev/null
		#~ for cfg in $(ls ${CONNMAN_SERVICES_DIR}/ethernet_* -d); do
			#~ if [ x"${CONNMAN_SERVICES_DIR}/$ETH_SER" != x"$cfg" ]; then
				#~ echo "-- Migrating $cfg ..."
				#~ SETTINGS_FILE="$cfg/settings"
				#~ OLD_MAC=$(cat $SETTINGS_FILE | grep "\[" | awk -F '_' '{print $2}')
				#~ NEW_MAC=$(echo $ETH_SER | awk -F '_' '{print $2}')
				#~ echo "$OLD_MAC -> $NEW_MAC"
				#~ sed -e "s/$OLD_MAC/$NEW_MAC/g" -i $SETTINGS_FILE
				#~ mv "$cfg" "${CONNMAN_SERVICES_DIR}/${ETH_SER}"
				#~ break
			#~ fi
		#~ done
	fi


	WLAN_SER=$(connman_iface2service wlan0)
	echo "HW WLAN: $WLAN_SER"
	if [ -e "${CONNMAN_SERVICES_DIR}/$WLAN_SER" ] && [ -n "$WLAN_SER" ]; then
		echo "- configured: YES"
	else
		echo "- configured: NO"
		rm -rf ${CONNMAN_SERVICES_DIR}/wifi_* &> /dev/null
	fi
}

##
# Disable bluetooth
#
disable_bluetooth()
{
	DISABLED="false"
    DISABLED_RESULT=$(connmanctl disable bluetooth 2>&1)
	if [ x"$DISABLED_RESULT" == x"Disabled bluetooth" ] ; then
		DISABLED="true"
	fi
	
	echo "{"
	echo "  \"disabled\": $DISABLED"
	echo "}"
}
##
# Enable bluetooth
enable_bluetooth()
{
	ENABLED="false"
	TETHER="false"
    ENABLED_RESULT=$(connmanctl enable bluetooth 2>&1)
	TETHER_RESULT=$(connmanctl tether bluetooth on 2>&1)
	
	if [ x"$ENABLED_RESULT" == x"Error bluetooth: Already enabled" ] || [ x"$ENABLED_RESULT" == x"Enabled bluetooth" ] ; then
		ENABLED="true"
	fi
	
	if [ x"$TETHER_RESULT" == x"Error enabling bluetooth tethering: Already enabled" ] || [ x"$TETHER_RESULT" == x"Enabled tethering for bluetooth" ] ; then
		TETHER="true"
	fi
	
	echo "{"
	echo "  \"enabled\": $ENABLED,"
	echo "  \"tethering\": $TETHER"
	echo "}"
}
##
# Get Bluetooth status
##
bluetooth_status()
{
	POWERED=$(connmanctl technologies | awk '/^$/{f=0} f{print} /\/net\/connman\/technology\/bluetooth/{f=1}' | grep -m 1 Powered | awk  '{print $3}')
	TETHERING=$(connmanctl technologies | awk '/^$/{f=0} f{print} /\/net\/connman\/technology\/bluetooth/{f=1}' | grep -m 1 Tethering | awk  '{print $3}')
	CONNECTED=$(connmanctl technologies | awk '/^$/{f=0} f{print} /\/net\/connman\/technology\/bluetooth/{f=1}' | grep -m 1 Connected | awk  '{print $3}')
	BT_ADDRESS=$(hcitool dev | grep -o -E '([[:xdigit:]]{1,2}:){5}[[:xdigit:]]{1,2}')
		
	POWERED_VALUE="false"
	TETHERING_VALUE="false"
	CONNECTED_VALUE="false"
	PAIRED_VALUE="false"
	
	if [ x"$POWERED" == x"True" ]; then
		POWERED_VALUE="true"
	fi
	
	if [ x"$TETHERING" == x"True" ]; then
		TETHERING_VALUE="true"
	fi
	
	if [ x"$CONNECTED" == x"True" ]; then
		CONNECTED_VALUE="true"
	fi
	
	echo "{"
	echo "  \"powered\": $POWERED_VALUE,"
	echo "  \"tethering\": $TETHERING_VALUE,"
	echo "  \"connected\": $CONNECTED_VALUE,"
	echo "  \"paired\":"
	
	DIRECTORY="/var/lib/bluetooth/$BT_ADDRESS/*:*:*:*:*:*"
	
	if [ -d $DIRECTORY ] && [ x"$POWERED" == x"True" ]; then
		
		# go to folder
		cd $DIRECTORY
		MAC_ADDRESS=${PWD##*/}
		
		NAME=$(awk -F "=" '/Name/ {print $2}'  $DIRECTORY/info)
		TRUSTED=$(awk -F "=" '/Trusted/ {print $2}'  $DIRECTORY/info)
		BLOCKED=$(awk -F "=" '/Blocked/ {print $2}'  $DIRECTORY/info)
		PAIR_CONNECTED="false"
		
		HCI_CONN=$(hcitool con | grep -o -E '([[:xdigit:]]{1,2}:){5}[[:xdigit:]]{1,2}')
		
		if [ x"$HCI_CONN" == x"$MAC_ADDRESS" ]; then
			PAIR_CONNECTED="true"
		fi
		
		echo "  {"
		echo "    \"mac_address\": \"$MAC_ADDRESS\"",
		echo "    \"name\": \"$NAME\","
		echo "    \"connected\": $PAIR_CONNECTED,"
		echo "    \"trusted\": $TRUSTED,"
		echo "    \"blocked\": $BLOCKED"
		echo "  }"
	else
		echo "false"
	fi
	
	echo "}"
}
##
# bluetooth remove device
#
bluetooth_remove_device()
{
	MAC_ADDRESS="$1"
	BT_ADDRESS=$(hcitool dev | grep -o -E '([[:xdigit:]]{1,2}:){5}[[:xdigit:]]{1,2}')
	
	DIRECTORY="/var/lib/bluetooth/$BT_ADDRESS/$MAC_ADDRESS"
	
	if [ -d $DIRECTORY ]; then
		rm -rvf $DIRECTORY 
		/etc/init.d/bluetooth restart
	fi
}

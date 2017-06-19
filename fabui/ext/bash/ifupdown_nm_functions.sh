#!/bin/bash

HOSTAPD_CONF_DIR=/etc/hostapd
HOSTAPD_RUN_DIR=/run/hostapd
WPA_CONF_DIR=/etc/wpa_supplicant
WPA_RUN_DIR=/run/wpa_supplicant
INTERFACESD=/etc/network/interfaces.d
MAX_STATIONS=10

##
# 
#
#
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

##
# Create a IPv4 range based on gateway IPv4
# 
# $1 - Gateway IPv4
#
make_range()
{
    IP="${1}"
IFS=. read -r i1 i2 i3 i4 << EOF
$IP
EOF
    printf "%d.%d.%d.%d,%d.%d.%d.%d\n" $i1 $i2 $i3 10 $i1 $i2 $i3 100
}

##
# Reset interface configuration
#
# $1 - interface
#
ifupdown_iface_cleanup()
{
    IFACE="${1}"

    ifdown --force "$IFACE"
    ip addr flush dev "$IFACE"
}

## 
#
#
ifupdown_iface_up()
{
    IFACE="${1}"
    
    ifup --force "$IFACE"
}

##
# Set static address mode
# 
# $1 - interface
# $2 - IPv4 address
# $3 - netmask
# $4 - Gateway
# $5 - Path to WPA config file (optional)
#
ifupdown_static_address()
{
    IFACE="${1}"
    IP="${2}"
    NETMASK="${3}"
    GATEWAY="${4}"
    WPA_CONF="${5}"
        
cat <<EOF > ${INTERFACESD}/${IFACE}
# Automatically generated, do not edit \n

allow-hotplug $IFACE
auto $IFACE
iface $IFACE inet static
  address  $IP
  netmask  $NETMASK
EOF

    if [ -n "$GATEWAY" ]; then
cat <<EOF >> ${INTERFACESD}/${IFACE}
  gateway  $GATEWAY
EOF
    fi

    if [ -n "$WPA_CONF" ]; then
cat <<EOF >> ${INTERFACESD}/${IFACE}
  wpa-conf $WPA_CONF
EOF
    fi
}

##
# Set dhcp address mode
# 
# $1 - interface
# $2 - Path to WPA config file (optional)
#
ifupdown_dhcp_address()
{
    IFACE="${1}"
    WPA_CONF="${2}"

cat <<EOF > ${INTERFACESD}/${IFACE}
# Automatically generated, do not edit \n

allow-hotplug $IFACE
auto $IFACE
iface $IFACE inet dhcp
EOF
    if [ -n "$WPA_CONF" ]; then
cat <<EOF >> ${INTERFACESD}/${IFACE}
  wpa-conf $WPA_CONF
  wpa-timeout 30
EOF
    fi

}

ifupdown_ap_address()
{
    IFACE="${1}"
    IP="${2}"
    NETMASK="${3}"
    HOSTAPD_CONF="${4}"
    
    masked=$(mask_ip $IP $NETMASK)
    RANGE=$(make_range $masked)
    
cat <<EOF > ${INTERFACESD}/${IFACE}
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

##
# Create hostapd config file
# 
# $1 - interface
# $2 - SSID
# $3 - Passphrase
# $4 - Channel
#
ifupdown_hostapd_conf()
{
    IFACE="${1}"
    SSID="${2}"
    PASS="${3}"
    CHANNEL="${4}"

    HOSTAPD_CONF="${HOSTAPD_CONF_DIR}/${IFACE}.conf"
    
    echo "${HOSTAPD_CONF}"

cat <<EOF > ${HOSTAPD_CONF}
# Automatically generated, do not edit
interface=${IFACE}
driver=nl80211
ctrl_interface=${HOSTAPD_RUN_DIR}
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
# Generate wpa_supplicant config file
# $1 - interface
# $2 - ssid
# $3 - passphrase
#
ifupdown_wpa_supplicant_conf()
{
    IFACE="$1"
    SSID="$2"
    PASS="$3"
    
    WPA_CONF="${WPA_CONF_DIR}/${IFACE}.conf"
    
    echo "${WPA_CONF}"
    
    if [ x"$PASS" == x"-" ]; then
cat <<EOF > $WPA_CONF
# Automatically generated file, do not edit by hand.
ctrl_interface=DIR=${WPA_RUN_DIR} GROUP=netdev
update_config=1

network={
  ssid="$SSID"
  key_mgmt=NONE
}
EOF
    else
    
cat <<EOF > $WPA_CONF
# Automatically generated file, do not edit by hand.
ctrl_interface=DIR=${WPA_RUN_DIR} GROUP=netdev
update_config=1

network={
  ssid="$SSID"
  psk="$PASS"
}
EOF
    fi
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

    ifupdown_iface_cleanup "$IFACE"
    WPA_CONF=$(ifupdown_wpa_supplicant_conf "$IFACE" "$SSID" "$PASS")
    ifupdown_dhcp_address "$IFACE" "$WPA_CONF" 
    ifupdown_iface_up "$IFACE"
    
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

    ifupdown_iface_cleanup "$IFACE"
    WPA_CONF=$(ifupdown_wpa_supplicant_conf "$IFACE" "$SSID" "$PASS")
    ifupdown_static_address "$IFACE" "$IP" "$NETMASK" "$GATEWAY" "$WPA_CONF"
    ifupdown_iface_up "$IFACE"
    
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

    ifupdown_iface_cleanup "$IFACE"
    HOSTAPD_CONF=$(ifupdown_hostapd_conf "$IFACE" "$SSID" "$PASS" "$CHANNEL")
    ifupdown_ap_address "$IFACE" "$IP" "$NETMASK" "$HOSTAPD_CONF"
    ifupdown_iface_up "$IFACE"
    
    return 0
}

##
# Confiture wifi interface to a default state
#
# $1 - interface (ex: wlan0)
#
# returns 0 on success
#
config_wifi_default()
{
    IFACE="$1"

    #~ disconnect_wifi "$IFACE"

    ifupdown_iface_cleanup "$IFACE"

cat <<EOF > $WPA_CONF
ctrl_interface=DIR=${WPA_RUN_DIR} GROUP=netdev
update_config=1
network={
}
EOF
    IDX=$(echo $IFACE | sed s/wlan//)
    let "IDX++"
    IP="172.17.0.$IDX"
    NETMASK="255.255.0.0"
    
    ifupdown_static_address "$IFACE" "$IP" "$NETMASK"
    ifupdown_iface_up "$IFACE"
    
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
# Disable wireless hardware
#
disable_wifi()
{
    true
}

##
# Enable wireless hardware
enable_wifi()
{
    true
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
    
    wpa_cli -p ${WPA_RUN_DIR} -i$IFACE disconnect
    config_wifi_default "$IFACE"
    
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

    ifupdown_iface_cleanup "$IFACE"
    ifupdown_dhcp_address "$IFACE"
    ifupdown_iface_up "$IFACE"
    
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

    ifupdown_iface_cleanup "$IFACE"
    ifupdown_static_address "$IFACE" "$IP" "$NETMASK" "$GATEWAY" "$WPA_CONF"
    ifupdown_iface_up "$IFACE"
    
    return 0
}

##
# Get internet state
#
# returns "online" or "offline"
# 
get_internet_state()
{
	WLAN_INET=$(route -n  | grep wlan0 | grep UG)
	ETH_INET=$(route -n | grep eth0 | grep UG)
	ETH_GW=$(echo $ETH_INET |  awk '{print $2}')
	if [ -z "$WLAN_INET" ] && [ "$ETH_GW" == "169.254.1.1" ]; then
	  # no internet, bail out
	  echo "offline"
	  return 0
	fi
	
	echo -e "GET http://google.com HTTP/1.0\n\n" | nc google.com 80 -w 5 > /dev/null 2>&1

	if [ $? -eq 0 ]; then
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
    
    echo "{"
    PREV=
    for iface in $(echo $IFACES); do
        if [ "$iface" != "lo" ]; then
            # Get driver used by this interface
            DRIVER=$(basename $(readlink /sys/class/net/$iface/device/driver))
            
            if [ -n "$PREV" ]; then
                echo -e "  },"
            fi
            
            echo "  \"$iface\" : {"
            echo "    \"driver\" : \"$DRIVER\", "
            
            if [ "$iface" == "eth0" ]; then
                CABLE_PLUGGED_IN=$(ethtool eth0 | grep "Link detected" | awk '{print $NF}')
                echo "    \"cable\" : \"$CABLE_PLUGGED_IN\","
            fi
            
            # Get interface addresses
            IPADDR=$(ip addr show $iface | awk '/(link|inet)/ {print $2;}')
            MAC=$(echo $IPADDR | awk '{print $1;}')
            IPv4=$(echo $IPADDR | awk '{print $2;}')
            IPv6=$(echo $IPADDR | awk '{print $3;}')
            echo "    \"mac_address\" : \"$MAC\","
            echo "    \"ipv4_address\" : \"$IPv4\","
            echo "    \"ipv6_address\" : \"$IPv6\","
            
            if [ -e "/etc/network/interfaces.d/$iface" ]; then
                MODE=$(cat /etc/network/interfaces.d/$iface | grep "^iface $iface" | awk '{print $4}')
                HOSTAPD=$(cat /etc/network/interfaces.d/$iface | grep "^[ \t]*hostapd" | awk '{print $2}')
                WPASUP=$(cat /etc/network/interfaces.d/$iface | grep "^[ \t]*wpa-conf" | awk '{print $2}')
                GATEWAY=$(cat /etc/network/interfaces.d/$iface | grep "^[ \t]*gateway" | awk '{print $2}')
                echo "    \"address_mode\" : \"$MODE\","
            else
                echo "    \"address_mode\" : \"unknown\","
            fi
            
            # Get interface gataway if present
            if [ -z "$GATEWAY" ]; then
                GATEWAY=$(ip route  | grep $iface | awk '/default/ {print $3;}' | awk 'NR==1{print $1}')
            fi
            echo -n "    \"gateway\" : \"$GATEWAY\""
            
            # Check if the interface has wireless capabilities
            if [ -e "/sys/class/net/$iface/wireless" ]; then
                echo ","
                echo "    \"wireless\" : {"
                if [ "$DRIVER" == "brcmfmac_sdio" ] || [ $TETHER == "yes" ] ; then
                    echo "      \"can_be_ap\" : \"yes\","
                else
                    echo "      \"can_be_ap\" : \"no\","
                fi
                echo "      \"support_ap_channel\" : \"yes\","
                echo -n "      \"support_ap_custom_address\" : \"yes\""
                
                MODE=$(iwconfig $iface | awk '/Mode/{print $1}')
                
                if [ x"$MODE" == x"$iface" ]; then
                    MODE=$(iwconfig $iface | awk '/Mode/{print $4}')
                fi
                
                if [ $MODE == "Mode:Master" ]; then
                    echo ","
                    echo "      \"mode\" : \"accesspoint\","
                    if [ -n "$HOSTAPD" ]; then
                        PASS=$(cat "$HOSTAPD" | grep wpa_passphrase| awk 'BEGIN{FS="="}{print $2;}')
                        echo "      \"passphrase\" : \"$PASS\","
                        
                        CHANNEL=$(cat /etc/hostapd/$iface.conf | awk -F '=' '/channel/{print $2}')
                        if [ -n "CHANNEL" ]; then
                            echo "      \"channel\" : \"$CHANNEL\","
                        fi
                    fi
                    
                    a=$(hostapd_cli -p /run/hostapd -i$iface get_config | sed -e 's@^@"@g; s@$@",@g; s@=@" : "@'; echo -n ",")
                    echo $a | sed -e 's@, ,@@g'
                elif [ $MODE == "Mode:Managed" ]; then
                    if [ -n "$WPASUP" ]; then
                        echo ","
                        if [ -e "$WPASUP" ]; then
                            PSK=$(cat $WPASUP | grep -E "[ \t]*(psk=[0-9a-z\"])" | awk 'BEGIN{FS="="}{print $2;}')
                            
                            #~ echo "      \"psk\" : $PSK,"
                            echo "      \"passphrase\" : $PSK,"
                        fi
                        a=$(wpa_cli -p /run/wpa_supplicant -i$iface status | sed -e 's@^@"@g; s@$@",@g; s@=@" : "@'; echo -n ",")
                        echo $a | sed -e 's@, ,@@g'
                    fi
                elif [ $MODE == "Mode:Auto" ]; then
                    echo ","
                    echo "      \"mode\" : \"auto\""
                else
                    echo ""
                fi
                
                echo "    }"
            else
                echo ""
            fi
            
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

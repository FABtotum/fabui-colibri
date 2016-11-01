#!/bin/bash

if [ -z "$1" ]; then
    IFACES=$(ls /sys/class/net)
else
    IFACES=$1
fi

echo "{"

for iface in $(echo $IFACES); do
    if [ "$iface" != "lo" ]; then
        # Get driver used by this interface
        DRIVER=$(basename $(readlink /sys/class/net/$iface/device/driver))
        
        echo "  \"$iface\" : {"
        echo "    \"driver\" : \"$DRIVER\", "
        
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
            echo "    \"address_mode\" : \"$MODE\","
        fi
        
        # Get interface gataway if present
        ROUTE=$(ip route  | grep $iface | awk '/default/ {print $3;}')
        echo "    \"gateway\" : \"$ROUTE\","
        
        # Check if the interface has wireless capabilities
        if [ -e "/sys/class/net/$iface/wireless" ]; then
            echo "    \"wireless\" : {"
            
            MODE=$(iwconfig $iface | awk '/Mode/{print $1}')
            if [ $MODE == "Mode:Master" ]; then
                echo "      \"mode\" : \"accesspoint\","
                hostapd_cli -p /run/hostapd -i$iface get_config | sed -e 's@ *$@@g;s@=@\" : \"@;s@$@",@g;s@^@      "@'

                
            elif [ $MODE == "Mode:Managed" ]; then
                wpa_cli -p /run/wpa_supplicant -i$iface status | sed -e 's@ *$@@g;s@=@\" : \"@;s@$@",@g;s@^@      "@'
            fi
            
            echo "    }"
        fi
        
        echo "  }"
    fi
done

echo "}"

#!/bin/bash

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
            GATEWAY=$(ip route  | grep $iface | awk '/default/ {print $3;}')
        fi
        echo -n "    \"gateway\" : \"$GATEWAY\""
        
        # Check if the interface has wireless capabilities
        if [ -e "/sys/class/net/$iface/wireless" ]; then
            echo ","
            echo "    \"wireless\" : {"
            #~ if [[ "$DRIVER" == "rtl8192cu" ]] || [[ "$DRIVER" == "brcmfmac_sdio" ]]; then
            if [[ "$DRIVER" == "brcmfmac_sdio" ]]; then
                echo -n "      \"can_be_ap\" : \"yes\""
            else
                echo -n "      \"can_be_ap\" : \"no\""
            fi
            MODE=$(iwconfig $iface | awk '/Mode/{print $1}')
            
            if [ x"$MODE" == x"$iface" ]; then
				MODE=$(iwconfig $iface | awk '/Mode/{print $4}')
            fi
            
            if [ $MODE == "Mode:Master" ]; then
                echo ","
                echo "      \"mode\" : \"accesspoint\","
                #~ hostapd_cli -p /run/hostapd -i$iface get_config | sed -e 's@ *$@@g;s@=@\" : \"@;s@$@",@g;s@^@      "@'
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
                        PSK=$(cat $WPASUP | grep -E "[ \t]*(psk=[0-9a-z])" | awk 'BEGIN{FS="="}{print $2;}')
                        echo "      \"psk\" : \"$PSK\","
                    fi
                    a=$(wpa_cli -p /run/wpa_supplicant -i$iface status | sed -e 's@^@"@g; s@$@",@g; s@=@" : "@'; echo -n ",")
                    echo $a | sed -e 's@, ,@@g'
                    #~ cat "$WPASUP" | grep "^[ \t]*wpa-conf" | awk '{print $2}')
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
        
        #~ echo "  }"
        PREV=yes
    fi
done
echo "  }"
echo "}"

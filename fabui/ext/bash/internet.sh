#!/bin/bash

# Check if internet is configured on wifi or eth
WLAN_INET=$(route -n  | grep wlan0 | grep UG)
ETH_INET=$(route -n | grep eth0 | grep UG)
ETH_GW=$(echo $ETH_INET |  awk '{print $2}')

if [ -z "$WLAN_INET" ] && [ "$ETH_GW" == "169.254.1.1" ]; then
  # no internet, bail out
  echo "offline"
  exit 0
fi

echo -e "GET http://google.com HTTP/1.0\n\n" | nc google.com 80 -w 3 > /dev/null 2>&1

if [ $? -eq 0 ]; then
    echo "online"
else
    echo "offline"
fi

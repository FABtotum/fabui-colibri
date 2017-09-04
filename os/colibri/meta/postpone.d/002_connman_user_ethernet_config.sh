#!/bin/bash

source /mnt/live/mnt/boot/earlyboot/earlyboot.conf
source /usr/share/fabui/ext/bash/connman_nm_functions.sh



########################################################################

CONNMAN_UPGRADE_FILE="/var/lib/fabui/upgrade.d/002_connman_user_ethernet.sh"
CONNMAN_ETH_CONFIG_FILE="user_ethernet.config"

########################################################################

if [ -f "${CONNMAN_SERVICES_DIR}/${CONNMAN_ETH_CONFIG_FILE}" ]; then
	echo "Skipping user_ethernet.config upgrade"
else
	mkdir -p $(dirname $CONNMAN_UPGRADE_FILE)
	
#~ cat <<EOF > ${CONNMAN_UPGRADE_FILE}
#~ # Automatically generated upgrade file, do not edit \n
#~ source /usr/share/fabui/ext/bash/connman_nm_functions.sh

#~ # Ethernet configuration upgrade
#~ EOF

	#~ chmod +x ${CONNMAN_UPGRADE_FILE}

	SERVICE=$(connman_iface2service eth0)
	SETTINGS_FILE="${CONNMAN_SERVICES_DIR}/${SERVICE}/settings"
	IPv4=$(echo $NETWORK_IPV4 | awk -F / '{print $1}')
	# Generate netmask based on prefix
	#~ export eval ETH_$(ipcalc -m $IPv4)
	GATEWAY=$NETWORK_GW
	
	if [ -e "$SETTINGS_FILE" ]; then
		echo "Reading settings from file"
		# fallback, read addresses from settings
		METHOD=$(cat "$SETTINGS_FILE" |  grep "IPv4.method" | awk -F= '{print $2}')
		NETMASK_PREFIX=$(cat "$SETTINGS_FILE" |  grep "IPv4.netmask_prefixlen" | awk -F= '{print $2}')
		IPv4=$(cat "$SETTINGS_FILE" |  grep "IPv4.local_address" | awk -F= '{print $2}')
		GATEWAY=$(cat "$SETTINGS_FILE" |  grep "IPv4.gateway" | awk -F= '{print $2}')
		NS="Nameservers = $(connman_get_dns_tail)"
		# Generate netmask based on ip/prefix
		eval $(ipcalc -m $IPv4/$NETMASK_PREFIX)
		
		echo METHOD: $METHOD
		
		case $METHOD in
			fixed|manual)
				#echo "config_ethernet_static eth0 $IPv4 $NETMASK $GATEWAY" >> ${CONNMAN_UPGRADE_FILE}
				#~ echo "config_ethernet_static eth0 $IPv4 $NETMASK $GATEWAY"
cat <<EOF > ${CONNMAN_SERVICES_DIR}/${CONNMAN_ETH_CONFIG_FILE}
# Automatically generated, do not edit \n
[service_ethernet]
Type = ethernet
IPv4 = $IPv4/$NETMASK/$GATEWAY
$NS
IPv6 = Off
EOF
				;;
			dhcp)
cat <<EOF > ${CONNMAN_SERVICES_DIR}/${CONNMAN_ETH_CONFIG_FILE}
# Automatically generated, do not edit \n
[service_ethernet_wifi]
Type = ethernet
IPv4 = dhcp
IPv6 = Off
EOF
				;;
		esac
		
		rm -rf ${CONNMAN_SERVICES_DIR}/${SERVICE}
		/etc/init.d/connman restart
		
		#echo "rm -rf ${CONNMAN_SERVICES_DIR}/${SERVICE}" >> ${CONNMAN_UPGRADE_FILE}
		
	fi
fi


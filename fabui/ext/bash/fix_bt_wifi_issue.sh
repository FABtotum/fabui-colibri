#!/bin/bash

test -r /etc/default/fabui && source /etc/default/fabui
[ -z $BT_FIX_WIFI_ISSUE ] && BT_FIX_WIFI_ISSUE=no
[ x"$BT_FIX_WIFI_ISSUE" != x"yes" ] && exit 0

HCI=$(hciconfig 2>&1)

if [ x"$HCI" != x"" ]; then
    awk -f /usr/share/fabui/ext/bash/connman_bt_disable.awk < /var/lib/connman/settings > /tmp/new_settings
    mv /tmp/new_settings /var/lib/connman/settings
fi

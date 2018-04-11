#!/bin/bash
##########################################################################
##                                                                      ##
##                     Set wifi connection                              ##
##                                                                      ##
##                                                                      ##
## This program is free software; you can redistribute it and/or modify ##
## it under the terms of the GNU General Public License as published by ##
## the Free Software Foundation; either version 2 of the License, or    ##
## (at your option) any later version.                                  ##
##                                                                      ##
##########################################################################

test -r /etc/default/network && source /etc/default/network
[ -z $NETWORK_MANAGER ] && NETWORK_MANAGER=ifupdown
source /usr/share/fabui/ext/bash/${NETWORK_MANAGER}_nm_functions.sh

usage()
{
cat << EOF
usage: $0 options

This script configures bluetooth.

OPTIONS:
   -h      Show this message
   -a      action
EOF
}


ACTION=
MAC=


echo $@ > /tmp/args

while getopts “h:a:m:” OPTION
do
    case $OPTION in
		h)
            usage
            exit 1
            ;;
        a)
            ACTION="$OPTARG"
            ;;
		m)
			MAC="$OPTARG"
			;;
        ?)
            usage
            exit
            ;;
     esac
done

# echo "ACTION: $ACTION"

if [[ -z "$ACTION" ]]
then
     usage
     exit 1
fi


case $ACTION in
	status)
		bluetooth_status
		;;
    enable)
        enable_bluetooth
        ;;
    disable)
        disable_bluetooth
        ;;
	restart)
		sudo /etc/init.d/bluetooth restart
		;;
	remove)
		bluetooth_remove_device "$MAC"
		;;
    *)
        echo "error: unknown mode \'$ACTION\'"
        usage
        ;;
esac

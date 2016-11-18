#!/bin/bash

usage() {
	echo "usage: $(basename $0) activate|deactivate"
	exit 1
}

remount_boot_rw() {
	mount -o remount,rw /mnt/live/mnt/boot
}

remount_boot_ro() {
	mount -o remount,ro /mnt/live/mnt/boot
}

#######################

if [ -z "$1" ]; then
	usage
fi

case $1 in
	activate)
		echo "Recovery is activated"
		remount_boot_rw
		sed -i /mnt/live/mnt/boot/cmdline.txt -e 's/colibri.recovery=0/colibri.recovery=1/'
		remount_boot_ro
		;;
	deactivate)
		echo "Recovery is deactivated"
		remount_boot_rw
		sed -i /mnt/live/mnt/boot/cmdline.txt -e 's/colibri.recovery=1/colibri.recovery=0/'
		remount_boot_ro
		;;
	*)
		usage
		;;
esac

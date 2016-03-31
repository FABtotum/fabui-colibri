#!/bin/bash
source=${1}
if [ -z "$source" ]; then
	echo "missing source parameter"
	exit
fi
echo "Installing fw $source"
sudo /usr/bin/avrdude -D -q -V  -p atmega1280 -C /etc/avrdude.conf -c arduino -b 57600 -P  /dev/ttyAMA0 -U flash:w:$source:i
sleep 1
sudo php /var/www/fabui/script/boot.php
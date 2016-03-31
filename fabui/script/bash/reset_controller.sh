#!/bin/bash
echo  > /var/www/temp/LOCK
sudo python /var/www/fabui/python/force_reset.py
sleep 3
sudo php /var/www/fabui/script/boot.php
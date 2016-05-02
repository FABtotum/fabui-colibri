#!/bin/bash
sudo killall -KILL python php
sudo php /var/www/fabui/script/socket_server.php > /var/log/socket_server.log &
python /var/www/fabui/python/monitor.py > /var/log/monitor.log &
rm /run/task_*
sudo sh -c "echo 1 >/proc/sys/vm/drop_caches"
sudo sh -c "echo 2 >/proc/sys/vm/drop_caches"
sudo sh -c "echo 3 >/proc/sys/vm/drop_caches"
sudo python /var/www/fabui/python/force_reset.py
sleep 2
sudo php /var/www/fabui/script/boot.php
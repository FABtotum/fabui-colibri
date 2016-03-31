#!/bin/bash
echo "Cleaning files and folders"
sudo rm -rf /var/www/recovery/recovery
sudo rm -rf /var/www/recovery/update
sudo rm /var/www/fabui/script/blog_feed.php
sudo rm /var/www/fabui/script/faq.php
sudo rm /var/www/fabui/script/instagram_feed.php
sudo rm /var/www/fabui/script/twitter_feed.php
sudo rm -rf /var/www/fabui/application/plugins/gcodeviewer
sudo rm -rf /var/www/fabui/application/plugins/slic3r
sudo rm -rf /var/www/fabui/application/plugins/unity
echo "complete"
echo "Unistalling unuseful programs"
sudo apt-get remove --purge --force-yes --yes --quiet tightvncserver
sudo apt-get remove --purge --force-yes --yes --quiet lxpanel
sudo apt-get remove --purge --force-yes --yes --quiet pcmanfm
sudo apt-get remove --purge --force-yes --yes --quiet openbox
echo "unistall complete"
echo "Update repository"
sudo  apt-get update --yes
echo "Installing programs"
sudo apt-get --force-yes --yes --quiet install wpasupplicant
sudo apt-get --force-yes --yes --quiet install usbmount
sudo apt-get --force-yes --yes --quiet install dos2unix
sudo apt-get --force-yes --yes --quiet install avahi-daemon
sudo apt-get --force-yes --yes --quiet install avahi-utils
sudo apt-get --force-yes --yes --quiet install python-rpi.gpio
sudo apt-get --force-yes --yes --quiet install python3-rpi.gpio
echo "Installing python modules"
sudo pip install ws4py
sudo pip install watchdog
#!/bin/bash
#######################################
#
# check if internet access is available
#
#######################################
STATUS_FILE=/var/www/temp/internet
echo -e "GET http://google.com HTTP/1.0\n\n" | nc google.com 80 > /dev/null 2>&1
if [ $? -eq 0 ]; then
    STATUS=1
else
    STATUS=0
fi
echo -e $STATUS > $STATUS_FILE
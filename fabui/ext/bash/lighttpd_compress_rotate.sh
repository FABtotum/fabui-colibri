#!/bin/bash
# Shell script to clean web server cache stored at /mnt/bigtemp/lighttpd/compress directory. 
#
# -------------------------------------------------------------------------

# Cache dir path
CROOT="/mnt/bigtemp/lighttpd/compress"

#Deleting files older than 10 days
DAYS=1

# Lighttpd user and group
LUSER="www-data"
LGROUP="www-data"

# start cleaning 
find ${CROOT} -type f -mtime +${DAYS} | xargs -r /bin/rm

# if directory missing just recreate it
if [ ! -d $CROOT ]
then 
	mkdir -p $CROOT
	chown ${LUSER}:${LGROUP} ${CROOT}
fi
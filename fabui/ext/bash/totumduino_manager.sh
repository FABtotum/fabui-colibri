#!/bin/bash
################################################################################
# @author FABteam (C) 2016 
# @license https://opensource.org/licenses/GPL-3.0
################################################################################

source /etc/default/fabui

CMD=$1
HEXFILE=$2

# Settings
AVRDUDE="/usr/bin/avrdude"
AVRDUDE_PORT="/dev/ttyAMA0"
AVRDUDE_BAUD="57600"
AVRDUDE_PART="atmega1280"
AVRDUDE_ARGS="-D -q -V -p ${AVRDUDE_PART} -C /etc/avrdude.conf -c arduino -b ${AVRDUDE_BAUD} -P ${AVRDUDE_PORT}"

usage()
{
    echo "Usage:"
    echo
    echo "  $(basename $0) test                - Check the totumduino connection"
    echo "  $(basename $0) backup <file.hex>   - Backup the flashed firmware "
    echo "  $(basename $0) update <file.hex>   - Update firmware"
    exit 1
}

log_header()
{
    echo "============ cmd = $CMD ============"  >> /var/log/fabui/avrdude.log
    echo "$@" >> /var/log/fabui/avrdude.log
}

log_footer()
{
    echo "============ result = $RETR ============"  >> /var/log/fabui/avrdude.log
}

case $CMD in
    backup)
        [ "x${HEXFILE}" == "x" ] && usage
        
        log_header "${AVRDUDE} ${AVRDUDE_ARGS} -F -U flash:r:${HEXFILE}:i"
        ${AVRDUDE} ${AVRDUDE_ARGS} -F -U flash:r:${HEXFILE}:i >> /var/log/fabui/avrdude.log 2>&1
        RETR=$?
        log_footer ${RETR}
        exit $RETR
        ;;
    update)
        [ "x${HEXFILE}" == "x" ] && usage
        
        log_header "${AVRDUDE} ${AVRDUDE_ARGS} -U flash:w:${HEXFILE}:i"
        ${AVRDUDE} ${AVRDUDE_ARGS} -U flash:w:${HEXFILE}:i >> /var/log/fabui/avrdude.log 
        RETR=$?
        log_footer ${RETR}
        exit $RETR
        ;;
    factory)
        if [ -f "/mnt/live/mnt/boot/firmware.hex" ]; then
            echo "TODO: factory reset"
        fi
        ;;
    remote-update)
        FW_REPO_URL=$(cat /var/lib/fabui/config.ini | grep firmware_endpoint | awk 'BEGIN{FS="="}{print $2}')
        FILE_URL="${FW_REPO_URL}fablin/atmega1280/$2/firmware.zip"
        TMP_DIR="/tmp/fabui/firmware"
        mkdir -p $TMP_DIR

        wget -P $TMP_DIR $FILE_URL
        wget -P $TMP_DIR ${FILE_URL}.md5sum
        
        cd $TMP_DIR
        
        md5sum -c firmware.zip.md5sum
        RETR="$?"
        if [ "$RETR" == "0" ]; then
            unzip firmware.zip
            HEXFILE=$(find -name "*.hex")
            echo $HEXFILE
            
            log_header "${AVRDUDE} ${AVRDUDE_ARGS} -U flash:w:${HEXFILE}:i"
            ${AVRDUDE} ${AVRDUDE_ARGS} -U flash:w:${HEXFILE}:i >> /var/log/fabui/avrdude.log 
            RETR=$?
            log_footer ${RETR}
        fi
        
        cd /tmp
        rm -rf $TMP_DIR
        
        exit $RETR
        ;;
    test)
        log_header "${AVRDUDE} ${AVRDUDE_ARGS}"
        ${AVRDUDE} ${AVRDUDE_ARGS} >> /var/log/fabui/avrdude.log 2>&1
        RETR=$?
        log_footer ${RETR}
        
        if [ "$RETR" == "0" ]; then
            echo "OK"
        else
            echo "Error, check /var/log/fabui/avrdude.log"
        fi
        exit $RETR
        ;;
    *)
        usage
        ;;
esac

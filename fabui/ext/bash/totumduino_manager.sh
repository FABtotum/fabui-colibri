#!/bin/bash
################################################################################
# @author FABteam (C) 2016 
# @license https://opensource.org/licenses/GPL-3.0
################################################################################

source /etc/default/fabui

CMD=$1
HEXFILE=$2
EXT=${HEXFILE##*.}

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
    echo "  $(basename $0) factory             - Restore factory firmware"
    exit 1
}

log_header()
{
    echo "============ cmd = $CMD ============"  >> /var/log/fabui/avrdude.log
    pwd
    echo "$@" >> /var/log/fabui/avrdude.log
}

log_footer()
{
    echo "============ result = $RETR ============"  >> /var/log/fabui/avrdude.log
}

dump_eeprom()
{
	HEXDUMPFILE=$1
	if [ -f "$HEXDUMPFILE" ]; then
		rm ${HEXDUMPFILE}
	fi
	echo "============ DUMP EEPROM ============"  >> /var/log/fabui/avrdude.log
	echo "${AVRDUDE} ${AVRDUDE_ARGS} -F -U eeprom:r:${HEXDUMPFILE}:i" >> /var/log/fabui/avrdude.log
	${AVRDUDE} ${AVRDUDE_ARGS} -F -U eeprom:r:${HEXDUMPFILE}:i >> /var/log/fabui/avrdude.log 2>&1
}

write_eeprom()
{
	HEXDUMPFILE=$1
	if [ -f "$HEXDUMPFILE" ]; then
		echo "============ WRITE EEPROM ============"  >> /var/log/fabui/avrdude.log
		echo "${AVRDUDE} ${AVRDUDE_ARGS} -F -U eeprom:w:${HEXDUMPFILE}:i" >> /var/log/fabui/avrdude.log
		${AVRDUDE} ${AVRDUDE_ARGS} -F -U eeprom:w:${HEXDUMPFILE}:i >> /var/log/fabui/avrdude.log 2>&1
		rm ${HEXDUMPFILE}
	fi
	
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
        
        if [ x"$EXT" == x"zip" ]; then
            TMPDIR=$(mktemp -d)
            echo "temp-dir: $TMPDIR"
            unzip -o ${HEXFILE} -d $TMPDIR
            HEXFILE=$(find ${TMPDIR} -name "*.hex")
            echo "hex-file: $HEXFILE"
        fi
        
		
		dump_eeprom "/tmp/fabui/dumped_eeprom.hex"
        log_header "${AVRDUDE} ${AVRDUDE_ARGS} -U flash:w:${HEXFILE}:i"
        ${AVRDUDE} ${AVRDUDE_ARGS} -U flash:w:${HEXFILE}:i >> /var/log/fabui/avrdude.log 2>&1
        RETR=$?
		write_eeprom "/tmp/fabui/dumped_eeprom.hex"
        log_footer ${RETR}
        
        if [ x"$TMPDIR" != x"" ]; then
            rm -rf $TMPDIR
        fi
        
        exit $RETR
        ;;
    factory)
        if [ -e "/mnt/live/mnt/boot/firmware.zip" ]; then
			sh $0 update /mnt/live/mnt/boot/firmware.zip
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
            unzip -o firmware.zip
            HEXFILE=$(find -name "*.hex")
            echo $HEXFILE
            dump_eeprom "/tmp/fabui/dumped_eeprom.hex"
            log_header "${AVRDUDE} ${AVRDUDE_ARGS} -U flash:w:${HEXFILE}:i"
            ${AVRDUDE} ${AVRDUDE_ARGS} -U flash:w:${HEXFILE}:i >> /var/log/fabui/avrdude.log 2>&1
            RETR=$?
			write_eeprom "/tmp/fabui/dumped_eeprom.hex"
            log_footer ${RETR}
        fi
        
        cd /tmp
        rm -rf $TMP_DIR
        
        exit $RETR
        ;;
	dump-eeprom)
		echo "dump eeprom"
		dump_eeprom "/tmp/fabui/dumped_eeprom.hex"
		RETR=$?
		exit $RETR
		;;
	write-eeprom)
		echo "write eeprom"
		write_eeprom "/tmp/fabui/dumped_eeprom.hex"
		RETR=$?
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

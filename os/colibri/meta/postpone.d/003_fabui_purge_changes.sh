#!/bin/bash

source /mnt/live/lib/.config
source /etc/default/fabui

########################################################################

BASE_DIR="/mnt/live${CHANGES}"

rm -f "${BASE_DIR}/${LIB_PATH}/config.ini"
rm -f "${BASE_DIR}/${LIB_PATH}/lang.ini"
rm -rf "${BASE_DIR}/${LIB_PATH}/cameras"


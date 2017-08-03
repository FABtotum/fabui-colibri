#!/bin/bash

# Find the real path of this script
BASE_DIR="$(dirname $(readlink -f "$0"))"
# Find out the filename of this script
EXEC_NAME=$(basename $0)
# Base on the script filename, select the subfolder with same name + ".d"
SUB_DIR="${EXEC_NAME%.*}.d"

SCRIPT_PATH="${BASE_DIR}/${SUB_DIR}"

# Look for *.sh scripts in SUB_DIR and don't go into sub-folders
for script in $(find ${SCRIPT_PATH} -maxdepth 1 -mindepth 1 -name "*.sh" | sort); do
	sh $script
	echo "$script, RET=$?" >> /tmp/${EXEC_NAME}.log
done

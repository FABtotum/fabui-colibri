#!/bin/bash

if [[ $# -lt 3 ]]; then
	echo "$0 <config_template> <config_filename> KEY=VALUE..."
	echo "Number of arguments provided: $#"
	exit 1
fi

# Get config.in filename
CONFIG_IN=${1}
shift

# Get config filename
CONFIG_OUT=${1}
shift

cp ${CONFIG_IN} ${CONFIG_OUT}

while [ "$#" != "0" ]; do
	
	if [[ ${1} = *"="* ]]; then 	
		KEY=${1%%=*}
		VALUE=${1#*=}
		
#		echo "@${KEY}@ - ${VALUE}"
		
		sed -i ${CONFIG_OUT} -e "s#@${KEY}@#${VALUE}#g"
	fi
	
	shift
done

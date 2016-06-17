#!/bin/bash 
readonly CONFIG_INI=/var/lib/fabui/config.ini
###################################
## READ CONFIG INI VALUE
###################################
getConfigValue(){
	search=$1
	command="awk -F \"=\" '/$search/ {print \$2}' $CONFIG_INI"
	eval $command
}

#result=$( getConfigValue default_settings )
#echo $result
#!/bin/bash
#################################
## RESET CONTROLLER BOARD      ##
## then init hardware settings ##
#################################

## include utilities
CURRENT_DIR="$(dirname "$0")"
source "$CURRENT_DIR/utilities.sh" 
#read ini configuration
lock_file=$(getConfigValue lock)
python_folder=$(getConfigValue python_scripts)
touch $lock_file
#echo "sudo python $python_folder/forceReset.py"
eval "sudo python $python_folder/forceReset.py"
sudo php /usr/share/fabui/index.php Control hardwareBootstrap
sleep 2
echo "reset done"

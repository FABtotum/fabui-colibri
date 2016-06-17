#!/bin/bash
## include utilities
CURRENT_DIR="$(dirname "$0")"
source "$CURRENT_DIR/utilities.sh" 
#read ini configuration
lock_file=$(getConfigValue lock)
python_folder=$(getConfigValue python_scripts)
touch $lock_file
eval "python $python_folder/forceReset.py"
sleep 3
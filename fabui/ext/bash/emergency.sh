#################################
## STOP ALL SCRIPTS 
#################################
#!/bin/bash
## include utilities
CURRENT_DIR="$(dirname "$0")"
source "$CURRENT_DIR/utilities.sh"
lock_file=$(getConfigValue lock)
python_folder=$(getConfigValue python_scripts)
touch $lock_file
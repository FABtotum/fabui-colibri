#!/bin/bash
############################################################
# Add "init_gcode" key to json files
# "init_gcode" init with "custom_gcode" value
# "init_gcode" will be handled only by fabtotum
# "custom_gcode" is for users modifications
# Add laser head pro json profile
# 
############################################################

DYNAMIC_UPGRADE_FILE="/var/lib/fabui/upgrade.d/004_heads_feeders_json_upgrade.sh"

echo "Heads & feeders json upgrade #004 preparation"

mkdir -p $(dirname $DYNAMIC_UPGRADE_FILE)

cat <<EOF > ${DYNAMIC_UPGRADE_FILE}
#!/bin/bash
# Automatically generated upgrade file, do not edit

echo "Adding 'init_gcode' key to heads & feeders json files"
python /var/lib/colibri/bundle/fabui/postpone.d/004_heads_feeders_json_upgrade.py

EOF

chmod +x ${DYNAMIC_UPGRADE_FILE}


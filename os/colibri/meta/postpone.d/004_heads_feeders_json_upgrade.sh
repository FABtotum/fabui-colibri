#!/bin/bash
############################################################
# Add "init_gcode" key to json files
# "init_gcode" init with "custom_gcode" value
# "init_gcode" will be handled only by fabtotum
# "custom_gcode" is for users modifications
# updated printing head pro "init_gcode" for upcoming silent version
# add prism module json profile
# 
############################################################

DYNAMIC_UPGRADE_FILE="/var/lib/fabui/upgrade.d/004_heads_feeders_json_upgrade.sh"
PRISM_MODULE_FILE="/usr/share/fabui/heads/prism_module.json"
USERDATA_HEADS_FOLDER="/mnt/userdata/heads/"

echo "Adding prism module json profile preparation"
echo "Heads & feeders json upgrade #004 preparation"

mkdir -p $(dirname $DYNAMIC_UPGRADE_FILE)

cat <<EOF > ${DYNAMIC_UPGRADE_FILE}
#!/bin/bash
# Automatically generated upgrade file, do not edit

echo "Adding prism module json profile"
if [[ ! -f "$USERDATA_HEADS_FOLDER/prism_module.json" ]] ; then
	cp -R $PRISM_MODULE_FILE $USERDATA_HEADS_FOLDER
fi
echo "Adding 'init_gcode' key to heads & feeders json files"
python /var/lib/colibri/bundle/fabui/postpone.d/004_heads_feeders_json_upgrade.py

EOF

chmod +x ${DYNAMIC_UPGRADE_FILE}


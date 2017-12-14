#!/bin/bash
############################################################
# Add "order" key to json files
# Add laser head pro json profile
# 
############################################################

DYNAMIC_UPGRADE_FILE="/var/lib/fabui/upgrade.d/002_heads_json.sh"
LASER_PRO_HEAD_FILE="/usr/share/fabui/heads/laser_head_pro.json"
USERDATA_HEADS_FOLDER="/mnt/userdata/heads/"

echo "Head json upgrade #002 preparation"
mkdir -p $(dirname $DYNAMIC_UPGRADE_FILE)

cat <<EOF > ${DYNAMIC_UPGRADE_FILE}
#!/bin/bash
# Automatically generated upgrade file, do not edit

echo "Adding laser pro head json profile"
if [[ ! -f "$USERDATA_HEADS_FOLDER/laser_head_pro.json" ]] ; then
	cp -R $LASER_PRO_HEAD_FILE $USERDATA_HEADS_FOLDER
fi
echo "Upgrading head json files"
python /var/lib/colibri/bundle/fabui/postpone.d/002_heads_json_upgrade.py

EOF

chmod +x ${DYNAMIC_UPGRADE_FILE}


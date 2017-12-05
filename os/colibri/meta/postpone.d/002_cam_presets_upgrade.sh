#!/bin/bash
############################################################
# Rename print_lite_head to print_head and update attributes
# Rename milling_head_v2 to milling_head and update attributes
# Fix setting.json to use new filenames
############################################################

DYNAMIC_UPGRADE_FILE="/var/lib/fabui/upgrade.d/002_cam_presets.sh"

echo "CAM presets json upgrade #001 preparation"
mkdir -p $(dirname $DYNAMIC_UPGRADE_FILE)

cat <<EOF > ${DYNAMIC_UPGRADE_FILE}
#!/bin/bash
# Automatically generated upgrade file, do not edit
echo "Upgrading CAM preset json files"
python /var/lib/colibri/bundle/fabui/postpone.d/002_cam_presets_laser_pro.py
EOF

chmod +x ${DYNAMIC_UPGRADE_FILE}
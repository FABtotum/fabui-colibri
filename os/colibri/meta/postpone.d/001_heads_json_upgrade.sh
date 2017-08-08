#!/bin/bash
############################################################
# Rename print_lite_head to print_head and update attributes
# Rename milling_head_v2 to milling_head and update attributes
# Fix setting.json to use new filenames
############################################################

DYNAMIC_UPGRADE_FILE="/var/lib/fabui/upgrade.d/001_heads_json.sh"

echo "Head json upgrade #001 preparation"
mkdir -p $(dirname $DYNAMIC_UPGRADE_FILE)

cat <<EOF > ${DYNAMIC_UPGRADE_FILE}
#!/bin/bash
# Automatically generated upgrade file, do not edit

if [ -f "/var/lib/fabui/heads/milling_head_v2.json" ] || [ -f "/var/lib/fabui/heads/printing_head_lite.json" ]; then
	echo "Upgrading head json files"
	python /var/lib/colibri/bundle/fabui/postpone.d/001_heads_json_upgrade.py
fi
EOF

chmod +x ${DYNAMIC_UPGRADE_FILE}


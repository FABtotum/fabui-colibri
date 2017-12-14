#!/bin/bash
############################################################
# Add "order" key to json files
# 
# 
############################################################

DYNAMIC_UPGRADE_FILE="/var/lib/fabui/upgrade.d/002_heads_json.sh"

echo "Head json upgrade #002 preparation"
mkdir -p $(dirname $DYNAMIC_UPGRADE_FILE)

cat <<EOF > ${DYNAMIC_UPGRADE_FILE}
#!/bin/bash
# Automatically generated upgrade file, do not edit

echo "Upgrading head json files"
python /var/lib/colibri/bundle/fabui/postpone.d/002_heads_json_upgrade.py

EOF

chmod +x ${DYNAMIC_UPGRADE_FILE}


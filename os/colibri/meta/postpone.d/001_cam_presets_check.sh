#!/bin/bash
############################################################
# if /mnt/userdata/cam doesn't exist create it 
# cam toolbox introduced in fabui colibri 1.1.0
############################################################

CAM_001_CHECK_FILE="/var/lib/fabui/upgrade.d/001_cam.sh"

echo "CAM toolbox presets check #001 preparation"

mkdir -p $(dirname $CAM_001_CHECK_FILE)

cat <<EOF > ${CAM_001_CHECK_FILE}
#!/bin/bash
# Automatically generated upgrade file, do not edit

CAM_USERDATA_FOLDER="/mnt/userdata/cam"
CAM_FABUI_FOLDER="/usr/share/fabui/cam"

if [[ ! -e $CAM_USERDATA_FOLDER ]] ; then
	mkdir -p $CAM_USERDATA_FOLDER
	
	if [[ -e  $CAM_FABUI_FOLDER ]] ; then
		cp -R $CAM_FABUI_FOLDER/* $CAM_USERDATA_FOLDER
		chown -R 33:33 /mnt/userdata/*
		sudo -uwww-data ln -s /mnt/userdata/cam /var/lib/fabui
	fi
fi
EOF

chmod +x ${CAM_001_CHECK_FILE}
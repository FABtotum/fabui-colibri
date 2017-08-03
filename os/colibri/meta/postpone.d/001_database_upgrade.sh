#!/bin/bash
############################################################
# add "deleted" column for sys_files and sys_objects tables
############################################################

DATABASE_001_UPGRADE_FILE="/var/lib/fabui/upgrade.d/001_database.sh"

echo "Database upgrade #001 preparation"
mkdir -p $(dirname $DATABASE_001_UPGRADE_FILE)

cat <<EOF > ${DATABASE_001_UPGRADE_FILE}
# Automatically generated upgrade file, do not edit \n
echo "Database upgrade #001"
echo 'alter table sys_files ADD COLUMN deleted INTEGER NOT NULL DEFAULT 0;' | sqlite3 /mnt/userdata/settings/fabtotum.db
echo 'alter table sys_objects ADD COLUMN deleted INTEGER NOT NULL DEFAULT 0;' | sqlite3 /mnt/userdata/settings/fabtotum.db
EOF

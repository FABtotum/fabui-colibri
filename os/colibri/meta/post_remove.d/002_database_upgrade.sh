#!/bin/bash
############################################################
# add "deleted" column for sys_files and sys_objects tables
############################################################
echo "Database upgrade #002"
echo 'alter table sys_files ADD COLUMN deleted INTEGER NOT NULL DEFAULT 0;' | sqlite3 /mnt/userdata/settings/fabtotum.db
echo 'alter table sys_objects ADD COLUMN deleted INTEGER NOT NULL DEFAULT 0;' | sqlite3 /mnt/userdata/settings/fabtotum.db

FAB-UI
======

New development branches of the FAB-UI intended for the new FABtotum 
Colibri os flavor.

FABtotum/Colibri
----------------
System features and development status:

### What changes
- Base system switch from Raspbian to homebrew
  [Colibri](https://github.com/Colibri-Embedded)
- Http server Apache 2 replaced by Lighttpd
- Application database MySQL replaced by SQLite 3
- Codegniter framework updated to last version

### Improvements
- Easy installation / reinstallation: just copy the installation files
  on a FAT32 formatted SD card
- Fast boot (<20 secs) and shutdown (<10 secs)
- Resilient to power failures / hard power-offs
- Reengineered code


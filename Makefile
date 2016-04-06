# Base name of distribution and release files
NAME	=	fabui

# Version is read from first paragraph of REAMDE file
VERSION		?=	$(shell grep '^FABUI [0-9]\+\.[0-9]\+' README.md README.md | head -n1 | cut -d' ' -f2)

# Priority for colibri bundle
PRIORITY	?= 090

# OS flavour identifier
OS_FLAVOUR	?= colibri

# FAB-UI system paths
#LIB_PATH		?= /var/lib/$(NAME)/
WWW_PATH		?= /var/www/
MOUNT_BASE_PATH	?= /mnt
FABUI_PATH		?= $(WWW_PATH)fabui/
PYTHON_PATH		?= $(FABUI_PATH)python/
SCRIPT_PATH		?= $(FABUI_PATH)script/
TASKS_PATH		?= $(WWW_PATH)tasks/
RECOVERY_PATH	?= $(WWW_PATH)recovery/
TEMP_PATH		?= $(WWW_PATH)temp/
UPLOAD_PATH		?= $(WWW_PATH)upload/
LIB_PATH		?= $(WWW_PATH)lib/
BIGTEMP_PATH	?= $(MOUNT_BASE_PATH)/bigtmp/
USERDATA_PATH	?= $(MOUNT_BASE_PATH)/userdata/
DB_PATH			?= $(LIB_PATH)/

############################ Files #####################################
# <files>/* is to avoid making <files>/<files> path
PYTHON_FILES	= 	fabui/python/*
# <files>/* is to avoid making <files>/<files> path
SCRIPT_FILES	=	fabui/script/*

WWW_FILES		= 	index.php \
					install.php \
					assets \
					upload \
					lib \
					cron \
					LICENSE \
					README.md
					
FABUI_FILES		=	fabui/index.php \
					fabui/application \
					fabui/config \
					fabui/system

# <files>/* is to avoid making <files>/<files> path
RECOVERY_FILES	=	recovery/*

# Databases (SQLite)
DB				= 	sqlite3
DB_FILES		= 	fabtotum.db

CONFIG_FILES	=	lib/config.php

# Build/Install paths
DESTDIR 		?= .
TEMP_DIR 		= ./temp
BDATA_DIR 		= $(TEMP_DIR)/bdata
BDATA_STAMP		= $(TEMP_DIR)/.bdata_stamp
FABUI_BUNDLE	= $(DESTDIR)/$(PRIORITY)-$(NAME)-$(VERSION)-bundle-v$(shell date +%Y%m%d).cb

OS_FILES_DIR	= ./os

# This is not a mistake. OS_STAMP is general dependency used bundle rule
OS_STAMP		= $(TEMP_DIR)/.os_$(OS_FLAVOUR)_stamp
# OS_COLIBRI_STAMP is specific stamp used in case OS_FLAVOUR is colibri
OS_COLIBRI_STAMP= $(TEMP_DIR)/.os_colibri_stamp

OS_COMMON_STAMP	= $(TEMP_DIR)/.os_common_stamp

# Tools
INSTALL			?= install
FAKEROOT 		?= fakeroot
FAKEROOT_ENV 	= $(FAKEROOT) -s $(TEMP_DIR)/.fakeroot_env -i $(TEMP_DIR)/.fakeroot_env -- 
MKSQUASHFS		?= mksquashfs
BUNDLE_COMP		?= lzo
########################### Makefile rules #############################

all:

clean:
	rm -rf $(TEMP_DIR)
	rm -rf $(CONFIG_FILES)
	rm -rf $(DB_FILES)
	
distclean: clean
	rm -rf *.cb
	rm -rf *.cb.md5sum
	

check-tools:
	@echo "Looking for fakeroot"
	@which fakeroot &> /dev/null
	@echo "OK"
	@echo "Looking for mksquashfs"
	@which mksquashfs  &> /dev/null
	@echo "OK"

bundle: $(FABUI_BUNDLE)

lib/config.php: config.php.in
	./generate_config.sh $^ $@ \
		WWW_PATH=$(WWW_PATH) \
		FABUI_PATH=$(FABUI_PATH) \
		PYTHON_PATH=$(PYTHON_PATH) \
		SCRIPT_PATH=$(SCRIPT_PATH) \
		TASKS_PATH=$(TASKS_PATH) \
		RECOVERY_PATH=$(RECOVERY_PATH) \
		TEMP_PATH=$(TEMP_PATH) \
		UPLOAD_PATH=$(UPLOAD_PATH) \
		LIB_PATH=$(LIB_PATH) \
		BIGTEMP_PATH=$(BIGTEMP_PATH) \
		USERDATA_PATH=$(USERDATA_PATH)

$(TEMP_DIR):
	mkdir -p $@
	
$(BDATA_DIR):
	mkdir -p $@

$(BDATA_STAMP): $(TEMP_DIR) $(BDATA_DIR) $(CONFIG_FILES) $(DB_FILES)
# 	Copy python files
	$(FAKEROOT_ENV) mkdir -p $(BDATA_DIR)$(PYTHON_PATH)
	$(FAKEROOT_ENV) cp -a $(PYTHON_FILES) 	$(BDATA_DIR)$(PYTHON_PATH)
# 	Copy script files
	$(FAKEROOT_ENV) mkdir -p $(BDATA_DIR)$(SCRIPT_PATH)
	$(FAKEROOT_ENV) cp -a $(SCRIPT_FILES) 	$(BDATA_DIR)$(SCRIPT_PATH)
# 	Copy www files
	$(FAKEROOT_ENV) mkdir -p $(BDATA_DIR)$(WWW_PATH)
	$(FAKEROOT_ENV) cp -a $(WWW_FILES) 		$(BDATA_DIR)$(WWW_PATH)
# 	Copy fabui files
	$(FAKEROOT_ENV) mkdir -p $(BDATA_DIR)$(FABUI_PATH)
	$(FAKEROOT_ENV) cp -a $(FABUI_FILES) 		$(BDATA_DIR)$(FABUI_PATH)
# 	Copy recovery files
	$(FAKEROOT_ENV) mkdir -p $(BDATA_DIR)$(RECOVERY_PATH)
	$(FAKEROOT_ENV) cp -a $(RECOVERY_FILES) 	$(BDATA_DIR)$(RECOVERY_PATH)
#	Create runtime data directory
	$(FAKEROOT_ENV) $(INSTALL) -d -o 33 -g 33 -m 0755 $(BDATA_DIR)$(LIB_PATH)
#	Create log directory
	$(FAKEROOT_ENV) $(INSTALL) -d -o 33 -g 33 -m 0755 $(BDATA_DIR)/var/log/fabui
#	Install DB
	$(FAKEROOT_ENV) cp -a $(DB_FILES) $(BDATA_DIR)$(DB_PATH)
#	The autoinstall flag file is created at compile time
	$(FAKEROOT_ENV) touch $(BDATA_DIR)/$(WWW_PATH)/AUTOINSTALL
#	Public runtime directories
	$(FAKEROOT_ENV) $(INSTALL) -d -g 33 -m 0775 $(BDATA_DIR)/$(TEMP_PATH)
	$(FAKEROOT_ENV) $(INSTALL) -d -g 33 -m 0775 $(BDATA_DIR)/$(TASKS_PATH)
# 	Fix permissions
	$(FAKEROOT_ENV) chown -R 33:33 $(BDATA_DIR)$(WWW_PATH)
# 	Create a stamp file
	touch $@

$(OS_COLIBRI_STAMP):
	$(FAKEROOT_ENV) mkdir -p $(BDATA_DIR)/etc/init.d
		
	$(FAKEROOT_ENV) $(INSTALL) -D -m 0775 $(OS_FILES_DIR)/colibri/fabtotum.init \
		$(BDATA_DIR)/etc/init.d/fabtotum
	$(FAKEROOT_ENV) $(INSTALL) -D -m 0644 $(OS_FILES_DIR)/colibri/fabtotum.default \
		$(BDATA_DIR)/etc/default/fabtotum
		
	$(FAKEROOT_ENV) $(INSTALL) -D -m 0775 $(OS_FILES_DIR)/colibri/fabui.init \
		$(BDATA_DIR)/etc/init.d/fabui
#	$(FAKEROOT_ENV) $(INSTALL) -D -m 0644 $(OS_FILES_DIR)/colibri/fabui.default \
#		$(BDATA_DIR)/etc/default/fabui
	$(FAKEROOT_ENV) $(INSTALL) -D -m 0775 $(OS_FILES_DIR)/colibri/fabui.first \
		$(BDATA_DIR)/etc/firstboot.d/fabui		
	
	$(FAKEROOT_ENV) mkdir -p $(BDATA_DIR)/etc/rc.d/rc.firstboot.d
	$(FAKEROOT_ENV) ln -fs ../../firstboot.d/fabui \
		$(BDATA_DIR)/etc/rc.d/rc.firstboot.d/S10fabui
	
	$(FAKEROOT_ENV) mkdir -p $(BDATA_DIR)/etc/rc.d/rc.startup.d	
	$(FAKEROOT_ENV) ln -fs ../../init.d/fabtotum \
		$(BDATA_DIR)/etc/rc.d/rc.startup.d/S30fabtotum
	$(FAKEROOT_ENV) ln -fs ../../init.d/fabui \
		$(BDATA_DIR)/etc/rc.d/rc.startup.d/S40fabui
	
	$(FAKEROOT_ENV) mkdir -p $(BDATA_DIR)/etc/rc.d/rc.shutdown.d
	$(FAKEROOT_ENV) ln -fs ../../init.d/fabui \
		$(BDATA_DIR)/etc/rc.d/rc.shutdown.d/S20fabui
	$(FAKEROOT_ENV) ln -fs ../../init.d/fabtotum \
		$(BDATA_DIR)/etc/rc.d/rc.shutdown.d/S98fabtotum
# 	Create a stamp file
	touch $@
	
$(OS_COMMON_STAMP):
#	Sudoers fabui rule
	$(FAKEROOT_ENV) install -d -m 0750 $(BDATA_DIR)/etc/sudoers.d
	$(FAKEROOT_ENV) install -D -m 0440 $(OS_FILES_DIR)/common/fabui.sudoers $(BDATA_DIR)/etc/sudoers.d/fabui
	$(FAKEROOT_ENV) chmod 0440 $(BDATA_DIR)/etc/sudoers.d/fabui
#	Lighttpd fabui config
	$(FAKEROOT_ENV) $(INSTALL) -D -m 0644 $(OS_FILES_DIR)/common/fabui.lighttpd \
		$(BDATA_DIR)/etc/lighttpd/conf-available/99-fabui.conf
# 	Create a stamp file
	touch $@
	
%.db: recovery/install/sql/%.$(DB)
	$(DB) $@ < $< 
		
$(FABUI_BUNDLE): $(BDATA_STAMP) $(OS_COMMON_STAMP) $(OS_STAMP)
	$(FAKEROOT_ENV) $(MKSQUASHFS) $(BDATA_DIR) $@ -noappend -comp $(BUNDLE_COMP) -b 512K -no-xattrs
	md5sum $@ > $@.md5sum

	
#~ # Application files
#~ legacy_HTDOCS_FILES  := assets fabui .htaccess index.php install.php lib LICENSE README.md recovery
#~ HTDOCS_FILES := assets fabui index.php install.php lib LICENSE README.md recovery

#~ all: dist dist-legacy

#~ #
#~ # make dist-legacy
#~ #
#~ # Make a versioned distribution archive for the legacy system.
#~ #
#~ legacy_NAME := $(NAME)
#~ dist-legacy: DESTDIR ?= ./dist
#~ dist-legacy: temp/$(NAME).zip
#~ 	mkdir -p $(DESTDIR)/update/FAB-UI/download/$(VERSION)
#~ 	mv temp/$(NAME).zip $(DESTDIR)/update/FAB-UI/download/$(VERSION)/
#~ 	echo $(VERSION) > $(DESTDIR)/update/FAB-UI/version.txt
#~ #	TODO: extract changelog from README
#~ #	TODO: compute and write md5 checksum into MD5
#~ 	touch $(DESTDIR)

#~ %.zip:
#~ 	zip -r9 $@ $(legacy_HTDOCS_FILES) -x Makefile

#~ #
#~ # make dist-colibri
#~ #
#~ # Make a versioned bundle for colibri system.
#~ #
#~ RELEASE=$(PRIORITY)-$(NAME)-$(VERSION)-v$(shell date +%Y%m%d)
#~ dist: DESTDIR ?= ./dist
#~ dist: temp/$(RELEASE).cb
#~ 	mkdir -p $(DESTDIR)/bundles
#~ 	mv temp/$(RELEASE).cb $(DESTDIR)/bundles/
#~ 	touch $(DESTDIR)

#~ %.cb: clean $(DB_FILES)
#~ #	TODO: maybe separate 'installation' step from 'squashing' step
#~ #	Copy public htdocs files
#~ 	mkdir -p temp/bdata$(HTDOCSDIR)
#~ 	cp -a $(HTDOCS_FILES) $(DB_FILES) temp/bdata$(HTDOCSDIR)/
#~ #	Create runtime data directory
#~ #	mkdir -p temp/bdata$(LOCALSTATEDIR)/lib/fabui
#~ #	The autoinstall flag file is created now
#~ 	touch temp/bdata$(HTDOCSDIR)/AUTOINSTALL
#~ #	We still need a temp directory for fab_ui_security
#~ 	mkdir temp/bdata$(HTDOCSDIR)/temp
#~ #	Relocate system configuration files into their final place
#~ 	mkdir -p temp/bdata$(SYSCONFDIR)
#~ 	for file in $(SYSCONF_FILES); do mv temp/bdata/var/www/recovery/install/system/etc/$$file temp/bdata$(SYSCONFDIR)/; done
#~ #	Fix some ownership
#~ 	chown -R root:$(htdocs_GID) temp/bdata$(HTDOCSDIR)
#~ 	chmod -R ug+rwX temp/bdata$(HTDOCSDIR)
#~ 	chown -R --from=$(maintainer_UID)  root:root temp/bdata$(SYSCONFDIR)/*
#~ #	Squash the file system thus created
#~ 	mksquashfs temp/bdata $@ -noappend -comp xz -b 512K -no-xattrs

#~ %.db: recovery/install/sql/%.$(DB)
#~ 	$(DB) $@ < $< 

#~ clean:
#~ #	Remove any runtime or installation files from temp directory
#~ 	rm -rf temp/*

#~ distclean: clean
#~ #	Remove distribution files
#~ 	rm -rf dist

#~ maintainer-clean:
#~ 	chown -R --from=:$(maintainer_GID) :www-data $(colibri_HTDOCS_FILES)

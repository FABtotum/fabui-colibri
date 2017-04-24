# Base name of distribution and release files
NAME	=	fabui

# Version is read from first paragraph of REAMDE file
VERSION		?=	$(shell grep '^FABUI [0-9]\+\.[0-9]\+' README.md README.md | head -n1 | cut -d' ' -f2)

# Priority for colibri bundle
PRIORITY	?= 090

# FABUI license
LICENSE		?= GPLv2

# OS flavour identifier
OS_FLAVOUR	?= colibri

# FAB-UI system paths
LIB_PATH		 ?= /var/lib/$(NAME)/
SHARED_PATH		 ?= /usr/share/$(NAME)/
COLIBRI_LIB_PATH ?= /var/lib/colibri/
METADATA_PATH	 ?= $(COLIBRI_LIB_PATH)bundle/$(NAME)
WWW_PATH		 ?= /var/www/
MOUNT_BASE_PATH	 ?= /mnt/
FABUI_PATH		 ?= $(SHARED_PATH)
RECOVERY_PATH	 ?= $(WWW_PATH)recovery/
UPLOAD_PATH_LNS  ?= $(WWW_PATH)uploads
UPLOAD_PATH		 ?= $(UPLOAD_PATH_LNS)/
FABUI_TEMP_PATH	 ?= $(WWW_PATH)temp/
PYTHON_PATH		 ?= $(FABUI_PATH)ext/py/
BASH_PATH		 ?= $(FABUI_PATH)ext/bash/
TEMP_PATH	 	 ?= /tmp/
RUN_PATH		 ?= /run/$(NAME)/
BIGTEMP_PATH	 ?= $(MOUNT_BASE_PATH)bigtemp/
USERDATA_PATH	 ?= $(MOUNT_BASE_PATH)userdata/
DB_PATH			 ?= $(LIB_PATH)/
USB_MEDIA_PATH	 ?= /run/media/
LOCALE_PATH		 ?= $(FABUI_PATH)locale/

# FAB-UI parameters
SERIAL_PORT 	?= /dev/ttyAMA0
SERIAL_BAUD		?= 250000
# OS paths
PHP_CONFIG_FILE_SCANDIR ?= /etc/php/conf.d/
CRON_FOLDER ?= /var/spool/cron/crontabs/

########################## Input Files #################################
# File paths of local files taht will be installed to the configured
# paths according to their type


# <files>/* is to avoid making <files>/<files> path
#PYTHON_FILES	= 	fabui/ext/py*
# <files>/* is to avoid making <files>/<files> path
#SCRIPT_FILES	=	fabui/ext/bash/*

# Files that will end up in WWW_PATH
WWW_FILES		= 	index.php \
					LICENSE \
					README.md
					
# Files that will end up in FABUI_PATH
FABUI_FILES		=	fabui/recovery \
					fabui/ext \
					fabui/index.php \
					fabui/application \
					fabui/system \
					fabui/assets \
					fabui/utilities \
					fabui/locale \
					fabui/heads \
					fabui/feeders

# <files>/* is to avoid making <files>/<files> path
RECOVERY_FILES	=	recovery/*

# Databases (SQLite)
DB				= 	sqlite3
DB_FILES		= 	fabtotum.db

CONFIG_FILES	=	config.ini serial.ini lang.ini

# Files that will end up in SHARED_PATH
STATIC_FILES	=	

# Files that will end up in LIB_PATH
DYNAMIC_FILES	=	$(CONFIG_FILES) \
					$(DB_FILES) \
					fabui/heads \
					fabui/feeders \
					fabui/plugins \
					fabui/cameras \
					fabui/settings

# List of files that should go through the generator script
GENERATED_FILES = $(CONFIG_FILES) \
				  os/colibri/fabui.default

########################################################################

# Build/Install paths
DESTDIR 		?= .
TEMP_DIR 		= ./temp
BDATA_DIR 		= $(TEMP_DIR)/bdata
BDATA_STAMP		= $(TEMP_DIR)/.bdata_stamp
#FABUI_BUNDLE	= $(DESTDIR)/$(PRIORITY)-$(NAME)-$(VERSION)-bundle-v$(shell date +%Y%m%d).cb
ifeq ($(VERSION),)
#~ VERSION = 0.9999
VERSION = 0.99.$(shell date +%Y%m%d.%H%M)
endif
FABUI_BUNDLE	= $(DESTDIR)/$(PRIORITY)-$(NAME)-v$(VERSION).cb

OS_FILES_DIR	= ./os

# This is not a mistake. OS_STAMP is general dependency used bundle rule
OS_STAMP		= $(TEMP_DIR)/.os_$(OS_FLAVOUR)_stamp
# OS_COLIBRI_STAMP is specific stamp used in case OS_FLAVOUR is colibri
OS_COLIBRI_STAMP= $(TEMP_DIR)/.os_colibri_stamp

OS_COMMON_STAMP	= $(TEMP_DIR)/.os_common_stamp

# User/group
WWW_DATA_NAME	= www-data
WWW_DATA_UID 	= 33
WWW_DATA_GID 	= 33
WWW_DATA_GROUPS	= wheel,dialout,tty,plugdev,video

# Tools
INSTALL			?= install
FAKEROOT 		?= fakeroot
FAKEROOT_ENV 	= $(FAKEROOT) -s $(TEMP_DIR)/.fakeroot_env -i $(TEMP_DIR)/.fakeroot_env -- 
MKSQUASHFS		?= mksquashfs
BUNDLE_COMP		?= lzo
########################### Makefile rules #############################

.PHONY: locale clean distclean bundle check-tools fabui/locale/*.pot

all: check-tools $(FABUI_BUNDLE)

clean:
	rm -rf $(TEMP_DIR)
	rm -rf $(CONFIG_FILES)
	rm -rf $(DB_FILES)
	
distclean: clean
	rm -rf *.cb
	rm -rf *.cb.md5sum
	rm -f $(GENERATED_FILES)

check-tools:
	which fakeroot
	@echo "Looking for fakeroot: FOUND"
	which mksquashfs
	@echo "Looking for mksquashfs: FOUND"
	which pojson
	@echo "Looking for pojson: FOUND"
	which sqlite3
	@echo "Looking for sqlite3: FOUND"
	
bundle: check-tools distclean $(FABUI_BUNDLE)

# Collects rules of all *.in files and uses the generator on them.
% : %.in
	./generate_config.sh $^ $@ \
		WWW_PATH=$(WWW_PATH) \
		FABUI_PATH=$(FABUI_PATH) \
		PYTHON_PATH=$(PYTHON_PATH) \
		BASH_PATH=$(BASH_PATH) \
		RECOVERY_PATH=$(RECOVERY_PATH) \
		TEMP_PATH=$(TEMP_PATH) \
		FABUI_TEMP_PATH=$(FABUI_TEMP_PATH) \
		UPLOAD_PATH=$(UPLOAD_PATH) \
		UPLOAD_PATH_LNS=$(UPLOAD_PATH_LNS) \
		LIB_PATH=$(LIB_PATH) \
		SHARED_PATH=$(SHARED_PATH) \
		BIGTEMP_PATH=$(BIGTEMP_PATH) \
		USERDATA_PATH=$(USERDATA_PATH) \
		USB_MEDIA_PATH=$(USB_MEDIA_PATH) \
		SERIAL_PORT=$(SERIAL_PORT) \
		SERIAL_BAUD=$(SERIAL_BAUD) \
		COLIBRI_LIB_PATH=$(COLIBRI_LIB_PATH) \
		LOCALE_PATH=$(LOCALE_PATH)

$(TEMP_DIR):
	mkdir -p $@
	
$(BDATA_DIR):
	mkdir -p $@

$(BDATA_STAMP): $(TEMP_DIR) $(BDATA_DIR) $(DB_FILES) $(GENERATED_FILES)
# 	Copy www files
	$(FAKEROOT_ENV) mkdir -p $(BDATA_DIR)$(WWW_PATH)
	$(FAKEROOT_ENV) cp -R $(WWW_FILES) 		$(BDATA_DIR)$(WWW_PATH)
# 	Copy fabui files
	$(FAKEROOT_ENV) mkdir -p $(BDATA_DIR)$(FABUI_PATH)
	$(FAKEROOT_ENV) cp -R $(FABUI_FILES) 		$(BDATA_DIR)$(FABUI_PATH)
# 	Copy recovery files
	$(FAKEROOT_ENV) mkdir -p $(BDATA_DIR)$(RECOVERY_PATH)
	$(FAKEROOT_ENV) cp -R $(RECOVERY_FILES) 	$(BDATA_DIR)$(RECOVERY_PATH)
#	Create runtime data directory
	$(FAKEROOT_ENV) $(INSTALL) -d -o $(WWW_DATA_UID) -g $(WWW_DATA_GID) -m 0755 $(BDATA_DIR)$(LIB_PATH)
#	Create log directory
	$(FAKEROOT_ENV) $(INSTALL) -d -o $(WWW_DATA_UID) -g $(WWW_DATA_GID) -m 0755 $(BDATA_DIR)/var/log/fabui
#	Install static files
ifneq ($(STATIC_FILES),)
	$(FAKEROOT_ENV)mkdir -p $(BDATA_DIR)$(SHARED_PATH)
	$(FAKEROOT_ENV) cp -a $(STATIC_FILES) $(BDATA_DIR)$(SHARED_PATH)
endif
#	Install dynamic files
ifneq ($(DYNAMIC_FILES),)
	$(FAKEROOT_ENV) cp -a $(DYNAMIC_FILES) $(BDATA_DIR)$(LIB_PATH)
endif
#	Create sym-links
	$(FAKEROOT_ENV) ln -s $(FABUI_PATH) 			$(BDATA_DIR)$(WWW_PATH)fabui
	$(FAKEROOT_ENV) ln -s $(FABUI_PATH)assets 		$(BDATA_DIR)$(WWW_PATH)assets
	$(FAKEROOT_ENV) ln -s $(FABUI_PATH)utilities	$(BDATA_DIR)$(WWW_PATH)utilities
	$(FAKEROOT_ENV) ln -s $(TEMP_PATH)fabui 		$(BDATA_DIR)$(WWW_PATH)temp
	$(FAKEROOT_ENV) ln -s $(BIGTEMP_PATH) 			$(BDATA_DIR)$(WWW_PATH)bigtemp
#	Python integration
	$(FAKEROOT_ENV) $(INSTALL) -d -m 0755 			$(BDATA_DIR)/usr/lib/python2.7/site-packages
	$(FAKEROOT_ENV) ln -s $(PYTHON_PATH)fabtotum 	$(BDATA_DIR)/usr/lib/python2.7/site-packages/fabtotum
#	The autoinstall flag file is created at compile time
	$(FAKEROOT_ENV) touch $(BDATA_DIR)/$(WWW_PATH)/AUTOINSTALL
#	The sample flag file is created at compile time
	$(FAKEROOT_ENV) touch $(BDATA_DIR)/$(WWW_PATH)/SAMPLES
#	Public runtime directories
#~ 	$(FAKEROOT_ENV) $(INSTALL) -d -g $(WWW_DATA_GID) -m 0775 $(BDATA_DIR)/$(TEMP_PATH)
#	$(FAKEROOT_ENV) $(INSTALL) -d -g $(WWW_DATA_GID) -m 0775 $(BDATA_DIR)$(LIB_PATH)/plugins
#   Remove unused files
	$(FAKEROOT_ENV) find $(BDATA_DIR) -name "*.po" -delete
	$(FAKEROOT_ENV) find $(BDATA_DIR) -name "*.pot" -delete
########################################################################
# 	Fix permissions
	$(FAKEROOT_ENV) chown -R $(WWW_DATA_UID):$(WWW_DATA_GID) $(BDATA_DIR)$(WWW_PATH)
	$(FAKEROOT_ENV) chown -R $(WWW_DATA_UID):$(WWW_DATA_GID) $(BDATA_DIR)$(LIB_PATH)
#~ 	$(FAKEROOT_ENV) chown -R 0:0 $(BDATA_DIR)$(FABUI_FILES)
########################################################################
#	Add metadata
	$(FAKEROOT_ENV) mkdir -p $(BDATA_DIR)$(METADATA_PATH)
#	metadata/info
	$(FAKEROOT_ENV) echo "name: $(NAME)" >> $(BDATA_DIR)$(METADATA_PATH)/info
	$(FAKEROOT_ENV) echo "version: $(VERSION)" >> $(BDATA_DIR)$(METADATA_PATH)/info
	$(FAKEROOT_ENV) echo "build-date: $(shell date +%Y-%m-%d)" >> $(BDATA_DIR)$(METADATA_PATH)/info
#	metadata/packages
	$(FAKEROOT_ENV) echo "$(NAME): $(VERSION)" >> $(BDATA_DIR)$(METADATA_PATH)/packages
#	metadata/licenses
	$(FAKEROOT_ENV) echo "$(NAME): $(LICENSE)" >> $(BDATA_DIR)$(METADATA_PATH)/licenses
#	license files
	$(FAKEROOT_ENV) mkdir -p $(BDATA_DIR)/usr/share/licenses/$(NAME)
	$(FAKEROOT_ENV) cp LICENSE $(BDATA_DIR)/usr/share/licenses/$(NAME)
#|username |uid |group |gid |password |home |shell |groups |comment
	$(FAKEROOT_ENV) echo "$(WWW_DATA_NAME) $(WWW_DATA_UID) $(WWW_DATA_NAME) $(WWW_DATA_GID) * /var/www /bin/sh $(WWW_DATA_GROUPS) Web Data" > $(BDATA_DIR)$(METADATA_PATH)/user_table
# 	Create a stamp file
	touch $@

$(OS_COLIBRI_STAMP):
	$(FAKEROOT_ENV) mkdir -p $(BDATA_DIR)/etc/init.d
		
	$(FAKEROOT_ENV) $(INSTALL) -D -m 0775 $(OS_FILES_DIR)/colibri/fabtotum.init \
		$(BDATA_DIR)/etc/init.d/fabtotum
	$(FAKEROOT_ENV) $(INSTALL) -D -m 0644 $(OS_FILES_DIR)/colibri/fabtotum.default \
		$(BDATA_DIR)/etc/default/fabtotum
	$(FAKEROOT_ENV) $(INSTALL) -D -m 0644 $(OS_FILES_DIR)/colibri/fabui.default \
		$(BDATA_DIR)/etc/default/fabui
		
	$(FAKEROOT_ENV) $(INSTALL) -D -m 0775 $(OS_FILES_DIR)/colibri/fabui.init \
		$(BDATA_DIR)/etc/init.d/fabui
	$(FAKEROOT_ENV) $(INSTALL) -D -m 0775 $(OS_FILES_DIR)/colibri/fabui-ws.init \
		$(BDATA_DIR)/etc/init.d/fabui-ws
	$(FAKEROOT_ENV) $(INSTALL) -D -m 0644 $(OS_FILES_DIR)/colibri/fabui.default \
		$(BDATA_DIR)/etc/default/fabui
	$(FAKEROOT_ENV) $(INSTALL) -D -m 0775 $(OS_FILES_DIR)/colibri/fabui.first \
		$(BDATA_DIR)/etc/firstboot.d/fabui	
		
	$(FAKEROOT_ENV) $(INSTALL) -D -m 0775 $(OS_FILES_DIR)/colibri/fixes.init \
		$(BDATA_DIR)/etc/init.d/fixes
		
	$(FAKEROOT_ENV) mkdir -p $(BDATA_DIR)/etc/network/if-up.d
	$(FAKEROOT_ENV) $(INSTALL) -D -m 0775 $(OS_FILES_DIR)/colibri/zz_default_gateway \
		$(BDATA_DIR)/etc/network/if-up.d/zz_default_gateway	
			
	$(FAKEROOT_ENV) mkdir -p $(BDATA_DIR)/etc/rc.d/rc.firstboot.d
	$(FAKEROOT_ENV) ln -fs ../../firstboot.d/fabui \
		$(BDATA_DIR)/etc/rc.d/rc.firstboot.d/S10fabui
	
	$(FAKEROOT_ENV) mkdir -p $(BDATA_DIR)/etc/rc.d/rc.startup.d	
	$(FAKEROOT_ENV) ln -fs ../../init.d/fabtotum \
		$(BDATA_DIR)/etc/rc.d/rc.startup.d/S30fabtotum
	$(FAKEROOT_ENV) ln -fs ../../init.d/fabui \
		$(BDATA_DIR)/etc/rc.d/rc.startup.d/S40fabui
	$(FAKEROOT_ENV) ln -fs ../../init.d/fabui-ws \
		$(BDATA_DIR)/etc/rc.d/rc.startup.d/S39fabui-ws
	
	$(FAKEROOT_ENV) mkdir -p $(BDATA_DIR)/etc/rc.d/rc.shutdown.d
	$(FAKEROOT_ENV) ln -fs ../../init.d/fabui \
		$(BDATA_DIR)/etc/rc.d/rc.shutdown.d/S20fabui
	$(FAKEROOT_ENV) ln -fs ../../init.d/fabui-ws \
		$(BDATA_DIR)/etc/rc.d/rc.shutdown.d/S21fabui-ws
	$(FAKEROOT_ENV) ln -fs ../../init.d/fabtotum \
		$(BDATA_DIR)/etc/rc.d/rc.shutdown.d/S65fabtotum
	$(FAKEROOT_ENV) ln -fs ../../init.d/fixes \
		$(BDATA_DIR)/etc/rc.d/rc.shutdown.d/S62fixes
# 	Create a stamp file
	touch $@
	
$(OS_COMMON_STAMP):
# 	Avahi service
	$(FAKEROOT_ENV) install -d -m 0775 $(BDATA_DIR)/etc/avahi/services
	$(FAKEROOT_ENV) install -D -m 0644 $(OS_FILES_DIR)/common/fabtotum.service $(BDATA_DIR)/etc/avahi/services/fabtotum.service
# 	Rpi3 Wifi Module loading
	$(FAKEROOT_ENV) install -d -m 0775 $(BDATA_DIR)/etc/modules-load.d
	$(FAKEROOT_ENV) install -D -m 0644 $(OS_FILES_DIR)/common/rpi3_wifi.conf $(BDATA_DIR)/etc/modules-load.d/rpi3_wifi.conf
#	Sudoers fabui rule
	$(FAKEROOT_ENV) install -d -m 0750 $(BDATA_DIR)/etc/sudoers.d
	$(FAKEROOT_ENV) install -D -m 0440 $(OS_FILES_DIR)/common/fabui.sudoers $(BDATA_DIR)/etc/sudoers.d/fabui
	$(FAKEROOT_ENV) chmod 0440 $(BDATA_DIR)/etc/sudoers.d/fabui
#	Lighttpd fabui config
	$(FAKEROOT_ENV) $(INSTALL) -D -m 0644 $(OS_FILES_DIR)/common/fabui.lighttpd \
		$(BDATA_DIR)/etc/lighttpd/conf-available/99-fabui.conf
# PHP settings file
	$(FAKEROOT_ENV) install -D $(OS_FILES_DIR)/common/$(NAME).php $(BDATA_DIR)$(PHP_CONFIG_FILE_SCANDIR)$(NAME).ini
# CRON file
	$(FAKEROOT_ENV) mkdir -p $(BDATA_DIR)$(CRON_FOLDER)
	$(FAKEROOT_ENV) install -D $(OS_FILES_DIR)/common/cron/root $(BDATA_DIR)$(CRON_FOLDER)
# 	Create a stamp file
	touch $@
	
%.db: fabui/recovery/sql/%.$(DB)
	$(DB) $@ < $< 
		
$(FABUI_BUNDLE): $(BDATA_STAMP) $(OS_COMMON_STAMP) $(OS_STAMP)
	$(FAKEROOT_ENV) $(MKSQUASHFS) $(BDATA_DIR) $@ -noappend -comp $(BUNDLE_COMP) -b 512K -no-xattrs
	md5sum $@ > $@.md5sum

include Makefile.locale

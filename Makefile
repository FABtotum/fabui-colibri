# Base name of distribution and release files
NAME=fabui

# Version is read from first paragraph of REAMDE file
VERSION=$(shell grep '^FABUI [0-9]\+\.[0-9]\+' README.md README.md | head -n1 | cut -d' ' -f2)

# Application files
legacy_HTDOCS_FILES  := assets fabui .htaccess index.php install.php lib LICENSE README.md recovery
colibri_HTDOCS_FILES := assets fabui index.php install.php lib LICENSE README.md recovery

# System files
colibri_SYSCONF_FILES := firstboot.d init.d lighttpd rc.d

# Priority for colibri bundle
colibri_PRIORITY := 090

# These should be `configure`able
SYSCONFDIR=/etc
HTCONFDIR=$(SYSCONFDIR)/lightppd
# This last one may also end up in some sort of `configure.php.in` file
HTDOCSDIR=/var/www

maintainer_UID := $(shell id -u $$SUDO_USER)
maintainer_GID := $(shell id -g $$SUDO_USER)

.PHONY: all dist-legacy dist-colibri clean distclean

all: dist-legacy dist-colibri

#
# make dist-legacy
#
# Make a versioned distribution archive for the legacy system.
#
legacy_NAME := $(NAME)
dist-legacy: DESTDIR ?= ./dist
dist-legacy: README.md temp/$(NAME).zip
	mkdir -p $(DESTDIR)/update/FAB-UI/download/$(version)
	mv temp/$(NAME).zip $(DESTDIR)/update/FAB-UI/download/$(version)/
	echo $(version) > $(DESTDIR)/update/FAB-UI/version.txt
#	TODO: extract changelog from README

%.zip:
	zip -r9 $@ $(legacy_HTDOCS_FILES) -x Makefile

publish-legacy: dist-legacy
#	TODO: compute and write md5 checksum into MD5
	scp -rC dist/update/FAB-UI/* root@update.fabtotum.com/FAB-UI/

#
# make dist-colibri
#
# Make a versioned bundle for colibri system.
#
colibri_NAME=$(colibri_PRIORITY)-$(NAME)-$(VERSION)-v$(shell date +%Y%m%d)
dist-colibri: DESTDIR ?= ./dist
dist-colibri: temp/$(colibri_NAME).cb
	mkdir -p $(DESTDIR)/bundles
	mv temp/$(colibri_NAME).cb $(DESTDIR)/bundles/

%.cb: README.md
#	Copy public htdocs files
	mkdir -p temp/bdata$(HTDOCSDIR)
	cp -a $(colibri_HTDOCS_FILES) temp/bdata$(HTDOCSDIR)/
#	Relocate system configuration files into their final place
	mkdir -p temp/bdata$(SYSCONFDIR)
	for file in $(colibri_SYSCONF_FILES); do mv temp/bdata/var/www/recovery/install/system/etc/$$file temp/bdata$(SYSCONFDIR)/$$file; done
#	Fix some ownership
	chown -R --from=$(maintainer_UID) root temp/bdata$(HTDOCSDIR)/*
	chown -R --from=$(maintainer_UID) root:root temp/bdata$(SYSCONFDIR)/*
#	Squash the file system thus created
	mksquashfs temp/bdata $@ -noappend

clean:
#	Remove any runtime or installation files from temp directory
	rm -rf temp/*

distclean: clean
#	Remove distribution files
	rm -rf dist

maintainer-clean:
	chown -R --from=:$(maintainer_GID) :www-data $(colibri_HTDOCS_FILES)

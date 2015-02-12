# Base name of distribution and release files
NAME=fabui

# Version is read from first paragraph of REAMDE file
VERSION=$(shell grep '^FABUI [0-9]\+\.[0-9]\+' README.md README.md | head -n1 | cut -d' ' -f2)

# Priority for colibri bundle
PRIORITY := 090

# Application files
legacy_HTDOCS_FILES  := assets fabui .htaccess index.php install.php lib LICENSE README.md recovery
HTDOCS_FILES := assets fabui index.php install.php lib LICENSE README.md recovery

# System files
SYSCONF_FILES := firstboot.d init.d lighttpd rc.d


# These should be `configure`able
SYSCONFDIR=/etc
HTCONFDIR=$(SYSCONFDIR)/lightppd
# This last one may also end up in some sort of `configure.php.in` file
HTDOCSDIR=/var/www

maintainer_UID := $(shell id -u $$SUDO_USER)
maintainer_GID := $(shell id -g $$SUDO_USER)

.PHONY: all dist-legacy dist-colibri clean distclean

all: dist dist-legacy

#
# make dist-legacy
#
# Make a versioned distribution archive for the legacy system.
#
legacy_NAME := $(NAME)
dist-legacy: DESTDIR ?= ./dist
dist-legacy: temp/$(NAME).zip
	mkdir -p $(DESTDIR)/update/FAB-UI/download/$(VERSION)
	mv temp/$(NAME).zip $(DESTDIR)/update/FAB-UI/download/$(VERSION)/
	echo $(VERSION) > $(DESTDIR)/update/FAB-UI/version.txt
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
RELEASE=$(PRIORITY)-$(NAME)-$(VERSION)-v$(shell date +%Y%m%d)
dist: DESTDIR ?= ./dist
dist: temp/$(RELEASE).cb
	mkdir -p $(DESTDIR)/bundles
	mv temp/$(RELEASE).cb $(DESTDIR)/bundles/

%.cb: README.md
#	Copy public htdocs files
	mkdir -p temp/bdata$(HTDOCSDIR)
	cp -a $(HTDOCS_FILES) temp/bdata$(HTDOCSDIR)/
	touch temp/bdata$(HTDOCSDIR)/AUTOINSTALL
#	Relocate system configuration files into their final place
	mkdir -p temp/bdata$(SYSCONFDIR)
	for file in $(SYSCONF_FILES); do mv temp/bdata/var/www/recovery/install/system/etc/$$file temp/bdata$(SYSCONFDIR)/; done
#	Fix some ownership
	chown -R --from=$(maintainer_UID) root temp/bdata$(HTDOCSDIR)/*
	chown -R --from=$(maintainer_UID) root:root temp/bdata$(SYSCONFDIR)/*
#	Squash the file system thus created
	mksquashfs temp/bdata $@ -noappend

install: temp/$(RELEASE).cb
	mkdir -p $(DESTDIR)/bundles
	cp temp/$(RELEASE).cb $(DESTDIR)/bundles/

run: $(DESTDIR)/sdcard/bundles/$(RELEASE).cb
	$(MAKE) DESTDIR=$(DESTDIR)/sdcard install
	cd $(DESTDIR) && ./copy2sdcard.sh && ./fabemu.py

uninstall:
	rm $(DESTDIR)/bundles/$(PRIORITY)-$(NAME)-$(VERSION)-*.cb

clean:
#	Remove any runtime or installation files from temp directory
	rm -rf temp/*

distclean: clean
#	Remove distribution files
	rm -rf dist

maintainer-clean:
	chown -R --from=:$(maintainer_GID) :www-data $(colibri_HTDOCS_FILES)

FILES=assets fabui index.php install.php lib LICENSE README.md recovery

sysconfdir=/etc
htconfdir=/etc/lightppd
htdocsdir=/var/www

.PHONY: all clean distclean

all: clean

install: installdirs all
#	Install files in destination dir
	cp -a --no-preserve=ownership $(FILES) $(DESTDIR)$(htdocsdir)
#	Install system configuration files
	install -D recovery/install/system/etc/firstboot.d/fabui $(DESTDIR)$(sysconfdir)/firstboot.d/fabui
	install -D recovery/install/system/etc/init.d/fabui $(DESTDIR)$(sysconfdir)/init.d/fabui
	install -D recovery/install/system/etc/lighttpd/conf-available/99-fabui.conf $(DESTDIR)$(htconfdir)/conf-available/99-fabui.conf
#TODO: activate fab-ui init scripts

installdirs:
	mkdir -p $(DESTDIR)$(htdocsdir)

clean:
#	Remove runtime or installation files
	rm -rf temp/*

distclean: clean
#	Clean package of any development file so it is in a 'redistributable' state
	rm -rf recovery/install/system/*
	rm -rf .git .gitignore

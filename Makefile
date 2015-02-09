FILES=assets fabui .htaccess index.php install.php lib LICENSE logs README.md recovery sql temp

sysconfdir=/etc
htconfdir=/etc/lightppd
htdocsdir=/var/www

.PHONY: all

all: clean

install: installdirs all
#	Install files in destination dir
	install -m ug+rwX -o root $(FILES) $(DESTDIR)$(htdocsdir)
#	Install system configuration files
	install -D -o root recovery/install/system/etc/firstboot.d/fabui $(DESTDIR)$(sysconfdir)/firstboot.d/
	install -D -o root recovery/install/system/etc/init.d/fabui $(DESTDIR)$(sysconfdir)/init.d/
	install -D recovery/install/system/etc/lighttpd/conf-available/99-fabui.conf $(DESTDIR)$(htconfdir)/conf-available/
#TODO: activate fab-ui init script

installdirs:
	mkdir -p $(DESTDIR)$(htdocsdir)

clean:
#	Remove runtime or installation files
	rm -rf temp/*

distclean: clean
#	Clean package of any development file so it is in a 'redistributable' state
	rm -rf recovery/install/system/*
	rm -rf .git .gitignore

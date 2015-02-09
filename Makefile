FILES=assets fabui index.php install.php lib LICENSE README.md recovery

sysconfdir=/etc
htconfdir=$(sysconfdir)/lightppd
htdocsdir=/var/www

.PHONY: all clean distclean

all: clean

install: installdirs all
#	Install files in destination dir
	cp -a --no-preserve=ownership $(FILES) $(DESTDIR)$(htdocsdir)
#	Install system configuration files (for colibri system)
	cp -a --no-preserve=ownership recovery/install/system/etc/* $(DESTDIR)$(sysconfdir)/

installdirs:
	mkdir -p $(DESTDIR)$(htdocsdir)

clean:
#	Remove runtime or installation files
	rm -rf temp/*

distclean: clean
#	Clean package of any development file so it is in a 'redistributable' state
	rm -rf recovery/install/system/*
	rm -rf .git .gitignore

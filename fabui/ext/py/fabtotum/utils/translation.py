#!/bin/env python
# -*- coding: utf-8; -*-
#
# (c) 2017 FABtotum, http://www.fabtotum.com
#
# This file is part of FABUI.
#
# FABUI is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
#
# FABUI is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with FABUI.  If not, see <http://www.gnu.org/licenses/>.

# Import standard python module
import os
import gettext
import ConfigParser

# Import external modules

# Import internal modules
from fabtotum.fabui.config  import ConfigService


class Translation():
	
	def __init__(self, config=None):
		if not config:
			self.config = ConfigService()
			
		self.tr = gettext.translation('fabui', '/usr/share/fabui/locale', fallback=True)
		self.tp = gettext.translation('fabui', '/usr/share/fabui/locale', fallback=True)
		
	def setLanguage(self, lang, domain='fabui'):
		
		locale_path = self.config.get('general', 'locale_path')
		self.tr = gettext.translation('fabui', locale_path, fallback=True, languages=[lang])
		
	def setPluginLanguage(self, plugin_name, lang):
		plugins_path = self.config.get('general', 'plugins_path')
		locale_path = '{0}{1}/locale'.format(plugins_path, plugin_name)
		self.tp = gettext.translation(plugin_name, locale_path, fallback=True, languages=[lang])
	
	def get(self, text):
		
		t = self.tp.gettext(text)
		if t == text :
			t = self.tr.gettext(text)
		return t
	

tr = Translation()
_ = tr.get


def setLanguage(lang, domain='fabui', config=None):
	tr.setLanguage(lang, domain)
	
def setPluginLanguage(name, lang):
	tr.setPluginLanguage(name, lang)
	



				
				
	
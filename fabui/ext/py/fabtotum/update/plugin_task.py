#!/bin/env python
# -*- coding: utf-8; -*-
#
# (c) 2016 FABtotum, http://www.fabtotum.com
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

import shlex, subprocess
import time

from fabtotum.update.subtask  import SubTask
from fabtotum.utils.plugin import install_plugin
from fabtotum.utils.common import shell_exec

class PluginTask(SubTask):

	def __init__(self, name, data, factory=None):
		super(PluginTask, self).__init__(name, "plugin", factory)
		
		self.latest        = data["latest"]
		rel                = data['releases'][self.latest]
		self.version       = rel['version']
		self.slug          = data['slug']
		
		download_url = rel['url_zip']
		
		self.addFile('plugin', download_url, self.slug + '.zip', use_endpoint=False)
		self.setMainFile("plugin")
	
	def serialize(self):
		data = super(PluginTask, self).serialize()
		data["latest"] = self.latest
		data["slug"] = self.slug
		return data

	def install(self):
		self.setStatus('installing')
		
		errorcode = 0
		success = [0]
		install_output = ""
		
		plugin_name = self.getName()
		plugin_file = self.getFile("plugin").getLocal()
		
		if install_plugin( plugin_file, config=self.factory.config):
			print "Plugin installed"
			self.setStatus('installed')
		else:
			print "Plugin not installed"
			self.setStatus('error')
			self.setMessage('Failed to install plugin "{0}"'.format(plugin_name))
	
	def remove(self):
		### remove files ###
		shell_exec('sudo rm {0}'.format(self.getFile("plugin").getLocal()))

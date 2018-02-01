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
from fabtotum.utils.common import shell_exec

class BootTask(SubTask):
	def __init__(self, name, data, repository,  factory=None):
		super(BootTask, self).__init__(name, "boot", factory)
		
		self.latest        = data["latest"]
		self.date_uploaded = data[self.latest]['date-uploaded']
		self.version       = data[self.latest]['version']
		self.repository    = repository
		
		for tag in data[self.latest]['files']:
			self.addFile(tag, self.repository + "/" + data[self.latest]['files'][tag])
	
		self.setMainFile("boot")
	
	def serialize(self):
		data = super(BootTask, self).serialize()
		data["latest"] = self.latest
		return data

	def install(self):
		self.setStatus('installing')
		print "TODO: boot install"
		
		cmd = 'colibrimngr updateboot all ' + self.getFile("boot").getLocal()
		
		errorcode = 0
		success = [0]
		
		try:
			install_output = subprocess.check_output( shlex.split(cmd) )
		except subprocess.CalledProcessError as e:
			install_output = e.output
			errorcode = e.returncode
		
		print cmd
		print install_output
		#~ time.sleep(2)
		
		if errorcode in success:
			print "Boot installed"
			self.setStatus('installed')
		else:
			print "Boot not installed"
			self.setStatus('error')
			self.setMessage(install_output)
		
		self.setStatus('installed')
		
	def remove(self):
		### remove files ###
		shell_exec('sudo rm {0}'.format(self.getFile("boot").getLocal()))

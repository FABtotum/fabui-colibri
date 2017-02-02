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

from fabtotum.update.subtask  import SubTask

class BundleTask(SubTask):
	
	def __init__(self, name, data, factory=None):
		super(BundleTask, self).__init__(name, "bundle", factory)
		
		self.latest        = data["latest"]
		self.date_uploaded = data[self.latest]['date-uploaded']
		self.priority      = data[self.latest]['priority']
		self.version       = data[self.latest]['version']
		self.optional      = data[self.latest]['optional']
		
		for tag in data[self.latest]['files']:
			self.addFile(tag, data[self.latest]['files'][tag])

		self.setMainFile("bundle")

	def serialize(self):
		data = super(BundleTask, self).serialize()
		data["latest"] = self.latest
		return data
		
	def install(self):
		print "installing: " , self.getName()
		self.setStatus('installing')

		cmd = 'colibrimngr install -postpone ' + self.getFile("bundle").getLocal()
		
		errorcode = 0
		success = [0, 1]
		
		try:
			install_output = subprocess.check_output( shlex.split(cmd) )
		except subprocess.CalledProcessError as e:
			install_output = e.output
			errorcode = e.returncode

		if errorcode in success:
			print "Bundle installed"
			self.setStatus('installed')
			if errorcode == 2:
				self.factory.setRebootRequired(True)
			#self.factory.incraeseUpdatedCount()
		else:
			print "Bundle not installed"
			self.setStatus('error')
			self.setMessage(install_output)
			
		self.factory.update()
		print "installed:", self.getName()

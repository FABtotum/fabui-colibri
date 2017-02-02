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

class FirmwareTask(SubTask):

	def __init__(self, name, data, factory=None):
		super(FirmwareTask, self).__init__(name, "firmware", factory)
		
		self.latest        = data["latest"]
		self.date_uploaded = data[self.latest]['date-uploaded']
		self.version       = data[self.latest]['version']
		
		for tag in data[self.latest]['files']:
			self.addFile(tag, data[self.latest]['files'][tag])
	
		self.setMainFile("firmware")
	
	def serialize(self):
		data = super(FirmwareTask, self).serialize()
		data["latest"] = self.latest
		return data

	def install(self):
		self.setStatus('installing')
		print "TODO: firmware install"
		
		cmd = 'sh /usr/share/fabui/ext/bash/totumduino_manager.sh update ' + self.getFile("firmware").getLocal()
		
		errorcode = 0
		success = [0]
		
		self.factory.gcs.release_serial()
		
		try:
			install_output = subprocess.check_output( shlex.split(cmd) )
		except subprocess.CalledProcessError as e:
			install_output = e.output
			errorcode = e.returncode
		
		self.factory.gcs.acquire_serial()
		
		print cmd
		print install_output
		
		time.sleep(2)
		
		if errorcode in success:
			print "Firmware installed"
			self.setStatus('installed')
		else:
			print "Firmware not installed"
			self.setStatus('error')
			self.setMessage(install_output)
		
		self.setStatus('installed')

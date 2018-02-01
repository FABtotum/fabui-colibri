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
import time, re

from fabtotum.update.subtask  import SubTask
from fabtotum.utils.common import shell_exec

class FirmwareTask(SubTask):

	def __init__(self, name, data, repository,  factory=None):
		super(FirmwareTask, self).__init__(name, "firmware", factory)
		
		self.latest        = data["latest"]
		self.date_uploaded = data[self.latest]['date-uploaded']
		self.version       = data[self.latest]['version']
		self.repository    = repository
		
		for tag in data[self.latest]['files']:
			self.addFile(tag, self.repository + "/" + data[self.latest]['files'][tag])
	
		self.setMainFile("firmware")
	
	def serialize(self):
		data = super(FirmwareTask, self).serialize()
		data["latest"] = self.latest
		return data

	def install(self):
		self.setStatus('installing')
		
		cmd = 'sh /usr/share/fabui/ext/bash/totumduino_manager.sh update "' + self.getFile("firmware").getLocal()+'"'
				
		avrdude_log = '/var/log/fabui/avrdude.log'
		
		errorcode = 0
		success = [0]
		install_output = ""
		
		self.factory.gcs.close_serial()
		
		# Execute firmware update
		try:
			install_output = subprocess.check_output( shlex.split(cmd) )
		except subprocess.CalledProcessError as e:
			install_output = e.output
			errorcode = e.returncode
		
		install_output = open(avrdude_log, 'r').read()
		
		
		# self.factory.gcs.open_serial()
		
		match_error1 = re.search('stk500_recv\(\): programmer is not responding', install_output, re.IGNORECASE)
		match_error2 = re.search('stk500_getsync\(\)', install_output, re.IGNORECASE)
		
		if ((match_error1 != None) or ( match_error2 != None)):
			errorcode = 1
		
		# time.sleep(2)
		
		cmd = 'cp ' + self.getFile("gcodes").getLocal() + ' /var/lib/fabui/settings/gcodes.json';
		
		try:
			subprocess.check_output( shlex.split(cmd) )
		except subprocess.CalledProcessError as e:
			pass
		
		time.sleep(5)
		self.factory.gcs.open_serial()
		
		if errorcode in success:
			print "Firmware installed"
			self.setStatus('installed')
		else:
			print "Firmware not installed"
			self.setStatus('error')
			self.setMessage('Firmware was not flashed\nPlease try again\nIf the problem persists please contact support\n\n' + install_output)
		
	
	def remove(self):
		### remove files ###
		shell_exec('sudo rm {0}'.format(self.getFile("firmware").getLocal()))

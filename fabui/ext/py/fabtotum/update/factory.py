from fabtotum.update.version   import RemoteVersion
from fabtotum.fabui.config  import ConfigService
from fabtotum.utils.pyro.gcodeclient import GCodeServiceClient

class UpdateFactory:
	def __init__(self, arch='armhf', mcu='atmega1280', notify_update=None, config=None, gcs=None):
		self.config = config
		if not config:
			self.config = ConfigService()
			
		if not gcs:
			self.gcs = GCodeServiceClient()
		else:
			self.gcs = gcs
			
		self.arch = arch
		self.mcu  = mcu
		self.remote = RemoteVersion(arch, mcu, config=config)
		
		self.tasks = []
		self.notify_update = notify_update
		
		self.reboot_required = False
		self.status = ''
		self.task = ''
	
	def getTasks(self):
		return self.tasks
	
	def getBundles(self):
		return self.remote.getBundles()
		
	def getFirmware(self):
		return self.remote.getFirmware()
		
	def getBoot(self):
		return self.remote.getBoot()
		
	def getPlugins(self):
		return self.remote.getPlugins()
	
	def getEndpoint(self, name):
		if name == "bundle":
			return self.remote.getColibriEndpoint()
		elif name == "firmware":
			return self.remote.getFirmwareEndpoint()
		elif name == "boot":
			return self.remote.getColibriEndpoint()
		elif name == "plugins":
			return self.remote.getPluginsEndpoint()
		
	def getTempFolder(self):
		return self.config.get('general', 'bigtemp_path')

	def addTask(self, task):
		task.setFactory(self)
		self.tasks.append(task)

	def update(self):
		if self.notify_update:
			self.notify_update(self)
	
	def getCurrentStatus(self):
		task = self.getTaskByName(self.task)
		if task:
			return task.getStatus()
		return ""
				
	def getTaskByName(self, name):
		if not name:
			return None
			
		for task in self.tasks:
			if task.getName() == name:
				return task
				
		return None
				
	def setStatus(self, status):
		self.status = status
		self.update()
	
	def getStatus(self):
		return self.status
	
	def setCurrentTask(self, task):
		self.task = task
	
	def getCurrentTask(self):
		return self.task
	
	def getCurrentName(self):
		task = self.getTaskByName(self.task)
		if task:
			return task.getName()
		return ""
		
	def getCurrentType(self):
		task = self.getTaskByName(self.task)
		if task:
			return task.getType()
		return ""
	
	def setRebootRequired(self, reboot):
		self.reboot_required = reboot
		
	def getRebootRequired(self):
		return self.reboot_required
	
	def serialize(self):
		data = {
			"current":{
				"name": self.getCurrentName(),
				"type": self.getCurrentType(),
				"status": self.getStatus(),
				"task": self.getCurrentTask(),
				"reboot": self.getRebootRequired()
			},
			"tasks":[]
		}
		
		#~ for task in self.tasks:
			#~ data["tasks"][task.getName()] = task.serialize()
		for task in self.tasks:
			data["tasks"].append( task.serialize() )
			
		return data

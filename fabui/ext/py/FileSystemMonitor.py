__author__ = "Krios Mane"
__license__ = "GPL - https://opensource.org/licenses/GPL-3.0"
__version__ = "1.0"
#--------------------------------#
# Listener to all the events on /tmp/
#--------------------------------#
import FabtotumConfig
import json
from watchdog.events import PatternMatchingEventHandler
from watchdog.events import FileSystemEventHandler

###################################################################################################################
## Event Listener for the most used files
###################################################################################################################
class FolderTempMonitor(PatternMatchingEventHandler):
    
    patterns = [FabtotumConfig.TRACE, FabtotumConfig.MACRO_RESPONSE, FabtotumConfig.TASK_MONITOR]
    ignore_directories = None
    ignore_patterns = None 
    case_sensitive = None
    ws = None #web socket, used to notify UI
    
    def __init__(self, WebSocket):
        self.ignore_directories = None
        self._ignore_patterns = None
        self.case_sensitive = None
        self.ws = WebSocket
        
    def on_modified(self, event):
        
        messageType = ''
        messageData = ''
        
        if(event.src_path == FabtotumConfig.TRACE):
            messageData = {'type': 'trace', 'content': str(self.getFileContent(FabtotumConfig.TRACE))}
            messageType ="macro"
        
        self.sendMessage(messageType, messageData)
            
        
    def on_created(self, event):
        #self.process(event)
        print "CRAETED: ", event.src_path
        #self.ws.send("CRAETED")
    
    def on_deleted(self, event):
        #self.process(event)
        print "DELETED: ", event.src_path
        #self.ws.send("CRAETED")
        
    def sendMessage(self, type, data):
        message = {'type': type, 'data':data}
        self.ws.send(json.dumps(message))
        
    def getFileContent(self, file_path):
        file = open(file_path, 'r')
        content= file.read()
        file.close()
        return content

################################################################################################################### 
## Notify UI when an USB DISK is inserted or removed
###################################################################################################################   
class UsbMonitor(FileSystemEventHandler):
    ws = None    
    def __init__(self, WebSocket ):
        self.ws = WebSocket
        self.Empty = None
        
    def on_created(self, event):
        if(event.src_path == FabtotumConfig.USB_FILE):
            self.sendMessage(True)
    
    def on_deleted(self, event):
        if(event.src_path == FabtotumConfig.USB_FILE):
            self.sendMessage(False)
    
    def sendMessage(self, status):
        message={'type':'usb', 'data':{'status': status, 'alert':True}}
        self.ws.send(json.dumps(message))
    
    
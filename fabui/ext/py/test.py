__author__ = "Krios Mane"
__license__ = "GPL - https://opensource.org/licenses/GPL-3.0"
__version__ = "1.0"
#------------------------------------------------------------#
# Daemon script that monitor file system events to notify UI
#------------------------------------------------------------#
import FabtotumConfig
from FileSystemMonitor import FolderTempMonitor
from FileSystemMonitor import UsbMonitor
from watchdog.observers import Observer
from ws4py.client.threadedclient import WebSocketClient

ws = WebSocketClient('ws://'+FabtotumConfig.SOCKET_HOST +':'+FabtotumConfig.SOCKET_PORT+'/')
ws.connect();

## folder temp monitor
ftm = FolderTempMonitor(ws)
observer = Observer()
observer.schedule(ftm, '/tmp/', recursive=False)
observer.start()

## usb disk monitor
um = UsbMonitor(ws)
usbObserver =  Observer()
usbObserver.schedule(um, '/dev/', recursive=False)
usbObserver.start()

try:
    observer.join()
    usbObserver.join()
except KeyboardInterrupt:
    observer.stop()
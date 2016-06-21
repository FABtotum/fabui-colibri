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
import RPi.GPIO as GPIO
import serial, re, json, os, time


GPIO.setmode(GPIO.BCM) # set up BCM GPIO numbering
GPIO.setup(int(FabtotumConfig.GPIO_PIN), GPIO.IN, pull_up_down = GPIO.PUD_DOWN) # set GPIO 2 as input (button)

ws = WebSocketClient('ws://'+FabtotumConfig.SOCKET_HOST +':'+FabtotumConfig.SOCKET_PORT+'/')
ws.connect();

serialComm = serial.Serial(FabtotumConfig.SERIAL_PORT, FabtotumConfig.SERIAL_BAUD, timeout=0.6)
serialComm.close()


def gpioEventListener(channell):
    print "====== START ============"
    print 'GPIO STATUS: ', GPIO.input(int(FabtotumConfig.GPIO_PIN))
    if GPIO.input(int(FabtotumConfig.GPIO_PIN)) == 0 :
        open(FabtotumConfig.LOCK_FILE, 'w+').close() #lock file to take over serial
        os.chmod(FabtotumConfig.LOCK_FILE, 0777)
        serialComm.open()
        serialComm.flushInput()
        serialComm.write("M730\r\n")
        #print "REPLY ALL: ", serialComm.read(4096)
        #reply=serialComm.readline().strip()
        reply= serialComm.read(4096)
        print "REPLY ALL: ", reply
        serialComm.reset_input_buffer()
        serialComm.reset_output_buffer()
        serialComm.close()
        #print "REPLY: ", reply
        search = re.search('ERROR\s:\s(\d+)', reply)
        if search != None:
            errorNumber = int(search.group(1))
            print "Error: ", errorNumber
            manageErrorNumber(errorNumber)
        else:
            print "Error number not recognized: ", reply
        os.remove(FabtotumConfig.LOCK_FILE) # release serial priority
    #GPIO_STATUS=GPIO.HIGH
    print 'GPIO STATUS on EXIT: ', GPIO.input(int(FabtotumConfig.GPIO_PIN))
    print "====== EXIT ============"
#########################################################################                  
## Check error number to notify UI and do what have to do
#########################################################################
def manageErrorNumber(error):
    alertErrors = [110]
    shutdownErros = [120, 121]
    errorType = 'emergency'
    
    if error in shutdownErros:
        print "shutdown"
        return None
    elif error in alertErrors:
        errorType = 'alert'
        serialComm.open()
        serialComm.reset_input_buffer()
        serialComm.write("M999\r\n")
        serialComm.close()
        
    message = {'type': str(errorType), 'code': str(error)}
    ws.send(json.dumps(message))
    writeEmergency(json.dumps(message))
     
#########################################################################
## IF browser don't support websockets write emgency error tu file
## so the UI can check it via pulling
#########################################################################    
def writeEmergency(message):
    emergencyFile = open(FabtotumConfig.EMERGENCY_FILE, 'w+')
    emergencyFile.write(message)
    emergencyFile.close()
    os.chmod(FabtotumConfig.EMERGENCY_FILE, 0777)
    return


GPIO.add_event_detect(int(FabtotumConfig.GPIO_PIN), GPIO.BOTH, callback=gpioEventListener, bouncetime=100)

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
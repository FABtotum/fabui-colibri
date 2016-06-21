__author__ = "Krios Mane"
__license__ = "GPL - https://opensource.org/licenses/GPL-3.0"
__version__ = "1.0"
#--------------------------------#
# Detect events on GPIO
#--------------------------------#
import FabtotumConfig
import RPi.GPIO as GPIO
from ws4py.client.threadedclient import WebSocketClient
import serial, re, json, os, time


print "=== GPIOMONITOR ==="
ws = WebSocketClient('ws://'+FabtotumConfig.SOCKET_HOST +':'+FabtotumConfig.SOCKET_PORT+'/')
ws.connect();

serialComm = serial.Serial(FabtotumConfig.SERIAL_PORT, FabtotumConfig.SERIAL_BAUD, timeout=0.6)
serialComm.close()

GPIO.setmode(GPIO.BCM) # set up BCM GPIO numbering
GPIO.setup(int(FabtotumConfig.GPIO_PIN), GPIO.IN, pull_up_down = GPIO.PUD_DOWN) # set GPIO 2 as input (button)



def gpioEventListener(channell):
    open(FabtotumConfig.LOCK_FILE, 'w').close() #lock file to take over serial
    print "====== START ============"
    print 'GPIO STATUS: ', GPIO.input(int(FabtotumConfig.GPIO_PIN))
    if GPIO.input(int(FabtotumConfig.GPIO_PIN)) == 0 :
        serialComm.open()
        serialComm.flushInput()
        serialComm.write("M730\r\n")
        print "REPLY ALL: ", serialComm.read(4096)
        #reply=serialComm.readline().strip()
        reply= serialComm.read(4096).strip()
        serialComm.reset_input_buffer()
        serialComm.reset_output_buffer()
        serialComm.close()
        print "REPLY: ", reply
        search = re.search('ERROR\s:\s(\d+)', reply)
        if search != None:
            errorNumber = int(search.group(1))
            manageErrorNumber(errorNumber)
        else:
            print "Error number not recognized: ", reply
    GPIO_STATUS=GPIO.HIGH
    print 'GPIO STATUS on EXIT: ', GPIO.input(int(FabtotumConfig.GPIO_PIN))
    print "====== EXIT ============"
    os.remove(FabtotumConfig.LOCK_FILE) # release serial priority
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
    return

#########################################################################                    
## when a changing edge is detected on port 2, regardless of whatever
## else is happening in the program, the function my_callback will be run 
#########################################################################   
GPIO.add_event_detect(int(FabtotumConfig.GPIO_PIN), GPIO.BOTH, callback=gpioEventListener, bouncetime=100)

while True:
    time.sleep(0.1)
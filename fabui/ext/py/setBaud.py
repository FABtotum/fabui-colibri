__author__ = "Krios Mane"
__license__ = "GPL - https://opensource.org/licenses/GPL-3.0"
__version__ = "1.0"
#--------------------------------#
# SET BAUD SPEED
#--------------------------------#
import serial
import ConfigParser
import os
import FabtotumConfig


SERIAL_INI = '/var/lib/fabui/serial.ini'

''' Set the highest baudrate available '''
def testBaud(port, baud_rate):
    ser = serial.Serial(port, baud_rate, timeout=0.5)
    ser.flushInput()
    ser.write("G0\r\n")
    serial_reply=ser.readline().rstrip()
    ser.close()
    return serial_reply != ''

#serial_port = config.get('serial', 'port')

baud_list=[250000, 115200]
accepted_baud=0

for baud in baud_list:
    if(testBaud(FabtotumConfig.SERIAL_PORT, baud)):
        accepted_baud = baud
        break

if accepted_baud > 0:
    print "Baud Rate available is: " + str(accepted_baud)
    
else:
    accepted_baud=115200

if os.path.exists(SERIAL_INI) == False:
    file = open(SERIAL_INI, 'w+')
    file.write("[serial]\n")
    file.close()

config = ConfigParser.ConfigParser()
config.read(SERIAL_INI)

config.set('serial', 'baud', accepted_baud)
config.set('serial', 'port', FabtotumConfig.SERIAL_PORT)
with open(SERIAL_INI, 'w') as configfile:
    config.write(configfile)
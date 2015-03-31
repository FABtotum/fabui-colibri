import os, sys
import time
import serial
import json
import ConfigParser
from subprocess import call

config = ConfigParser.ConfigParser()
config.read('/var/www/fabui/python/config.ini') 


#startup script (see crontab)
print "Boot script"
#time.sleep(60) #wait 60 seconds so connections can be made.
print "Start"

#tell the board that the raspi has been connected.

#settting serial communication
serail_port = config.get('serial', 'port')
serail_baud = config.get('serial', 'baud')

ser = serial.Serial(serail_port,serail_baud,timeout=1)
ser.flushInput()
ser.flushOutput()
ser.flushInput()

# Tell the board that the raspi has been connected.
ser.write('M728\r\n') #machine alive

time.sleep(0.5)

#read configs
json_f = open(config.get('printer', 'settings_file'))
config = json.load(json_f)

# UNITS
#load custom units
#ser.write("M92 X"+str(config[x])+"\r\n")
#ser.write("M92 Y"+str(config[y])+"\r\n")
#ser.write("M92 Z"+str(config[z])+"\r\n")
#ser.write("M92 E"+str(config[e])+"\r\n")

# SAFETY
try:
    safety_door = config['safety']['door']
except KeyError:
    safety_door = 0

ser.flushInput()
print "Safety door:",
ser.write("M732 S"+str(safety_door)+"\r\n")
if ser.readline().rstrip() != "ok":
    print "ERROR"
else:
    print "set"

# HOMING
try:
    switch = config['switch']
except KeyError:
    switch = 0

print "Homing direction:",
ser.flushInput()
ser.write("M714 S"+str(switch)+"\r\n")
if ser.readline().rstrip() != "ok":
    print "ERROR"
else:
    print "set"

# Clean the buffer and leave
ser.flush()
ser.close()

print "Boot completed"
sys.exit()

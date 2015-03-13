import os, sys
import time
import serial
import json
from subprocess import call

print "FABtotum boot script"

ser = serial.Serial("/dev/ttyAMA0",115200,timeout=1)
ser.flushOutput()
ser.flushInput()

# Tell the board that the raspi has been connected.
ser.write('M728\r\n') #machine alive

time.sleep(0.5)

# LOAD USER CONFIG
json_f = open("/var/www/fabui/config/config.json")
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

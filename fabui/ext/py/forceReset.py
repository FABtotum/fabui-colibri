__author__ = "Krios Mane"
__license__ = "GPL - https://opensource.org/licenses/GPL-3.0"
__version__ = "1.0"
#!/usr/bin/python
#############################################################
## Force Totumduino Reset
#############################################################
import RPi.GPIO as GPIO
import time,sys
import serial
import logging
import os
import FabtotumConfig
#write LOCK FILE  
#open(FabtotumConfig.LOCK_FILE, 'w').close()
#open(FabtotumConfig.TRACE, 'w').close() #reset trace file

#logging.basicConfig(filename=FabtotumConfig.TRACE,level=logging.INFO,format='%(message)s')

#def trace(string):
#    logging.info(string)
#    return


#trace("Start reset controller...")

#GPIO.cleanup()
GPIO.setmode(GPIO.BOARD)
GPIO.setwarnings(False)

def reset():
  pin = 11
  GPIO.setup(pin, GPIO.OUT)
  GPIO.output(pin, GPIO.HIGH)
  time.sleep(0.15)
  GPIO.output(pin, GPIO.LOW)
  time.sleep(0.15)
  GPIO.output(pin, GPIO.HIGH)

reset()
GPIO.cleanup()

serial = serial.Serial(FabtotumConfig.SERIAL_PORT, FabtotumConfig.SERIAL_BAUD, timeout=0.5)
serial.flush()
serial.reset_input_buffer()
serial.reset_output_buffer()
serial.close()
time.sleep(1)
#trace("Controller ready")
#write_status(False)
#os.remove(config.get('task', 'lock_file'))


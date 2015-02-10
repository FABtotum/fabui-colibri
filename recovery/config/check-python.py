#!/usr/bin/python

import sys, time

def cpuinfo ():
	cpus = dict()
	i = 0
	with open("/proc/cpuinfo", "r") as cpuinfo:
		for line in cpuinfo:
			if (len(line) <= 0): continue
			row = line.split(':', 2)
			key = row[0].strip().lower()
			if (len(key) <= 0): continue
			if (len(row) == 2):
				val = row[1].strip()
			else:
				val = ''
			if (key == "processor"):
				i = int(val)
				cpus[i] = dict()
			else:
				cpus[i][key] = val
	return cpus

def is_rpi ():
	cpu_info = cpuinfo()
	if (len(cpu_info) and "hardware" in cpu_info[0]):
		hw = cpu_info[0]["hardware"]
		if hw in ("BCM2708", "BCM2709", "BCM2835", "BCM2836"):
			return True
	return False

try:
	code = 0  # 0=OK, 1=WARNINGS, 2=ERRORS, 3=FAILURE

	#TEST: Check python version == 2.7.x
	try:
		version = sys.version_info
		if (version[0] == 2 and version[1] == 7):
			print "Checking `python` version: ok ", sys.version
		else:
			print "Checking `python` version: ERROR "+sys.version
			sys.exit(3)
	except Exception:
		print "Checking `python` version: ERROR (< 2.0)"
		sys.exit(3)

	#TEST: detect hardware
	print "Detecting hardware platform...",
	cpu_info = cpuinfo()
	for i in cpu_info:
		if ("model name" in cpu_info[i]):
			print ("["+str(i)+"]:"), cpu_info[i]["model name"],
		else:
			print ("["+str(i)+"]: undefined"),
	print ""

	#TEST: RPi.GPIO
	# just test the module for inclusion, but only if we know we are on a
	# areal RPi otherwise the modulo will throw an unuseful exception
	print "Checking RPi.GPIO module...",
	try:
		if (is_rpi()):
			import RPi.GPIO as GPIO
			print "ok"
		else:
			print "skipped"
			if (code < 1): code = 1
	except ImportError:
		print "missing"

	#TEST: pyserial (https://pypi.python.org/pypi/pyserial)
	print "Checking pyserial module...",
	try:
		import serial
		print "ok"
	except ImportError:
		print "ERROR"
		sys.exit(2)

	#TEST: Try to access main serial port
	port = '/dev/ttyAMA0'
	baud = 115200
	print "Trying to access serial port `"+port+"`...",
	port = serial.Serial(port, baud, timeout=1)
	print "ok"

	#TEST: Try to write something on the main serial port
	# Init raspberry
	print "Trying to send wake-up signal to the machine...",
	port.flushInput()
	port.write("M728\r\n")
	if (port.readline().rstrip() != "ok"):
		print "ERROR"
		sys.exit(2)
	print "ok"

	print "Trying to send some g-codes to the machine using serial port...",
	# Cycle through led colors
	# Machine should respond with 'ok' after every command
	done = True
	for i in range(0, 24):
		c = 1 << i
		r = c % 256
		g = (c >> 8)  % 256
		b = (c >> 16) % 256
		port.flushInput()
		port.write("M701 S"+str(r)+"\r\n")
		port.write("M702 S"+str(g)+"\r\n")
		port.write("M703 S"+str(b)+"\r\n")
		port.flush()
		if (port.readline().rstrip() != 'ok'):
			done = False
			break
	if (done):
		print "ok"
	else:
		print "ERROR"
		sys.exit(2)

	#TEST: Read gcodes
	print "Trying to read some values from the machine using serial port...",
	port.flushInput()
	port.write("M105\r\n")
	port.flush()
	temp = port.readline().split(" ")
	if (temp[0] != "ok"):
		print "ERROR"
		sys.exit(2)
	print "ok"

	#TEST: numpy (numpy)
	try:
		print "Checking numpy module...",
		import numpy
		print "ok"
	except ImportError:
		print "ERROR"
		sys.exit(2)

	#TEST: OpenCV (cv2 e cv)
	try:
		print "Checking OpenCV...",
		import cv, cv2
		print "ok"
	except ImportError:
		print "ERROR"
		sys.exit(2)

	sys.exit(code)

except Exception:
	print "FAILURE"
	sys.exit(2)

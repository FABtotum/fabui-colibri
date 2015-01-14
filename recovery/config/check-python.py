#!/usr/bin/python

import sys, time

try:
	code = 0  # 0=OK, 1=WARNINGS, 2=ERRORS, 3=FAILURE

	#TEST: Check python version == 2.7.x
	try:
		version = sys.version_info
		if (version[0] == 2 and version[1] == 7):
			print "Checking `python` version:", sys.version, "ok"
		else:
			print "Checking `python` version: "+sys.version+" ERROR"
			sys.exit(3)
	except Exception:
		print "Checking `python` version: < 2.0 ERROR"
		sys.exit(3)

	#TEST: RPi.GPIO

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




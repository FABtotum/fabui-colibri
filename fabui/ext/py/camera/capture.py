#!/bin/env python

from picamera import PiCamera

pc = PiCamera()
pc.start_preview()


#~ pc.resolution = (1920,1080)
pc.resolution = (1296, 972)
pc.rotation = 0

total = 20

for idx in xrange(0, total):
    raw_input("Press Enter to capture {0}/{1}...".format(idx+1,total) )
    pc.capture('calibration/{0}.jpg'.format(idx) )

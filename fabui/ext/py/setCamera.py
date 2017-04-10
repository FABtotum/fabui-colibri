#!/bin/env python

__author__ = "Daniel Kesler"
__license__ = "GPL - https://opensource.org/licenses/GPL-3.0"
__version__ = "1.0"


# Import standard python module
import os, re
import ConfigParser
import gettext

# Import external modules
try:
    from picamera import PiCamera
except:
    pass

# Import internal modules
from fabtotum.os.paths  import CAMERA_INI

# Set up message catalog access
tr = gettext.translation('autotune', 'locale', fallback=True)
_ = tr.ugettext

if os.path.exists(CAMERA_INI) == False:
    print "writing empty config"
    file = open(CAMERA_INI, 'w+')
    file.write("[camera]\n")
    file.close()


camera_version  = "v1"
camera_detected = False

#~ raspistill_output = os.popen('raspistill -v')
#~ raspistill_output = raspistill_output.read()

#~ match_temp = re.search('Camera is not detected', raspistill_output, re.IGNORECASE)

#~ if match_temp != None:
    #~ camera_detected = True

#~ if camera_detected :
try:
	# Try to set max resolution for v2 camera
	camera = PiCamera()
	camera.resolution = (3280, 2464)
	camera_version = "v2"
	camera_detected = True
except:
	# Ok, it failed so its a v1 camera
	try:
		camera = PiCamera()
		camera.resolution = (2592, 1944)
		camera_version = "v1"
	except:
		pass

config = ConfigParser.ConfigParser()
config.read(CAMERA_INI)


config.set('camera', 'enabled', camera_detected)
config.set('camera', 'version', camera_version)
with open(CAMERA_INI, 'w') as configfile:
    config.write(configfile)

print "Camera enabled:", camera_detected
print "Camera version:", camera_version

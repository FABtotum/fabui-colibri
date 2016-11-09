#!/bin/env python
import os, sys, getopt
import glob
import json

filters_extensions=[".gcode",".nc",".gc",".stl",".obj"]

path = "/run/media/"

if len(sys.argv) > 1:
	path = os.path.join(path, sys.argv[1])

files=[]

try:
	if os.path.isdir(path):

		for fn in os.listdir(path):
			
			include=False
			
			if(os.path.isfile(path+"/"+fn)):
			
				extension = os.path.splitext(path+"/"+fn)[1]
				if extension in filters_extensions:
					include=True        
			elif(os.path.isdir(path+"/"+fn)):
				include=True
				fn = fn + "/"
				
			if(include):    
				files.append(fn)
				
		print json.dumps(files)
		
	else:
		print "[]"
except:
	print "[]"

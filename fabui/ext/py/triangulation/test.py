#!/bin/env python
# -*- coding: utf-8; -*-
#
# (c) 2016 FABtotum, http://www.fabtotum.com
#
# This file is part of FABUI.
#
# FABUI is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
#
# FABUI is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with FABUI.  If not, see <http://www.gnu.org/licenses/>.
from __future__ import print_function

__authors__ = "Daniel Kesler"
__license__ = "GPL - https://opensource.org/licenses/GPL-3.0"
__version__ = "1.0"

import triangulation
import time
import cv2, cv
import numpy as np

def test1(img_fn, img_l_fn, threshold):
    
    #threshold   =   40
    fail = 0
    subrange    = 15
    domain=np.arange(subrange*2, dtype=np.uint8)
    
    img = cv2.imread(img_fn)
    img_l = cv2.imread(img_l_fn)
    
    img_height = img.shape[0]
    img_width = img.shape[1]
    
    line_pos=np.zeros(img_width,dtype=np.float)
    
    or_difference = cv2.absdiff(img_l, img)

    if threshold == 0:
        #first time only (calculate dynamic threshold)
        tresh_difference = cv2.cvtColor(or_difference, cv.CV_BGR2GRAY)
        
        maxval = tresh_difference.max()
        print("maxval :" , maxval)
        
        tresh= int(maxval*0.4)		 #use 40% of max value as a treshold
        print("Dynamic Treshold :" , tresh)
        
    # Remove differences that are smaller that [tresh] (threshold) and are just sensor noise
    ret,difference = cv2.threshold(or_difference,tresh,255,cv2.THRESH_TOZERO)

    # Create enhanced view of the Laser line
    difference = cv2.cvtColor(difference, cv.CV_BGR2GRAY)
    
    cv2.imwrite("py_gray.jpg", difference)
    
    # Max value for each column
    ind = difference.argmax(axis=0)
        
    # Declare empty position array for post process.
    #//~ line_pos=np.zeros(img_width,dtype=np.float)

    for col,value in enumerate(ind):
        #print( "({0},{1}), ".format(col, value), end="")
        if(value>0): #if column has a point to process. otherwise skip to next
            
            #resize analysis domain if outside image size values
            if(value-subrange<=0):
                y1=0
            else:
                y1=value-subrange
                
            if(value+subrange>=img_height):
                y2 = img_height
            else:
                y2 = value+subrange
            
            luminance_col = difference[y1:y2,col:col+1]
            luminance_col = np.swapaxes(luminance_col,1,0)
            
            #print("y1, y2:", y1, y2)
            
            if(domain.shape==luminance_col[0].shape):
                #Use np.average: average(a, axis=None, weights=None, returned=False):
                w_position = np.average(domain, 0, luminance_col[0])    #find index in the search domain with weighted position
                #print("w_position(1):", w_position)
                w_position = value+(w_position-subrange)				#correction of the original position
                #print("w_position(2):", w_position)
                #if debug and cs==slices-1:
                    #print col , "-", w_position
            else:
                fail+=1
                #if debug:
                #	print "Exiting subdomain in col :" + str(col) +" of slice " + str(cs) + " value:" + str(value)
                #	print "Domain:" + str(domain.shape) +" , Luminance col:" +str(luminance_col[0].shape)
                #	print "Domain resized."
                w_position = value		#keep the max luminance found since the subdomain has violated the image borders
                
            #add the position in the empty array
            line_pos[col] = w_position
            
            #print("{0}, ".format(w_position), end="")
                
            #~ if debug:
                #~ #print str(x)+ "," + str(y) + "," + str(z) + "\n"
                #~ or_difference[w_position,col,1]=255  #set green pixel in CV debug image  (BGR)
                #~ or_difference[y1,col,0]=255  #set blue pixel in CV debug image  (BGR)
                #~ or_difference[y2-1,col,0]=255  #set blue pixel in CV debug image  (BGR)
            
        #holes map
        if value == 0:
            pass
            #the holemap maps where there is data.
            #hole_image[col,cs,2]=255 #place a red pixel (BGR)

print ( "Version:", triangulation.version() )

IMG_DIR = "../sweep_scan/images"
IMG_NUM = 28

#~ triangulation.test1("{0}/{1}.jpg".format(IMG_DIR, IMG_NUM),  "{0}/{1}_l.jpg".format(IMG_DIR, IMG_NUM))
print("Python")
t1 = time.time()
test1("{0}/{1}.jpg".format(IMG_DIR, IMG_NUM),  "{0}/{1}_l.jpg".format(IMG_DIR, IMG_NUM), 0)
py_dt = time.time() - t1
print( "Time :", py_dt )

print("C++")
t1 = time.time()
triangulation.test1("{0}/{1}.jpg".format(IMG_DIR, IMG_NUM),  "{0}/{1}_l.jpg".format(IMG_DIR, IMG_NUM), 0)
cpp_dt = time.time() - t1
print("Time :", cpp_dt)

print("Speed up: x{0}".format( round(py_dt / cpp_dt,2) ))

# https://picamera.readthedocs.io/en/release-1.12/fov.html#camera-modes
# Camera V1
fov_v1_full = [54, 41]
fov_v1_partial = [54, 30.8]
# Camera V2
fov_v1 = [62.2, 48.8]

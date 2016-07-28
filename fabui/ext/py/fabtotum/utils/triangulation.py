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

# Import standard python module

# Import external modules
import numpy as np
import cv2,cv

# Import internal modules


################################################################################

MMPPH       = 6.0 # mm per pixel height
MMPPL       = 6.0 # mm per pixel lenght 
LASER_ANGLE = 35
BETA_ANGLE  = 24
FAN_ANGLE   = 53
HALF_APARATURE = np.tan( np.radians(FAN_ANGLE/2) )
    
def process_slice(img_fn, img_l_fn, threshold = 40):
    
    #threshold   =   40
    fail        = 0
    subrange    = 15
    domain      = np.arange(subrange*2, dtype=np.uint8)
    
    img     = cv2.imread(img_fn)
    img_l   = cv2.imread(img_l_fn)
    
    img_height = img.shape[0]
    img_width = img.shape[1]
    
    or_difference = cv2.absdiff(img_l, img)

    if threshold == 0:
        #first time only (calculate dynamic threshold)
        tresh_difference = cv2.cvtColor(or_difference, cv.CV_BGR2GRAY)
        
        maxval = tresh_difference.max()
        #~ print("maxval :" , maxval)
        
        threshold = int(maxval*0.4)		 #use 40% of max value as a treshold
        #~ print("Dynamic Treshold :" , threshold)
        
    # Remove differences that are smaller that [tresh] (threshold) and are just sensor noise
    ret,difference = cv2.threshold(or_difference, threshold, 255, cv2.THRESH_TOZERO)

    # Create enhanced view of the Laser line
    difference = cv2.cvtColor(difference, cv.CV_BGR2GRAY)
    
    #~ cv2.imwrite("py_gray.jpg", difference)
    
    # Max value for each column
    ind = difference.argmax(axis=0)
        
    # Declare empty position array for post process.
    line_pos = np.zeros(img_width, dtype=np.float)

    for col,value in enumerate(ind):
        #print( "({0},{1}), ".format(col, value), end="")
        if(value>0): #if column has a point to process. otherwise skip to next
            
            # Resize analysis domain if outside image size values
            if(value-subrange<=0):
                y1 = 0
            else:
                y1 = value-subrange
                
            if(value+subrange>=img_height):
                y2 = img_height
            else:
                y2 = value+subrange
            
            luminance_col = difference[y1:y2,col:col+1]
            luminance_col = np.swapaxes(luminance_col,1,0)
            
            if(domain.shape==luminance_col[0].shape):
                w_position = np.average(domain, 0, luminance_col[0])    # find index in the search domain with weighted position
                w_position = value+(w_position-subrange)                # correction of the original position
            else:
                fail += 1
                #if debug:
                #	print "Exiting subdomain in col :" + str(col) +" of slice " + str(cs) + " value:" + str(value)
                #	print "Domain:" + str(domain.shape) +" , Luminance col:" +str(luminance_col[0].shape)
                #	print "Domain resized."
                w_position = value		#keep the max luminance found since the subdomain has violated the image borders
                
            # Add the position to the empty array
            line_pos[col] = w_position

    return line_pos, threshold, img_width, img_height

def sweep_line_to_xyz(line_pos, pos, img_width, img_height):
    pass
        #-----------------------------------
        #~ if mode == "s":
            #~ #SWEEPING laser scan reconstruction
            #~ #area params:
            #~ #			start  : x0 (mm, x position)
            #~ #			end	   : x1 (mm, x position)
            #~ #			slices : linear slices

            #~ x = ((end-start)/slices*cs)+int(start)															#distance from the camera
            #~ y = float((230-z_offset)-((float(img_height)/float(2))-col)/float(mmpph))		   					#Y columns
            #~ #z=float(((img_width/2)-w_positionline_pos[col])/(img_width/2))*np.tan(30*math.pi/180)*x		#
            #~ z = x*(np.tan(np.radians(self.BETA_ANGLE)-np.arctan(((img_width/2-line_pos[col])/(img_width/2))*self.HALF_APARATURE)))

def rotary_line_to_xyz(line_pos, pos, img_width, img_height):
    # Convert to XYZ points
    tri_side = float(2*np.tan( np.radians(LASER_ANGLE) ))
    points = np.zeros(4, dtype=np.float)
    
    for col,value in enumerate(line_pos):
        if value == 0:
            continue
        
        #------------------RECONSTRUCTION----------------------------
        #-------Calculate XYZ coordinates for cloud points-----------						
        
        #ROTATIVE laser_scan reconstruction
        a_deg        = -np.radians(pos) #+=CCW,degrees/shot in radiants
        app_distance = float(abs((img_width/2)-line_pos[col])/MMPPL)		#apparent distance in pixels *mm per pixels
        ro  = float(app_distance*tri_side)
        x   = float(ro*(np.cos(a_deg)))               						#switching to cartesian
        y   = float(ro*(np.sin(a_deg)))						
        z   = col/float(MMPPH)
        
        #~ if col == 100:
            #~ print "a deg", a_deg , " app_dist:", app_distance , " ro :", ro 

        #data collected, now add to cloud
        new_point = [x,y,z,1]
        points = np.vstack([points, new_point])

    return points

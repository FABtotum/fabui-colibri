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

def process_slice2(img_fn, img_l_fn, cam_m, dist_coefs, width, height):
    fail        = 0
    subrange    = 15
    domain      = np.arange(subrange*2, dtype=np.uint8)
    
    dil         = 4
    thr2        = 12
    
    img     = cv2.imread(img_fn)
    img_l   = cv2.imread(img_l_fn)
    
    img_height = img.shape[0]
    img_width = img.shape[1]
    
    newcameramtx, roi = cv2.getOptimalNewCameraMatrix(cam_m, dist_coefs, (width,height), 1, (img_width,img_height))
    
    img     = cv2.undistort(img, cam_m, dist_coefs, None, newcameramtx)
    img_l   = cv2.undistort(img_l, cam_m, dist_coefs, None, newcameramtx)
    
    or_difference = cv2.absdiff(img_l, img)
    img_gray = cv2.cvtColor(or_difference, cv.CV_BGR2GRAY)
    
    img_hvs = cv2.cvtColor(img_l, cv2.COLOR_BGR2HSV);
    # Low intensity laser light
    r_mask = cv2.inRange(img_hvs, cv.Scalar(150, 0, 50), cv.Scalar(255, 255, 255))
    # High intensity laser light
    y_mask = cv2.inRange(img_hvs, cv.Scalar(0, 51, 209), cv.Scalar(90, 171, 255))
    
    ry_mask = cv2.bitwise_or(r_mask, y_mask)
    
    kernel = cv2.getStructuringElement(cv2.MORPH_ELLIPSE, (dil, dil) )
    mask3 = cv2.dilate( ry_mask, kernel ); 
    
    res = cv2.bitwise_and(img_gray, img_gray, mask=mask3)
    
    line_pos = np.zeros(img_height, dtype=np.float)
    
    ind = res.argmax(axis=1)
    
    for col,value in enumerate(ind):
        if value > 0 and int(res[col:col+1,value]) > thr2:
            # Resize analysis domain if outside image size values
            if(value-subrange <= 0):
                x1 = 0
            else:
                x1 = value-subrange
                
            if(value+subrange >= img_width):
                x2 = img_height
            else:
                x2 = value+subrange
            
            luminance_col = res[col:col+1,x1:x2]

            if( domain.shape == luminance_col[0].shape):
                w_position = np.average(domain, 0, luminance_col[0])
                w_position = value+(w_position-subrange)
            else:
                fail +=1
                w_position = value
            
            #cv2.circle(darw, (int(w_position), col), 2, (0,255,255), 1)
            line_pos[col] = w_position
    
    return line_pos, img_width, img_height

def rotx_matrix(a):
    a = np.radians(a)
    c1 = np.cos(a)
    c2 = np.sin(a)

    return np.matrix([
                [1, 0,    0],
                [0, c1, -c2],
                [0, c2,  c1],
                ])
                
def rotz_matrix(a):
    a = np.radians(a)
    c1 = np.cos(a)
    c2 = np.sin(a)

    return np.matrix([
                    [c1, -c2,  0],
                    [c2,  c1,  0],
                    [0,    0,  1],
                    ])
                    
def roty_matrix(a):
    a = np.radians(a)
    c1 = np.cos(a)
    c2 = np.sin(a)

    return np.matrix([
                    [c1,  0,  c2],
                    [0,   1,  0],
                    [-c2, 0,  c1],
                    ])

def scale_matrix(sx, sy, sz):
    return np.matrix([
                    [sx, 0, 0],
                    [0, sy, 0],
                    [0, 0, sz],
                    ])

def laser_line_to_xyz(line_pos, M, R, t, x_known, offset, T):
    xyz_points = None
    first = True

    RMi = R.I * M.I
    T2 = R.I * t

    y2d = 0
    for x2d in line_pos:
        if x2d != 0:
            
            uvPoint3 = np.matrix( [x2d, y2d, 1] )
            
            T1 = RMi * uvPoint3.T
            s2 = float( (x_known + T2[0]) / T1[0] )
            PP = (s2 * T1 - T2)
            
            # Correct the Z offset
            PP -= offset.T
            
            if PP[2] >= 0:
                
                PP = T * PP
                
                if first:
                    xyz_points = np.array(PP.T)
                    first = False
                else:
                    xyz_points = np.vstack([xyz_points, PP.T])
            
        y2d += 1
        
    return xyz_points
    
#~ def sweep_line_to_xyz2(line_pos, M, R, t, x_known, y_offset, z_offset, img_width, img_height):
def sweep_line_to_xyz2(line_pos, M, R, t, x_known, offset, img_width, img_height):
    
    xyz_points = None
    first = True
    
    #~ offset = np.matrix( [0, 0, z_offset] )
    
    y2d = 0
    for x2d in line_pos:
        if x2d != 0:
            
            uvPoint3 = np.matrix( [x2d, y2d, 1] )
            
            T1 = R.I * M.I * uvPoint3.T
            T2 = R.I * t
            s2 = float( (x_known + T2[0]) / T1[0] )
            PP = (s2 * T1 - T2)
            
            # Correct the Z offset
            PP -= offset.T
            
            if first:
                xyz_points = np.array(PP.T)
                first = False
            else:
                xyz_points = np.vstack([xyz_points, PP.T])
            
        y2d += 1
        
    return xyz_points

#def sweep_line_to_xyz2(line_pos, M, R, t, x_known, z_offset, y_offset, img_width, img_height):

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

def sweep_line_to_xyz(line_pos, pos, z_offset, y_offset, a_offset, img_width, img_height):
    # Convert to XYZ points
    tri_side = float(2*np.tan( np.radians(LASER_ANGLE) ))
    points = np.zeros(4, dtype=np.float)
    
    for col,value in enumerate(line_pos):
        if value == 0:
            continue

        x = pos # distance from the camera
        y = float((230-z_offset)-((float(img_height)/float(2))-col)/float(MMPPH))    # Y columns
        #z=float(((img_width/2)-w_positionline_pos[col])/(img_width/2))*np.tan(30*math.pi/180)*x
        z = x*(np.tan(np.radians(BETA_ANGLE)-np.arctan(((img_width/2-line_pos[col])/(img_width/2))*HALF_APARATURE)))

        #data collected, now add to cloud
        new_point = [x,y,z,1]
        points = np.vstack([points, new_point])

    return points

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

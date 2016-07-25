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
from __future__ import division 

__authors__ = "Daniel Kesler"
__license__ = "GPL - https://opensource.org/licenses/GPL-3.0"
__version__ = "1.0"

import triangulation
import time
import cv2, cv
import numpy as np

def to_rad(x):
    return x * np.pi / 180.0

#~ # https://picamera.readthedocs.io/en/release-1.12/fov.html#camera-modes
#~ # Camera V1
#~ fov_v1_full = [54, 41]
#~ fov_v1_partial = [54, 30.8]

#~ cam_angle = [45.0, 0.0, 0.0]

#~ # Y120 X0 Z: 180mm + 25mm
#~ cam_pos = [0.0, 120.0, 180.0+25.0]
#~ #cam_pos = [0.0, 0.0, 180.0+25.0]
#~ cam_pos = [240, 180.0+25.0, 160]
#~ cam_pos = [0, 0, 180.0+25.0]

#~ # Camera V2
#~ fov_v1 = [62.2, 48.8]



#~ print "WxH", width, height

def line(p1, p2):
    A = (p1[1] - p2[1])
    B = (p2[0] - p1[0])
    C = (p1[0]*p2[1] - p2[0]*p1[1])
    return A, B, -C

def intersection(L1, L2):
    D  = L1[0] * L2[1] - L1[1] * L2[0]
    Dx = L1[2] * L2[1] - L1[1] * L2[2]
    Dy = L1[0] * L2[2] - L1[2] * L2[0]
    if D != 0:
        x = Dx / D
        y = Dy / D
        return x,y
    else:
        return False

def eye_matrix():
    return np.matrix([
                [1, 0, 0, 0 ],
                [0, 1, 0, 0 ],
                [0, 0, 1, 0 ],
                [0, 0, 0, 1 ]
                ])


def trans_matrix(x, y, z):
    return np.matrix([
                    [1, 0, 0, x ],
                    [0, 1, 0, y ],
                    [0, 0, 1, z ],
                    [0, 0, 0, 1 ]
                    ])
                    
def rotx_matrix(a):
    #a = to_rad(cam_angle[0])
    a = to_rad(a)
    c1 = np.cos(a)
    c2 = np.sin(a)

    return np.matrix([
                [1, 0,    0,  0],
                [0, c1, -c2,  0],
                [0, c2,  c1,  0],
                [0, 0,    0,  1]
                ])
                
def rotz_matrix(a):
    #a = to_rad(cam_angle[0])
    a = to_rad(a)
    c1 = np.cos(a)
    c2 = np.sin(a)

    return np.matrix([
                    [c1, -c2,  0,  0],
                    [c2,  c1,  0,  0],
                    [0,    0,  1,  0],
                    [0,    0,  0,  1]
                    ])
                    
def roty_matrix(a):
    #a = to_rad(cam_angle[1])
    a = to_rad(a)
    c1 = np.cos(a)
    c2 = np.sin(a)

    return np.matrix([
                    [c1,  0,  c2,  0],
                    [0,   1,  0,   0],
                    [-c2, 0,  c1,  0],
                    [0,   0,  0,   1]
                    ])

def scale_matrix(sx, sy, sz):
    return np.matrix([
                    [sx, 0, 0, 0 ],
                    [0, sy, 0, 0 ],
                    [0, 0, sz, 0 ],
                    [0, 0, 0, 1 ]
                    ])

def ortogonal_matrix(w, h, n, f):\
    # http://www.codinglabs.net/article_world_view_projection_matrix.aspx
    #~ orto_width  = 200.0 # mm
    #~ orto_height = 200.0 # mm
    #~ orto_znear  = 10.0  # mm
    #~ orto_zfar   = 200.0 # mm

    c1 = 1 / w
    c2 = 1 / h
    c3 = 2 / (f-n)
    c4 = (f + n) / (f - n)

    return np.matrix([
                    [c1, 0.0, 0.0, 0.0],
                    [0.0, c2, 0.0, 0.0],
                    [0.0, 0.0, -c3, -c4 ],
                    [0.0, 0.0, 0.0, 1.0]
                    ])

def perspective_matrix(fovx, fovy, n, f):
    #persp_znear  = 10.0 # mm, r = 59mm, t = 44mm
    #persp_zfar   = 600.0 # mm

    #32.6373388942 37.6319955104

    rad_fovx   = to_rad(fovx) # 40.5
    #~ persp_fovx   = to_rad(37.6319955104) # 40.5
    rad_fovy   = to_rad(fovy) # 30.75
    #~ persp_fovy   = to_rad(32.6373388942) # 30.75
    #~ persp_fovy   = 3*persp_fovx / 4 # 30.75

    #c1 = 2.71, persp_near(160) / (object_wdith/2 @ 160)
    c1 = 1 / np.tan(rad_fovx / 2)
    #c2 = 3.63, persp_near(160) / (object_height/2 @ 160)
    c2 = 1 / np.tan(rad_fovy / 2)
    c3 = (f + n) / (f - n)
    c4 = 2*(f*n) / (f - n)

    return np.matrix([
                    [c1, 0.0,   0.0, 0.0],
                    [0.0, c2,   0.0, 0.0],
                    [0.0, 0.0,  -c3, -c4],
                    [0.0, 0.0,  -1.0, 0.0]
                    ])
                    
def project(x, y, z, model_m, cam_m, persp_m):
    m_point = np.matrix([ [x, y, z, 1.0] ])
    
    # https://open.gl/transformations#TransformationsinOpenGL
    p2 = persp_m * cam_m * model_m * m_point.T
    
    #~ p1 = cam_m * m_point.T
    #~ p2 = persp_m * p1
    
    p3 = p2.T / p2.A1[3]
    
    return (p3.A1[0], p3.A1[1])

def project_array(a, model_m, cam_m, persp_m):
    result = []
    for p in a:
        x,y = project(p[0], p[1], p[2], model_m, cam_m, persp_m)
        result.append( (x,y) )
    return result

def scale(points, lines, w, h, aratio):
    
    def quadrant(pt, w, h):
        return 0
    
    scaled = []
    point_done = []
    
    #~ ratio = float(h) / float(w)
    #~ ratio = 1
    print "ratio", aratio
        
    for p in points:
        x = int(0.5*w + w*p[0])        
        y = int( 0.5*h + w*p[1] * aratio )
        scaled.append( (x,y) )
        
    return scaled

def add_grid(x, y, z, w, h, xn, yn, color):
    points = []
    lines  = []
    
    idx = 0
    
    dx = w / xn
    dy = h / yn
    
    print "dx, dy", dx,dy
        
    for i in xrange(0, xn+1):
        nx = x + i*dx
        ny1 = y
        ny2 = y + h
        nz  = z

        points.append( [nx, ny1, nz] )
        points.append( [nx, ny2, nz] )
        
        if i == 0:
            lines.append( [idx, idx+1, (0,255,0)] )
        else:
            lines.append( [idx, idx+1, color] )
        idx += 2
    

    for i in xrange(0, yn+1):
        nx1 = x
        nx2 = x + w
        ny = y + i*dy
        nz = z

        points.append( [nx1, ny, nz] )
        points.append( [nx2, ny, nz] )
        
        if i == 0:
            lines.append( [idx, idx+1, (0,0,255)] )
        else:
            lines.append( [idx, idx+1, color] )
            
        idx += 2
    
    nz = z
    
    points.append( [x+107, y, nz] )
    points.append( [x+107, y+h, nz] )
    lines.append( [idx, idx+1, (200,255,200) ] )
    idx += 2
        
    points.append( [x, y+117, nz] )
    points.append( [x+w, y+117, nz] )
    lines.append( [idx, idx+1, (200,200,255)] )
    idx += 2
        
    return points, lines
    
def test(pos, img=0, showit = False, fovx = 54, fovy = 41, ang_x = 35, ang_y = 0, ang_z = 0, cam_x = 0, cam_y = 27, cam_z = 30, id = None, label = None):

    #~ bed_pos = [0,0,71]
    #~ image_f = cv2.imread('test_full_{0}.jpg'.format(bed_pos[2]))
    #bed_pos = [0,0,h]
    
    #tmp = cv2.imread('checker_{0}.jpg'.format(bed_pos[2]))
    #~ image_f = cv2.imread('checker_{0}.jpg'.format(bed_pos[2]))
    if img < 27:
        image_f = cv2.imread('images/test_{0}.jpg'.format(27))
    else:
        image_f = cv2.imread('images/test_{0}.jpg'.format(img))


    aratio = 1 #float(fovx) / float(fovy)
    #~ rows,cols,ch = image_f.shape
    
    #~ M = cv2.getRotationMatrix2D((cols/2,rows/2),90,1)
    #~ dst = cv2.warpAffine(image_f,M,(cols,rows))

    #~ image_f = cv2.flip(image_f, 0)

    #m_cam = roty_matrix(1) * rotz_matrix(1.3) * rotx_matrix(-45) * trans_matrix(0, 27-bed_pos[1]-50, 40)
    #~ m_cam = rotz_matrix(1) * rotx_matrix(-45) * trans_matrix(-12, 27-bed_pos[2], 142) * scale_matrix(s, s, s)
    #~ m_cam = rotx_matrix(-45) *rotz_matrix(1) * trans_matrix(-12, (27-bed_pos[2]), 142)

    m_persp = perspective_matrix(fovx, fovy, 10, 600)

    #~ m_cam = rotz_matrix(1) * trans_matrix(-5, -30, 30) * rotx_matrix(-45)
    #~ m_cam = rotz_matrix(1) * roty_matrix(0.5) * rotx_matrix(-35) * trans_matrix(-6, 27, 30)
    
    
    # TransformedVector = TranslationMatrix * RotationMatrix * ScaleMatrix * OriginalVector;
    # !!! BEWARE !!! This lines actually performs the scaling FIRST, and THEN the rotation, and THEN the translation. This is how matrix multiplication works.
    
    #~ m_cam = trans_matrix(cam_x, cam_y, cam_z) * rotz_matrix(ang_z) * roty_matrix(ang_y) * rotx_matrix(ang_x)
    #~ m_cam = trans_matrix(cam_x, cam_y, cam_z) * rotz_matrix(ang_y) * roty_matrix(ang_z) * rotx_matrix(ang_x) Kind of working
    #m_cam = rotz_matrix(ang_y) * roty_matrix(ang_z) * rotx_matrix(ang_x) * trans_matrix(cam_x, cam_y, cam_z)
    m_cam = rotz_matrix(ang_z) * roty_matrix(ang_y) * rotx_matrix(ang_x) * trans_matrix(cam_x, cam_y, cam_z)
    #m_cam = rotz_matrix(ang_z)*roty_matrix(ang_y)*rotx_matrix(ang_x)*trans_matrix(cam_x, cam_y, cam_z)
    #~ m_cam = rotx_matrix(-90) * trans_matrix(0, 1000, 1)
    
    
    #~ m_cam = roty_matrix(0) * rotx_matrix(35) * trans_matrix(-3.5,30,-30);

    px = 0
    py = 40
    pz = 0 #-5 + h - 60

    dx = 5
    dy = 5
    dz = 5

    xyz = [
        [ px,    py,  pz],
        [ px,    py,  pz+dz],
        [ px+dx, py,  pz+dz],
        [ px+dx, py,  pz],
        
        [ px,    py+dy,  pz],
        [ px,    py+dy,  pz+dz],
        [ px+dx, py+dy,  pz+dz],
        [ px+dx, py+dy,  pz],
        
        [ 0, 0, 0],
        [ 10, 0, 0],
        [ 0, 10, 0],
        [ 0, 0, 10]
    ]

    width = image_f.shape[1]
    height = image_f.shape[0]
    
    print "w x h :", width, height

    lines = [ # BGR
        # 
        #~ [4,0, (255,0,0)],
        #~ [5,1, (255,0,0)],
        #~ [6,2, (255,0,0)],
        #~ [7,3, (255,0,0)],
        
        #~ [0,1, (0,255,0)],
        #~ [1,2, (0,255,255)],
        #~ [2,3, (0,255,0)],
        #~ [3,0, (0,255,0)],
        
        #~ [4,5, (0,0,255)],
        #~ [5,6, (0,255,255)],
        #~ [6,7, (0,0,255)],
        #~ [7,4, (0,0,255)],
        # Axes
        [9, 8,  (0,0,255)],
        [10,8, (0,255,0)],
        [11,8, (255,0,0)],
        # Cross
    ]
    
    m_model = eye_matrix()
    #~ m_model = rotx_matrix(55) * trans_matrix(-3.4,131.5,pos[2]+38)
    #~ m_model = rotx_matrix(55) * trans_matrix(pos[0]-3.5-10, pos[1]+70.5, pos[2]-16) # Good @ 80
    m_model = rotx_matrix(pos[3]) * trans_matrix(pos[0], pos[1], pos[2]) # Good @ 80
    #~ m_model = rotx_matrix(66) * trans_matrix(0,0,pos[2]) #* trans_matrix(pos[0]-3.5-10, pos[1]+70.5, pos[2]-16)
    
    #~ m_model = trans_matrix(-2,6,h) * rotx_matrix(0)
    #m_model = trans_matrix(-1,-1,h) * rotx_matrix(ang)
    #~ m_model = rotx_matrix(35)
    
    
    xy = project_array(xyz, m_model, m_cam, m_persp)
    xy = scale(xy, lines, width, height, aratio)

    #~ print xy
    
    for ln in lines:
        cv2.line(image_f, xy[ ln[0] ] , xy[ ln[1] ], ln[2], 1 )
    
    w2 = int(width/2)
    h2 = int(height/2)
    
    cv2.line(image_f, ( w2, 0) , (w2, height), (255,255,255), 1 )
    cv2.line(image_f, (0, h2) , (width, h2), (255,255,255), 1 )
    
    #~ xyz, lines = add_grid(0,0,bed_pos[2], 210, 230, 21, 23, (255,255,255) )
    xyz, lines   = add_grid( 0,0,0, 230, 210, 23, 21, (100,100,100) )
    #xyz2, lines2 = add_grid( 0,0,20, 210, 230, 21, 23, (150,150,150) )
    
    #xyz += xyz2
    #lines += lines2
    
    #~ m_model = trans_matrix(-2,6,h) * rotx_matrix(0)
    #m_model = trans_matrix(0,0,h) * rotx_matrix(0)
    
    #~ m_model = eye_matrix()
    #m_model = trans_matrix(0,0,30)

    xy = project_array(xyz, m_model, m_cam, m_persp)
    xy = scale(xy, lines, width, height, aratio)
    
    for ln in lines:
        cv2.line(image_f, xy[ ln[0] ] , xy[ ln[1] ], ln[2], 1 )
    
    font = cv2.FONT_HERSHEY_SIMPLEX
    cv2.putText(image_f, 'X: {0}'.format(pos[0]),(0,20), font, 0.8,(255,255,255), 2)
    cv2.putText(image_f, 'Y: {0}'.format(pos[1]),(0,40), font, 0.8,(255,255,255), 2)
    cv2.putText(image_f, 'Z: {0}'.format(pos[2]),(0,60), font, 0.8,(255,255,255), 2)
    if label:
        cv2.putText(image_f, 'L: {0}'.format(label),(0,80), font, 0.8,(255,255,255), 2)
    
    if id:
        cv2.imwrite('out/out{0}.jpg'.format(id), image_f)
    else:
        cv2.imwrite('out/out{0}.jpg'.format(img), image_f)
    
    if showit:
        cv2.imshow('orig{0}'.format(img), image_f)
        cv2.waitKey(0)
        cv2.destroyAllWindows()
#~ -6.5 + 
#~ for i in xrange(70, 170):
    #~ test(i)

#~ for i in xrange(70, 160):
    #~ test(i, showit=False, ang_x=55, ang_y=0, ang_z=0, cam_x=-4, cam_y=-30, cam_z=i-27, id=i)

#~ test(120, showit=True, ang_x=34, cam_z = 27)


#~ for i in xrange(70, 160):
    #~ test(i, showit=False, ang_x=55, ang_y=0, ang_z=0, cam_x=0, cam_y=i-27, cam_z=30)


#~ i=63
#~ test(i, showit=True, fovx=54, fovy=41 , ang_x=55, ang_y=0, ang_z=1, cam_x=-1.5, cam_y=0, cam_z=60, id=idx)

#~ x = 88
#~ y = 60
#~ z = 199
#~ test([x, y, z], showit=s, fovx=41, fovy=54, ang_x=35, ang_y=0, ang_z=0, cam_x=-y+19, cam_y=0, cam_z=0, id=idx, label=i)
#~ m_model = rotx_matrix(55) * trans_matrix(pos[0]-3.5-10, pos[1]+70.5, pos[2]-16) # Good @ 80

#~ test([-3.5-10, +70.5, 80-16, 55], img=80, showit=True, fovx=55, fovy=55, ang_x=0, ang_y=0, ang_z=0.5, cam_x=0, cam_y=0, cam_z=0)
test([0, 0, 0, 52], img=80, showit=True, fovx=53, fovy=41, ang_x=0, ang_y=0.0, ang_z=0.0, cam_x=-3.5, cam_y=-0.5, cam_z=120)
exit()

idx = 0

#~ for i in xrange(27, 200, 2):
    #~ s=False
for i in xrange(199, 200):
    s=True
    #~ test(i, showit=s, ang_x=35, ang_y=1, ang_z=0, cam_x=-4.5, cam_y=52, cam_z=27, id=idx) Almost there

    #~ x = 88
    #~ y = 60
    #~ z = i
    x = 0
    y = 0  
    z = i
    #~ test([88, 60, i], showit=s, fovx=60, ang_x=40, ang_y=-1.3, ang_z=1, cam_x=-y+11.5, cam_y=31.5, cam_z=27, id=idx)
    test([x, y, i], img=i, showit=s, fovx=55, fovy=55, ang_x=0, ang_y=0, ang_z=0.5, cam_x=0, cam_y=0, cam_z=0, id=idx, label=i)
    idx += 1

    #32.6373388942 37.6319955104
    # 40.5 30.75

#!/bin/env python

import cv2, cv
import numpy as np
import json
from cv3d_utils import Camera, Cube, Grid

pattern_size = (8,6)

def drawPoints(img, corners, object_points, color = (0,0,255)):
    idx = 0
    for c in corners:
        x = int(c[0])
        y = int(c[1])
        
        font = cv2.FONT_HERSHEY_SIMPLEX
        p = object_points[idx]
        #~ cv2.putText(img, '{0}'.format(idx),(x,y), font, 0.3,(0,0,255), 1)
        #~ cv2.putText(img, '({0},{1})'.format(p[0], p[1]),(x,int(y+15)), font, 0.3,(255,0,255), 1)
        cv2.circle(img, (x,y), 2, color, 1 )
        
        idx += 1

size = pattern_size[1] * pattern_size[0]
object_points = range(0, size)
idx = 0

x = 13
y = 15

for i in range(0, pattern_size[1] ):
    for j in range(0, pattern_size[0] ):
        #print i,j, idx
        object_points[idx] = [ x-i,y-j, 0]
        
        idx += 1
        
#~ for idx in xrange(122,219,2):
#~ for idx in [130,158,194,218]:

layers = []
colors = [(255,0,0), (0,255,255), (0,255,0), (0,0,255)]

def new_layer(corners, idx, object_points):
    obj2d = []
    obj3d = []
    z3d = idx
    for j in xrange(0, len(corners)):
        x2d = corners[j][0][0]
        y2d = corners[j][0][1]
        
        obj2d.append( [x2d, y2d] )
        
        x3d = object_points[j][0]
        y3d = object_points[j][1]

        obj3d.append( [x3d, y3d, z3d] )
        
    return [obj2d, z3d, obj3d]

for idx in [130,158,194,218]:
#~ for idx in [218]:

    img = cv2.imread('images/test_{0}.jpg'.format(idx))
    h,  w = img.shape[:2]

    json_f = open('output/calibration.json')
    cal = json.load(json_f)
    
    mtx = np.array( cal['matrix'] )
    dist = np.array( cal['coefs'] )
    roi = cal['roi']
    
    
    #fovx, fovy, focalLength, principalPoint, aspectRatio = cv2.calibrationMatrixValues(newcameramtx, (w, h), 3.67,2.74)
    newcameramtx, roi=cv2.getOptimalNewCameraMatrix(mtx,dist,(w,h),1,(w,h))
    img = cv2.undistort(img, mtx, dist, None, newcameramtx)

    found, corners = cv2.findChessboardCorners(img, pattern_size)
    
    #cv2.drawChessboardCorners(img, pattern_size, corners, found)
    
    layers.append( new_layer(corners, idx, object_points) )
    
    print idx, found
    #print corners
    #~ drawPoints(img, corners, object_points)

    #~ cv2.imshow('result', img)
    #~ cv2.waitKey(0)
    #~ cv2.destroyAllWindows()

#~ img = cv2.imread('images/test_{0}.jpg'.format(218))

obj2d_points = []
obj3d_points = []

l = len(layers)
for i in xrange(3, 4):
    corners = layers[i][0]
    z3d = layers[i][1]
    drawPoints(img, corners, object_points, colors[i] )
    
    for j in xrange(0, len(corners)):
        x2d = corners[j][0]
        y2d = corners[j][1]
        
        obj2d_points.append( [x2d, y2d] )
        
        x3d = object_points[j][0]
        y3d = object_points[j][1]
        

        obj3d_points.append( [x3d, y3d, z3d] )

data = {
    'obj3d': np.float32(obj3d_points).tolist(), 
    'obj2d': np.float32(obj2d_points).tolist()
    }

with open('obj3dpoints.json', 'w') as f:
    f.write( json.dumps(data) )

dist_coef = np.zeros(4)

verts = np.float32(obj3d_points)

retval, rvec, tvec = cv2.solvePnP( verts, np.float32(obj2d_points), mtx, dist_coef)
#~ rvec, tvec, inliners = cv2.solvePnPRansac( verts, np.float32(obj2d_points), mtx, dist_coef)
#~ dist_coef = np.zeros(4)
#~ ret, rvec, tvec = cv2.solvePnP(quad_3d, tracked.quad, K, dist_coef)
#~ verts = ar_verts * [(x1-x0), (y1-y0), -(x1-x0)*0.3] + (x0, y0, 0)

#~ print "retval",retval
print "rvec",rvec
print "tvec",tvec

print "verts-len:", len(verts)

verts = cv2.projectPoints(verts, rvec, tvec, mtx, dist_coef)[0].reshape(-1, 2)

print "verts-len:", len(verts)

idx = 0
for v in verts:
    x = v[0]
    y = v[1]

    cv2.circle(img, (x,y), 10, (255,255,255), 1 )
    font = cv2.FONT_HERSHEY_SIMPLEX
    cv2.putText(img, '{0}'.format(idx),(x,y), font, 0.3,(0,0,255), 1)
    #cv2.putText(img, '({0},{1})'.format(p[0], p[1]),(x,int(y+15)), font, 0.3,(255,0,255), 1)
    idx += 1
    
cv2.imshow('result', img)
cv2.imwrite('result.png', img)
cv2.waitKey(0)
cv2.destroyAllWindows()

#print obj3d_points

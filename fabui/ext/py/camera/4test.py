#!/bin/env python

import cv2, cv
import numpy as np
import json
from cv3d_utils import Camera, Cube, Grid, eye_matrix

#~ rvec = np.array([
    #~ -2.95813073,
    #~ -0.02420302,
    #~ -1.04764347,
    #~ ])
#~ tvec = np.array([
    #~ -147.00105384,
      #~ 10.16630114,
     #~ 186.48746448,
    #~ ])

json_f = open('rpi_cam_v1_3.json')
cal = json.load(json_f)

json_f = open('obj3dpoints.json')
obj = json.load(json_f)

json_f = open('matrix.json')
matrix = json.load(json_f)

#print np.degrees(rvec)
#print tvec

cam_m = np.matrix( matrix['cam_mat'], dtype=float )
tvec = np.array( matrix['tvec'], dtype=float )
rvec = np.array( matrix['rvec'], dtype=float )
mtx = np.array(cal['matrix'], dtype=float)
dist_coef = np.array(cal['coefs'], dtype=float)

#~ img_orig = cv2.imread('images/test_218.jpg')
img_orig = cv2.imread('preview2.jpg')
h,  w = img_orig.shape[:2]

newcameramtx, roi=cv2.getOptimalNewCameraMatrix(mtx, dist_coef, (w,h), 1, (w,h))
img_orig = cv2.undistort(img_orig, mtx, dist_coef, None, newcameramtx)

#~ print cam_m
#~ print "tvec",tvec
#~ print "rvec",rvec

##########
'''
idx (x,y): 0 (671.238647461,313.621063232)
idx (x,y): 7 (664.870117188,682.154907227)
idx (x,y): 16 (586.25970459,304.421203613)
idx (x,y): 24 (540.307800293,299.416351318)
idx (x,y): 40 (440.391998291,288.480010986)
idx (x,y): 47 (433.044342041,710.03125)

idx (x,y): 0 (537.472351074,470.131561279)
idx (x,y): 1 (613.931274414,472.373596191)

'''

rotM, _ = cv2.Rodrigues(rvec)
#~ print "rotM", rotM

# @24
tgt = 0
u = 613
v = 472
laser_x = 100
laser_z = 218
laser_y = 120
object_h = 22

object_z = laser_z
#~ print "laser ({0}, {1}, {2})".format(laser_x, laser_y, laser_z)
#~ print "object_z", object_z

uvPoint = np.matrix([u, v, 1])


cameraMatrix = np.asmatrix(mtx, dtype=float)
rotationMatrix = np.asmatrix(rotM, dtype=float)

tempMat = rotationMatrix.I * cameraMatrix.I * uvPoint.T
tempMat2 = rotationMatrix.I * tvec
#~ print "tempMat", tempMat
#~ print "tempMat2", tempMat2

s = float(object_z) + tempMat2[2][0]
s /= tempMat[2][0]

P = rotationMatrix.I * np.multiply(s,cameraMatrix.I)  * uvPoint.T - tvec

######################

tmp1 = np.hstack( (rotationMatrix, tvec) )
tranformationMatrix = np.vstack( (tmp1, [0, 0, 0, 1]) )
tmp1 = np.hstack( (cameraMatrix, np.matrix([0,0,0]).T ) )
cameraMatrix = np.vstack( (tmp1, [0,0,0,1] ) )
MR = cameraMatrix * tranformationMatrix
#######################################

#~ sPP = cameraMatrix * (rotationMatrix * uvPoint.T + tvec)
# idx (x,y): 1 (613.931274414,472.373596191) = (100,120,196)
uvPoint = np.matrix([613, 472, 1, 1])
xyzPoint = np.matrix([100, 120, 196, 1])

# Direct
sPP = MR * xyzPoint.T
s = sPP[2]
PP = sPP / s

uvPoint = PP.T
print "3d -> 2d: {0} -> {1}".format( xyzPoint, np.int32(uvPoint) )

# Inverse
tmp1 = MR.I * uvPoint.T
#~ print "tmp1", tmp1
s2 = xyzPoint.T[0] / tmp1[0]
print "s2:",s2
tmp1 *= s2

print "2d -> 3d: {0} -> {1}".format(np.int32(uvPoint), np.int32(tmp1.T) )

#~ print "s:", sPP[2]
#~ print "s2:", s2
#~ print "sPP:", sPP
#~ print "PP:", PP
cv2.circle(img_orig, (PP[0], PP[1]), 6, (255,0,255), 2 )
cv2.circle(img_orig, (PP[0], PP[1]), 7, (0,255,0), 1 )

#~ print "uvPoint:", uvPoint.T
#~ print "P:", P
'''
cv::Mat uvPoint = cv::Mat::ones(3,1,cv::DataType<double>::type); //u,v,1
uvPoint.at<double>(0,0) = 363.; //got this point using mouse callback
uvPoint.at<double>(1,0) = 222.;
cv::Mat tempMat, tempMat2;
double s;
tempMat = rotationMatrix.inv() * cameraMatrix.inv() * uvPoint;
tempMat2 = rotationMatrix.inv() * tvec;
s = 285 + tempMat2.at<double>(2,0); //285 represents the height Zconst
s /= tempMat.at<double>(2,0);
std::cout << "P = " << rotationMatrix.inv() * (s * cameraMatrix.inv() * uvPoint - tvec) << std::endl;
'''

##########

cam_m = eye_matrix()

#~ grid = Grid( 200, 200, 20, 20, (0,255,255), mx = 100, my = 100, width = 2 )
#~ grid.draw(img_orig, cam_m)

#~ obj3d_points = obj['obj3d']


obj3d_points = [  [laser_x, laser_y, laser_z], [laser_x, laser_y, laser_z-object_h] ]

verts = np.float32(obj3d_points)
verts = cv2.projectPoints(verts, rvec, tvec, mtx, dist_coef)[0].reshape(-1, 2)

idx = 0
for v in verts:
    x = v[0]
    y = v[1]

    #~ if tgt == idx:
        #~ cv2.circle(img_orig, (x,y), 5, (0,255,255), 2 )
    #~ else:
    cv2.circle(img_orig, (x,y), 3, (255,255,255), 1 )
    font = cv2.FONT_HERSHEY_SIMPLEX
    #~ cv2.putText(img_orig, '{0}'.format(idx),(x,y), font, 0.3,(0,0,255), 1)
    #cv2.putText(img, '({0},{1})'.format(p[0], p[1]),(x,int(y+15)), font, 0.3,(255,0,255), 1)
    
    print "idx (x,y): {0} ({1},{2}) = ({3},{4},{5})".format(idx, x, y, obj3d_points[idx][0], obj3d_points[idx][1], obj3d_points[idx][2])
    
    idx += 1

#~ cv2.imshow('result', img_orig)
#~ cv2.waitKey(0)
#~ cv2.destroyAllWindows()

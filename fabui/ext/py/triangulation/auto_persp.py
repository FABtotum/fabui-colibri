#!/bin/env python

import cv2, cv
import numpy as np

#~ img = cv2.imread('1original.jpg',0)

#~ src = np.array([[50,50],[450,450],[70,420],[420,70]],np.float32)
#~ dst = np.array([[0,0],[299,299],[0,299],[299,0]],np.float32)

#~ ret = cv2.getPerspectiveTransform(src,dst)
#~ print ret

im3 = cv2.imread('checker_180.jpg')
chessboard_dim = (7, 7)

tmp = []
for h in range(chessboard_dim[0]):
    for w in range(chessboard_dim[1]):
        tmp.append((float(h), float(w), 0.0))
objectPoints = np.array(tmp)
#print objectPoints
#found_all, corners = cv2.findChessboardCorners(im3, chessboard_dim)

#print found_all, corners, chessboard_dim

# Compute matrices
_found_all, _corners = cv2.findChessboardCorners(im3, chessboard_dim, flags=cv.CV_CALIB_CB_ADAPTIVE_THRESH | cv.CV_CALIB_CB_FILTER_QUADS)
cv2.drawChessboardCorners(im3, chessboard_dim, _corners, _found_all)

#~ _found_all, _corners = cv2.findChessboardCorners(im3, chessboard_dim, flags=cv.CV_CALIB_CB_ADAPTIVE_THRESH | cv.CV_CALIB_CB_FILTER_QUADS)
#~ #cv2.drawChessboardCorners(im3, chessboard_dim, _corners, _found_all)
#~ retval, cameraMatrix, distCoeffs, rvecs, tvecs = cv2.calibrateCamera([objectPoints.astype('float32')], [_corners.astype('float32')], im3.shape, np.eye(3), np.zeros((5, 1)))
#~ fovx, fovy, focalLength, principalPoint, aspectRatio = cv2.calibrationMatrixValues(cameraMatrix, im3.shape, 1.0, 1.0)


imageSize = (im3.shape[0], im3.shape[1])

retval, cameraMatrix, distCoeffs, rvecs, tvecs = cv2.calibrateCamera (
        [objectPoints.astype('float32')],
        [_corners.astype('float32')],
        imageSize,
        np.eye(3), 
        np.zeros((5, 1))
        )
        #~ _corners.astype('float32'),
        #~ imageSize=im3.shape)
fovx, fovy, focalLength, principalPoint, aspectRatio = cv2.calibrationMatrixValues(cameraMatrix, imageSize, 1.0, 1.0)

print fovx, fovy, focalLength, principalPoint, aspectRatio

#~ print cameraMatrix

cv2.imshow('orig', im3)
cv2.waitKey(0)
cv2.destroyAllWindows()

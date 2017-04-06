#!/bin/env python
# -*- coding: utf-8; -*-
#
# Source code is an adaptation of <opencv>/samples/python/calibrate.py
#

# Python 2/3 compatibility
from __future__ import print_function

# Import standard python module
import argparse
import os
import json

# Import external modules
import numpy as np
import cv2

# Import internal modules
from fabtotum.utils import makedirs
   
def splitfn(fn):
    path, fn = os.path.split(fn)
    name, ext = os.path.splitext(fn)
    return path, name, ext
   
def main(img_mask, debug_dir = None, output_file = 'intrinsic.json', square_size = 10, pattern_size=(7,7) ):
    #~ import sys
    #~ import getopt
    from glob import glob

    #~ args, img_mask = getopt.getopt(sys.argv[1:], '', ['debug=', 'square_size='])
    #~ args = dict(args)
    #~ args.setdefault('--debug', './output/')
    #~ args.setdefault('--square_size', 1.0)

    img_names = glob(img_mask)
    #~ debug_dir = args.get('--debug')
    if debug_dir:
        if not os.path.isdir(debug_dir):
            os.mkdir(debug_dir)
    
    #~ square_size = float(args.get('--square_size'))

    #pattern_size = (7, 7)
    pattern_points = np.zeros((np.prod(pattern_size), 3), np.float32)
    pattern_points[:, :2] = np.indices(pattern_size).T.reshape(-1, 2)
    pattern_points *= square_size

    obj_points = []
    img_points = []
    
    h, w = 0, 0
    img_names_undistort = []
    for fn in img_names:
        print('processing %s... ' % fn, end='')
        img = cv2.imread(fn, 0)
        if img is None:
            print("Failed to load", fn)
            continue

        h, w = img.shape[:2]
        height,  width = h, w
        found, corners = cv2.findChessboardCorners(img, pattern_size)
        if found:
            term = (cv2.TERM_CRITERIA_EPS + cv2.TERM_CRITERIA_COUNT, 30, 0.1)
            cv2.cornerSubPix(img, corners, (5, 5), (-1, -1), term)

        if debug_dir:
            vis = cv2.cvtColor(img, cv2.COLOR_GRAY2BGR)
            cv2.drawChessboardCorners(vis, pattern_size, corners, found)
            path, name, ext = splitfn(fn)
            outfile = os.path.join(debug_dir, name + '_chess.png')
            cv2.imwrite(outfile, vis)
            if found:
                img_names_undistort.append(outfile)

        if not found:
            print('chessboard not found')
            continue

        img_points.append(corners.reshape(-1, 2))
        obj_points.append(pattern_points)

        print('ok')

    # calculate camera distortion
    rms, camera_matrix, dist_coefs, rvecs, tvecs = cv2.calibrateCamera(obj_points, img_points, (w, h), None, None)

    print("\nRMS:", rms)
    print("camera matrix:\n", camera_matrix)
    print("distortion coefficients: ", dist_coefs.ravel())

    # undistort the image with the calibration
    print('')
    for img_found in img_names_undistort:
        img = cv2.imread(img_found)

        h,  w = img.shape[:2]
        newcameramtx, roi = cv2.getOptimalNewCameraMatrix(camera_matrix, dist_coefs, (w, h), 1, (w, h))

        dst = cv2.undistort(img, camera_matrix, dist_coefs, None, newcameramtx)

        # crop and save the image
        x, y, w, h = roi
        #dst = dst[y:y+h, x:x+w]
        outfile = img_found + '_undistorted.png'
        print('Undistorted image written to: %s' % outfile)
        cv2.imwrite(outfile, dst)

    cv2.destroyAllWindows()
    
    w, h = width, height
    newcameramtx, roi = cv2.getOptimalNewCameraMatrix(camera_matrix, dist_coefs, (w, h), 1, (w, h))
    
    data = {
        'matrix'        : camera_matrix.tolist(),
        'dist_coefs'    : dist_coefs.tolist(),
        'roi'           : roi,
        'width'         : width,
        'height'        : height
    }
    
    with open(output_file, 'w') as f:
        f.write( json.dumps(data) )
        
    print("Calibration parameters saved to '{0}'".format(output_file) )

if __name__ == '__main__':
    parser = argparse.ArgumentParser(formatter_class=argparse.ArgumentDefaultsHelpFormatter)
    parser.add_argument("-f", "--files",    help="Sample files glob pattern.",  default='samples/*.jpg' )
    parser.add_argument("-d", "--debug",    help="Debug output directory.",     default='' )
    parser.add_argument("-o", "--output",   help="Calibration output file.",    default='intrinsic.json' )
    parser.add_argument("--lang",           help="Output language", 			default='en_US.UTF-8' )
    
    args = parser.parse_args()
    
    pattern     = args.files
    debug_dir   = args.debug
    output_file = args.output
    lang			= args.lang
    
    main(pattern, debug_dir, output_file)

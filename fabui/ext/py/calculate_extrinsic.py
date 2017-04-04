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
import argparse
import time
import os
import gettext
import json
from threading import Event, Thread

# Import external modules
from picamera import PiCamera
import numpy as np
import cv2, cv

# Import internal modules
from fabtotum.fabui.config  import ConfigService
from fabtotum.fabui.gpusher import GCodePusher

# Set up message catalog access
tr = gettext.translation('calibration', 'locale', fallback=True)
_ = tr.ugettext

class Extrinsic(GCodePusher):
    def __init__(self, log_trace, monitor_file, scan_dir, output_file, width, height, rotation):
        super(Extrinsic, self).__init__(log_trace, monitor_file)

        self.camera = PiCamera()
        self.camera.resolution  = (width, height)
        self.camera.rotation    = rotation
        self.progress           = 0.0
        
        self.scan_dir           = scan_dir
        self.output_file        = output_file

    def trace(self, msg):
        print msg

    def get_progress(self):
        """ Custom progress implementation """
        return self.progress

    def take_a_picture(self, resolution = None):
        """ Camera control wrapper """
        scanfile = os.path.join(self.scan_dir, "sample_{0}x{1}.png".format(resolution[0], resolution[1]) )
        if resolution:
            self.camera.resolution = resolution
        self.camera.capture(scanfile, quality=100)
        
        return scanfile
    
    def check_projection(self, point2d, point3d, M, R, t, width, height, error_margin2d = 4.0, error_margin3d = 1.0):
        uvPoint3 = np.matrix( np.round( point2d + [1] ) )
        xyzPoint3 = np.matrix( point3d )
        
        ew = width / 300.0
        eh = height / 300.0
        
        error_margin2d = max(ew, eh, 4.0)
        
        # 3D to 2D projection
        PP2d = M * ( R*xyzPoint3.T + t)
        s1 = float(PP2d[2])
        PP2d /= s1
        
        err = False
        
        ex = abs( float(PP2d[0]) - float(uvPoint3.T[0]) )
        
        if ex > error_margin2d:
            err = True
            print "x: {0} - {1} = {2} [{3}]".format(float(PP2d[0]), float(uvPoint3.T[0]), ex, error_margin2d)
        
        ey = abs( float(PP2d[1]) - float(uvPoint3.T[1]) )
        if ey > error_margin2d:
            err = True
            print "y: {0} - {1} = {2} [{3}]".format(float(PP2d[1]), float(uvPoint3.T[1]), ey, error_margin2d)
            
        if err:
            print "Warning: (2D->3D): {0} {1}".format(PP2d.T, uvPoint3)
            return False
        
        print "(2D->3D): error-margin (x,y): {0}, {1}".format(ex, ey)
        
        #~ PP2d = np.round(PP2d)
        
        # 2D to 3D projection
        x_known = xyzPoint3.T[0]
        T1 = R.I * M.I * PP2d
        T2 = R.I * t
        s2 = float( (x_known + T2[0]) / T1[0] )
        PP = (s2 * T1 - T2)
        
        err = False
        
        ex = abs( PP[0] - xyzPoint3.T[0] )
        if ex > error_margin3d:
            err = True
        
        ey = abs( PP[1] - xyzPoint3.T[1] )
        if ey > error_margin3d:
            err = True
        
        ez = abs( PP[2] - xyzPoint3.T[2] )
        if ez > error_margin3d:
            err = True
        
        print "(3D->2D): error-margin (x,y,z): {0}, {1}, {2}".format(ex, ey, ez)
        
        if err:
            print "Warning: (3D->2D): {0} {1} [{2}/{3}]".format(PP.T, xyzPoint3, float(s1), float(s2))
            print "       * {0}".format(PP.T - xyzPoint3)
            return False
            
        return True
    
    def calculate_extrinsic(self, fn, intrinsic, output_file, x, y, z, x_offset, y_offset, z_offset):
        
        z_offset = z # this one bas bed heigh correction
        
         # Chessboard size 
        pattern_size = (8,6)
        #~ pattern_size = (6,8)
        # Square size in mm
        square_size  = (10,10)
        
        obj3d_points = []
        obj2d_points = []
        
        cam_m       = np.matrix( intrinsic['matrix'], dtype=float )
        dist_coefs  = np.matrix( intrinsic['dist_coefs'], dtype=float )
        width       = int(intrinsic['width'])
        height       = int(intrinsic['height'])
        
        img = cv2.imread(fn)
        h,  w = img.shape[:2]

        print "get new matrix"
        newcameramtx, roi = cv2.getOptimalNewCameraMatrix(cam_m, dist_coefs, (width,height), 1, (w,h))
        
        print "newcameramtx",newcameramtx
        
        img = cv2.undistort(img, cam_m, dist_coefs, None, newcameramtx)
        cv2.imwrite('undistort.jpg', img)
        
        print "find chessboard"
        found, corners = cv2.findChessboardCorners(img, pattern_size)

        # Create 2d points
        if found:
            self.trace( _('Find chessboard: SUCCESS.') )
            for i in xrange(0, len(corners)):
                x2d = corners[i][0][0]
                y2d = corners[i][0][1]
                obj2d_points.append( [x2d, y2d] )
                cv2.circle(img, (x2d,y2d), 3, (0,0,255), 1 )
                font = cv2.FONT_HERSHEY_SIMPLEX
                cv2.putText(img, '{0}'.format(i),(x2d,y2d), font, 0.3,(0,0,255), 1)
                cv2.imwrite('found_chess.jpg', img)
        else:
            self.trace( _('Find chessboard: FAILED.') )
            return False

        cx = x - (pattern_size[0]-1)*square_size[1]
        cy = y - square_size[1]

        # Create 3d points
        for j in xrange(0, pattern_size[1] ):
            for i in xrange(0, pattern_size[0] ):
                x3d = cx+i*square_size[0]
                y3d = cy-j*square_size[1]
                z3d = z
                obj3d_points.append( [ x3d, y3d, z3d] )
            
        verts3d = np.float32(obj3d_points)
        verts2d = np.float32(obj2d_points)
        
        # Image has been undistorted already so no need to take care of it anymore
        dist_coef = np.zeros(4)
        
        print "solve pose"
        
        retval, rvec, tvec = cv2.solvePnP( verts3d, verts2d, newcameramtx, dist_coefs)
        
        if retval:
            self.trace( _('Pose estimation: SUCCESS.') )
        else:
            self.trace( _('Pose estimation: FAILED.') )
            return False
        
        #~ print "project points"
        verts = cv2.projectPoints(verts3d, rvec, tvec, newcameramtx, dist_coef)[0].reshape(-1, 2)
        
        idx = 0
        for v in verts:
            x = v[0]
            y = v[1]

            cv2.circle(img, (x,y), 10, (255,255,255), 1 )
            font = cv2.FONT_HERSHEY_SIMPLEX
            cv2.putText(img, '{0}'.format(idx),(x,y), font, 0.3,(0,0,255), 1)
            #cv2.putText(img, '({0},{1})'.format(p[0], p[1]),(x,int(y+15)), font, 0.3,(255,0,255), 1)
            idx += 1
        
        print "rotation matrix"
        rotM, jacobian = cv2.Rodrigues(rvec)
        
        # http://docs.opencv.org/2.4/modules/calib3d/doc/camera_calibration_and_3d_reconstruction.html
        # s*uvP = A * [R|t] * xyzP
        # uvP = [u v 1].T
        # xyz = [x y z 1].T
        
        cmatrix = np.asmatrix(newcameramtx)
        rmatrix = np.asmatrix(rotM)
        
        M33 = cmatrix
        R33 = rmatrix
        MR33 = M33 * R33
        Rt34 = np.hstack( (R33, tvec) )
        MRt34 = M33 * Rt34
        
        Rt44 = np.vstack( (Rt34, [0, 0, 0, 1]) )
        M43 = np.hstack( (M33, np.matrix([0,0,0]).T ) )
        M44 = np.vstack( (M43, [0, 0, 0, 1]) )
        MRt44 = M44 * Rt44
        
        Rit = R33.I * tvec
        
        label = "{0}x{1}".format(w, h)
        data = {}

        if os.path.exists(output_file):
            json_f = open(output_file)
            data = json.load(json_f)
        
        data[label] = {
            'offset'    : [x_offset, y_offset, z_offset],
            'dist_coef' : dist_coef.tolist(),
            'M33'       : cmatrix.tolist(),
            'R33'       : rmatrix.tolist(),
            'R33_invt'  : Rit.tolist(),
            'MRt34'     : MRt34.tolist(),
            'MRt34_inv' : MRt34.I.tolist(),
            'r'         : rvec.tolist(),
            't'         : tvec.tolist(),
            'width'     : w,
            'height'    : h,
            'roi'       : roi
        }
        
        # Check parameter validity
        for idx in xrange(0, len(obj2d_points)):
            retval = self.check_projection(obj2d_points[idx], obj3d_points[idx], M33, R33, tvec, w, h)
            if not retval:
                self.trace( _('Parameter validation: FAILED.') )
                return False
                
        self.trace( _('Parameter validation: PASSED.') )
        
        with open(output_file, 'w') as f:
            f.write( json.dumps(data) )
        self.trace( _('Parameter saved to "{0}".'.format(output_file)) )
        
        return True
        
    def run(self, task_id, intrinsic_file, x_offset, y_offset, z_offset, base_height):
        
        self.prepare_task(task_id, task_type='capture')
        self.set_task_status(GCodePusher.TASK_RUNNING)
        
        self.send("G27")
        self.send("M700 S200")
        
        self.send("M701 S255")
        self.send("M702 S255")
        self.send("M703 S255")
        
        self.send("G0 X{0} Y{1} Z{2} F5000".format(x_offset, y_offset, z_offset) )
        self.camera.start_preview()
        
        raw_input("Press Enter to continue..." )
        self.send("M400")
        self.send("M700 S0")

        
        json_f = open(intrinsic_file)
        intrinsic_data = json.load(json_f)
        
        self.trace( _('Loaded intrinsic parameters from "{0}".'.format(intrinsic_file)) )
        
        for label in intrinsic_data.keys():
            w = intrinsic_data[label]['width']
            h = intrinsic_data[label]['height']
            
            fn = self.take_a_picture( resolution=(w,h) )
            
            self.trace( _('{0} x {1}'.format(w,h) ) )
            
            self.trace( _('Calculation started.') )
            # Because chessboard base raises the image for base_height, the real z_offset should be reduced by it 
            # as the Z axis decreses the higher you go
            # x=13cm, y=15cm is the reference point of the upper right corner
            retval = self.calculate_extrinsic(fn, intrinsic_data[label], self.output_file, 110, 150, z_offset-base_height, x_offset, y_offset, z_offset)
            if not retval:
                self.trace( _('Calculation for {0} failed.'.format(label) ) )
            else:
                self.trace( _('Calculation for {0} successful.'.format(label) ) )
            
        self.camera.stop_preview()
        
        self.send("M701 S0")
        self.send("M702 S0")
        self.send("M703 S0")
        self.send('M300')
        #self.trace( _('Calculation finished.') )
        self.set_task_status(GCodePusher.TASK_COMPLETED)
        
        self.stop()
        
def main():
    config = ConfigService()

    # SETTING EXPECTED ARGUMENTS
    destination = config.get('general', 'bigtemp_path')
    
    # SETTING EXPECTED ARGUMENTS
    parser = argparse.ArgumentParser(formatter_class=argparse.ArgumentDefaultsHelpFormatter)
    parser.add_argument("task_id",          help="Task ID." )
    parser.add_argument("-d", "--dest",     help="Destination folder."),      default=destination )
    parser.add_argument("-i", "--intrinsic",help="Intrinsic camera parameters.", default='intrinsic.json' )
    parser.add_argument("-o", "--output",   help="Extrinsic camera parameters.", default='extrinsic.json' )
    parser.add_argument("-W", "--width",    help="Image width in pixels.",   default=1296)
    parser.add_argument("-H", "--height",   help="Image height in pixels.",  default=972)
    parser.add_argument("-r", "--rotation", help="Image rotation.",          default=0)
    parser.add_argument("-x", "--x-offset", help="X offset.",                default=104)
    parser.add_argument("-y", "--y-offset", help="Y offset.",                default=117)
    parser.add_argument("-z", "--z-offset", help="Z offset.",                default=220)
    parser.add_argument("-b", "--base-height", help="Chessboard height of it's base.", default=0)
    
    
    # GET ARGUMENTS    
    args = parser.parse_args()
    
    # INIT VARs
    task_id         = int(args.task_id)
    destination     = args.dest
    intrinsic_file  = args.intrinsic
    monitor_file    = config.get('general', 'task_monitor') # TASK MONITOR FILE (write stats & task info, ex: temperatures, speed, etc
    log_trace       = config.get('general', 'trace')        # TASK TRACE FILE 
    width           = int(args.width)
    height          = int(args.height)
    rotation        = int(args.rotation)
    x_offset        = float(args.x_offset)
    y_offset        = float(args.y_offset)
    z_offset        = float(args.z_offset)
    base_height     = float(args.base_height)
    output_file     = args.output
    
    output_dir      = destination

    if not os.path.exists(output_dir):
        makedirs(output_dir)
    
    app = Extrinsic(log_trace,
                  monitor_file, 
                  output_dir,
                  output_file,
                  width=width,
                  height=height,
                  rotation=rotation)

    app_thread = Thread( 
            target = app.run, 
            args=( [task_id, intrinsic_file, x_offset, y_offset, z_offset, base_height] ) 
            )
    app_thread.start()
    #~ app.calculate_extrinsic('sample.png', x =130, y=150, z=z_offset)

    # app.loop() must be started to allow callbacks
    app.loop()
    app_thread.join()


if __name__ == "__main__":
    main()
        

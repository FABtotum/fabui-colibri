#!/bin/env python

import cv2, cv
import numpy as np
import json

from cv3d_utils import Camera, Cube, Grid

img_orig = cv2.imread('capture.jpg')

img_draw = img_orig

width = img_orig.shape[1]
height = img_orig.shape[0]

cx = int(width/2.0)
cy = int(height/2.0)



class Interactive:
    def __init__(self, filename):
        self.cx = 0
        self.cy = 0
        
        self.wname = 'Interactive'
        self.wname2 = 'Controls'
        self.wname3 = '3D Controls'
        
        self.img_orig = cv2.imread(filename)
        self.width = self.img_orig.shape[1]
        self.height = self.img_orig.shape[0]

        self.cx = int(self.width/2.0)
        self.cy = int(self.height/2.0)
        #~ self.fx = 1.26538737e+03
        #~ self.fy = 1.26227835e+03
        
        self.fx = 2000-1265
        self.fy = 2000-1262
        
        self.k1 = 50
        self.k2 = 100
        self.p1 = 50
        self.p2 = 50
        
        self.img = 84
        self.x = 400
        self.y = 400
        self.z = 800
        self.rx = 180
        self.rz = 90
        self.ry = 180
        
        size = (w, h, channels) = (200, 400, 1)
        self.stats_img = np.zeros(size, np.uint8)

        json_f = open('output/calibration.json')
        cal = json.load(json_f)
        
        self.mtx = np.array( cal['matrix'] )
        self.dist = np.array( cal['coefs'] )
        self.roi = cal['roi']

        json_f = open('obj3dpoints.json')
        obj = json.load(json_f)
        self.obj3d = np.array( obj['obj3d'] )
        self.obj2d = np.array( obj['obj2d'] )

        self.update_image()
        
    def draw2Doverlay(self, img):
        for p in self.obj2d:
            print p
            x = int(p[0])
            y = int(p[1])
            cv2.circle(img, (x,y), 2, (0,0,255), 1 )
        
    def show(self):
        cv2.namedWindow(self.wname, cv2.WINDOW_OPENGL)
        #~ cv2.namedWindow(self.wname2, cv2.WINDOW_NORMAL)
        cv2.namedWindow(self.wname3)
        cv2.imshow(self.wname, self.img_draw)
        
        #~ control_window = self.wname2
        
        #~ cv.CreateTrackbar('cx', control_window, self.cx, self.width, self.cb_cx)
        #~ cv.CreateTrackbar('cy', control_window, self.cy, self.height, self.cb_cy)
        #~ cv.CreateTrackbar('fx', control_window, self.fx, 2000, self.cb_fx)
        #~ cv.CreateTrackbar('fy', control_window, self.fy, 2000, self.cb_fy)
        
        #~ cv.CreateTrackbar('k1', control_window, 50, 100, self.cb_k1)
        #~ cv.CreateTrackbar('k2', control_window, 100, 200, self.cb_k2)
        #~ cv.CreateTrackbar('p1', control_window, 50, 100, self.cb_p1)
        #~ cv.CreateTrackbar('p2', control_window, 50, 100, self.cb_p2)

        control_window2 = self.wname3
        cv.CreateTrackbar('img',  control_window2, self.img, 92, self.cb_img)
        cv.CreateTrackbar('x',  control_window2, 400, 800, self.cb_x)
        cv.CreateTrackbar('y',  control_window2, 400, 800, self.cb_y)
        cv.CreateTrackbar('z',  control_window2, 800, 1600, self.cb_z)
        cv.CreateTrackbar('ry', control_window2, 180, 360, self.cb_ry)
        cv.CreateTrackbar('rx', control_window2, 180, 360, self.cb_rx)
        #~ cv.CreateTrackbar('rz', control_window2, 90, 180, self.cb_rz)

        cv2.waitKey(0)
        cv2.destroyAllWindows()
        
    def update_image(self):
        
        img = 50 + self.img*2
        self.img_orig = cv2.imread('images/test_{0}.jpg'.format(img))
        self.width = self.img_orig.shape[1]
        self.height = self.img_orig.shape[0]
        

        
        #~ cx = mtx[0][2]
        #~ cy = mtx[1][2]

        h,  w = self.img_orig.shape[:2]
        newcameramtx, roi=cv2.getOptimalNewCameraMatrix(self.mtx, self.dist, (w,h),1,(w,h))

        fovx, fovy, focalLength, principalPoint, aspectRatio = cv2.calibrationMatrixValues(newcameramtx, (w, h), 3.67,2.74)
        
        #~ h,  w = (self.height, self.width)
        newcameramtx, roi= cv2.getOptimalNewCameraMatrix(self.mtx, self.dist,(w,h),1,(w,h))
        
        #self.img_draw = self.img_orig.copy() 
        self.img_draw = cv2.undistort(self.img_orig, self.mtx, self.dist, None, newcameramtx)
        
        #cv2.circle(self.img_draw, (int(self.cx)+10,int(self.cy)+10 ), 20, (255,255,255), 3 )
        
        w2 = self.roi[0]
        cv2.line(self.img_draw, ( w2, 0) , (w2, h), (0,255,0), 3 )
        w2 = self.roi[2]
        cv2.line(self.img_draw, ( w2, 0) , (w2, h), (0,255,0), 3 )
        h2 = self.roi[1]
        cv2.line(self.img_draw, (0, h2) , (w, h2), (0,255,0), 3 )
        h2 = self.roi[3]
        cv2.line(self.img_draw, (0, h2) , (w, h2), (0,255,0), 3 )
        
        self.draw2Doverlay(self.img_draw)
        
        scale = 0.8
        self.img_draw = cv2.resize(self.img_draw, ( int(width*scale), int(height*scale) ))
        
        
        x = (400 - self.x) / 4.0
        y = (400 - self.y) / 4.0
        z = (800 - self.z) / 4.0
        rx = (180 - self.rx) / 2.0
        ry = (180 - self.ry) / 2.0
        rz = (90 - self.rz) / 10.0
        
        aratio = (float(self.width)/float(self.height))
        
        
        
        #cam = Camera([0,0,-(158+80)], [107,117,0], [0,1,0])
        #~ cam = Camera([0,0,100], [0,107,0], [0,-1,0])
        cam = Camera([0,0,70], [-117,0,0], [1,0,0])
        cam.setShape2(fovx, fovy, 0.05, 350)
        
        
        
        cam.rollUp(90)
        #cam.pitch(58.5)
        #cam.moveBoth(x, y, z)
        
        m_mvp = cam.getVPMatrix()
        
        #~ grid = Grid( 210, 230, 21, 23, (0,255,255), mx = 50, my = 107, width = 2 )
        ox = -200-150+47.25+x
        oy = -30-70+y
        oz = -50 + z

        #grid = Grid( 200, 200, 20, 20, (0,255,255), mx = 100, my = 100, width = 2 )
        #grid.rotateByY(ry)
        #grid.translateBy(ox, oy, oz)
        
        #cube1 = Cube(10,10,150, (255,0,0), width=2)
        #cube1.rotateByY(ry)
        #cube1.translateBy(ox+200, oy+100, oz-150)
        #~ grid.rotateByY(ry)
        
        #~ grid.translateBy(x,y-50,z)
        
        #grid.draw(self.img_draw, m_mvp)
        #cube1.draw(self.img_draw, m_mvp)
        
        cv2.imshow(self.wname, self.img_draw)
        
        stats = self.stats_img.copy()
        font = cv2.FONT_HERSHEY_SIMPLEX
        cv2.putText(stats, 'X:    {0}'.format(x),(0,20), font, 0.7,(255,255,255), 1)
        cv2.putText(stats, 'Y:    {0}'.format(y),(0,40), font, 0.7,(255,255,255), 1)
        cv2.putText(stats, 'Z:    {0}'.format(z),(0,60), font, 0.7,(255,255,255), 1)
        cv2.putText(stats, 'X(a): {0}'.format(rx),(0,80), font, 0.7,(255,255,255), 1)
        cv2.putText(stats, 'Y(a): {0}'.format(ry),(0,100), font, 0.7,(255,255,255), 1)
        cv2.putText(stats, 'img @ {0}'.format(img),(0,120), font, 0.7,(255,255,255), 1)
        
        #~ cv2.putText(stats, 'Cx: {0}'.format(cy),(0,80+10), font, 0.7,(255,255,255), 1)
        #~ cv2.putText(stats, 'Cy: {0}'.format(cx),(0,100+10), font, 0.7,(255,255,255), 1)
        #~ cv2.putText(stats, 'Fx: {0}'.format(fx),(0,120+10), font, 0.7,(255,255,255), 1)
        #~ cv2.putText(stats, 'Fy: {0}'.format(fy),(0,140+10), font, 0.7,(255,255,255), 1)
        
        #~ cv2.putText(stats, 'k1: {0}'.format(k1),(150,80+10), font, 0.7,(255,255,255), 1)
        #~ cv2.putText(stats, 'k2: {0}'.format(k2),(150,100+10), font, 0.7,(255,255,255), 1)
        #~ cv2.putText(stats, 'p1: {0}'.format(p1),(150,120+10), font, 0.7,(255,255,255), 1)
        #~ cv2.putText(stats, 'p2: {0}'.format(p2),(150,140+10), font, 0.7,(255,255,255), 1)
        
        cv2.imshow(self.wname3, stats)
        
    def cb_cx(self, pos):
        self.cx = pos
        self.update_image()
        
    def cb_img(self, pos):
        self.img = pos
                
        self.update_image()
        
    def cb_cy(self, pos):
        self.cy = pos
        self.update_image()
        
    def cb_k1(self, pos):
        self.k1 = pos
        self.update_image()
        
    def cb_k2(self, pos):
        self.k2 = pos
        self.update_image()
        
    def cb_p1(self, pos):
        self.p1 = pos
        self.update_image()
        
    def cb_p2(self, pos):
        self.p2 = pos
        self.update_image()
        
    def cb_x(self, pos):
        self.x = pos
        self.update_image()
        
    def cb_y(self, pos):
        self.y = pos
        self.update_image()
        
    def cb_z(self, pos):
        self.z = pos
        self.update_image()
        
    def cb_rx(self, pos):
        self.rx = pos
        self.update_image()
        
    def cb_ry(self, pos):
        self.ry = pos
        self.update_image()
        
    def cb_rz(self, pos):
        self.rz = pos
        self.update_image()
        
    def cb_fx(self, pos):
        self.fx = pos
        self.update_image()
        
    def cb_fy(self, pos):
        self.fy = pos
        self.update_image()


gui = Interactive('cam_test2.png')
gui.show()

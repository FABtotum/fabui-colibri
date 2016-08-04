#!/bin/env python

import cv2, cv
import numpy as np

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
        
        self.x = 90
        self.y = 90
        self.rx = 180
        self.rz = 90
        
        size = (w, h, channels) = (200, 400, 1)
        self.stats_img = np.zeros(size, np.uint8)

        self.update_image()
        
    def show(self):
        cv2.namedWindow(self.wname, cv2.WINDOW_OPENGL)
        cv2.namedWindow(self.wname2, cv2.WINDOW_NORMAL)
        cv2.namedWindow(self.wname3)
        cv2.imshow(self.wname, self.img_draw)
        
        control_window = self.wname2
        
        cv.CreateTrackbar('cx', control_window, self.cx, self.width, self.cb_cx)
        cv.CreateTrackbar('cy', control_window, self.cy, self.height, self.cb_cy)
        cv.CreateTrackbar('fx', control_window, self.fx, 2000, self.cb_fx)
        cv.CreateTrackbar('fy', control_window, self.fy, 2000, self.cb_fy)
        
        cv.CreateTrackbar('k1', control_window, 50, 100, self.cb_k1)
        cv.CreateTrackbar('k2', control_window, 100, 200, self.cb_k2)
        cv.CreateTrackbar('p1', control_window, 50, 100, self.cb_p1)
        cv.CreateTrackbar('p2', control_window, 50, 100, self.cb_p2)

        control_window2 = self.wname3
        cv.CreateTrackbar('x',  control_window2, 90, 180, self.cb_x)
        cv.CreateTrackbar('y',  control_window2, 90, 180, self.cb_y)
        cv.CreateTrackbar('rx', control_window2, 180, 360, self.cb_rx)
        #~ cv.CreateTrackbar('rz', control_window2, 90, 180, self.cb_rz)

        cv2.waitKey(0)
        cv2.destroyAllWindows()
        
    def update_image(self):
        k1 = (50-self.k1)*1e-01 / 2
        k2 = (100-self.k2)*1e-02 / 2
        p1 = (50-self.p1)*1e-03
        p2 = (50-self.p2)*1e-03
        
        dist = np.matrix([
            k1,
            k2,
            p1,
            p2,
            0])

        fx = ( (2000-self.fx) )
        fy = ( (2000-self.fy) )
        cx = self.cx
        cy = self.cy

        mtx = np.matrix([
         [  fx,   0.0,   cx],
         [  0.0,  fy,    cy],
         [  0.0,  0.0,  1.0],
         ])
        
        h,  w = (self.height, self.width)
        newcameramtx, roi= cv2.getOptimalNewCameraMatrix(mtx,dist,(w,h),1,(w,h))
        
        #self.img_draw = self.img_orig.copy() 
        self.img_draw = cv2.undistort(self.img_orig, mtx, dist, None, newcameramtx)
                
        cv2.circle(self.img_draw, (int(self.cx)+10,int(self.cy)+10 ), 20, (255,255,255), 3 )
        
        scale = 0.8
        self.img_draw = cv2.resize(self.img_draw, ( int(width*scale), int(height*scale) ))
        
        x = (90 - self.x) / 8.0
        y = (90 - self.y) / 8.0
        rx = (180 - self.rx) / 2.0
        rz = (90 - self.rz) / 10.0
        
        CAMERA_FOV          = 54 # should be 54 # http://elinux.org/Rpi_Camera_Module#Technical_Parameters_.28v.1_board.29, Angle of View        
        # Camera angle to bed. 0 is looking at the bed from above
        CAMERA_ANGLE_BED    = 0 # This one works, but why?!
        
        aratio = (float(self.width)/float(self.height))
        cam = Camera([0,0,250], [0,0,0], [1,0,0])
        cam.setShape(CAMERA_FOV, aratio, 0.05, 350)
        
        cam.rollUp(90)
        
        cam.moveBoth(60, 0, 0)
        
        m_mvp = cam.getVPMatrix()
        
        grid = Grid( 120, 100, 12, 10, (255,255,255), mx = 0, my = 0 )
        grid.rotateByX(rx)
        grid.translateBy(x,y-50,0)
        
        grid.draw(self.img_draw, m_mvp)
        
        cv2.imshow(self.wname, self.img_draw)
        
        stats = self.stats_img.copy()
        font = cv2.FONT_HERSHEY_SIMPLEX
        cv2.putText(stats, 'X:    {0}'.format(x),(0,20), font, 0.7,(255,255,255), 1)
        cv2.putText(stats, 'Y:    {0}'.format(y),(0,40), font, 0.7,(255,255,255), 1)
        cv2.putText(stats, 'Z(a): {0}'.format(rx),(0,60), font, 0.7,(255,255,255), 1)
        
        cv2.putText(stats, 'Cx: {0}'.format(cy),(0,80+10), font, 0.7,(255,255,255), 1)
        cv2.putText(stats, 'Cy: {0}'.format(cx),(0,100+10), font, 0.7,(255,255,255), 1)
        cv2.putText(stats, 'Fx: {0}'.format(fx),(0,120+10), font, 0.7,(255,255,255), 1)
        cv2.putText(stats, 'Fy: {0}'.format(fy),(0,140+10), font, 0.7,(255,255,255), 1)
        
        cv2.putText(stats, 'k1: {0}'.format(k1),(150,80+10), font, 0.7,(255,255,255), 1)
        cv2.putText(stats, 'k2: {0}'.format(k2),(150,100+10), font, 0.7,(255,255,255), 1)
        cv2.putText(stats, 'p1: {0}'.format(p1),(150,120+10), font, 0.7,(255,255,255), 1)
        cv2.putText(stats, 'p2: {0}'.format(p2),(150,140+10), font, 0.7,(255,255,255), 1)
        
        cv2.imshow(self.wname3, stats)
        
    def cb_cx(self, pos):
        self.cx = pos
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
        
    def cb_rx(self, pos):
        self.rx = pos
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


gui = Interactive('capture.jpg')
gui.show()

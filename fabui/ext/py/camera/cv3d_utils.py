import cv2
import numpy as np

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
    a = np.radians(a)
    c1 = np.cos(a)
    c2 = np.sin(a)

    return np.matrix([
                [1, 0,    0,  0],
                [0, c1, -c2,  0],
                [0, c2,  c1,  0],
                [0, 0,    0,  1]
                ])
                
def rotz_matrix(a):
    a = np.radians(a)
    c1 = np.cos(a)
    c2 = np.sin(a)

    return np.matrix([
                    [c1, -c2,  0,  0],
                    [c2,  c1,  0,  0],
                    [0,    0,  1,  0],
                    [0,    0,  0,  1]
                    ])
                    
def roty_matrix(a):
    a = np.radians(a)
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

def create_cube(x, y, z, w, h, d, color, width):
    
    points = [
        [ x,    y,  z],
        [ x,    y,  z+d],
        [ x+w, y,  z+d],
        [ x+w, y,  z],
        
        [ x,    y+h,  z],
        [ x,    y+h,  z+d],
        [ x+w, y+h,  z+d],
        [ x+w, y+h,  z]
    ]

    lines = [
        [4,0, color, width],
        [5,1, color, width],
        [6,2, color, width],
        [7,3, color, width],
                     
        [0,1, color, width],
        [1,2, color, width],
        [2,3, color, width],
        [3,0, color, width],
                    
        [4,5, color, width],
        [5,6, color, width],
        [6,7, color, width],
        [7,4, color, width],
    ]
    
    return points, lines
    
def create_grid(x, y, z, w, h, xn, yn, color, mx = None, my = None, width = 1):
    points = []
    lines  = []
    
    idx = 0
    
    dx = w / xn
    dy = h / yn
    
    #~ print "dx, dy", dx,dy
        
    for i in xrange(0, xn+1):
        nx = x + i*dx
        ny1 = y
        ny2 = y + h
        nz  = z

        points.append( [nx, ny1, nz] )
        points.append( [nx, ny2, nz] )
        
        if i == 0:
            lines.append( [idx, idx+1, (0,255,0), width] )
        else:
            lines.append( [idx, idx+1, color, width] )
        idx += 2
    

    for i in xrange(0, yn+1):
        nx1 = x
        nx2 = x + w
        ny = y + i*dy
        nz = z

        points.append( [nx1, ny, nz] )
        points.append( [nx2, ny, nz] )
        
        if i == 0:
            lines.append( [idx, idx+1, (0,0,255), width] )
        else:
            lines.append( [idx, idx+1, color, width] )
            
        idx += 2
    
    nz = z
    
    if mx == None:
        mx = w / 2
    if my == None:
        my = h / 2
    
    points.append( [x+mx, y, nz] )
    points.append( [x+mx, y+h, nz] )
    lines.append( [idx, idx+1, (200,255,200), width ] )
    idx += 2
        
    points.append( [x, y+my, nz] )
    points.append( [x+w, y+my, nz] )
    lines.append( [idx, idx+1, (200,200,255), width] )
    idx += 2
        
    return points, lines

class Object(object):
    def __init__(self, _vlist, _elist):
        self.m_modelMatrix = eye_matrix()
        self.vlist = _vlist
        self.elist = _elist
        
    def translateTo(self, x, y, z):
        self.m_modelMatrix = trans_matrix(x, y, z)
    
    def translateBy(self, dx, dy, dz):
        self.m_modelMatrix = self.m_modelMatrix * trans_matrix(dx, dy, dz)
    
    def rotateByX(self, a):
        self.m_modelMatrix = self.m_modelMatrix * rotx_matrix(a)
    
    def rotateByY(self, a):
        self.m_modelMatrix = self.m_modelMatrix * roty_matrix(a)
    
    def rotateByZ(self, a):
        self.m_modelMatrix = self.m_modelMatrix * rotz_matrix(a)
        
    def draw(self, image, m_mvp):
        width  = image.shape[1]
        height = image.shape[0]
        
        xy = self.project_array(self.vlist, self.m_modelMatrix, m_mvp)
        xy = self.scale(xy, self.elist, width, height, 1)
        
        for ln in self.elist:
            cv2.line(image, xy[ ln[0] ] , xy[ ln[1] ], ln[2], ln[3] )
    
    def project_point(self, x, y, z, model_m, mvp_m):
        m_point = np.matrix([ [x, y, z, 1.0] ])
        
        # https://open.gl/transformations#TransformationsinOpenGL
        p2 = mvp_m * model_m * m_point.T   
        p3 = p2.T / p2.A1[3]
        
        return (p3.A1[0], p3.A1[1])

    def project_array(self, a, model_m, mvp_m):
        result = []
        for p in a:
            x,y = self.project_point(p[0], p[1], p[2], model_m, mvp_m)
            result.append( (x,y) )
        return result

    def scale(self, points, lines, w, h, aratio):
       
        scaled = []
        point_done = []
            
        for p in points:
            x = int(0.5*w + w*p[0])        
            y = int( 0.5*h + h*p[1]*aratio )
            scaled.append( (x,y) )
            
        return scaled

class Grid(Object):
    def __init__(self, w, h, xn, yn, color, mx = None, my = None, width = 1, x=0, y=0, z=0):
        
        if mx == None:
            mx = w / 2
            
        if my == None:
            my = h/2
        
        points,lines = create_grid(x, y, z, w, h, xn, yn, color, mx, my, width)
        super(Grid, self).__init__(points, lines)

class Cube(Object):
    def __init__(self, w, h, d, color, width = 1):
        points,lines = create_cube(0, 0, 0, w, h, d, color, width)
        super(Cube, self).__init__(points, lines)

class Camera:
    def __init__(self, _eye, _look, _up):
        self.set(_eye, _look, _up)

    def getVPMatrix(self):
        return self.m_projectionMatrix * self.m_viewMatrix
        #~ return self.m_viewMatrix * self.m_projectionMatrix
        
    def getProjectionMatrix(self):
        return self.m_projectionMatrix
        
    def getViewMatrix(self):
        return self.m_viewMatrix

    @staticmethod
    def normalize(v):
        norm=np.linalg.norm(v)
        if norm==0: 
           return v
        return v/norm
        
    def setShape(self, _viewAngle, _aspect, _near, _far):
        #self.m_fov = _viewAngle
        self.m_fovy = _viewAngle
        self.m_fovx = _viewAngle / _aspect
        
        self.m_aspect = _aspect
        self.m_zNear = _near
        self.m_zFar = _far
        self.setProjectionMatrix()
        
    def setShape2(self, _viewAngleX, _viewAngleY, _near, _far):
        self.m_fovx = _viewAngleX
        self.m_fovy = _viewAngleY
        self.m_zNear = _near
        self.m_zFar = _far
        self.setProjectionMatrix()
        
    def setProjectionMatrix(self):
        #self.m_projectionMatrix = np.asmatrix( np.zeros( (4,4) ) )
        self.setPerspProjection()
        
    def setPerspProjection(self):
        #~ f = 1.0 / np.tan( np.radians(self.m_fov) )
        fx2 = 1.0 / np.tan( np.radians(self.m_fovx) / 2.0 )
        fy2 = 1.0 / np.tan( np.radians(self.m_fovy) / 2.0 )
        
        
        c1 = fx2
        c2 = fy2
        
        c3 = (self.m_zFar+self.m_zNear)/(self.m_zNear-self.m_zFar)
        c4 = (2*self.m_zFar*self.m_zNear)/(self.m_zNear-self.m_zFar)
        
        self.m_projectionMatrix = np.matrix([
                    [c1, 0.0,   0.0, 0.0],
                    [0.0, c2,   0.0, 0.0],
                    [0.0, 0.0,  c3, c4],
                    [0.0, 0.0,  -1.0, 0.0]
                    ], dtype=float)

    def setViewMatrix(self):
        
        #~ print type(self.m_u)
        #~ print type(self.m_n)
        #~ print type(self.m_v)
        #~ print type(self.m_eye)
        
        a1 = np.dot(-self.m_eye, self.m_u.T)
        a2 = np.dot(-self.m_eye, self.m_v.T)
        a3 = np.dot(-self.m_eye, self.m_n.T)
        
        self.m_viewMatrix = np.matrix([
            [ self.m_u.A1[0], self.m_v.A1[0], self.m_n.A1[0], a1 ],
            [ self.m_u.A1[1], self.m_v.A1[1], self.m_n.A1[1], a2 ],
            [ self.m_u.A1[2], self.m_v.A1[2], self.m_n.A1[2], a3 ],
            [ 0, 0, 0, 1 ],
        ], dtype=float)
    
    def updateUNV(self):
        #~ // make U, V, N vectors
        #~ m_n=m_eye-m_look;
        self.m_n = self.m_eye - self.m_look
        #~ print "m_n", self.m_n
        #~ m_u=m_up.cross(m_n);
        self.m_u = np.asmatrix( np.cross( self.m_up, self.m_n) )
        #~ print "m_u", self.m_u
        #~ m_v=m_n.cross(m_u);
        self.m_v = np.asmatrix( np.cross( self.m_n, self.m_u) )
        #~ print "m_v", self.m_v
        #~ m_u.normalize();
        self.m_u = self.normalize(self.m_u)
        #~ m_v.normalize();
        self.m_v = self.normalize(self.m_v)
        #~ m_n.normalize();
        self.m_n = self.normalize(self.m_n)
        
        self.setViewMatrix()
        
    def set(self, _eye, _look, _up):
        self.m_eye   = np.matrix([ _eye[0], _eye[1], _eye[2] ], dtype=float)
        self.m_look  = np.matrix([ _look[0], _look[1], _look[2] ], dtype=float)
        self.m_up    = np.matrix([ _up[0], _up[1], _up[2] ], dtype=float)

        self.updateUNV();
        
    def moveEye(self, _dx, _dy, _dz):
        self.m_eye += np.matrix([ _dx, _dy, _dz ], dtype=float)
        self.updateUNV()
        
    def moveLook(self, _dx, _dy, _dz):
        self.m_look += np.matrix([ _dx, _dy, _dz ], dtype=float)
        self.updateMNV()
        
    def moveBoth(self, _dx, _dy, _dz):
        vd = np.matrix([ _dx, _dy, _dz ], dtype=float)
        
        self.m_eye  += vd
        self.m_look += vd
        self.updateUNV()
        
    def rotAxes(self, io_a, io_b, _angle):
        ang = np.radians(_angle)
        c = np.cos(ang)
        s = np.sin(ang)
        #~ // tmp for io_a vector
        #~ Vec4 t( c * io_a.m_x + s * io_b.m_x,  c * io_a.m_y + s * io_b.m_y,  c * io_a.m_z + s * io_b.m_z);
        a = np.matrix([c*io_a.A1[0] + s*io_b.A1[0],
                       c*io_a.A1[1] + s*io_b.A1[1],
                       c*io_a.A1[2] + s*io_b.A1[2]
                      ])
                      
        #~ // now set to new rot value
        #~ io_b.set(-s * io_a.m_x + c * io_b.m_x, -s * io_a.m_y + c * io_b.m_y, -s * io_a.m_z + c * io_b.m_z);
        b = np.matrix([
                            -s*io_a.A1[0] + c*io_b.A1[0],
                            -s*io_a.A1[1] + c*io_b.A1[1],
                            -s*io_a.A1[2] + c*io_b.A1[2]
                         ])

        #~ // put tmp into _a'
        #~ io_a.set(t.m_x, t.m_y, t.m_z);
        #io_a = t
        
        return a, b
    
    def roll(self, _angle):
        """ roll the cameara around the  Z axis """
        self.m_u, self.m_v = self.rotAxes(self.m_u, self.m_v, -_angle);
        self.setViewMatrix()

    def pitch(self, _angle):
        """ roll the cameara around the  X axis """
        self.m_n, self.m_v = self.rotAxes(self.m_n, self.m_v, _angle);
        self.setViewMatrix()

    def yaw(self, _angle):
        """ roll the cameara around the  Y axis """
        self.m_u, self.m_n = self.rotAxes(self.m_u, self.m_n, _angle);
        self.setViewMatrix()
    
    def rollUp(self, _angle):
        """ roll the cameara UP vector around the  Z axis """
        
        a = np.radians(-_angle)
        c = np.cos(a)
        s = np.sin(a)

        m = np.matrix([
                    [c, -s,  0],
                    [s,  c,  0],
                    [0,  0,  1],
                    ])
                    
        t =  m * self.m_up.T
        
        self.m_up = t.T
        
        self.updateUNV();
        self.setViewMatrix()

    def pitchUp(self, _angle):
        """ roll the cameara UP vector around the  X axis """
        
        a = np.radians(_angle)
        c = np.cos(a)
        s = np.sin(a)

        m = np.matrix([
                    [1, 0,  0],
                    [0, c, -s],
                    [0, s,  c],
                    ])
                    
        t =  m * self.m_up.T
        
        self.m_up = t.T
        
        self.updateUNV();
        self.setViewMatrix()

    def yawUp(self, _angle):
        """ roll the cameara UP vector around the  Y axis """
        
        a = np.radians(_angle)
        c = np.cos(a)
        s = np.sin(a)

        m = np.matrix([
                    [c,  0,  s],
                    [0,  1,  0],
                    [-s, 0,  c],
                    ])
                    
        t =  m * self.m_up.T
        
        self.m_up = t.T
        
        self.updateUNV();
        self.setViewMatrix()

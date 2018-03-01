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

__authors__ = "Daniel Kesler"
__license__ = "GPL - https://opensource.org/licenses/GPL-3.0"
__version__ = "1.0"

# Import external modules
import numpy as np
from loaders import dxfgrabber
import loaders.librecadfont1 as lff

class Layer2D(object):
    def __init__(self, name, color = 255):
        self.name = name
        self.color = color
        self.primitives = []
        
    def addPrimitive(self, data):
        self.primitives.append(data)

class Drawing2D(object):
    """
    :todo: 
       - filled circle
       - filled ellipse
       - filled polyline
    """
    
    # Text attachment point.
    TOP_LEFT = 1
    TOP_CENTER = 2
    TOP_RIGHT = 3
    MIDDLE_LEFT = 4
    MIDDLE_CENTER = 5
    MIDDLE_RIGHT = 6
    BOTTOM_LEFT = 7
    BOTTOM_CENTER = 8
    BOTTOM_RIGHT = 9
    
    # Text alignment
    ALIGN_LEFT = 1
    ALIGN_CENTER = 2
    ALIGN_RIGHT = 3
    
    def __init__(self):
        self.clear()
        self.fonts = {}
        self.add_layer(name='Default', color=7)
    
    def extend_bounds(self, points):
        """
        Extend boundries to fit the points.
        """
        if type(points) is list:
            pass
        elif type(points) is tuple:
            points = [ points ]
        else:
            # print 'neither a tuple or a list'
            return
        
        x1 = 1.0e10
        y1 = 1.0e10
        x2 = -1.0e10
        y2 = -1.0e10
        
        for pt in points:
            if pt[0] > x2:
                x2 = pt[0]
                
            if pt[0] < x1:
                x1 = pt[0] 
                
            if pt[1] > y2:
                y2 = pt[1]
                
            if pt[1] < y1:
                y1 = pt[1]
            
        if x2 > self.max_x:
            self.max_x = x2
            
        if x1 < self.min_x:
            self.min_x = x1
            
        if y2 > self.max_y:
            self.max_y = y2
            
        if y1 < self.min_y:
            self.min_y = y1
        
        return x1,y1, np.absolute(x2-x1), np.absolute(y2-y1)
    
    def get_font(self, font):
        filename = '/var/lib/fabui/plugins/fab_laser/fonts/lff/{0}.lff'.format(font)
        
        # print "loading font", filename
        
        if font not in self.fonts:    
            f = lff.readfile(filename)
            if f:
                self.fonts[font] = f
            return f
        else:
            return self.fonts[font]       
    
    def add_layer(self, name = None, color = 7):
        """
        Add a new layer.
        """
        idx = len(self.layers)
        if name is None:
            name = 'Layer_{0}'.format(idx)

        layer = Layer2D(name, color)
        self.layers.append(layer)
        return idx
        
    def __bulge2arc(self, p1, p2, b):
        center = (0,0)
        start = 0.0
        end = 0.0
        radius = 0.0
        reverse = False
        theta_half = 2*np.arctan(b) 
        
        dx = p2[0] - p1[0]
        dy = p2[1] - p1[1]

        chord = np.sqrt(dx * dx + dy* dy)

        dx_norm = dx / chord
        dy_norm = dy / chord
        
        radius = chord / (2*np.sin(theta_half))
        
        p3 = ( p1[0] + dx*0.5, p1[1] + dy*0.5 )
        
        x = np.cos(theta_half) * radius

        center = ( p3[0] - dy_norm*x, p3[1] + dx_norm*x )

        if b < 0:
            # CW, flip start end
            
            theta = 2*theta_half

            dx = center[0] - p2[0]
            dy = center[1] - p2[1]
            
            a1 = np.arctan2(dy,dx)

            A = np.rad2deg( a1 )
            B = A - np.rad2deg( theta )
            
            end = B
            start = A
            reverse = True
        else:
            # CCW
            
            theta = 2*theta_half

            dx = center[0] - p1[0]
            dy = center[1] - p1[1]
            
            a1 = np.arctan2(dy,dx)

            start = np.rad2deg( a1 ) - 180
            end = start + np.rad2deg( theta )
        
        return center, radius, start, end, reverse
        
    def __translate_points(self, points, tx, ty):
        tpoints = []
        for pt in points:
            x = pt[0] + tx
            y = pt[y] + ty
            tpoints.append( (x,y) )
            
        return tpoints
        
    def add_rect(self, x1, y1, x2, y2, layer = 0, filled = False):
        points = [
            (x1,y1),
            (x2,y1),
            (x2,y2),
            (x1,y2),
            (x1,y1)
        ]
        data = { 'type' : 'rect', 'first': (x1,y1), 'second' : (x2,y2), 'points' : points, 'filled' : filled }
        self.layers[layer].addPrimitive(data)
        return self.extend_bounds(points)
        
    def add_rect2(self, x1, y1, w, h, layer = 0, filled = False):
        points = [
            (x1,y1),
            (x1+w,y1),
            (x1+w,y1+h),
            (x1,y1+h),
            (x1,y1),
        ]
        data = { 'type' : 'rect', 'first': (x1,y1), 'second' : (x1+w,y1+h), 'points' : points, 'filled' : filled }
        self.layers[layer].addPrimitive(data)
        return self.extend_bounds(points)
        
    def add_line(self, start, end, layer = 0):
        data = { 'type' : 'line', 'points': [start, end] }
        self.layers[layer].addPrimitive(data)
        
        return self.extend_bounds( [start, end] )
    
    def add_polyline(self, points, bulges, closed = False, layer = 0, filled = False, dummy = False):

        if closed:
            points.append(points[0])
        
        points2 = []
        
        cnt = 0
        
        idx = 0
        has_prev = False
        for pt in points:
            if has_prev:
                b = bulges[idx]
                if b != 0:
                    # Construct and arc
                    center, radius, start, end, reverse = self.__bulge2arc(p0, pt, b)
                    arc = self.__arc(center, radius, start, end, step = 10.0, reverse=reverse)
                    
                    points2 += arc
                else:
                    # Use a straight line 
                    points2.append(pt)
                    
                idx += 1
            else:
                points2.append(pt)
                
            p0 = pt
            has_prev = True
            
        data = { 'type' : 'polyline', 'points' : points2, 'bulges' : bulges, 'closed' : closed, 'filled' : filled }
        if not dummy:
            self.layers[layer].addPrimitive(data)
        return self.extend_bounds(points2)
        
    def add_circle(self, center, radius, layer = 0, filled = False):
        points = self.__circle(center, radius)
        data = { 'type' : 'circle', 'center' : center, 'radius' : radius, 'points' : points, 'filled' : filled }
        self.layers[layer].addPrimitive(data)
        return self.extend_bounds(points)
    
    def add_arc(self, center, radius, start, end, layer = 0):
        points = self.__arc(center, radius, start, end)
        data = { 'type' : 'arc', 'center' : center, 'radius' : radius, 'start' : start, 'end': end, 'points' : points }
        self.layers[layer].addPrimitive(data)
        return self.extend_bounds(points)
    
    def add_spline(self, control_points, knots, degree, layer = 0):
        
        npts = len(control_points)
        k = degree + 1
        #~ p1 = (dxf.header['$SPLINESEGS'] or 8) * npts
        p1 = (8) * npts
        
        points = self.__rbspline(npts, k, p1, control_points, knots)
        
        data = { 'type' : 'spline', 'control_points' : control_points, 'knots' : knots, 'degree' : degree, 'points' : points}
        self.layers[layer].addPrimitive(data)
        return self.extend_bounds(points)
    
    def add_ellipse(self, center, major_axis, ratio, start, end, layer = 0, filled = False):
        points = self.__ellipse(center, major_axis, ratio, start, end)
        data = { 'type' : 'ellipse', 'center' : center, 'major_axis' : major_axis, 'ratio' : ratio, 'start' : start, 'end' : end, 'points' : points, 'filled' : filled}
        self.layers[layer].addPrimitive(data)
        self.extend_bounds(points)
    
    def add_text(self, position, align, width, height, direction, font_name, text_lines, layer = 0):
        # TODO:
        # - alignment
        # - direction
        #~ font_name = 'OpenGostTypeA-Regular'
        #~ font_name = 'iso'
        font = self.get_font(font_name)
        
        if not font:
            # print "Font '{0}' not found".format(font_name)
            return
        
        # print
        # print "direction", direction
        # print "height", height
        
        scale = height / 9.0
        # print "scale",scale
        wordSpacing = float(font.meta['WordSpacing']) * scale
        letterSpacing = float(font.meta['LetterSpacing']) * scale
        letterHeight = height
        
        # print "letterSpacing", letterSpacing
        # print "wordSpacing", wordSpacing
        
        # print text_lines
        
        off_x = position[0]
        off_y = position[1]
        
        for text in text_lines:
            #~ off_x = position[0]
            
            for c in text:
                if c == ' ':
                    off_x += wordSpacing
                else:
                    sym = font.getSymbol(c)
                    # TODO: load reference character
                    if sym.ref:
                        #~ print "has ref to '{0}'".format(sym.ref)
                        sym_ref = font.getSymbol(sym.ref)
                        lines = sym_ref.lines + sym.lines
                    else:
                        lines = sym.lines
                    cnt = 0
                    
                    max_x = -1e10
                    max_y = -1e10
                    
                    for line in lines:
                        eat_one = True
                        points = []
                        bulges = []
                        for pt in line:
                            x = pt[0]*scale + off_x
                            y = pt[1]*scale + off_y
                            p1 = (x, y)
                            points.append(p1)
                            
                            if len(pt) == 3:
                                bulges.append(pt[2])
                            else:
                                if len(points) > 0:
                                    if eat_one:
                                        eat_one = False
                                        pass
                                    else:
                                        bulges.append(0.0)
                            
                            #~ max_x = max(x, max_x)
                            #~ max_y = max(y, max_y)
                        
                        if eat_one == False:
                            bulges.append(0.0)
                                
                        x,y,w,h = self.add_polyline(points, bulges, layer=layer)
                        #~ print x,y,w,h
                        max_x = max(x+w, max_x)
                        max_y = max(y+h,max_y)
                        
                    #~ print "R",off_x,off_y,max_x,max_y
                    #~ self.add_rect(off_x,off_y,max_x,max_y,layer=layer)
                    
                    # print "off_x", off_x, position[0]
                        
                    off_x = max_x + letterSpacing
                    # print "+off_x", off_x
                    
            off_y -= letterHeight
            off_x = position[0]
            
    def __ellipse_point(self, center, r1, r2, rotM, t):
        x1 = r1 * np.cos( np.radians(t) )
        y1 = r2 * np.sin( np.radians(t) )
        tmp = np.array([x1,y1])
        p1 = center + (tmp * rotM)
        return p1.A1[0], p1.A1[1]
        
    def __ellipse(self, center, axis, ratio, start, end, step = 10.0):
        
        points = []
        x0 = center[0]
        y0 = center[1]
        # Get length of axis vector
        r1 = np.linalg.norm(axis)
        # Get second radius
        r2 = r1 * ratio
        
        # Get axis angle
        if axis[0] == 0:
            a = np.radians(90.0) 
        elif axis[1] == 0:
            a = np.radians(0.0) 
        else:
            a = np.arctan(axis[1] / axis[0])

        # Prepare rotation matrix
        center = np.array([x0,y0])
        c1 = np.cos(a)
        c2 = np.sin(a)
        rotM = np.matrix([
            [c1,c2],
            [-c2,c1]
        ])
        rotMCCW = np.matrix([
            [c1,-c2],
            [c2,c1]
        ])
        
        eye = np.matrix([ [1.0, 0.0], [0.0, 1.0] ])
        
        fix = 0
        if start > end:
            start += 180.0
            if start > 360.0:
                start -= 360.0
            end += 180.0
                   
        have_prev = False
        
        tl = np.arange(start, end, step)
        
        for t in tl:
            x2,y2 = self.__ellipse_point(center, r1, r2, rotM, t)

            points.append( (x2,y2) )
                        
            x1 = x2
            y1 = y2
            have_prev = True

        x2,y2 = self.__ellipse_point(center, r1, r2, rotM, end)
        points.append( (x2,y2) )
        
        return points
        
    def __circle(self, center, radius, step = 10.0):
        x0 = center[0]
        y0 = center[1]
        r = radius
        points = []
        start = 0.0
        end = 360.0
        steps = int( 360.0 / step )

        have_prev = False
        
        for a in xrange(steps):
            angle = np.deg2rad(start + a*step)
            x2 = x0 + np.cos(angle)*r
            y2 = y0 + np.sin(angle)*r
            
            points.append( (x2,y2) )

            x1 = x2
            y1 = y2
            have_prev = True
            
        if (start + (steps-1)*step) != end:
            angle = np.deg2rad(end)
            x2 = x0 + np.cos(angle)*r
            y2 = y0 + np.sin(angle)*r
            
            points.append( (x2,y2) )
            
        return points
    
    def __wrapTo360(self, angle):
        angle = np.fmod(angle,360);
        if angle < 0:
            angle += 360;
        return angle;
    
    def __arc(self, center, radius, start, end, step = 10.0, reverse=False):
        """
        Draw an arc.
        
        :param center: Tuple (x,y) representing the arc center point
        :param radius: Arc radius
        :param start: Arc start angle (degree)
        :param end: Arc end angle (degree)
        :param step: Angle change step
        :param reverse: Reverse arc points
        
        :returns: Arc points
        """
        x0 = center[0]
        y0 = center[1]
        points = []
        r = radius
        angle = self.__wrapTo360(end - start)
        
        steps = int(abs(angle / step))

        have_prev = False
        
        start = self.__wrapTo360(start)
        end = self.__wrapTo360(end)
        
        a1 = start
        a2 = end
        sign = 1
        
        if reverse:
            a1 = end
            a2 = start
            sign = -1
        
        for a in xrange(steps):
            angle = np.deg2rad(a1 + sign*a*step)
            x2 = x0 + np.cos(angle)*r
            y2 = y0 + np.sin(angle)*r
            
            points.append( (x2,y2) )

            x1 = x2
            y1 = y2
            have_prev = True
            
        if (a1 + sign*(steps-1)*step) != a2:
            angle = np.deg2rad(a2)
            x2 = x0 + np.cos(angle)*r
            y2 = y0 + np.sin(angle)*r
            
            points.append( (x2,y2) )
            
        return points
        
    def __rbasis(self, c, t, npts, x, h):
        """
        Generates rational B-spline basis functions for an open knot vector.
        :note: Source code converted from LibreCad (rs_spline.cpp)
        
        """
        nplusc = npts + c
        temp = np.zeros(nplusc)
        
        # calculate the first order nonrational basis functions n[i]
        for i in xrange(nplusc-1):
            if t >= x[i] and t < x[i+1]:
                temp[i] = 1

        # calculate the higher order nonrational basis functions
        for k in xrange(2,c+1):
            for i in xrange(nplusc-k):
                # if the lower order basis function is zero skip the calculation
                if temp[i] != 0:
                    temp[i] = ((t-x[i])*temp[i])/(x[i+k-1]-x[i])
                    
                # if the lower order basis function is zero skip the calculation
                if temp[i+1] != 0:
                    temp[i] += ((x[i+k]-t)*temp[i+1])/(x[i+k]-x[i+1])
                    
        # pick up last point
        if t >= x[nplusc-1]:
            temp[npts-1] = 1

        # calculate sum for denominator of rational basis functions
        sum = 0.0
        for i in xrange(npts):
            sum += temp[i]*h[i]

        r = np.zeros(npts)
        # form rational basis functions and put in r vector
        if sum != 0:
            for i in xrange(npts):
                r[i] = (temp[i]*h[i])/sum
        return r

    def __rbspline(self, npts, k, p1, b, knot):
        """
        Generates a rational B-spline curve using a uniform open knot vector.
        :note: Source code converted from LibreCad (rs_spline.cpp)
        
        :param npts: Number of control points
        :param k: Spline degree
        :param b: Control point list
        :param knot: knot list
        """
        p = []
        h = np.ones(npts+1)
        nplusc = npts + k

        # generate the open knot vector (we have one already)
        x = knot

        # calculate the points on the rational B-spline curve
        t = 0.0
        step = x[nplusc-1] / (p1-1)
            
        vp = np.zeros(shape=(p1,2))
            
        for i in xrange(p1):
            if x[nplusc-1] - t < 5e-6:
                t = x[nplusc-1]
            # generate the basis function for this value of t
            nbasis = self.__rbasis(k, t, npts, x, h)

            # generate a point on the curve
            for j in xrange(npts):
                x0 = b[j][0] * nbasis[j]
                y0 = b[j][1] * nbasis[j]
                vp[i] += ( x0, y0 )
                
            t += step
            
            p.append( vp[i] )
            
        return p
        
    def transform(self, sx = 1.0, sy = 1.0, ox = 0.0, oy = 0.0):
        self.max_x *= sx
        self.max_y *= sy
        self.min_x *= sx
        self.min_y *= sy
        
        for l in self.layers:
            for e in l.primitives:
                t = e['type']
                
                if 'points' in e:
                    points = []
                    for p in e['points']:
                        points.append( ( (ox + p[0])*sx, (oy + p[1])*sy) )
                    e['points'] = points
                
                if t == 'polyline' or t == 'spline':
                    pass

                elif t == 'circle' or t == 'arc':
                    p = e['center']
                    e['center'] = ( (ox + p[0])*sx, (oy + p[1])*sy)
                    e['radius'] *= (sx+sy) / 2.0
                    
                elif t == 'ellipse':
                    p = e['center']
                    e['center'] = ( (ox + p[0])*sx, (oy + p[1])*sy)
                    m = e['major_axis']
                    e['major_axis'] = ( m[0] * sx, m[1] * sy, 0.0)
                    # TODO scale other parameters
        
    def scale(self, sx = 1.0, sy = 1.0):
        self.transform(sx, sy)
        
    def width(self):
        return self.max_x - self.min_x
        
    def height(self):
        return self.max_y - self.min_y
        
    def scale_to(self, target_width = 0.0, target_height = 0.0):
        width = self.width()
        height = self.height()
        
        sx = 1.0
        sy = 1.0
        
        if target_width != 0.0:
            sx = target_width / width
            sy = sx
            
        if target_height != 0.0:
            sy = target_height / height
            sx = sy

        self.scale(sx, sy)

    def normalize(self, margin = 0.1):
        self.transform(1.0, 1.0, -self.min_x+margin, -self.min_y+margin)
        
        width = self.max_x - self.min_x
        height = self.max_y - self.min_y
        
        self.min_x = 0
        self.min_y = 0
        self.max_x = width
        self.max_y = height

    def clear(self):
        """
        Clear all values.
        """
        self.layers = []
        self.max_x = 0
        self.max_y = 0
        self.min_x = 0
        self.min_y = 0

    def load_from_dxf(self, filename, clear=True):
        dxf = dxfgrabber.readfile(filename)
        
        if clear:
            self.clear()
            
        layer_map = {}

        #~ print "- Layers:"
        for l in dxf.layers:
            #~ print "  ", l.name, l.color, l.linetype
            color = l.color
            layer_map[l.name] = self.add_layer(l.name, color)

        #~ print "- Entries:"
        for e in dxf.entities:
            t = e.dxftype
            
            if e.layer not in layer_map:
                color = e.color
                layer_map[e.layer] = self.add_layer(e.layer, color)
            #~ else:
                #~ print "forcing", e.layer
                #~ color = e.layer
                
            #~ print "== ", t
            if t == 'LWPOLYLINE' or t == 'POLYLINE':
                is_closed = False
                if e.is_closed:
                    is_closed = True
                self.add_polyline(e.points, e.bulge, is_closed, layer_map[e.layer])
                
            elif t == 'LINE':
                self.add_line(e.start, e.end, layer_map[e.layer])
                
            elif t == 'CIRCLE':
                self.add_circle(e.center, e.radius, layer_map[e.layer])
                
            elif t == 'ELLIPSE':
                self.add_ellipse(e.center, e.major_axis, e.ratio, np.rad2deg(e.start_param), np.rad2deg(e.end_param), layer_map[e.layer] )
                
            elif t == 'ARC':
                self.add_arc(e.center, e.radius, e.start_angle, e.end_angle, layer_map[e.layer])
                
            elif t == 'SPLINE':
                self.add_spline(e.control_points, e.knots, e.degree, layer_map[e.layer])
            
            elif t == 'MTEXT':
                ln = len(e.lines())
                lh = e.height
                w = e.rect_width
                x = e.insert[0]
                y = e.insert[1] - lh
                align = self.ALIGN_LEFT
                
                if e.attachment_point == self.TOP_LEFT:
                    pass
                elif e.attachment_point == self.TOP_CENTER:
                    #~ x -= w * 0.5
                    align = self.ALIGN_CENTER
                elif e.attachment_point == self.TOP_RIGHT:
                    #~ x -= w
                    align = self.ALIGN_RIGHT
                elif e.attachment_point == self.MIDDLE_LEFT:
                    y += ln*lh * 0.5
                elif e.attachment_point == self.MIDDLE_CENTER:
                    #~ x -= w * 0.5
                    y += ln*lh * 0.5
                    align = self.ALIGN_CENTER
                elif e.attachment_point == self.MIDDLE_RIGHT:
                    #~ x -= w
                    y += ln*lh * 0.5
                    align = self.ALIGN_RIGHT
                elif e.attachment_point == self.BOTTOM_LEFT:
                    y += ln*lh
                    pass
                elif e.attachment_point == self.BOTTOM_CENTER:
                    #~ x -= w * 0.5
                    y += ln*lh
                    align = self.ALIGN_CENTER
                elif e.attachment_point == self.BOTTOM_RIGHT:
                    #~ x -= w
                    y += ln*lh
                    align = self.ALIGN_RIGHT
    
                self.add_text((x,y), align, e.rect_width, e.height, e.xdirection, e.font, e.lines(), layer=layer_map[e.layer])


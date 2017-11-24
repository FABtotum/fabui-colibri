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

import argparse
import os
import gettext
import json

# Import external modules
import numpy as np
import cv2,cv
from loaders import dxfgrabber
# Import internal modules
from common.drawing import Drawing2D


# Set up message catalog access
tr = gettext.translation('img2gcode', 'locale', fallback=True)
_ = tr.ugettext


def preprocess_dxf_image(filename): 
    output = Drawing2D()
    output.load_from_dxf(filename)
    return output

def main():
    # SETTING EXPECTED ARGUMENTS
    parser = argparse.ArgumentParser(formatter_class=argparse.ArgumentDefaultsHelpFormatter)
    parser.add_argument("-i", "--image", help=_("Image file [raster: jpg, png; vector: dxf]"))
    parser.add_argument("-I", "--info",   action='store_true', help=_("Only show info about the image"),   default=False)
    parser.add_argument("-W", "--width",    help=_("Engraving width"),        default=0)
    parser.add_argument("-H", "--height",    help=_("Engraving height"),      default=0)
    
    # GET ARGUMENTS
    args = parser.parse_args()
    
    image_file = args.image
    target_width    = float(args.width)
    target_height   = float(args.height)
    
    
    filename, ext = os.path.splitext(image_file) 
    
    if ext == '.dxf':
        drawing = preprocess_dxf_image(image_file)
        drawing.normalize()
        drawing.scale_to(target_width, target_height)
        
        info = {
            'type' : 'VECTOR',
            'layers' : []
        }
        
        for l in drawing.layers:
            info['layers'].append( {'name':l.name.replace(" ", "_"), 'description': l.name, 'color':l.color, 'elements_count' : len(l.primitives)} )
        
    elif ext == '.jpg' or ext == '.jpeg' or ext == '.png':
        
        info = {
            'type' : 'RASTER',
            'layers' : [],
            'width' : 0,
            'height' : 0
        }
        
        img = cv2.imread(image_file)
        h, w = img.shape[:2]
        
        info['width'] = w
        info['height'] = h
    
    print json.dumps(info)


if __name__ == "__main__":
    main()
#!/bin/env python
import json
import os
import shutil
import glob

from fabtotum.os.paths import LIB_PATH, FABUI_PATH, USERDATA_PATH


laser_presets_factory_folder  = FABUI_PATH    + 'cam/laser/'
laser_presets_userdata_folder = USERDATA_PATH + 'cam/laser/'
default_head                  = [4]


laser_presets_factory_files = glob.glob(laser_presets_factory_folder + "*.json")

for file in laser_presets_factory_files:
    
    write  = False
    preset = {}
    
    with open(file) as json_data:
        preset = json.load(json_data)
    
    ### add fan key if not exists
    if 'fan' not in preset['general']:
        preset['general']['fan'] = True
        write = True
    
    ### add head key if not exists    
    if 'head' not in preset['general']:
        preset['general']['head'] = default_head
        write = True
        
    ### if changes were made write file and copy to userdata folder
    if(write == True):
        ### write file
        with open(file, 'w') as outfile:
            json.dump(preset, outfile, sort_keys=True, indent=4)
        ### copy to userdata
        userdata_file = laser_presets_userdata_folder + os.path.basename(file)
        shutil.copy(file, userdata_file)
        

#!/bin/env python
import json
import os
import shutil
import glob

from fabtotum.os.paths import LIB_PATH, FABUI_PATH, USERDATA_PATH

heads_factory_folder  = FABUI_PATH    + 'heads/'
heads_userdata_folder = USERDATA_PATH + 'heads/'

feeders_factory_folder  = FABUI_PATH    + 'feeders/'
feeders_userdata_folder = USERDATA_PATH + 'feeders/'



def add_new_field(head_list, head):
    
    for file in head_list:
    
        filename = os.path.basename(file)
        
        with open(file) as json_data:
            data = json.load(json_data)
        
        """
        if "init_gcode" key doesn't exists and is a fabtotum head/module
        add "init_gcode" key and set "custom_gcode" to ""
        """
        
        write = False
        
        if head == True :
            
            if not "init_gcode" in data and int(data['fw_id']) < 100:
                data['init_gcode']   = data['custom_gcode']
                data['custom_gcode'] = ''
                write = True
                
        if head == False :
            
            if not "init_gcode" in data :
                data['init_gcode'] = ''
                write = True
        
        if write == True :
            with open(file, 'w') as outfile:
                json.dump(data, outfile, sort_keys=True, indent=4)          
        

# load factory heads
heads_factory_files = glob.glob(heads_factory_folder + "*.json")

# load userdata heads
heads_userdata_files = glob.glob(heads_userdata_folder + "*.json")

# load factory feeders
feeders_factory_files = glob.glob(feeders_factory_folder + "*.json")

# load factory feeders
feeders_userdata_files = glob.glob(feeders_userdata_folder + "*.json")

# fix factory heads
add_new_field(heads_factory_files, True)

# fix userdata heads
add_new_field(heads_userdata_files, True)

# fix factory feeders
add_new_field(feeders_factory_files, False)

# fix userdata feeders
add_new_field(feeders_userdata_files, False)

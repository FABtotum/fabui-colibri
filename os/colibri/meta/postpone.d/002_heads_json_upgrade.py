#!/bin/env python
import json
import os
import shutil
import glob

from fabtotum.os.paths import LIB_PATH, FABUI_PATH, USERDATA_PATH

factory_folder  = FABUI_PATH    + 'heads/'
userdata_folder = USERDATA_PATH + 'heads/'


HEADS_ORDER = {
    "printing_head_pro.json" : 10,
    "laser_head_pro.json"    : 20,
    "printing_head.json"     : 30,
    "milling_head.json"      : 40,
    "laser_head.json"        : 50,
    "hybrid_head.json"       : 60
}



def fix_heads_order(head_list):
    
    for file in head_list:
    
        filename = os.path.basename(file)
        
        with open(file) as json_data:
            head_data = json.load(json_data)
        
        # set order
        if(filename in HEADS_ORDER):
            head_data['order'] = HEADS_ORDER[filename]
        else:
            head_data['order'] = 1000
        
        #write to file
        with open(file, 'w') as outfile:
            json.dump(head_data, outfile, sort_keys=True, indent=4)


# load factory heads
heads_factory_files = glob.glob(factory_folder + "*.json")

# load userdata heads
heads_userdata_files = glob.glob(userdata_folder + "*.json")

# fix factory heads
fix_heads_order(heads_factory_files)

# fix userdata heads
fix_heads_order(heads_userdata_files)

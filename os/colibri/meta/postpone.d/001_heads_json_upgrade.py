#!/bin/env python
import json
import os
import shutil

from fabtotum.os.paths import LIB_PATH, FABUI_PATH

print_head_old 			= os.path.join(LIB_PATH,   'heads', 'printing_head_lite.json')
print_head_new 			= os.path.join(LIB_PATH,   'heads', 'printing_head.json')
print_head_factory 		= os.path.join(FABUI_PATH, 'heads', 'printing_head.json')

milling_head_old 		= os.path.join(LIB_PATH,   'heads', 'milling_head_v2.json')
milling_head_new 		= os.path.join(LIB_PATH,   'heads', 'milling_head.json')
milling_head_factory	= os.path.join(FABUI_PATH, 'heads', 'milling_head.json')

settings_file			= os.path.join(LIB_PATH,   'settings', 'settings.json')

def upgrade_head_json(old_fn, new_fn, factory_fn):
	shutil.move(old_fn, new_fn)
	
	with open(new_fn) as json_data:
		old_info = json.load(json_data)
	
	with open(factory_fn) as json_data:
		factory_info = json.load(json_data)
	
	# Preserve all previous attributes except name and description
	old_info['name'] = factory_info['name']
	old_info['description'] = factory_info['description']
	
	with open(new_fn, 'w') as outfile:
		json.dump(old_info, outfile, sort_keys=True, indent=4)

################################################################################

# Rename and upgrade print_head_lite.json
if os.path.exists(print_head_old):
	print "Printing head lite upgrade"
	upgrade_head_json(print_head_old, print_head_new, print_head_factory)

# Rename and upgrade milling_head_v2.json
if os.path.exists(milling_head_old):
	print "Milling head upgrade"
	upgrade_head_json(milling_head_old, milling_head_new, milling_head_factory)
	
# Upgrade to new head names in settings.json
with open(settings_file) as json_data:
	settings = json.load(json_data)
	
if settings['hardware']['head'] == 'printing_head_lite':
	settings['hardware']['head'] = 'printing_head'
	
if settings['hardware']['head'] == 'milling_head_v2':
	settings['hardware']['head'] = 'milling_head'
	
with open(settings_file, 'w') as outfile:
	json.dump(settings, outfile, sort_keys=True, indent=4)

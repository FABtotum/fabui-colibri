#!/bin/env python
# -*- coding: utf-8; -*-
#
# (c) 2017 FABtotum, http://www.fabtotum.com
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
import os
import json
import shlex, subprocess

# Import external modules

# Import internal modules
from fabtotum.fabui.config import ConfigService
from fabtotum.utils import create_dir, create_link, build_path, \
                            find_file, copy_files, remove_dir, remove_file
from fabtotum.database      import Database
from fabtotum.database.plugin import Plugin

#############################################
def activate_plugin(plugin, config=None, db=None):
	if not config:
		config = ConfigService()
	if not db:
		db = Database(config)
		
	plugins_path = config.get('general', 'plugins_path')
	fabui_path = config.get('general', 'fabui_path')

	plugin_dir = os.path.join(plugins_path, plugin)
	
	if not os.path.exists(plugin_dir):
		return False

	# Link controller
	create_link( os.path.join(plugin_dir, 'controller.php'), os.path.join(fabui_path, 'application/controllers/Plugin_{0}.php'.format(plugin) ) ) 
	# Link views
	create_dir(os.path.join( fabui_path, 'application/views/plugin' ) )
	create_link( os.path.join(plugin_dir, 'views'), os.path.join(fabui_path, 'application/views/plugin/{0}'.format(plugin)) ) 
	# Link assets
	create_dir( os.path.join( fabui_path, 'assets/plugin' ) )
	create_link( os.path.join(plugin_dir, 'assets'), os.path.join(fabui_path, 'assets/plugin/{0}'.format(plugin)) ) 

	meta_file = os.path.join( plugin_dir, "meta.json" )

	with open(meta_file) as f:
		meta_content = f.read()

	p = Plugin(db)
	p['name'] = plugin
	p['attributes'] = meta_content
	p.write()

	return True
	
def deactivate_plugin(plugin, config=None, db=None):
	if not config:
		config = ConfigService()
	if not db:
		db = Database(config)
		
	fabui_path = config.get('general', 'fabui_path')
	
	remove_file( os.path.join(fabui_path, 'application/controllers/Plugin_{0}.php'.format(plugin) ) )
	remove_file( os.path.join(fabui_path, 'application/views/plugin/{0}'.format(plugin)) )
	remove_file( os.path.join(fabui_path, 'assets/plugin/{0}'.format(plugin)) )
	
	p = Plugin(db)
	p.query_by('name', plugin)
	p.delete()
	
	return True

def get_installed_plugins(config=None):
	if not config:
		config = ConfigService()
		
	plugins_path = config.get('general', 'plugins_path')
	
	result = {}
	
	for dirname in os.listdir(plugins_path):
		plugin_dir = os.path.join( plugins_path, dirname)
		plugin_meta = os.path.join(plugin_dir, "meta.json")
		if os.path.exists(plugin_meta):
			with open(plugin_meta) as f:
				meta = json.loads( f.read() )
				result[dirname] = meta
				
	return result
	
def get_active_plugins(config=None, db=None):
	if not config:
		config = ConfigService()
	if not db:
		db = Database(config)
		
	return Plugin(db).get_active_plugins()

def remove_plugin(plugin, config=None):
	if not config:
		config = ConfigService()
	
	plugins_path = config.get('general', 'plugins_path')
	plugin_dir = os.path.join( plugins_path, plugin )
	
	if os.path.exists(plugin_dir):
		remove_dir(plugin_dir)
		return True
		
	return False

def extract_plugin(plugin_filename, config=None):
	"""
	Extract plugin file and verify that it IS a plugin archive
	"""
	if not config:
		config = ConfigService()
		
	top = ""
	meta = {}
	
	temp_dir = build_path( config.get('general', 'temp_path'), 'new_plugin' )
	create_dir(temp_dir)
	
	cmd = "unzip {0} -d {1} -o".format(plugin_filename, temp_dir)
	try:
		subprocess.check_output( shlex.split(cmd) )
	except subprocess.CalledProcessError as e:
		pass
	
	fn = find_file("meta.json", temp_dir)

	try:
		fn = fn[0]
		f = open(fn)
		meta =  json.loads( f.read() )
		top = os.path.dirname(fn)
	except Exception as e:
		remove_dir(temp_dir)
	
	return top, meta
	
def install_plugin(plugin_filename, config=None, db=None):
	if not config:
		config = ConfigService()
		
	plugins_path = config.get('general', 'plugins_path')
	top, meta = extract_plugin(plugin_filename, config)

	if meta:
		slug = meta['plugin_slug']
		plugin_dir  = os.path.join(plugins_path, slug)
		
		installed_plugins = get_installed_plugins(config)
		active_plugins = get_active_plugins(config, db)
		
		was_active = False
		if slug in active_plugins:
			deactivate_plugin(slug, config, db)
			was_active = True
		
		if slug in installed_plugins:
			remove_plugin(slug, config)

		create_dir(plugin_dir)
		copy_files( os.path.join(top, '*'), plugin_dir)
		
		remove_dir(top)
		
		if was_active:
			activate_plugin(slug, config, db)
		
		return True
		
	return False

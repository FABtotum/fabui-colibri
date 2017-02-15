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

# Import standard python module
import argparse
import time
import os
import json
import gettext

# Import external modules

# Import internal modules
from fabtotum.development.templating import create_from_template, create_dir, create_link, build_path

# Set up message catalog access
tr = gettext.translation('fab_creator', 'locale', fallback=True)
_ = tr.ugettext 

def create_plugin(args, raw_meta):
        
    # Plugin name
    #~ plugin_name = raw_input("Plugin name (human readable): ")
    #~ if not plugin_name:
        #~ print "No name provided. Exiting..."
        #~ exit()
        
    #~ # Plugin slag
    #~ slug = plugin_name.lower().replace(' ', '_')
    #~ plugin_slug = raw_input("Plugin slag ({0}): ".format(slug))
    #~ if not plugin_slug:
        #~ plugin_slug = slug

    #~ # Version
    #~ plugin_version = raw_input("Plugin version (1.0): ")
    #~ if not plugin_version:
        #~ plugin_version = "1.0"
            
    #~ # Plugin description
    #~ plugin_desc = raw_input("Description: ")
    #~ # Plugin autor
    #~ plugin_author = raw_input("Author: ")
    
    #~ # Menu item
    #~ menu_title = plugin_name
    #~ menu_icon = 'fa-cube'
    #~ menu_url = 'plugin/' + plugin_slug
    
    #~ plugin_menu_title = raw_input("Manu title ({0}): ".format(menu_title))
    #~ if not plugin_menu_title:
        #~ plugin_menu_title = menu_title
    #~ plugin_menu_icon = raw_input("Manu icon ({0}): ".format(menu_icon))
    #~ if not plugin_menu_icon:
        #~ plugin_menu_icon = menu_icon
    #~ plugin_menu_url = raw_input("Manu url ({0}): ".format(menu_url))
    #~ if not plugin_menu_url:
        #~ plugin_menu_url = menu_url
        
    meta = {
        "name" : raw_meta['name'],
        "version" : raw_meta['version'],
        "description" : raw_meta['description'],
        "plugin_uri" : raw_meta['url'],
        "plugin_slug" : raw_meta['slug'],
        "required_bundles" : [],
        "author" : raw_meta['author'],
        "author_uri" : raw_meta['author_url'],
        "icon" : "",
        "filetypes" : [],
        "hooks" : [],
        "menu" : {}
    }
    
    menu_loc = raw_meta['menu'][0].pop('loc')
    meta['menu'][menu_loc] = raw_meta['menu']

    destDir = build_path(args.dest, raw_meta['slug'])
    create_dir(destDir)
    
    meta_filename = build_path(destDir, 'meta.json')

    with open(meta_filename, 'w') as outfile:
        outfile.write( json.dumps(meta, indent=4) )
    
    env = {
        "plugin_name" : raw_meta['name'],
        "plugin_version" : raw_meta['version'],
        "plugin_description" : raw_meta['description'],
        "plugin_author" : raw_meta['author'],
        "plugin_slug" : raw_meta['slug'],
        "plugin_icon" : raw_meta['menu'][0]['icon']
    }
    
    filename = build_path(destDir, 'controller.php')
    create_from_template('php/controller.php.template', filename, env, overwrite=True)
    
    create_dir( build_path(destDir, 'views') )
    create_dir( build_path(destDir, 'assets/img') )
    create_dir( build_path(destDir, 'assets/js') )
    create_dir( build_path(destDir, 'scripts') )
    create_dir( build_path(destDir, 'bin') )
    
    filename = build_path(destDir, 'views/js.php')
    create_from_template('php/js.php.template', filename, env, overwrite=True)
    
    filename = build_path(destDir, 'views/main_widget.php')
    create_from_template('php/js.php.template', filename, env, overwrite=True)

def create_controller(args):
    
    env = {
        "plugin_name" : "Hello World",
        "plugin_version" : "1.0",
        "plugin_description" : "",
        "plugin_author" : "FABteam",
        "plugin_slug" : "helloworld"
    }
    
    create_from_template('php/controller.php.template', 'controller.php', env, overwrite=True)
    pass
    
def main():
   
    # SETTING EXPECTED ARGUMENTS
    parser = argparse.ArgumentParser(formatter_class=argparse.ArgumentDefaultsHelpFormatter)
    parser.add_argument("command",       help=_("Command.") )
    parser.add_argument("-d", "--dest",  help="Destination",  default='.' )
    parser.add_argument("-i", "--input",  help="Generation plan", required=True)
    #parser.add_argument("-t", "--type",  help="Destination",  default='default' )
    # GET ARGUMENTS    
    args = parser.parse_args()
    
    CMDS = {
        'plugin' : create_plugin,
        'controller' : create_controller
    }
    
    plan_file = args.input
    
    with open(plan_file) as f:
        plan = json.loads( f.read() )
    
    cmd = args.command
    if cmd in CMDS:
        CMDS[cmd](args, plan[cmd])

if __name__ == "__main__":
    main()

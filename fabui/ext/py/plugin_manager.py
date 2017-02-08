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
import os
import re
import argparse
import time
import gettext
import json
import shlex, subprocess

# Import external modules
from watchdog.observers import Observer
from watchdog.events import PatternMatchingEventHandler
import pycurl

# Import internal modules
from fabtotum.fabui.gpusher import GCodePusher
from fabtotum.update.factory  import UpdateFactory
from fabtotum.update import BundleTask, FirmwareTask, BootTask
from fabtotum.utils import create_dir, create_link, build_path, \
                            find_file, copy_files, remove_dir

# Set up message catalog access
tr = gettext.translation('update', 'locale', fallback=True)
_ = tr.ugettext

################################################################################

class PluginManagerApplication(GCodePusher):
    """
    Update application.
    """
    
    def __init__(self, arch='armhf', mcu='atmega1280'):
        super(PluginManagerApplication, self).__init__()
        
        #~ self.factory = UpdateFactory(arch=arch, mcu=mcu, config=self.config, gcs=self.gcs, notify_update=self.update_monitor)
        self.update_stats = {}
        
        self.add_monitor_group('update', self.update_stats)      
        
    def playBeep(self):
        self.send('M300')

    def finalize_task(self):
        if self.is_aborted():
            self.set_task_status(GCodePusher.TASK_ABORTING)
        else:
            self.set_task_status(GCodePusher.TASK_COMPLETING)
        
        #~ # do some final stuff
        
        if self.is_aborted():
            self.set_task_status(GCodePusher.TASK_ABORTED)
        else:
            self.set_task_status(GCodePusher.TASK_COMPLETED)
                
        self.stop()
    
    # Only for development
    def trace(self, msg):
        print msg
         
    def state_change_callback(self, state):
        if state == 'aborted' or state == 'finished':
            self.trace( _("Print STOPPED") )
            self.finalize_task()

    #~ def update_monitor(self):
        #~ with self.monitor_lock:
            #~ self.update_stats.update( self.factory.serialize() )
            #~ self.update_monitor_file()
    
    def extract_plugin(self, plugin_filename):
        """
        Extract plugin file and verify that it IS a plugin archive
        """
        top = ""
        meta = {}
        
        temp_dir = build_path( self.config.get('general', 'temp_path'), 'new_plugin' )
        create_dir(temp_dir)
        
        cmd = "unzip {0} -d {1} -o".format(plugin_filename, temp_dir)
        try:
            subprocess.check_output( shlex.split(cmd) )
        except subprocess.CalledProcessError as e:
            pass
        
        fn = find_file("meta.json", temp_dir)
        print "FN", fn
        try:
            fn = fn[0]
            f = open(fn)
            meta =  json.loads( f.read() )
            top = os.path.dirname(fn)
        except Exception as e:
            remove_dir(temp_dir)
        
        return top, meta
    
    def run_install(self, plugins):
        """
        Add plugins to the system
        """
        plugins_path = self.config.get('general', 'plugins_path')
        
        for plugin in plugins:
            top, meta = self.extract_plugin(plugin)
            
            if meta:
                plugin_slug = meta['plugin_slug']
                plugin_dir  = os.path.join(plugins_path, plugin_slug)
                create_dir(plugin_dir)
                
                copy_files( os.path.join(top, '*'), plugin_dir)
                
                remove_dir(top)
            else:
                print "ERROR: File '{0}' is not a plugin archive".format(plugin)
                
        
    def run_remove(self, plugins):
        """
        Remove plugins from the system
        """
        self.run_deactivate(plugins)
        plugins_path = self.config.get('general', 'plugins_path')
        
        for plugin in plugins:
            plugin_dir = os.path.join( plugins_path, plugin )
            
            if os.path.exists(plugin_dir):
                remove_dir(plugin_dir)
        
    def run_activate(self, plugins):
        """
        Activate plugins by creating links in the system to plugin resources
        """
        plugins_path = self.config.get('general', 'plugins_path')
        fabui_path = self.config.get('general', 'fabui_path')
        
        for plugin in plugins:
            plugin_dir = os.path.join(plugins_path, plugin)
            
            if not os.path.exists(plugin_dir):
                continue

            # Link controller
            create_link( os.path.join(plugin_dir, 'controller.php'), os.path.join(fabui_path, 'application/controllers/Plugin_{0}.php'.format(plugin) ) ) 
            # Link views
            create_dir(os.path.join( fabui_path, 'application/view/plugin' ) )
            create_link( os.path.join(plugin_dir, 'views'), os.path.join(fabui_path, 'application/views/plugin/{0}'.format(plugin)) ) 
            # Link assets
            create_dir( os.path.join( fabui_path, 'assets/plugin' ) )
            create_link( os.path.join(plugin_dir, 'assets'), os.path.join(fabui_path, 'assets/plugin/{0}'.format(plugin)) ) 
        
    def run_deactivate(self, plugins):
        """
        Deactivate plugins by removing their links to the system
        """
        fabui_path = self.config.get('general', 'fabui_path')
        
        for plugin in plugins:
            os.unlink( os.path.join(fabui_path, 'application/controllers/Plugin_{0}.php'.format(plugin) ) )
            os.unlink( os.path.join(fabui_path, 'application/views/plugin/{0}'.format(plugin)) )
            os.unlink( os.path.join(fabui_path, 'assets/plugin/{0}'.format(plugin)) )
        
    def run_update(self, task_id, plugins):
        """
        Plugin update procedure
        """
        self.prepare_task(task_id, task_type='plugin', task_controller='update')
        self.set_task_status(GCodePusher.TASK_RUNNING)
    
        # TODO

        print "finishing task"
        self.finish_task()

def main():
    # SETTING EXPECTED ARGUMENTS
    parser = argparse.ArgumentParser(formatter_class=argparse.ArgumentDefaultsHelpFormatter)
    parser.add_argument("command", help=_("Command (actvivate|deactivate|install|remove|update)"))
    parser.add_argument("-T", "--task-id",     help=_("Task ID."),                      default=0)
    parser.add_argument("-p", "--plugins", help=_("Plugin list") )
    
    # GET ARGUMENTS
    args = parser.parse_args()
    # INIT VARs
    task_id     = args.task_id
    command		= args.command
    if args.plugins:
        plugins     = args.plugins.split(',')
    else:
        plugins     = []
        
    app = PluginManagerApplication()

    if command == 'activate':
        app.run_activate(plugins)
    elif command == 'deactivate':
        app.run_deactivate(plugins)
    elif command == 'install':
        app.run_install(plugins)
    elif command == 'remove':
        app.run_remove(plugins)
    elif command == 'update':
        app.run_update(task_id, plugins)
    
    # Only update procedure is a real task with monitor file so it has to
    # initialize loop, other commands don't need any backend support
    if command == 'update':
        app.loop()

if __name__ == "__main__":
    main()



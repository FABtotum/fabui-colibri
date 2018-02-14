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

# Import internal modules
from fabtotum.utils.translation import _, setLanguage
from fabtotum.fabui.gpusher import GCodePusher
from fabtotum.update.factory  import UpdateFactory
from fabtotum.update import BundleTask, FirmwareTask, BootTask
from fabtotum.utils import create_dir, create_link, build_path, \
                            find_file, copy_files, remove_dir, remove_file
from fabtotum.utils.plugin import activate_plugin, deactivate_plugin, \
                            remove_plugin, install_plugin, get_installed_plugins
from fabtotum.database.plugin import Plugin
from fabtotum.update import UpdateFactory, PluginTask

################################################################################

class PluginManagerApplication(GCodePusher):
    """
    Update application.
    """
    
    def __init__(self, arch='armhf', mcu='atmega1280', lang = 'en_US.UTF-8'):
        super(PluginManagerApplication, self).__init__(lang=lang)
        
        self.resetTrace()
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
         
    def state_change_callback(self, state):
        if state == 'aborted' or state == 'finished':
            print "Task STOPPED"
            self.finalize_task()

    def update_monitor(self, factory=None):
        with self.monitor_lock:
            self.update_stats.update( self.factory.serialize() )
            self.update_monitor_file()
    
    def run_install(self, plugins):
        """
        Add plugins to the system
        """
        plugins_path = self.config.get('general', 'plugins_path')
        
        for plugin in plugins:
            if install_plugin(plugin):
                print "ok"
            else:
                print "ERROR: File '{0}' is not a plugin archive".format(plugin)
                
        
    def run_remove(self, plugins):
        """
        Remove plugins from the system
        """
        self.run_deactivate(plugins)
        plugins_path = self.config.get('general', 'plugins_path')
        
        for plugin in plugins:
            self.trace( _("Deleting plugin <strong>{0}</strong> files...").format(plugin) )
            remove_plugin(plugin, self.config)
            self.trace( _("Done") )
            print "ok"
        
    def run_activate(self, plugins):
        """
        Activate plugins by creating links in the system to plugin resources
        """        
        for plugin in plugins:
            self.trace( _("Integrating plugin <strong>{0}</strong> into system...").format(plugin) )
            activate_plugin(plugin, self.config)
            self.trace( _("Done") )
            print "ok"
        
    def run_deactivate(self, plugins):
        """
        Deactivate plugins by removing their links to the system
        """
        for plugin in plugins:
            self.trace( _("Decoupling plugin <strong>{0}</strong> from system...").format(plugin) )
            deactivate_plugin(plugin, self.config)
            self.trace( _("Done") )
            print "ok"
    
    def run_update(self, task_id, plugins):
        """
        Plugin update procedure
        """
        #~ task_id = -1
        print "task started"
        
        self.factory = UpdateFactory(config=self.config, gcs=self.gcs, notify_update=self.update_monitor)
        
        self.prepare_task(task_id, task_type='plugin', task_controller='update')
        self.set_task_status(GCodePusher.TASK_RUNNING)
    
        print "getting online repo"
        repo_plugins = self.factory.getPlugins()
        
        for slug in plugins: 
            print "check", slug
            if slug in repo_plugins:
                repo_plugin = repo_plugins[slug]
                latest = repo_plugin['latest']
                print "Name:", repo_plugin['name']
                print "Latest:", latest
                print "Rels:", repo_plugin['releases']
                
                task = PluginTask(slug, repo_plugin)
                self.factory.addTask(task)
        
        print "downloading"
        
        self.factory.setStatus('downloading')
        for task in self.factory.getTasks():
            self.factory.setCurrentTask( task.getName() )
            self.factory.update()
            self.trace(_("Downloading plugin {0}...").format(task.getName()) )
            task.download()

        active_plugins = Plugin(self.db).get_active_plugins()
        installed_plugins = get_installed_plugins()

        print "installing"
        self.factory.setStatus('installing')    
        for task in self.factory.getTasks():
            self.factory.setCurrentTask( task.getName() )
            self.factory.update()
            self.trace(_("Installing plugin {0}...").format(task.getName()) )
            task.install()
            self.trace(_("Installed plugin {0}").format(task.getName()) )
        
        self.trace(_("Finishing task"))
        print "finishing task"
        self.finalize_task()

def main():
    # SETTING EXPECTED ARGUMENTS
    parser = argparse.ArgumentParser(formatter_class=argparse.ArgumentDefaultsHelpFormatter)
    parser.add_argument("command", help="Command (actvivate|deactivate|install|remove|update)")
    parser.add_argument("-T", "--task-id",     help="Task ID.",       	default=0)
    parser.add_argument("-p", "--plugins",     help="Plugin list" )
    parser.add_argument("--lang",              help="Output language",	default='en_US.UTF-8' )
    
    # GET ARGUMENTS
    args = parser.parse_args()
    # INIT VARs
    task_id     = args.task_id
    command		= args.command
    lang		= args.lang
    if args.plugins:
        plugins     = args.plugins.split(',')
    else:
        plugins     = []
        
    app = PluginManagerApplication(lang=lang)

    if command == 'activate':
        app.run_activate(plugins)
    elif command == 'deactivate':
        app.run_deactivate(plugins)
    elif command == 'install':
        app.run_install(plugins)
    elif command == 'remove':
        app.run_remove(plugins)
    elif command == 'check-updates':
        app.run_check_updates()
    elif command == 'check-repo':
        app.run_check_repo()
    elif command == 'update':
        app.run_update(task_id, plugins)
    
    # Only update procedure is a real task with monitor file so it has to
    # initialize loop, other commands don't need any backend support
    if command == 'update':
        app.loop()

if __name__ == "__main__":
    main()



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
import os, sys
import re
import argparse
import time
import gettext
import commands

# Import external modules
from watchdog.observers import Observer
from watchdog.events import PatternMatchingEventHandler
import pycurl

# Import external modules
from threading import Event, Thread

# Import internal modules
from fabtotum.utils.translation import _, setLanguage
from fabtotum.utils.common import shell_exec, get_dir_free_space, clear_big_temp
from fabtotum.fabui.gpusher import GCodePusher
from fabtotum.update.factory  import UpdateFactory
from fabtotum.update import BundleTask, FirmwareTask, BootTask, PluginTask

################################################################################

class UpdateApplication(GCodePusher):
    """
    Update application.
    """
    
    def __init__(self, arch='armhf', mcu='atmega1280', lang = 'en_US.UTF-8'):
        super(UpdateApplication, self).__init__(lang=lang)
        
        self.resetTrace()
        self.factory = UpdateFactory(arch=arch, mcu=mcu, config=self.config, gcs=self.gcs, notify_update=self.update_monitor)
        self.update_stats = {}
        
        self.add_monitor_group('update', self.update_stats)      
        
    def playBeep(self):
        self.send('M300')

    def finalize_task(self):
        if self.is_aborted():
            self.set_task_status(GCodePusher.TASK_ABORTING)
        else:
            self.set_task_status(GCodePusher.TASK_COMPLETING)
        
        # do some final stuff
        
        if self.is_aborted():
            self.set_task_status(GCodePusher.TASK_ABORTED)
        else:
            self.set_task_status(GCodePusher.TASK_COMPLETED)
        
        print "task finalized"        
        self.stop()
        sys.exit()
    
    # Only for development
    def trace(self, msg):
        print msg
         
    def state_change_callback(self, state):
        if state == 'aborted' or state == 'finished':
            # self.trace( _("Print STOPPED") )
            self.finalize_task()

    def update_monitor(self, factory=None):
        with self.monitor_lock:
            self.update_stats.update( self.factory.serialize() )
            self.update_monitor_file()
    
    def clearFolder(self):
        """ clear old files """
        #folder = self.config.get('general', 'bigtemp_path')
        #shell_exec('sudo rm -rvf {0}/fabui/*.cb {1}/fabui/*.md5sum {2}/fabui/boot-*.zip {3}/fabui/fab_*.zip'.format(folder, folder, folder, folder))
        clear_big_temp()
    
    def run(self, task_id, bundles, firmware_switch, boot_switch, plugins):
        """
        """
        
        # TODO
        # check availabel free disk space
        #
        
        self.clearFolder()
        self.prepare_task(task_id, task_type='update', task_controller='updates')
        self.set_task_status(GCodePusher.TASK_RUNNING)
        
        free_disk_space = get_dir_free_space(self.factory.getTempFolder())
        
        # print "free disk space ", free_disk_space
        # self.trace( _("Update initialized.") )
        
        self.factory.setStatus('init')
        self.factory.setMessage(_("Connecting to update server"))
        self.factory.update()
        
        if bundles:
            remote_bundles = self.factory.getBundles()
            if remote_bundles:
                for bundle_name in bundles:
                    bundle = BundleTask(bundle_name, remote_bundles[bundle_name], self.factory.getEndpoint('bundle'))
                    self.factory.addTask(bundle)
        
        if plugins:
            remote_plugins = self.factory.getPlugins()
            for slug in plugins:
                if slug in remote_plugins:
                    repo_plugin = remote_plugins[slug]
                    latest = repo_plugin['latest']
                    task = PluginTask(slug, repo_plugin)
                    self.factory.addTask(task)
                    
        
        if firmware_switch:
            remote_firmware = self.factory.getFirmware()
            if remote_firmware:
                firmware = FirmwareTask("fablin", remote_firmware, self.factory.getEndpoint('firmware'))
                self.factory.addTask(firmware)
        
        if boot_switch:
            remote_boot = self.factory.getBoot()
            if remote_boot:
                boot = BootTask("boot", remote_boot, self.factory.getEndpoint('boot'))
                self.factory.addTask(boot)
        
        # 
        # count update size
        # and compare with
        # available free disk space
        # abort task if space is not enough
        total_size = 0
        for task in self.factory.getTasks():
            size = task.getFilesSize()
            total_size += size
        
        if(free_disk_space <=  total_size):
            self.factory.setStatus('error')
            self.factory.setMessage(_("Not enough disk space for the update. Please free disk space and try again"))
            self.factory.update()
            self.finalize_task()
        
        # start downloading all bundles | plugins | firmware
        
        self.send('M150 R0 U255 B0 S50')
        
        self.factory.setStatus('downloading')
        
        for task in self.factory.getTasks():
            self.factory.setCurrentTask( task.getName() )
            self.factory.update()
            task.setInstallable(task.download()) # if download fails task is not installable
        
        
        self.factory.setStatus('installing')
        
        # start install all all bundles | plugins | firmware
        
        for task in self.factory.getTasks():
            
            if(task.isInstallable()):
                
                self.factory.setCurrentTask( task.getName() )
                self.factory.update()
                self.send('M150 R0 U255 B0 S100')
                task.install()
                self.send('M150 R0 U255 B0 S100')

        # clear downloaded files
        for task in self.factory.getTasks():
            task.remove()
        
        self.playBeep()
        
        # Set ambient colors
        try:
            color = self.config.get('settings', 'color')
        except KeyError:
            color = {
                'r' : 255,
                'g' : 255,
                'b' : 255,
            }
        
        self.send("M701 S{0}".format(color['r']), group='bootstrap')
        self.send("M702 S{0}".format(color['g']), group='bootstrap')
        self.send("M703 S{0}".format(color['b']), group='bootstrap')
        
        # print "finishing task"
        self.factory.setStatus('')
        self.finish_task()
        self.clearFolder()


def main():
    # SETTING EXPECTED ARGUMENTS
    parser = argparse.ArgumentParser(formatter_class=argparse.ArgumentDefaultsHelpFormatter)
    parser.add_argument("-T", "--task-id",                       help="Task ID.", default=0)
    parser.add_argument("-b", "--bundles",                       help="Bundle name to be updated" )
    parser.add_argument("-p", "--plugins",                       help="Plugins to update")
    parser.add_argument("--boot", action="store_true",           help="Update boot files" )
    parser.add_argument("-f", "--firmware", action="store_true", help="Update firmware" )
    parser.add_argument("--lang",                                help="Output language", default='en_US.UTF-8' )
    
    # GET ARGUMENTS
    args = parser.parse_args()

    # INIT VARs
    task_id     = args.task_id
    #~ bundle      = args.bundle
    if args.bundles:
        bundles     = args.bundles.split(',')
    else:
        bundles     = []
    
    # plugins
    if args.plugins:
        plugins = args.plugins.split(',')
    else:
        plugins = []    
        
    firmware    = args.firmware
    boot        = args.boot
    lang        = args.lang
    
    app = UpdateApplication(lang=lang)
    
    app.run(task_id, bundles, firmware, boot, plugins)
    
    app.loop()
    #app_thread.join()

if __name__ == "__main__":
    main()


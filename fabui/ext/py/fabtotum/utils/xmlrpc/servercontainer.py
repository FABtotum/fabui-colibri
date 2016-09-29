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

__authors__ = "Daniel Kesler"
__license__ = "GPL - https://opensource.org/licenses/GPL-3.0"
__version__ = "1.0"

from threading import Thread

from SimpleXMLRPCServer import SimpleXMLRPCServer
from SimpleXMLRPCServer import SimpleXMLRPCRequestHandler

class ServerContainer:
    
    # Restrict to a particular path.
    class RequestHandler(SimpleXMLRPCRequestHandler):
        rpc_paths = ('/FABUI',)
    
    def __init__(self, host, port, core_instance, logger = None):
        # Create server
        self.host = host
        self.port = port
        self.container = core_instance
        self.log = logger
        self.running = False
    
    def __loop_thread(self):
        while self.running:
            self.server.handle_request()
        if self.log:
            self.log.debug("XML-RPC server: stopped")
    
    def start(self):
        self.server = SimpleXMLRPCServer(
                            (self.host, self.port),
                            requestHandler=self.RequestHandler,
                            logRequests=True)
                                    
        self.server.register_introspection_functions()
        
        self.server.register_instance( self.container )
    
        self.running = True
        
        if self.log:        
            self.log.debug("XML-RPC server: started")
        
        self.loop_thread = Thread( target=self.__loop_thread )
        self.loop_thread.start()
    
    def stop(self):
        import xmlrpclib
        self.running = False
        # dummy request to wake up the server so that it can
        # "see" running is False
        s = xmlrpclib.ServerProxy('http://{0}:{1}/FABUI'.format(self.host, self.port))
        s.system.listMethods()
    
    def loop(self):
        self.loop_thread.join()


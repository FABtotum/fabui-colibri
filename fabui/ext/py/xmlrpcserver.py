import logging
import time
import signal
import json

# Import internal modules
from fabtotum.fabui.config import ConfigService
from fabtotum.utils.gcodefile import GCodeFile, GCodeInfo
from fabtotum.utils.pyro.gcodeclient import GCodeServiceClient
from fabtotum.database      import Database, timestamp2datetime, TableItem
from fabtotum.database.task import Task
from fabtotum.database.file import File
from fabtotum.database.object  import Object
from fabtotum.database.obj_file import ObjFile

from fabtotum.fabui.macros.all import PRESET_MAP

from fabtotum.utils.xmlrpc.servercontainer import ServerContainer

def signal_handler(signal, frame):
    print "You pressed Ctrl+C!"
    logger.debug("Shutting down services. Please wait...")
    rpc.stop()


class ExposeCommands:
    
    def __init__(self, gcs, config):
        print "MyFuncs.__init__"
        self.gcs = gcs
        self.config = config
        self.macro_warning = 0
        self.macro_error = 0
        self.macro_skipped = 0
    
    def trace(self, msg):
        print msg
    
    def send(self, code, block = True, timeout = None):
        return self.gcs.send(code, block=block, timeout=timeout)
        
    def exec_macro(self, preset, args = None, atomic = True):
        """
        Execute macro command.
        """
        self.reset_macro_status()
        if atomic:
            self.macro_start()
        
        if preset in PRESET_MAP:
            reply = PRESET_MAP[preset](self, args)
        #~ else:
            #~ print _("Preset '{0}' not found").format(preset)
        
        print 'reply:', reply
        
        if atomic:
            self.macro_end()
        
        if self.macro_error > 0:
            response = False
        else:
            response = True
            
        if reply is None:
            reply = 'ok'
            
        result = {}
        result['response']  = response
        result['reply']     = reply
        
        print 'reply fixed:', reply
        print 'result', result
        
        return json.dumps(result)
    
    def reset_macro_status(self):
        """
        Reset macro status counters to zero.
        """
        self.macro_warning = 0
        self.macro_error = 0
        self.macro_skipped = 0
    
    def macro_start(self):
        """ 
        Start macro execution block. This will activate atomic execution and 
        only commands marked as `macro` will be executed. Others will be aborted.
        """
        self.gcs.atomic_begin(group = 'macro')
        
    def macro_end(self):
        """ End macro execution block and atomic execution. """
        self.gcs.atomic_end()
    
    def macro(self, code, expected_reply, timeout, error_msg, delay_after, warning=False, verbose=True):
        """
        Send a command and check it's reply.
        
        :param code: gcode
        :param expected_reply: Expected reply
        :param error_msg: Error message to display
        :param timeout: Reply timeout in seconds
        :param delay_after: Time in seconds to wait after receiving the rely
        :param warning: Treat wrong reply as warning not as error
        :param verbose: Whether initial message should be displayed or not.
        :type code: string
        :type expected_reply: string
        :type timeout: float
        :type error_msg: string
        :type delay_after: float
        :type warning: bool
        :type verbose: bool
        """
        reply = None
        
        if self.macro_error == 0:
            if verbose:
                self.trace(error_msg)
            
            reply = self.gcs.send(code, timeout=timeout, group = 'macro')
            if expected_reply:
                # Check if the reply is as expected
                if reply:
                    if reply[0] != expected_reply:
                        if warning:
                            self.trace(error_msg + _(": Warning!"))
                            self.macro_warning += 1
                        else:
                            self.trace(error_msg + _(": Failed ({0})".format(reply[0]) ))
                            self.macro_error += 1
                else:
                    self.trace(error_msg + _(": Failed ({0})".format('<aborted>') ))
                    self.macro_error += 1
        else:
            self.trace(error_msg + _(": Skipped"))
            self.macro_skipped += 1
                
        time.sleep(delay_after) #wait the desired amount
        
        return reply

# Setup logger
logger = logging.getLogger('FabtotumService')
logger.setLevel(logging.DEBUG)

ch = logging.StreamHandler()

formatter = logging.Formatter("%(levelname)s : %(message)s")
ch.setFormatter(formatter)
ch.setLevel(logging.DEBUG)
logger.addHandler(ch)

gcs = GCodeServiceClient()

config = ConfigService()

SOCKET_HOST         = config.get('xmlrpc', 'host')
SOCKET_PORT         = config.get('xmlrpc', 'port')

rpc = ServerContainer(SOCKET_HOST, int(SOCKET_PORT), ExposeCommands(gcs, config), logger)
rpc.start()

# Ensure CTRL+C detection to gracefully stop the server.
signal.signal(signal.SIGINT, signal_handler)

rpc.loop()


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
import time
import re
import os
import threading
from threading import Event, Thread, RLock
try:
    import queue
except ImportError:
    import Queue as queue
    
# Import external modules
import serial

# Import internal modules
from fabtotum.utils.singleton import Singleton
from fabtotum.utils.gcodefile import GCodeFile
from fabtotum.totumduino.hooks import action_hook
from fabtotum.totumduino.hardware import reset as totumduino_reset
from fabtotum.fabui.bootstrap import hardwareBootstrap
from fabtotum.database import Database
from fabtotum.database.task import Task
from fabtotum.totumduino.format import partialM109, partialM190, partialM303
#############################################

HOOKS = [
    action_hook
]

class Command(object):
    
    """
    Command objects store individual commands sent to the ``GCodeService``. Along with the 
    command id they contain all the necesary data to execute and handle it.
    
    :param id: Command id can be ``NONE``, ``GCODE``, ``FILE``, ``ABORT``, ``TERMINATE``, ``PAUSE``, ``RESUME``, ``ZMODIFY`` and ``RESET``
    :param data: Any command data
    :param expected_reply: Expected command reply
    :param group: Aknowledge group. ``GCODE`` commands use `'gcode'` and ``FILE`` uses `'file'`
    :type data: any
    :type expected_reply: string
    :type group: string
    """    
    NONE    = 'none'
    GCODE   = 'gcode'
    FILE    = 'file'
    ABORT   = 'abort'
    FINISH  = 'finish'
    PAUSE   = 'pause'
    RESUME  = 'resume'
    RESUMED  = 'resumed'
    ZMODIFY = 'zmodify'
    KILL    = 'kill'
    RESET   = 'reset'
    TERMINATE   = 'terminate'
    
    def __init__(self, id, data = None, expected_reply = 'ok', group = 'raw', timeout = None, async = False):
        self.id = id
        self.aborted = False
        self.expired = False
        self.async = async
        self.data = data
        self.reply = []
        self.__ev = Event()
        self.expected_reply = expected_reply
        self.group = group
        self.timestamp = time.time()
        self.timeout = timeout

    def __str__(self):
        msg = 'cmd: ' + self.id
        if isinstance(self.data, str):
            msg += ', data: ' + self.data
        else:
            msg += ', data: <' + str(type(self.data)) + '>'
        return msg
    
    def __eq__(self, other):
        if isinstance(other, str):
            return self.id == other
        elif isinstance(other, Command):
            return self.id == other.id
        else:
            return NotImplemented
    
    def notify(self, abort = False, expire = False):
        """
        Notify the waiting thread that the reply has been received.
        """
        self.aborted = abort
        self.expired = expire
        self.__ev.set()
        
    def wait(self, timeout = None):
        """
        Wait for until a reply to this command is receiver or timeout expires.
        
        :param timeout: Time in seconds to wait until returning. If this parameter is omitted no timeout will be used.
        :type timeout: float, None
        """
        return self.__ev.wait(timeout)
    
    def isAborted(self):
        """
        Check whether the command was aborted.
        """
        return self.aborted
    
    def isExpired(self):
        """
        Check whether the command has expired.
        """
        return self.expired
    
    def isGroup(self, group):
        """
        Check whether the command belongs to he provided group.
        """
        return (self.group == group) or (self.group == '*')
    
    def hasExpired(self):
        """ Check whether timeout has expired. """
        if self.timeout == None:
            return False
        return ( (time.time() - self.timestamp) > self.timeout )
    
    def hasExpectedReply(self, line):
        """
        Check whether **line** contains expected reply.
        
        :param line: Line to be checked
        :type line: string
        :returns:   ``True`` if **line** contains the expected reply, ``False`` otherwise
        :rtype: bool
        """
        return line[:len(self.expected_reply)] == self.expected_reply;
        
    def hasError(self, line):
        """
        Check whether a **line** contains an error.
        
        :param line: Line to be checked
        :type line: string
        :returns:   ``True`` if **line** contains an error, ``False`` otherwise
        :rtype: bool
        """
        return line[:5] == 'ERROR';
    
    @classmethod
    def reset(cls):
        """ Constructor for ``RESET`` command. """
        return cls(Command.RESET, None)
        
    @classmethod
    def abort(cls):
        """ Constructor for ``ABORT`` command. """
        return cls(Command.ABORT, None)
        
    @classmethod
    def finish(cls):
        """ Constructor for ``ABORT`` command. """
        return cls(Command.FINISH, None)
        
    @classmethod
    def terminate(cls):
        """ Constructor for ``TERMINATE`` command. """
        return cls(Command.TERMINATE, None)

    @classmethod
    def kill(cls):
        """ Constructor for ``KILL`` command. """
        return cls(Command.KILL, None)

    @classmethod
    def pause(cls):
        """ Constructor for ``PAUSE`` command. """
        return cls(Command.PAUSE, None)

    @classmethod
    def resume(cls):
        """ Constructor for ``RESUME`` command. """
        return cls(Command.RESUME, None)
        
    @classmethod
    def resumed(cls):
        """ Constructor for ``RESUMED`` command. """
        return cls(Command.RESUMED, None)

    @classmethod
    def gcode(cls, code, expected_reply = 'ok', group = 'gcode', timeout = None, async = False):
        """
        Constructor for ``GCODE`` command.
        
        :param code: GCode
        :param expected_reply: Expected reply
        :param group: Acknowledge group. **Used internally**
        :param async: Command reply should be send asynchronously as well
        :type code: string
        :type expected_reply: string
        :type group: string
        """
        return cls(Command.GCODE, code, expected_reply, group, timeout, async)

    @classmethod
    def zmodify(cls, z):
        """
        Constructor for ``ZMODIFY`` command.
        
        :param z: Amount by which to modify z axis.
        :type z: float
        """
        return cls(Command.ZMODIFY, z)

    @classmethod
    def file(cls, filename):
        """
        Constructor for ``FILE`` command.
        
        :param filename: Filename of file to be pushed.
        :type filename: string
        """
        return cls(Command.FILE, filename, 'file')


class GCodeService:
    """This class docstring shows how to use sphinx and rst syntax

    The first line is brief explanation, which may be completed with 
    a longer one. For instance to discuss about its methods. The only
    method here is :func:`function1`'s. The main idea is to document
    the class and methods's arguments with 

    - **parameters**, **types**, **return** and **return types**::

          :param arg1: description
          :param arg2: description
          :type arg1: type description
          :type arg1: type description
          :return: return description
          :rtype: the return type description

    - and to provide sections such as **Example** using the double commas syntax::

          :Example:

          followed by a blank line !

      which appears as follow:

      :Example:

      followed by a blank line

    - Finally special sections such as **See Also**, **Warnings**, **Notes**
      use the sphinx syntax (*paragraph directives*)::

          .. seealso:: blabla
          .. warnings also:: blabla
          .. note:: blabla
          .. todo:: blabla

    .. note::
        There are many other Info fields but they may be redundant:
            * param, parameter, arg, argument, key, keyword: Description of a
              parameter.
            * type: Type of a parameter.
            * raises, raise, except, exception: That (and when) a specific
              exception is raised.
            * var, ivar, cvar: Description of a variable.
            * returns, return: Description of the return value.
            * rtype: Return type.

    .. note::
        There are many other directives such as versionadded, versionchanged,
        rubric, centered, ... See the sphinx documentation for more details.

    Here below is the results of the :func:`function1` docstring.

    """
    
    __metaclass__ = Singleton
    
    IDLE        = 0
    ATOMIC      = 1
    
    FILE_NONE   = 0
    FILE_PUSH   = 1
    FILE_WAIT   = 2
    FILE_PAUSED = 3
    FILE_PAUSED_WAIT = 4
    
    WRITE_TERM   = b'\r\n'
    READ_TERM    = b'\n'
    ENCODING = 'utf-8'
    UNICODE_HANDLING = 'replace'
    
    REPLY_QUEUE_SIZE = 1 
        
    def __init__(self, serial_port, serial_baud, serial_timeout = 5, use_checksum = False, logger = None):
        self.running = False
        self.released = False
        self.is_resetting = False
        self.is_terminating = False
        self.SERIAL_PORT = serial_port
        self.SERIAL_BAUD = serial_baud
        self.SERIAL_TIMEOUT = serial_timeout
        
        # Inter-thread communication
        # Must be defined before any thread is created
        self.cq = queue.Queue() # Command Queue
        self.rq = queue.PriorityQueue(self.REPLY_QUEUE_SIZE) # Reply Queue

        self.ev_tx_started = Event()
        self.ev_rx_started = Event()
        
        self.atomic_sync_lock = RLock()
        
        self.re_z_override = re.compile('(?<=Z)([+|-]*[0-9]*.[0-9]*)')
        self.__init_state()
                        
        # Callback handler
        self.callback = []
        
        self.use_checksum = use_checksum
        
        if logger:
            self.log = logger
        else:
            self.log = logging.getLogger('GCodeService')
            ch = logging.StreamHandler()
            ch.setLevel(logging.DEBUG)
            formatter = logging.Formatter("%(levelname)s : %(message)s")
            ch.setFormatter(formatter)
            self.log.addHandler(ch)
            
        self.gcode_state = {
            "axis_relative_mode" : {
                'x' : False,
                'y' : False,
                'z' : False,
                'e' : False
            },
            "feedrate": 1000.0
        }
    
    """ Internal *private* functions """
    
    @staticmethod
    def __terminate_all_running_tasks():
        db = Database()
        conn = db.get_connection()
        cursor = conn.execute("SELECT * from sys_tasks where status!='completed' and status!='aborted' and status!='terminated' ")
        for row in cursor:
           id = row[0]
           t = Task(db, id)
           t['status'] = 'terminated'
           t.write()
    
    
    def __init_state(self):
        self.active_cmd = None
        self.wait_for_cmd = None
        
        self.file_time_started = None
        self.file_time_finished = None
        self.idle_time_started = time.time()
        self.progress = 0.0
        self.current_line_number = 0
        self.total_line_number = 0
        self.z_override = 0.0
        
        # Note: experimental feature
        self.line_number = 0
        
        self.group_ack = {'gcode' : 0, 'file' : 0, 'macro' : 0, 'override' : 0, 'monitor' : 0}
        self.atomic_group = None
        self.is_atomic = False
        self.file_state = GCodeService.FILE_NONE
        self.last_command = None
        self.first_move = False
        self.gcode_count = 0
        self.printer_halted = False
    
    @staticmethod
    def __is_number(s):
        """ Check whether a string is a valid number """
        try:
            float(s)
            return True
        except ValueError:
            return False
    
    def __file_done_thread(self, last_command):
        """
        To ensure that the callback function cannot block sender/receiver threads
        calling it must be done from a separate thread.
        """
        if last_command:
            self.log.debug("waiting for last_command %s", str(last_command))
            last_command.wait()
        
        self.log.debug("last command finished")
        
        self.progress = 100.0
        
        if self.callback:
            for cb in self.callback:
                cb('file_done', None)
        
        self.progress = 0.0
    
    def __trigger_file_done(self, last_command):
        self.log.debug("trigger_callback %s %s", 'file_done', str(last_command) )
        callback_thread = Thread( 
                target = self.__file_done_thread, 
                args=( [last_command] ) 
                )
        callback_thread.start()
        
    def __callback_thread(self, callback_name, data):
        if self.callback:
            for cb in self.callback:
                cb(callback_name, data)
        
    def __trigger_callback(self, callback_name, data):
        self.log.debug("trigger_callback %s %s", callback_name, str(data) )
        callback_thread = Thread( 
                name="GCodeService-callback-{0}".format(callback_name),
                target = self.__callback_thread, 
                args=( [callback_name, data] ) 
                )
        callback_thread.start()
    
    """ APIs *public* functions """
    def trigger(self, callback_name, data):
        """ Trigger callback function """
        self.__trigger_callback(callback_name, data)
    
    def __reset_thread(self, trigger_file_done = False, destroy_scripts=True):
        if destroy_scripts:
            self.__trigger_callback('self_descruct', None)
            time.sleep(0.1)
            
        self.__reset_totumduino()
        if trigger_file_done:
            self.__trigger_file_done(None)
    
    def __trigger_reset(self, trigger_file_done=False, destroy_scripts=True):
        callback_thread = Thread(
            target = self.__reset_thread, 
            args = ([trigger_file_done, destroy_scripts]) )
        callback_thread.start()
    
    def __send_gcode_command(self, code, group = 'gcode', modify = True):
        """
        Internal gcode send function.
        """
        if isinstance(code, str):
            gcode_raw = code + '\r\n'
            gcode_command = Command.gcode(gcode_raw, group=group)
        elif isinstance(code, Command):
            gcode_raw = code.data + '\r\n'
            gcode_command = code
        else:
            self.log.info("Unknown command")
            raise AttributeError
        
        gcode_label = gcode_raw.split()[0]
        
        for hook in HOOKS:
            trigger, callback_name, callback_data = hook.process_command(self, gcode_raw)
            if trigger:
                self.__trigger_callback(callback_name, callback_data)
        
        # Z Override modification
        if (self.z_override != 0.0) and ('Z' in gcode_raw) and modify:
            get_z_match = self.re_z_override.search(gcode_raw[:-2])
            if get_z_match:
                num_orig = get_z_match.group(1)
                str_orig = 'Z' + num_orig
                if self.__is_number(num_orig):
                    new_z = float(num_orig)+float(self.z_override)
                    str_new = 'Z' + str(new_z) # Safer to use 'Z{num}' then just '{num}'
                    new_cmd = gcode_raw[:-2].replace(str_orig, str_new)
                    
                    self.log.debug('MODIFIED [%s] -> [%s]', gcode_raw[:-2], new_cmd )
                    
                    gcode_raw = new_cmd + '\r\n'
        
        priority = 1
                                
        # Note: experimental feature
        if self.use_checksum:
            data = gcode_raw[:-2]
            self.line_number += 1
            new_code = "N{0} {1}".format(self.line_number, data)
            
            checksum = 0
            
            for c in new_code:
                checksum = int(checksum) ^ ord(c)
            checksum = checksum & 0xff
            
            # Modify original command
            gcode_complete = "{0}*{1}\r\n".format(new_code, checksum)
        else:
            gcode_complete = gcode_raw
        
        if ( self.file_state != GCodeService.FILE_WAIT and
             self.file_state != GCodeService.FILE_PAUSED_WAIT):
            
            self.log.debug("<< %s [RQ: %d]", gcode_complete[:-2], self.rq.qsize() )
            
            self.rq.put( (priority, gcode_command) )
            self.serial.write(gcode_complete)
        else:
            self.log.debug("FILE_WAIT in progress, ignorig command [%s]", gcode_complete[:-2])
            gcode_command.notify(abort=True)

        return gcode_command

    def __push_line(self):
        try:
            # Push 8 commands from gcode file with little delay
            for i in xrange(8):
                if self.file_state == GCodeService.FILE_PUSH:
                    line, attrs = self.file_iter.next()
                    
                    line = line.rstrip()

                    # Access optimization
                    line2 = line[:2]
                    line3 = line[:3]
                    line4 = line[:4]

                    if attrs:
                        self.__trigger_callback('gcode_comment', attrs)
                    
                    if not self.first_move:
                        if ( line2 == 'G0' or 
                             line2 == 'G1'):
                            """ If there was not movement yet, this is identified as the first move """
                            self.__trigger_callback('first_move', None)
                            self.first_move = True
                    
                    self.current_line_number += 1
                    
                    self.progress = 100 * float(self.group_ack['file']) / float(self.gcode_count)
                    
                    if line:
                        self.last_command = self.__send_gcode_command(line, group='file')
                        
                        if ( line4 == 'M109' or
                             line4 == 'M190' or
                             line3 == 'G28'  or
                             line3 == 'G27'):
                            """ 
                            Goto wait state as those commands can take a while and 
                            no push operation must be done before they are executed.
                            """
                            self.file_state = GCodeService.FILE_WAIT
                        
                elif ( self.file_state == GCodeService.FILE_WAIT or 
                       self.file_state == GCodeService.FILE_PAUSED_WAIT):
                    """ Wait for reply before continuing (M109, M190, G28, G27) """
                    
                    if self.last_command.wait(1): # Wait for one second and give the context back
                        if self.file_state == GCodeService.FILE_WAIT:
                            self.file_state = GCodeService.FILE_PUSH
                        elif self.file_state == GCodeService.FILE_PAUSED_WAIT:
                            self.file_state = GCodeService.FILE_PAUSED
        
        except StopIteration:
            # Create a new thread that is waiting for the last command 
            # to get it's reply and call the callback function if one
            # was specified.
            self.file_state = GCodeService.FILE_NONE
            self.__trigger_file_done(self.last_command)
            
            return False

    def __sender_thread(self):
        """
        Sender thread used to send commands to Totumduino.
        """
        self.log.info("sender thread: started")
        
        self.state = GCodeService.IDLE
        
        self.ev_tx_started.set()
        
        while self.running and not self.released:
            
            if self.is_resetting:
                time.sleep(1)
                continue
            
            if self.file_state > GCodeService.FILE_NONE:
                try:
                    # Try to get a command
                    cmd = self.cq.get_nowait()
                except queue.Empty as e:
                    # No queued commands, send a line from the file
                    cmd = Command.NONE
                    self.__push_line()
                    
            else:
                cmd = self.cq.get()

            if self.is_resetting:
                time.sleep(1)
                continue

            if cmd == Command.NONE:
                #~ time.sleep(1)
                continue
                
            self.log.debug('get command from queue: %s', str(cmd))

            if cmd == Command.GCODE:
                # Synchronisation with atomic start/end
                self.atomic_sync_lock.acquire()
                self.atomic_sync_lock.release()
                if (self.is_atomic and cmd.isGroup(self.atomic_group)) or (not self.is_atomic):
                    if not cmd.hasExpired():
                        self.__send_gcode_command(cmd)
                    else:
                        cmd.notify(abort=True, expire=True)
                        self.log.info("Expired [%s]", cmd.data )
                else:
                    self.log.debug("Atomic (%s) in progress, ignoring [%s / %s]", self.atomic_group, cmd.data, cmd.group)
                    cmd.notify(abort=True)
            
            elif cmd == Command.ZMODIFY:
                new_z_override = float( cmd.data )
                old_z_override = float(self.z_override)
                
                z_offset = float(new_z_override - old_z_override)
                
                self.z_override = new_z_override
                
                self.log.debug("ZMODIFY: %f", z_offset)
                self.__send_gcode_command("G91", group="override", modify=False)
                self.__send_gcode_command("G0 Z{0}".format(z_offset), group="override", modify=False)
                self.__send_gcode_command("G90",  group="override", modify=False)
                self.__send_gcode_command("M400", group="override", modify=False)
                self.__send_gcode_command("M300", group="override", modify=False)
                
                self.__trigger_callback('gcode_action:z_override', [ str( round(self.z_override,5) ) ] )
            
            elif cmd == Command.FILE:
                filename = cmd.data
                self.last_command = None
                self.first_move = False
                self.z_override = 0.0
                self.progress = 0.0
                
                try:                
                    gfile = GCodeFile(filename)
                    
                    self.total_line_number = gfile.info['line_count']
                    self.current_line_number = 0
                    self.group_ack['file'] = 0
                    self.file_iter = iter(gfile)
                    
                    self.gcode_count = self.total_line_number = gfile.info['gcode_count']
                    
                    self.file_state = GCodeService.FILE_PUSH
                except Exception as e:
                    self.log.error("gcode file loading, %s", str(e))
                    continue
                
            elif cmd == Command.PAUSE:
                self.__trigger_callback('state_change', 'paused')
                if self.file_state == GCodeService.FILE_PUSH:
                    self.file_state = GCodeService.FILE_PAUSED
                    
                elif self.file_state == GCodeService.FILE_WAIT:
                    self.file_state = GCodeService.FILE_PAUSED_WAIT
            
            elif cmd == Command.RESUMED:
                self.__trigger_callback('state_change', 'resumed')
                if self.file_state == GCodeService.FILE_PAUSED_WAIT:
                    self.file_state = GCodeService.FILE_WAIT
                    
                elif self.file_state == GCodeService.FILE_PAUSED:
                    self.file_state = GCodeService.FILE_PUSH
                    
            elif cmd == Command.RESUME:
                self.__trigger_callback('state_change', 'resuming')
            
            elif cmd == Command.FINISH:
                self.__trigger_callback('state_change', 'finished')
                
                if self.file_state > GCodeService.FILE_NONE:                
                    if ( self.file_state == GCodeService.FILE_WAIT or
                         self.file_state == GCodeService.FILE_PAUSED_WAIT):
                        # There is a long command being executed, we need to
                        # restart the totumduino to abort it.
                        self.__trigger_reset(True, False);
                        self.file_state = GCodeService.FILE_NONE
                        # self.__trigger_file_done(self.last_command), True above means it will be triggered after reset
                    else:
                        self.file_state = GCodeService.FILE_NONE
                        self.__trigger_file_done(self.last_command)
                                    
            elif cmd == Command.ABORT:
                
                if self.active_cmd:
                    if (self.active_cmd.data[:4] == 'M303' or
                        self.active_cmd.data[:4] == 'M109' or
                        self.active_cmd.data[:4] == 'M190'):
                        self.active_cmd.notify(abort=True)
                        self.__trigger_callback('state_change', 'terminated')

                        self.__cleanup()
                        self.__terminate_all_running_tasks()
                        
                        self.__trigger_callback('state_change', 'aborted')
                        os.system('/etc/init.d/fabui emergency &')
                        self.is_terminating = False
                
                self.__trigger_callback('state_change', 'aborted')
                
                if self.file_state > GCodeService.FILE_NONE:                
                    if ( self.file_state == GCodeService.FILE_WAIT or
                         self.file_state == GCodeService.FILE_PAUSED_WAIT):
                        # There is a long command being executed, we need to
                        # restart the totumduino to abort it.
                        self.__trigger_reset(True, False);
                        self.file_state = GCodeService.FILE_NONE
                        # self.__trigger_file_done(self.last_command), True above means it will be triggered after reset
                    else:
                        self.file_state = GCodeService.FILE_NONE
                        self.__trigger_file_done(self.last_command)
            
            elif cmd == Command.TERMINATE:
                
                self.log.info("TERMINATING...")
                self.__trigger_callback('state_change', 'terminated')
                self.__cleanup()
                self.__terminate_all_running_tasks()
                
                os.system('/etc/init.d/fabui emergency &')
                self.is_terminating = False
                
            elif cmd == Command.KILL:
                break
                
        self.log.info("sender thread: stopped")
    
    def __handle_line(self, line_raw):
        """
        Process a one line of reply message.
        """
        
        #~ self.log.debug("__handle_line %s [%s]", line_raw, self.active_cmd)
        
        if self.is_resetting:
            return
        
        try:
            # The received packet is a bytearray so it has to be converted to a
            # string type according to selected ENCODING
            line = line_raw.decode(self.ENCODING, self.UNICODE_HANDLING)
        except Exception as e:
            print e
            return
        
        if not line:
            #print "__handle_line: return line_empty"
            return
        
        # Update idle time start
        self.idle_time_started = time.time()
        
        # If there is no active command try to get it from the reply queue
        if not self.active_cmd:
            try:
                if self.rq.qsize() > 0:
                    priority, self.active_cmd = self.rq.get()
                else:
                    priority, self.active_cmd = self.rq.get_nowait()
            except queue.Empty as e:
                pass
        
        if self.active_cmd:
            self.log.debug("  >> [%s] [%s]", self.active_cmd.data.rstrip(), line.rstrip())
        else:
            self.log.debug("  >> [%s] [%s]", 'None', line.rstrip())
        
        if self.active_cmd:
            # Get the active command as this is the on waiting for the reply.
            cmd = self.active_cmd
            
            if cmd.async:
                # TODO: async mode
                pass
            
            if cmd.data[:4] == 'M303':
                if line[:2] != 'ok':
                    cmd.reply.append( line )
            else:
                cmd.reply.append( line )

            if cmd.hasExpectedReply(line):
                
                # TODO: should a lost command be resent?
                if len(cmd.reply) > 1:
                    if cmd.reply[-2].startswith("Resend:") and cmd.data[:4]: # Second to last line of reply
                        if cmd.data[:4] != 'M999' and cmd.data[:4] != 'M998':
                            resend, line_no = cmd.reply[-2].split(':')
                            self.line_number = int(line_no) -1
                            self.log.error("Communication error. Need to resend command [{0}]".format(cmd.data))
                        
                cmd.notify()
                
                group = self.active_cmd.group
                if group:
                    count = 1
                    if group in self.group_ack:
                        count = self.group_ack[group]

                    count += 1
                        
                    self.group_ack[group] = count
                
                self.active_cmd = None
                
            # Line does not contain expected reply
            else:
                if cmd.reply[-1].startswith('Error:Printer halted.') or cmd.reply[-1].startswith('Printer stopped due to errors.'):
                    self.log.info("Printer halted [%s]", cmd.data)
                    cmd.notify(abort=True)
                    #~ self.printer_halted = True
                    if self.file_state > GCodeService.FILE_NONE:
                        self.file_state = GCodeService.FILE_NONE
                    # self.terminate()
                    # Note: was a fallback for gpiomonitor, not it can trigger a termination (emergency restart)
                    # and leave gpiomonitor frozen for a moment in M730 check so that it does not send the notification
                    # over ws and UI does not detect it
                
                else:
                
                    if cmd.data[:4] == 'M109': # Extruder
                        # [T:27.4 E:0 W:?]
                        #~ temps = line.split()
                        #~ T = temps[0].replace("T:","").strip()
                        temps = partialM109(line)
                        if temps:
                            T = temps['T']
                            self.__trigger_callback('temp_change:ext', [T])
                            
                    elif cmd.data[:4] == 'M190': # Bed
                        # [T:27.38 E:0 B:54.9]
                        temps = partialM190(line)
                        if temps:
                            T = temps['T']
                            B = temps['B']
                            #temps = line.split()
                            #T = temps[0].replace("T:","").strip()
                            #B = temps[2].replace("B:","").strip()
                            self.__trigger_callback('temp_change:ext_bed', [T,B])
                        
                    elif cmd.data[:4] == 'M303': # PID autotune
                        # [ok T:200.57 @:26]
                        #if not line.startswith("PID Autotune failed"):
                        temps = partialM303(line)
                        if temps:
                            T = temps['T']
                            self.__trigger_callback('temp_change:ext', [T])
                        #~ temps = line.split()
                        #~ try:
                            #~ if temps[0][:2] == 'ok':
                                #~ T = temps[1].replace("T:","").strip()
                                #~ self.__trigger_callback('temp_change:ext', [T])
                        #~ except:
                            #~ pass
    
    def __receiver_thread(self):
        """
        Thread handling incoming serial data.
        """
        self.log.info("receiver thread: started")
        
        if not hasattr(self.serial, 'cancel_read'):
            self.serial.timeout = 1
            self.log.info(" serial has no cancel_read")
        
        self.ev_rx_started.set()

        # Run this thread while the service is active        
        while self.running and not self.released:
            
            
            while self.is_resetting:
                time.sleep(1)
                
            #while self.serial.is_open:
            try:
                # read all that is there or wait for one byte (blocking)
                data = self.serial.read(self.serial.in_waiting or 1)
            except serial.SerialException as e:
                # probably some I/O problem such as disconnected USB serial
                # adapters -> exit
                error = e
                print e
                if self.is_resetting:
                    time.sleep(1)
                else:
                    break
            else:
                if data:
                    self.buffer.extend(data)
                    
                    while self.READ_TERM in self.buffer:
                        line_raw, self.buffer = self.buffer.split(self.READ_TERM, 1)
                        self.__handle_line(line_raw)
        
        self.log.info("receiver thread: stopped")
    
    def __reset_totumduino(self):
        """ Does a hardware reset of the totumduino board. """

        # Send a self_descruct command to all running gpusher applications
        
        self.is_resetting = True
        
        self.log.info("__reset_totumduino: started")
        
        self.__cleanup()
        
        totumduino_reset()
        
        time.sleep(5)
        self.__cleanup()
        
        self.atomic_begin('bootstrap')
        
        #time.sleep(1)
        #self.__cleanup()
        #time.sleep(3)
        
    
        self.is_resetting = False
        
        self.log.info("__reset_totumduino: finished")
        
        
        time.sleep(5)
        self.log.info("__reset_totumduino: bootstrap")
        hardwareBootstrap(self, logger=self.log)
        self.log.info("__reset_totumduino: bootstrap-finished")
        
        self.atomic_end()
    
    def __cleanup(self):
        """
        Internal function cleaning up queues and serial communication.
        """
        self.atomic_end()
        
        self.serial.flush()
        self.serial.reset_input_buffer()
        self.serial.reset_output_buffer()
        
        if self.active_cmd:
            self.active_cmd.reply = None
            self.active_cmd.notify(abort=True)
            self.active_cmd = None
        
        time.sleep(0.2)
        
        # Release all threads waiting for a reply (from reply queue)
        while not self.rq.empty:
            #print "reply queue is not empty"
            try:
                priority, cmd = self.rq.get_nowait()
                cmd.notify(abort=True)
                print "notifing ", cmd
            except queue.Empty as e:
                break
        
        time.sleep(0.2)
        
        # Release all threads waiting for a reply (from command queue)
        while not self.cq.empty:
            #print "command queue is not empty"
            try:
                cmd = self.cq.get_nowait()
                cmd.notify(abort=True)
                print "notifing ", cmd
            except queue.Empty as e:
                break
                
        self.__init_state()
        
    
    """ APIs *public* functions """
    
    def start(self, atomic_group=None):
        """
        Start GCodeService threads.
        """
        
        self.serial = serial.serial_for_url(
                                self.SERIAL_PORT,
                                baudrate = self.SERIAL_BAUD,
                                timeout = self.SERIAL_TIMEOUT
                                )
        self.serial.flushInput()
        self.buffer = bytearray()
        self.__init_state()
        self.serial.flushInput()
        
        self.running = True
        
        # Sender Thread
        self.sender = Thread( name="GCodeService-sender", target = self.__sender_thread )
        self.sender.start()
        # Receiver Thread
        self.receiver = Thread( name="GCodeService-receiver", target = self.__receiver_thread )
        self.receiver.start()
        
        # Wait for both threads to start before continuing
        self.ev_tx_started.wait()
        self.ev_rx_started.wait()
        
        if atomic_group:
            self.atomic_begin(group=atomic_group)
        
        self.log.info("All threads started")
    
    def loop(self):
        """
        Wait until all threads are closed.
        """
        self.sender.join()
        self.receiver.join()
    
    def close_serial(self):
        self.stop(release_only=True)
        
    def open_serial(self):
        self.released = False
        
        self.start(atomic_group='bootstrap')
        
        self.__cleanup()
        time.sleep(5)
        self.__cleanup()
        
        hardwareBootstrap(self, logger=self.log)
        
        self.atomic_end()
        
    
    def stop(self, wait_for_reply = False, release_only=False):
        """
        Stop the service by stopping all running threads.
        """
        self.wait_for_reply = wait_for_reply
        if release_only:
            self.released = True
        else:
            self.running = False
        if hasattr(self.serial, 'cancel_read'):
            self.cancel_read()
        self.cq.put( Command.kill() )
        
        # Wait for both threads to be stopped and then clean up the queues.
        self.log.info("Witing for sender...")
        self.sender.join()
        self.log.info("Witing for receiver...")
        self.receiver.join()
        
        # stop() is called from another thread of execution so try to suspend it
        # to allow the thread system to switch to other running threads
        time.sleep(1)
        
        self.__cleanup()
        
        self.serial.close()
        
        self.log.info("All threads stopped")
    
    def reset(self):
        """
        Force Totumduino hardware reset.
        """
        if self.is_resetting or self.released:
            return
        self.__reset_totumduino()
        
    def pause(self):
        """
        Pause current file push. In case no file is being pushed this command
        has no effect.
        """
        if self.is_resetting or self.released:
            return
            
        self.cq.put( Command.pause() )
        
    def resume(self):
        """
        Resume current file push. In case no file is being pushed this command
        has no effect.
        """
        if self.is_resetting or self.released:
            return
        
        self.cq.put( Command.resume() )
        
    def resumed(self):
        """
        
        """
        if self.is_resetting or self.released:
            return
        
        self.cq.put( Command.resumed() )
        
    def abort(self):
        """
        Abort current file push. In case no file is being pushed this command
        has no effect.
        """
        if self.is_resetting or self.released:
            return
        
        self.cq.put( Command.abort() )
        
    def finish(self):
        """
        Send finish request.
        """
        if self.is_resetting or self.released:
            return
        
        self.cq.put( Command.finish() )
        
    def terminate(self):
        """
        Terminated current file push and it's parent script. In case no file is being pushed this command
        has no effect.
        """
        
        self.log.error("Termination request")
        
        if self.is_resetting or self.released:
            self.log.info("Termination request ignored (already in reset)")
            return
        
        if not self.is_terminating:
            self.log.info("Termination request put on QUEUE")
            self.is_terminating = True
            self.cq.put( Command.terminate() )
    
    def z_modify(self, z):
        """
        Modify the Z axis by amount z
        """
        if self.is_resetting or self.released:
            return
        
        self.cq.put( Command.zmodify(z) )
    
    def register_callback(self, callback_fun):
        """
        Callbacks: update, file_done, paused, resumed
        """
        if callback_fun not in self.callback:
            self.callback.append(callback_fun)
        
    def unregister_callback(self, callback_fun):
        """
        Unregister previously registered callback function.
        """
        self.callback.remove(callback_fun)
    
    def set_atomic_group(self, group):
        """
        Set which group of commands are part of an atomic block.
        
        :param group:
        :tyoe group: string
        """
        self.atomic_group = group
    
    def atomic_begin(self, timeout = None, group = 'macro'):
        """
        Initiate an atomic block lock. Wait if an atomic operation is already
        in progress.
        Once the atomic lock is aquired and timeout is given. The atomic lock will 
        be released automatically if there is not new command or reply withing
        timeout period.
        
        :param timeout: Maximal allowed time of inactivity before atomic lock is automatically released. 
        :type timeout: float
        """
        self.atomic_sync_lock.acquire()
        self.atomic_group = group
        self.is_atomic = True
        self.atomic_sync_lock.release()
        
    def atomic_end(self):
        """
        Atomic block end. With this command the atomic lock is released.
        """
        self.atomic_sync_lock.acquire()
        self.is_atomic = False
        self.atomic_group = None
        self.atomic_sync_lock.release()
    
    def send(self, code, block = True, timeout = None, group = 'gcode', expected_reply = 'ok', async = False):
        """
        Send GCode and return reply.
        """
        if self.is_resetting or self.released:
            time.sleep(1)
            return None
        
        code = code.encode('latin-1')
        if self.running:
            sent_timestamp = time.time()
            # QUESTION: should this be handled or not?
            if code == 'M25':
                self.pause()
                return ['ok']
            elif code == 'M24':
                self.resume()
                return ['ok']
            
            self.log.debug("put on command queue: %s,%s", code, group)
            
            cmd = Command.gcode(code, expected_reply, group = group, timeout = timeout, async = async)
            self.cq.put(cmd)
            
            # Don't block, return immediately 
            if not block:
                return None
            
            # Protection #1 in case the service is stopped
            if not self.running or self.released:
                return None
            # Last resort protection #2 if service is stopped
            # As this function is called from a separate thread from 'sender'
            # and 'receiver' it can be active after they have been terminated.
            # In which case no one will trigger cmd.ev event to unlock it.
            # Timeout is a safety measure to handle this corner case.
            while not cmd.wait(3):
                self.log.debug("Waiting (3) for [%s,%s] aborted: %s", code, group, str(cmd.aborted))
                
                if self.is_resetting or self.released:
                    cmd.notify(abort=True)
                    time.sleep(1)
                    return None
                
                if not self.running or self.released:
                    # Aborting because the service has been stopped
                    self.log.info('Aborting reply due to stop. [%s]', code)
                    return None
                if timeout:
                    if ( time.time() - sent_timestamp ) >= timeout:
                        self.log.info('Timeout for [%s]', code)
                        return None
                        
            if cmd.aborted:
                self.log.info('Command aborted. [%s]', code)
                return None
                        
            return cmd.reply
        else:
            return None
        
    def push(self, id, data):
        """
        Push a notification to all clients.
        """
        self.__trigger_callback(id, data)
        return True
        
    def send_file(self, filename):
        """
        Send GCode from a file.
        Returns ``False`` if a file is already being pushed.
        
        :rtype: bool
        """
        if self.is_resetting or self.released:
            return False
        
        if self.running:
            if self.state == GCodeService.IDLE:
                cmd = Command.file(filename)
                self.cq.put(cmd)
                return True
                
        return False
            
    def get_progress(self):
        """
        Return current file progress.
        After file_done callback is finished executing, progress will be set to 0.
        """
        return self.progress
        
    def get_idle_time(self):
        """
        Return amount of time that no command was executed from a file.
        """
        return self.idle_time_started - time.time()
        
    def debug_info(self, args):
        
        self.log.debug("=== Debug Info: BEGIN ===")
        self.log.debug("Thread count: %d", threading.active_count())
        self.log.debug("Thread enumeration:")
        for e in threading.enumerate():
            self.log.debug("%s", str(e))
        self.log.debug("=== Debug Info: END ===")
        return True

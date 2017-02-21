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
import sys, traceback
import time
import json
import re
import gettext

# Import external modules

# Import internal modules
from fabtotum.fabui.macros.all import PRESET_MAP
from fabtotum.utils import glob2re
# Set up message catalog access
tr = gettext.translation('gmacro', 'locale', fallback=True)
_ = tr.ugettext
#############################################

class MacroException(Exception):
    def __init__(self, command, message):
        super(MacroException, self).__init__(message)
        self.command = command
        self.message = message
        
class MacroTimeOutException(MacroException):
    def __init__(self, command):
        super(MacroTimeOutException, self).__init__(command, _('Timeout for {0}'.format(command)) )
    
class MacroUnexpectedReply(MacroException):
    def __init__(self, command, message, expected_reply):
        super(MacroUnexpectedReply, self).__init__(command, message)
        self.expected_reply = expected_reply
    
class GMacroHandler:
    
    def __init__(self, gcs, config, trace, reset_trace, lang='en_US.UTF-8'):
        self.gcs = gcs
        self.config = config
        self.__trace = trace
        self.__reset_trace = reset_trace
        self.lang = lang
    
    def setLanguage(self, lang):
        self.lang = lang
    
    def trace(self, message):
        self.__trace(message)
    
    def macro(self, command, expected_reply, timeout, message, final_reply = 'ok', verbose=True, warning=False):
        if verbose:
            self.trace(message)
        
        reply = self.gcs.send(command, expected_reply=final_reply, block=True, timeout=timeout, group='macro')
        
        if('G0 ' in command):
            reply = self.gcs.send('M400', block=True, timeout=timeout, group='macro')
        
        if reply is None:
            if warning:
                self.trace( _('Timeout for {0}'.format(command)) )
            else:
                raise MacroTimeOutException(command)
        
        found = False
        if expected_reply is not None:
            for reply_line in reply:
                
                retr = re.match( glob2re(expected_reply), reply_line )
                if retr:
                    found = True
                    break
                    
        if not found:
            if warning:
                self.trace(message)
            else:
                raise MacroUnexpectedReply(command, message, expected_reply)
                
        return reply
    
    def run(self, preset, args = None, atomic = True):
        """
        Execute macro command.
        """
        MACRO_SUCCESS = 'success'
        MACRO_ERROR   = 'error'
        MACRO_UNKNOWN = 'unknown'
        
        reply = None
        error_message = ''
        response = MACRO_ERROR

        self.__reset_trace()
        if atomic:
            """ 
            Start macro execution block. This will activate atomic execution and 
            only commands marked as `macro` will be executed. Others will be aborted.
            """
            self.gcs.atomic_begin(group = 'macro')

        try:
            if preset in PRESET_MAP:
                reply = PRESET_MAP[preset](self, args, self.lang)
                response = MACRO_SUCCESS
            else:
                response = MACRO_UNKNOWN
                error_message = 'macro not found'
                self.trace('Macro "{0}" not found.'.format(preset));
        except MacroException as err:
            response = MACRO_ERROR
            error_message = str(err) + traceback.format_exc()
            self.trace( str(err) )
        except Exception as e:
            response = MACRO_ERROR
            error_message = str(e) + traceback.format_exc()
            self.trace( str(e) )
        if atomic:
            """ End macro execution block and atomic execution. """
            self.gcs.atomic_end()
            
        if reply is None:
            if response == MACRO_SUCCESS:
                reply = 'ok'
            else:
                reply = ''
            
        result = {}
        result['response']  = response
        result['reply']     = reply
        result['message']   = error_message
        
        return result

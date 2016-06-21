__author__ = "Krios Mane"
__license__ = "GPL - https://opensource.org/licenses/GPL-3.0"
__version__ = "1.0"
#--------------------------------#
# LOAD FABTOTUM SHARED CONFIGS
#--------------------------------#
import ConfigParser

# ====== GENERAL CONSTANTS
config = ConfigParser.ConfigParser()
config.read('/var/lib/fabui/config.ini')

LOCK_FILE           = config.get('general', 'lock')
TRACE               = config.get('general', 'trace')
MACRO_RESPONSE      = config.get('general', 'macro_response')
TASK_MONITOR        = config.get('general', 'task_monitor')
EMERGENCY_FILE      = config.get('general', 'emergency_file')
##################################################################
SOCKET_HOST         = config.get('socket', 'host')
SOCKET_PORT         = config.get('socket', 'port')
##################################################################
HW_DEFAULT_SETTINGS = config.get('hardware', 'default_settings')
HW_CUSTOM_SETTINGS  = config.get('hardware', 'custom_settings')
##################################################################
USB_DISK_FOLDER     = config.get('usb', 'usb_disk_folder')
USB_FILE            = config.get('usb', 'usb_file')
##################################################################
GPIO_PIN            = config.get('gpio', 'pin')
# ====== SERIAL CONSTANTS
serial = ConfigParser.ConfigParser()
serial.read('/var/lib/fabui/serial.ini')

SERIAL_PORT = serial.get('serial', 'PORT')
SERIAL_BAUD = serial.get('serial', 'BAUD')
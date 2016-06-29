# Static configurations

OS_FLAVOUR		= 'colibri'
WWW_PATH		= '/var/www/';
SHARED_PATH		= '/usr/shared/fabui/';
LIB_PATH		= '/var/lib/fabui/';
FABUI_PATH		= SHARED_PATH;

LOG_PATH        = '/var/log/fabui/'

PYTHON_PATH		= FABUI_PATH + 'ext/py/'
BASH_PATH		= FABUI_PATH + 'ext/bash/'

TASKS_PATH		= WWW_PATH + 'tasks'
RECOVERY_PATH	= WWW_PATH + 'recovery/'
UPLOAD_PATH		= WWW_PATH + 'upload/'
TEMP_PATH		= WWW_PATH + 'temp/'

BIGTEMP_PATH	= '/mnt/bigtemp/'
USERDATA_PATH	= '/mnt/userdata/'
USB_MEDIA_PATH	= '/run/media/'

CONFIG_INI		= LIB_PATH + 'config.ini'
SERIAL_INI		= LIB_PATH + 'serial.ini'

DEBUG_IS_ON			= False

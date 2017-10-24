/*                  ______________________________________
           ________|                                      |_______
           \       |        fabui-colibri Config          |      /
            \      |      Copyright © 2017 FABteam        |     /
            /      |______________________________________|     \
           /__________)                                (_________\
 *
 * =======================================================================
 * =======================================================================
**/
var base_url = '/fabui/'; 
/**
 * 
 */
var debugState = false;
/*
 * general
 */
number_updates = 0;
number_plugin_updates = 0;
number_tasks   = 0;
/*
 * DOM elements
 */
$.console = $(".console"); //where display trace content
/*
 *  main url actions
 */

var reboot_url_action           = base_url + '/control/reboot';
var poweroff_url_action         = base_url + '/control/poweroff';
var login_url                   = base_url + '/login/index';
var logout_url                  = base_url + '/login/out';
var reset_controller_url_action = base_url + '/control/resetController';
var stop_all_url_action         = base_url + '/control/emergency';
var set_secure_url              = base_url + '/control/setSecure';
var set_recovery_url            = base_url + '/control/setRecovery';
var websocket_fallback_url      = base_url + '/control/ws_fallback';
var control_url                 = base_url + '/control';
var dashboard_url               = base_url + '/dashboard';
var install_head_url            = base_url + '/head/setHead/';
var head_page_url               = base_url + '/maintenance/head';
var head_page_ajax_url          = base_url + '#maintenance/head';
var temperatures_file_url       = '/temp/temperature.json';
var jog_response_file_url       = '/temp/jog_response.json';
var task_monitor_file_url       = '/temp/task_monitor.json';
var updates_json_url            = '/temp/updates.json';
//var network_info_url            = '/temp/network.json';
var network_info_url            = base_url + '/control/getNetworkInfo' ;
var update_check_url            = '/fabui/updates/check';
var new_head_url_action         = '#maintenance/head?warning=1';
var emergency_json_url          = '/tmp/emergency.json';
var serial_exec_url_action      = base_url + '/jog/exec';
//var first_setup_url_action      = '/#controller/first_setup';
var first_setup_url_action      = base_url + '/control/firstSetup';
var check_internet_url_action   = '/temp/internet';
/*
* xmlrpc
*/
var xmlrpc = true;
/*
 * socket 
 */
var socket_host = window.location.hostname;
var socket_port = 9002;
var socket = null;
var socket_connected = false;

/*
 * intervals 
 */
var notification_interval       = null;
var notification_interval_timer = 10000; //10 seconds
var safety_interval             = null;
var safety_interval_timer       = 3000 //3 seconds
var temperatures_interval       = null;
var temperatures_interval_timer = 5000 //5 seconds
var status_interval             = null;
var status_interval_timer       = 5000;
/*
 * global flags
 */
var is_macro_on     = false; //true if macro is running
var is_task_on      = false; //true if taks is running
var is_stopping_all = false; //true if emergency button was clicked
var is_emergency    = false; //true if printer is in emergency status
/*
 * 
 * 
 */
var maxTemperaturesPlot = 200;
var temperaturesPlot = {extruder: {temp: [], target: []}, bed: {temp:[], target:[]}};
/***************************
 * HEAD WORKING MODE
 * GCODE M450
 ***************************/
HEAD_WORKING_MODE_HYBRID  = 0;
HEAD_WORKING_MODE_FFF     = 1;
HEAD_WORKING_MODE_LASER   = 2;
HEAD_WORKING_MODE_CNC     = 3
HEAD_WORKING_MODE_SCANNER = 4;
/****************************
* ERROR CODES
*****************************/
ERROR_KILLED           = 100
ERROR_STOPPED          = 101
ERROR_DOOR_OPEN        = 102 
ERROR_MIN_TEMP         = 103
ERROR_MAX_TEMP         = 104
ERROR_MAX_BED_TEMP     = 105
ERROR_X_MAX_ENDSTOP    = 106
ERROR_X_MIN_ENDSTOP    = 107
ERROR_Y_MAX_ENDSTOP    = 108
ERROR_Y_MIN_ENDSTOP    = 109
ERROR_IDLE_SAFETY      = 110
ERROR_WIRE_END         = 111
ERROR_Y_BOTH_TRIGGERED = 120
ERROR_Z_BOTH_TRIGGERED = 121
ERROR_AMBIENT_TEMP     = 122
ERROR_EXTRUDE_MINTEMP  = 123
ERROR_LONG_EXTRUSION   = 124
ERROR_HEAD_ABSENT      = 125
ERROR_PWR_OFF          = 999
/***************************
*  UNIT TYPE
***************************/
UNIT_GENERAL = 'GENERAL';
UNIT_CORE    = 'CORE';
UNIT_PRO     = 'PRO';
UNIT_HYDRA   = 'HYDRA';
UNIT_UNKNOWN = 'UNKNOWN';
/*
 * FABTOTUM APP CONFIG
 */ 
/*
 * general
 */
$.number_updates = 0;
$.number_tasks   = 0;
/*
 * DOM elements
 */
$.console = $(".console"); //where display trace content
/*
 *  main url actions
 */
$.reboot_url_action           = '/fabui/controller/reboot';
$.poweroff_url_action         = '/fabui/controller/poweroff';
$.logout_url                  = '/fabui/login/out';
$.reset_controller_url_action = '/fabui/application/modules/controller/ajax/reset_controller.php';
$.stop_all_url_action         = '/fabui/application/modules/controller/ajax/stop_all.php';
$.update_check_url            = '/fabui/updates/check';
$.check_notification_url      = '/fabui/application/modules/controller/ajax/check_notifications.php';
$.secure_url_action           = '/fabui/application/modules/controller/ajax/secure.php';
$.new_head_url_action         = '/fabui/maintenance/head/install?warning=1';
$.safety_json_url             = '/temp/safety.json';
$.serial_exec_url_action      = '/fabui/application/modules/jog/ajax/exec.php';
$.first_setup_url_action      = '/fabui/controller/first_setup';
//$.check_internet_url_action   = '/fabui/controller/internet';
$.check_internet_url_action   = '/temp/internet';
/*
 * socket 
 */
$.socket_host = window.location.hostname;
$.socket_port = 9001;
$.socket = null;
$.socket_connected = false;

/*
 * intervals 
 */
$.notification_interval       = null;
$.notification_interval_timer = 10000; //10 seconds
$.safety_interval             = null;
$.safety_interval_timer       = 3000 //3 seconds
$.temperatures_interval       = null;
$.temperatures_interval_timer = 2500 //2,5 seconds
/*
 * global flags
 */
$.is_macro_on     = false; //true if macro is running
$.is_task_on      = false; //true if taks is running
$.is_stopping_all = false; //true if emergency button was clicked
$.is_emergency    = false; //true if printer is in emergency status

/*
 * emergency descriptions 
 */
$.emergency_descriptions = {
	100 : 'General Safety Lockdown',
	101 : 'Printer stopped due to errors',
	102 : 'Front panel is open, cannot continue',
	103 : 'Head not properly aligned or absent',
	104 : 'Extruder Temperature critical, shutting down',
	105 : 'Bed Temperature critical, shutting down',
	106 : 'X max Endstop hit: Move the carriage to the center or check <span class="txt-color-orangeDark"><strong>Settings > Hardware > Custom Settings > Invert X Endstop Logic</strong></span>',
	107 : 'X min Endstop hit: Move the carriage to the center or check <span class="txt-color-orangeDark"><strong>Settings > Hardware > Custom Settings >Invert X Endstop Logic</strong></span>',
	108 : 'Y max Endstop hit: Move the carriage to the center and reset',
	109 : 'Y min Endstop hit: Move the carriage to the center and reset',
	110 : 'The FABtotum has been idling for more than 10 minutes. Temperatures and Motors have been turned off.',
	120 : 'Both Y Endstops hit at the same time',
	121 : 'Both Z Endstops hit at the same time',
	122 : 'Ambient temperature is less then 15°C. Cannot continue.',
	123 : 'Cannot extrude filament: the nozzle temperature is too low',
	124 : 'Cannot extrude so much filament!'
}

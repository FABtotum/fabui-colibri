/*
 * FABTOTUM APP CONFIG
 */ 

/*
 * general
 */
$.number_updates = 0;
$.number_tasks   = 0;

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
$.notification_interval;
$.notification_interval_timer = 10000;
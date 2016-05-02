<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/lib/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/lib/utilities.php';


/** CREATE LOG FILES */
$_time = $_POST['time'];
$_type = isset($_POST['type']) ? $_POST['type'] : '';

$_destination_trace    = TEMP_PATH.'macro_trace';
$_destination_response = TEMP_PATH.'macro_response';

write_file($_destination_trace, '', 'w');
write_file($_destination_response, '', 'w');

/** IF IS ADDITIVE $_raise_bed */
if($_type == 'additive'){
	
	$_engage_feeder = isset($_POST['engage_feeder']) && $_POST['engage_feeder'] == 1 ? true : false;
	$_raise_bed_macro = $_engage_feeder == true ? 'raise_bed_no_g27' : 'raise_bed';
	
	$_raise_bed = 'python '.PYTHON_PATH.'gmacro.py '.$_raise_bed_macro.' '.$_destination_trace.'  '.$_destination_response.' > /dev/null';
	$_output_command = shell_exec ( $_raise_bed );
	
}else{

	$_4th_axis_mmode = 'python '.PYTHON_PATH.'gmacro.py 4th_axis_mode '.$_destination_trace.'  '.$_destination_response.' ';
	$_output_command = shell_exec ( $_4th_axis_mmode );
	
}

/** EXEC COMMAND */
$_command        = 'python '.PYTHON_PATH.'gmacro.py check_pre_print '.$_destination_trace.' '.$_destination_response.' > /dev/null';
$_output_command = shell_exec ( $_command );

/** WAIT JUST 1 SECOND */
sleep(1);

$_response = file_get_contents($_destination_response);
$_trace    = file_get_contents($_destination_trace);
$_trace    = str_replace(PHP_EOL, '<br>', $_trace);

/** RESPONSE */
$_response_items['command']            = $_command;
$_response_items['url_check_response'] = host_name().'temp/response_'.$_time.'.log';
$_response_items['response']           = str_replace(PHP_EOL, '', $_response) == 'true' ? true : false;

$_response_items['trace']              = $_trace;
$_response_items['status']             = $_response_items['response']  == true ? 200 : 500;
/** WAIT JUST 1 SECOND */
sleep(1);
header('Content-Type: application/json');
echo minify(json_encode($_response_items));

?>
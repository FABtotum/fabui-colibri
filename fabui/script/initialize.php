<?php

require_once '/var/www/fabui/script/config.php';
require_once '/var/www/lib/Database.php';
require_once '/var/www/fabui/ajax/lib/utilities.php';

/* Set configured ambient color */
//TODO: put ui config in standard place like i.e. `/etc/fab/ui.conf`
$_conf = json_decode(file_get_contents('/var/www/fabui/config/config.json'), TRUE);

// Temporary snippet, use phpSerial one of these days
foreach (array(
	'M701' => 'r',
	'M702' => 'g',
	'M703' => 'b'
) as $gcode => $svalue) {
	//TODO: serial device should be env configurable (e.g.: export FABTOTUM_SERIAL_DEV=ttyAMA0)
	$ok = shell_exec("echo '{$gcode} S{$_conf['color'][$svalue]}' > /dev/ttyAMA0 && cat /dev/ttyAMA0");
}

/** INITIALIZE  */
/** WAIT UNTIL MYSQL SERVER START */
while (strpos(shell_exec('mysqladmin -u'.DB_USERNAME.' -p'.DB_PASSWORD.' ping'), 'mysqld is alive') != 0)
{
	sleep(1);
}

/** LOAD DB */
$db = new Database();

/** GET RUNNING TASKS FROM DB  */
$_tasks = $db->query('select * from sys_tasks where status = "running" or status is null');

if ($_tasks)
{
	if ($db->get_num_rows() == 1)
	{
		$_temp = $_tasks;
		$_tasks = array();
		$_tasks[] = $_temp;
	}

	foreach ($_tasks as $_task)
	{
		$_data_update['status'] = 'removed';
		$db->update('sys_tasks', array('column' => 'id', 'value' => $_task['id'], 'sign' => '='), $_data_update);
	}

	$db->close();
}

?>

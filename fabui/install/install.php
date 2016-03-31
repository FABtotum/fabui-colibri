<?php
require_once '/var/www/lib/config.php';
require_once '/var/www/lib/database.php';
require_once '/var/www/lib/utilities.php';

/** INSTALL NEEDED MODULE */
install_pkg('dos2unix');


/** CHECK IF EXIST FOLDER LIB FOR THE UPDATE */
if (file_exists(dirname(__FILE__) . '/lib')) {
	$_command_copy = 'cp -rvf ' . dirname(__FILE__) . '/lib /var/www/';
	shell_exec($_command_copy);
}

/** CHECK IF EXIST FOLDER RECOVERY FOR THE UPDATE */
if(file_exists(dirname(__FILE__).'/recovery')){
	$_command_copy = 'cp -rvf '.dirname(__FILE__).'/recovery /var/www/';
	shell_exec($_command_copy);	
}

//CRON FOLDER
if (file_exists(dirname(__FILE__) . '/cron')) {
	shell_exec('cp -rvf ' . dirname(__FILE__) . '/cron /var/www/');
}

//UPDATE CRON FILE
if (file_exists(dirname(__FILE__) . '/root')) {
	shell_exec('sudo cp ' . dirname(__FILE__) . '/root /var/spool/cron/crontabs/root');
	shell_exec('sudo chmod 600 /var/spool/cron/crontabs/root');
}

//APACHE2 CONFIG FILE
if (file_exists(dirname(__FILE__) . '/apache2/apache2.conf')) {
	shell_exec('sudo cp ' . dirname(__FILE__) . '/apache2/apache2.conf /etc/apache2/apache2.conf');
	shell_exec('sudo chmod 644 /etc/apache2/apache2.conf');
}

//API FOLDER
if (file_exists(dirname(__FILE__) . '/api')) {
	echo 'Coping api folder' . PHP_EOL;
	shell_exec('cp -rvf ' . dirname(__FILE__) . '/api /var/www/');
}

//UTILITIES FOLDER
if (file_exists(dirname(__FILE__) . '/utilities')) {
	echo 'Coping utilities folder' . PHP_EOL;
	shell_exec('cp -rvf ' . dirname(__FILE__) . '/utilities /var/www/');
	shell_exec('sudo chmod -R 777 /var/www/utilities');
}

/** OPTIMIZING BASH SCRIPTS */
if (file_exists(dirname(__FILE__) . '/fabui/script/bash')) {
	shell_exec('sudo dos2unix ' . dirname(__FILE__).'/fabui/script/bash/*');
}
if (file_exists(dirname(__FILE__) . '/bash')) {
	shell_exec('sudo dos2unix ' . dirname(__FILE__).'/bash/*');
}


//INIT SCRIPT FABUI
if (file_exists(dirname(__FILE__) . '/init/fabui')) {
	shell_exec('dos2unix ' . dirname(__FILE__) . '/init/fabui');
	shell_exec('sudo cp ' . dirname(__FILE__) . '/init/fabui /etc/init.d/fabui');
	shell_exec('sudo chmod 755 /etc/init.d/fabui');
	shell_exec('sudo update-rc.d fabui defaults');
}


/** UPDATE SYSTEM MODULES */
echo "check system...".PHP_EOL;
shell_exec('sudo bash '.dirname(__FILE__) . '/bash/check_system.sh');

//install pyserial 3.0.1
$min_serialpy_version = '3';
if (version_compare(pipGetVetsion('pyserial'), $min_serialpy_version) == -1) {
	shell_exec('sudo bash ' . dirname(__FILE__) . '/bash/upgrade_pyserial.sh');
}


//BOOT SCRIPT -- Introduced for baudrate 250000: bcm2708.uart_clock=4000000
if(file_exists(dirname(__FILE__).'/boot/config.txt')){
	shell_exec('sudo cp -rf ' . dirname(__FILE__) . '/boot/config.txt /boot/');
}


/** CHECK IF IS PRESENT FW */
if(file_exists(dirname(__FILE__).'/fw/Marlin.cpp.hex')){
	
}


/** CHECK IF EXIST FOLDER SQL */
if (file_exists(dirname(__FILE__) . '/sql')) {
	foreach (glob(dirname(__FILE__).'/sql/*') as $file_sql) {
		/** EXEC SQL FILES */
		if (file_exists($file_sql)) {
			echo 'executing ' . $file_sql . PHP_EOL;
			$_exec_sql = 'sudo mysql -u ' . DB_USERNAME . ' -p' . DB_PASSWORD . ' -h ' . DB_HOSTNAME . '  < ' . $file_sql;
			shell_exec($_exec_sql);

		}
	}
}

$db = new Database();
$tasks = $db -> query('select * from sys_tasks');
foreach ($tasks as $task) {

	$attributes = json_decode($task['attributes'], true);

	$_data_update = array();

	if (isset($attributes['id_object']))
		$_data_update['id_object'] = $attributes['id_object'];
	if (isset($attributes['id_file']))
		$_data_update['id_file'] = $attributes['id_file'];
	if ($task['type'] == 'print' || $task['type'] == 'scan')
		$_data_update['controller'] = 'make';
	if (isset($attributes['print_type']) && $attributes['print_type'] == 'subtractive')
		$_data_update['type'] = 'mill';

	$db -> update('sys_tasks', array('column' => 'sys_tasks.id', 'value' => $task['id'], 'sign' => '='), $_data_update);

}


echo "Complete".PHP_EOL;

function exists_py_module($mod) {

	global $python_modules;

	$return = false;

	foreach ($python_modules as $module) {
		if (strpos($module, $mod) !== false) {
			$return = true;
		}
	}

	return $return;

}

function install_py_module($module) {

	$response = shell_exec("sudo pip install " . $module);
	return strpos($response, 'Successfully installed') !== false ? true : false;
}

function unistall_pkg($pkg) {

	if (exists_pkg($pkg)) {
		echo 'unistalling ' . $pkg . PHP_EOL;
		$command = 'sudo apt-get --purge remove ' . $pkg . ' -y';
		shell_exec($command);
	}

}

function install_pkg($pkg) {

	if (file_exists(dirname(__FILE__) . '/bash/package.sh')) {
		
		shell_exec('sudo bash '.dirname(__FILE__) . '/bash/package.sh '.$pkg.' install');

	} else {

		if (!exists_pkg($pkg)) {
			echo 'Installing ' . $pkg . PHP_EOL;
			shell_exec('sudo apt-get --force-yes --yes ' . $pkg );
		}

	}
}

function exists_pkg($pkg) {

	$response = shell_exec('sudo dpkg -s ' . $pkg . ' 2>&1');
	$response = preg_replace('/\s\s+/', ' ', $response);

	preg_match('/is not installed/i ', $response, $result);

	if (isset($result[0]) && $result[0] == 'is not installed') {
		return false;
	} else {
		return true;
	}

}

function pipGetVetsion($module) {

	exec('pip show ' . $module, $shell);
	
	$shell = implode(" ", $shell);
	$shell = preg_replace('/\s\s+/', ' ', $shell);
	preg_match('/Version:\s(.*?)\s/i', $shell, $result);

	return isset($result[1]) ? $result[1] : false;
}
?>
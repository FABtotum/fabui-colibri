<?php #!/usr/bin/php

try
{
	$code = 0;  // 0=OK, 1=WARNINGS, 2=ERRORS, 3=FAILURE

	include '/var/www/lib/config.php';

	//TODO: test db drivers and report available ones
	echo "Looking for a DB driver to access database `".DB_DATABASE."`: "; flush();
	$test_drivers = array(
		'pdo_sqlite' => function ($q) {
			if (class_exists('PDO') and in_array('sqlite', pdo_drivers()))
			{
				if (!file_exists(DB_DATABASE))
					return FALSE;
				$db = new PDO('sqlite:'.DB_DATABASE, DB_USERNAME, DB_PASSWORD);
				if ($db) {
					$r = $db->query($q);
					$count = (int)($r->fetchColumn());
					if ($count > 0) RETURN $db;
				}
			}
			return FALSE;
		},
		'pdo_mysql' => function ($q) {
			if (class_exists('PDO') and in_array('mysql', pdo_drivers()))
			{
				$db = new PDO('mysql:host='.DB_HOSTNAME.';dbname='.DB_DATABASE, DB_USERNAME, DB_PASSWORD);
				if ($db) {
					$r = $db->query($q);
					$count = (int)($r->fetchColumn());
					if ($count > 0) RETURN $db;
				}
			}
			return FALSE;
		},
		'mysqli' => function ($q) {
			if(class_exists('mysqli'))
			{
				$db = new mysqli(DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE);
				if ($db) {
					$r = $db->query($q);
					$row = $r->fetch_row();
					if ((int)$row[0] > 0) return $db;
				}
			}
			return FALSE;
		}
	);
	$dbd = NULL;
	$db = NULL;
	$test_query = 'SELECT count(*) FROM sys_configuration';
	foreach ($test_drivers as $driver => $test)
	{
		echo "\n{$driver}... ";
		if (($dbh = $test($test_query)) !== FALSE) {
			$dbd = $driver;
			$db = $dbh;
			break;
		} else {
			echo " no";
		}
	}
	if ($dbd) {
		echo "ok\n";
	} else {
		echo "ERROR\n";
		exit(2);
	}

	//TEST: db configuration
	echo "Trying to connect to configured DB... "; flush();
	require_once('../../lib/database.php');
	$db = new Database();
	// Call _init explicitely to test for return value
	if ($db->_init()) {
		echo "ok\n";
	} else {
		echo "ERROR\n";
		exit(2);
	}

	//TEST: db commands
	echo "Performing some read/write operations on the DB... "; flush();
	$test_data = array(
		'sys_configuration' => array( 'key'=>"'test key'", 'value'=>"'test value'"),
	);
	$err = NULL;
	foreach ($test_data as $table => $data)
	{
		$insert_query = 'INSERT INTO `'.$table.'` (`'.implode('`,`', array_keys($data)).'`) VALUES ('.implode(', ', array_values($data)).')';
		$id = $db->insert($table, $data);
		if ($id === FALSE) {
			$err = $db->error;
			break;
		}

		$rows = $db->query('SELECT `'.implode('`,`',array_keys($data)).'` FROM `'.$table.'` WHERE id='.$id);
		if (!is_array($rows) or count($rows) < 1) {
			$err = $db->error;
			break;
		}

		$r = $db->query('DELETE FROM `'.$table.'` WHERE id='.$id);
		if ($r===FALSE or $r < 1) {
			$err = $db->error;
			break;
		}
	}
	if (!$err) {
		echo "ok\n";
	} else {
		echo "ERROR: ";
		print_r($err);
		echo "\n";
		exit(2);
	}

	//TEST curl
	echo "Checking cURL extension... "; flush();
	if (function_exists('curl_init')) {
		echo "ok\n";
	} else {
		echo "MISSING\n";
		exit(2);
	}

	//TEST file download
	echo "Trying to download a file with cURL... "; flush();
	$url = MARLIN_DOWNLOAD_URL.MARLIN_DOWNLOAD_FILE;
	$path = TEMP_PATH.MARLIN_DOWNLOAD_FILE;
	$test = curl_init($url);
	curl_setopt_array($test, array(
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_FILE => fopen($path, 'w')
	));
	curl_exec($test);
	curl_close($test);
	if (file_exists($path)) {
		unlink($path);
		echo "ok\n";
	} else {
		echo "ERROR\n";
		exit(2);
	}

	//TEST: ZipArchive
	echo "Checking Zip extension... ";
	if (class_exists('ZipArchive')) {
		echo "ok\n";
	} else {
		echo "MISSING";
		exit(2);
	}


	//TEST: Slic3r stable (v 1.1.7) at (/var/www/fabui/slic3r/slic3r)
	echo "Looking for Slic3r executable... "; flush();
	$slic3r_path = '/var/www/fabui/slic3r/slic3r';
	$exp_version = '1.1.7';
	$act_version = exec ($slic3r_path.' --version 2> /dev/null');
	if ($act_version)
	{
		echo "ok\n";

		echo "Checking Slic3r version... ";
		if (preg_match('/^'.str_replace('.', '\.', $exp_version).'/', $act_version)) {
			echo "ok ($act_version ~= $exp_version)\n";
		} else {
			echo "ERROR ($act_version != $exp_version)\n";
			if ($code < 2) $code = 2;
		}
	}
	else
	{
		// Warning
		echo "not found\n";
		if ($code < 1) $code = 1;
	}

	exit($code);

}
catch (Exception $e)
{
	echo $e->getMessage();
	exit(3);
}

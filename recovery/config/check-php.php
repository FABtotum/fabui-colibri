<?php #!/usr/bin/php

try
{
	$code = 0;  // 0=OK, 1=WARNINGS, 2=ERRORS, 3=FAILURE

	include '/var/www/fabui/ajax/config.php';

	//TEST: db driver
	echo "Looking for a MySQL DB driver to access database `".DB_DATABASE."`... "; flush();
	$test_drivers = array(
		'PDO' => function ($q) {
			if (class_exists('PDO'))
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
		if (($db = $test($test_query)) !== FALSE) {
			$dbd = $driver;
			break;
		}
	}
	if ($dbd) {
		echo "ok ($dbd)\n";
	} else {
		echo "ERROR\n";
		exit(2);
	}

	//TEST: db commands
	echo "Performing some read/write operations on the DB... "; flush();
	$test_data = array(
		'sys_configuration' => array( 'key'=>"'test key'", 'value'=>"'test value'"),
	);
	$test_connection = array(
		'PDO' => function ($data) use ($db) {
			foreach ($data as $table => $values) {
				$insert_query = 'INSERT INTO `'.$table.'` (`'.implode('`,`', array_keys($values)).'`) VALUES ('.implode(', ', array_values($values)).')';
				$r = $db->exec($insert_query);
				if ($r < 1) return $r->errorinfo();

				$id = $db->lastInsertId();
				$r = $db->query('SELECT `'.implode('`,`',array_keys($values)).'` FROM `'.$table.'` WHERE id='.$id);
				if ($r->rowCount() < 1) return $r->errorinfo();

				$fields = $r->fetch();
				foreach($values as $col => $val) {
					if (!array_key_exists($col, $fields) or $fields[$col] != trim($val, "'\""))
						return 'written values do not match read values';
				}

				$r = $db->exec('DELETE FROM `'.$table.'` WHERE id='.$id);
				if ($r < 1) return $r->errorinfo();
			}
			return TRUE;
		},
		'mysqli' => function ($data) use ($db) {
			return 'unimplemented';
		}
	);
	if (($err = $test_connection[$dbd]($test_data)) === TRUE) {
		echo "ok\n";
	} else {
		echo "ERROR: ";
		print_r($err);
		echo "\n";
		exit(2);
	}

	//TEST curl
	echo "Looking for cURL extension... ";
	if (function_exists('curl_init')) {
		echo "ok\n";
	} else {
		echo "ERROR\n";
		exit(2);
	}

	//TEST file download
	/*echo "Trying a download with cURL... ";
	$url = MYFAB_REMOTE_VERSION_URL
	$test = curl_init($url);
	curl_setopt_array($test, array(
		CURLOPT_FILE => $_SERVER['DOCUMENT_ROOT']
	));
	curl_exec($test);
	curl_close($test);*/

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

<?php #!/usr/bin/php

try
{
	$code = 0;  // 0=OK, 1=WARNINGS, 2=ERRORS, 3=FAILURE

	#TEST: Slic3r stable (v 1.1.7) (/var/www/fabui/slic3r/slic3r)
	echo "Checking Slic3r version... "; flush();
	$version = exec ('/var/www/fabui/slic3r/slic3r --version 2> /dev/null');
	if (preg_match('/^1\.1\.7/', $version)) {
		echo "$version OK\n";
	} else {
		echo "$version ERROR\n";
		if ($code < 1) $code = 1;
	}

	exit($code);

}
catch (Exception $e)
{
	echo $e->getMessage();
	exit(3);
}

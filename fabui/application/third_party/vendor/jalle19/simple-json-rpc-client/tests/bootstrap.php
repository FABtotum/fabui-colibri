<?php

/**
 * http://tech.vg.no/2013/07/19/using-phps-built-in-web-server-in-your-test-suites/
 */

// Command that starts the built-in web server
$command = sprintf(
		'php -S %s:%d -t %s >/dev/null 2>&1 & echo $!', WEB_SERVER_HOST, WEB_SERVER_PORT, WEB_SERVER_DOCROOT
);

// Execute the command and store the process ID
$output = array();
exec($command, $output);
$pid = (int)$output[0];

echo sprintf(
		'JSON-RPC server started on %s:%d with PID %d', WEB_SERVER_HOST, WEB_SERVER_PORT, $pid
).PHP_EOL;

// Wait for the built-in web server to start accepting requests
while (!@fsockopen(WEB_SERVER_HOST, WEB_SERVER_PORT))
	usleep(1000);

// Kill the web server when the process ends
register_shutdown_function(function() use ($pid) {
	echo sprintf('Killing process with ID %d', $pid).PHP_EOL;
	exec('kill '.$pid);
});

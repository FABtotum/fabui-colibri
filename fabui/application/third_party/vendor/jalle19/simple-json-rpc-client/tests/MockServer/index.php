<?php

require_once(__DIR__.'/../../vendor/autoload.php');
require_once('Calculator.php');

// Check authentication
if (!isset($_SERVER['PHP_AUTH_USER']) ||
		$_SERVER['PHP_AUTH_USER'] !== 'username' ||
		$_SERVER['PHP_AUTH_PW'] !== 'password')
{
	header('WWW-Authenticate: Basic realm="PHP JSON-RPC Server"');
	header('HTTP/1.1 401 Unauthorized');
	exit;
}

// Instantiate the JSON-RPC server
$server = new \Zend\Json\Server\Server();
$server->setClass('Calculator');
$server->handle();

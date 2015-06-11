<?php

include_once '/var/www/recovery/config/config.php';

//================================== DATABASE ============
defined("DB_HOSTNAME")  ? null : define("DB_HOSTNAME", 'localhost');
defined("DB_USERNAME")  ? null : define("DB_USERNAME", 'root');
defined("DB_PASSWORD")  ? null : define("DB_PASSWORD", 'fabtotum');
defined("DB_DATABASE")  ? null : define("DB_DATABASE", 'fabtotum');
//================================= SOCKET ===============
defined("SOCKET_HOST")  ? null : define("SOCKET_HOST", '0.0.0.0');
defined("SOCKET_PORT")  ? null : define("SOCKET_PORT", 666);

defined('SERIAL_DEVICE') ? null : define('SERIAL_DEVICE', (empty($_ENV['FABTOTUM_SERIAL_DEVICE'])? '/dev/ttyaMA0' : $_ENV['FABTOTUM_SERIAL_DEVICE']));

?>

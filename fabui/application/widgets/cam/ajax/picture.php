<?php

require_once $_SERVER['DOCUMENT_ROOT'].'/lib/config.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/lib/utilities.php';

$_image   = '/var/www/temp/picture.jpg';
$_command = 'sudo raspistill -n -hf -t 1 -rot 90 -awb sun -ISO 800 -w 768 -h 1024 -o '.$_image;
shell_exec ( $_command );
shell_exec ('sudo chgrp www-data '.$_image);
shell_exec ('sudo chmod ug+rw '.$_image);

$_response_items['command'] = $_command;
header('Content-Type: application/json');
echo json_encode($_response_items);

?>

<?php 
//DELETE FOLDER RECOVERY IN RECOVERY
if(file_exists('/var/www/recovery/recovery')){
	shell_exec('sudo rm -rf /var/www/recovery/recovery');   
	
}

//DELETE FOLDER RECOVERY IN RECOVERY
if(file_exists('/var/www/recovery/update')){
	shell_exec('sudo rm -rf /var/www/recovery/update');   
}


//CREATE LIB FOLDER IF NOT EXIST
if(!file_exists('/var/www/lib')){

	shell_exec('sudo mkdir /var/www/lib');
	shell_exec('sudo chmod -R 777 /var/www/lib');
	
}

//INSTALL LOG4PHP
if(!file_exists('/var/www/lib/log4php')){

	if(!file_exists(dirname(__FILE__).'/log4php')){
		shell_exec('sudo cp '.dirname(__FILE__).'/lib/log4php /var/www/lib/log4php -r');
	}
	
}



//CREATE LIB FOLDER IF NOT EXIST
if(!file_exists('/var/www/logs')){

	shell_exec('sudo mkdir /var/www/logs');
	shell_exec('sudo chmod -R 777 /var/www/logs');
	
}
//INSTALL NEW SLIC3R UPDATE

//SLICER
shell_exec('sudo chmod -R 777 /var/www/fabui/slic3r');



?>
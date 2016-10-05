<?php
/**
 * 
 * @author Krios Mane
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 * Load shared configs
 * 
 * 
 */
 
 //genarl ini
 $ini = parse_ini_file("/var/lib/fabui/config.ini");
 foreach($ini as $iniKey => $iniValue){
 	$config[$iniKey] = $iniValue;
 }
 
 //serial ini
 $ini = parse_ini_file("/var/lib/fabui/serial.ini");
 foreach($ini as $iniKey => $iniValue){
 	$config['serial_'.$iniKey] = $iniValue;
 }
 
 //serial ini
 $ini = parse_ini_file("/var/lib/fabui/camera.ini");
 foreach($ini as $iniKey => $iniValue){
 	$config['camera_'.$iniKey] = $iniValue;
 }
 
 unset($ini);
 
?>

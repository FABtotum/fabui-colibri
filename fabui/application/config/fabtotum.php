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
 
 //camera ini
 $ini = parse_ini_file("/var/lib/fabui/camera.ini");
 foreach($ini as $iniKey => $iniValue){
 	$config['camera_'.$iniKey] = $iniValue;
 }
 
 
 //languages ini
 $ini = parse_ini_file("/var/lib/fabui/lang.ini", true);
 
 $config['language_current']     = $ini['language']['current'];
 $config['language_code']        = $ini[$ini['language']['current']]['code'];
 $config['language_description'] = $ini[$ini['language']['current']]['description'];
 
 
 //bluetooth ini
 $ini = parse_ini_file("/var/lib/fabui/bluetooth.ini");
 foreach($ini as $iniKey => $iniValue){
     $config[$iniKey] = $iniValue;
 }
 
 unset($ini);
 
?>

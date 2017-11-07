<?php
/**
 * 
 * @author Krios Mane
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 * Load cam configs
 * 
 * 
 */

//cam ini
$ini = parse_ini_file("/var/lib/fabui/cam.ini");
foreach($ini as $iniKey => $iniValue){
	$config[$iniKey] = $iniValue;
}
unset($ini);
?>

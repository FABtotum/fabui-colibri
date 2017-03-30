<?php
/**
 * 
 * @author Krios Mane
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 *
 * Redirect to correct page
 *  
 */
 
 $endpoint = '/fabui/#dashboard';
 
 if(file_exists('AUTOINSTALL')){
 	$endpoint .= 'install';
 }
 
 header("Location: http://".$_SERVER['SERVER_NAME'].$endpoint);
?>
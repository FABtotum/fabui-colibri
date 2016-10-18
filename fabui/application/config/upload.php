<?php
/**
 * 
 * @author Krios Mane
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */
 
 $CI =& get_instance();
 $CI->load->helper('plugin_helper');
 
 $ini = parse_ini_file("/var/lib/fabui/config.ini");
 $config['upload_path'] = $ini['uploads'];
 $config['allowed_types'] = extendAllowedTypesWithPlugins('text|txt|gcode|gc|nc');
 unset($ini);
?>

<?php

/**
 * Maps Code Igniter config items into library options
 * (cfr. `/var/www/lib/System.php and children`)
 */
function map_library_options(&$library, $map)
{
	$app = get_instance();
	foreach ($map as $unit => $config)
	{
		$app->load->config($unit, TRUE);
		foreach ($config as $item => $option)
		{
			$value = $app->config->item($item, $unit);
			$library->$option = $value;
		}
	}
}

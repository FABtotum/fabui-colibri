<?php

/**
 * Pulls in generic System library
 */
include_once('/var/www/lib/System.php');

class Networking
{
	/*protected $_app;*/
	protected $_lib;

	/*
	 * Store here a map from CI config itmes to lib options
	 */
	private $_option_map = array(
		'fabtotum' => array(
			'fabtotum_network_interfaces' => 'NETWORK_INTERFACES'
		)
	);

	public function __construct ()
	{
		$_lib = System::load('Networking');
		$app = get_instance();
		$app->load->helper('libraries');
		map_library_options();
		foreach ($this->_option_map as $file => $map)
		{
			$app->load->config('fabtotum', TRUE);
			foreach ($map as $item => $option)
			{
				$value = $app->config->item()
			}
		}
	}
}

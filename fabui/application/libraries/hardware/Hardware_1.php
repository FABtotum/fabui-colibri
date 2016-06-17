<?php
/**
 * 
 * @author Krios Mane
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 * Settings for Hardware version 1
 * 
 */
 
 class Hardware_1 {
 	
	const HARDWARE_VERSION = 1;
	const SHOW_FEEDER      = true;
	const E_MODE           = 3048.1593;
	const A_MODE           = 177.777778;
	
	protected $CI;
	
	/**
	 * 
	 */
	public function __construct()
	{
		$this->CI =& get_instance();
	}
	
	/**
	 * 
	 */
	public function run()
	{
		//load default settings
		$defaultSettings = loadSettings();
		//set new settings values
		$defaultSettings['hardware']['id'] = self::HARDWARE_VERSION;
		$defaultSettings['feeder']['show'] = self::SHOW_FEEDER;
		$defaultSettings['e']              = self::E_MODE;
		$defaultSettings['a']              = self::A_MODE;
		//save new settings
		saveSettings($defaultSettings);
	}
	
	
 }
?>
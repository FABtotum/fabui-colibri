<?php
/**
 * 
 * @author Krios Mane
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 * Settings for Hardware version 2
 * 
 */
 
 class Hardware_2 {
 	
	const HARDWARE_VERSION = 2;
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
		//open serial
		$this->CI->serial->deviceOpen();
		//inver x endstop logic
		$this->CI->serial->sendMessage('M747 X1'.PHP_EOL);
		//set maximum feedrate
		$this->CI->serial->sendMessage('M203 X550.00 Y550.00 Z15.00 E12.00'.PHP_EOL);
		//save settings to EEPROM
		$this->CI->serial->sendMessage('M500'.PHP_EOL);
		//close serial
		$this->CI->serial->deviceClose();
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
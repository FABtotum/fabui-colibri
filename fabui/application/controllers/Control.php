<?php
/**
 * 
 * @author Krios Mane
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */
 defined('BASEPATH') OR exit('No direct script access allowed');
 
 class Control extends FAB_Controller {
 	
	public function index(){
		
	}
	
	/**
	 *  reboot fabtotum
	 */
	public function reboot()
	{
		session_destroy();
		shell_exec('sudo reboot');
	}
	
	/**
	 * shutdown fabtotum
	 */
	public function poweroff()
	{
		shell_exec('sudo poweroff');	
	}
	
	/**
	 * emergency action, stop all operations
	 */
	public function emergency()
	{
		$this->load->helper('fabtotum_helper');
		stopAll();
		resetController();
		$this->hardwareBootstrap();
	}
	
	/**
	 * reset controller board
	 */
	public function resetController()
	{
		$this->load->helper('fabtotum_helper');
		resetController();
		sleep(2); //just sleep for a moment
		$this->hardwareBootstrap();
	}
	
	/**
	 * init hardware settings and configs
	 */
	public function hardwareBootstrap()
	{
		//load config, libraries, helpers
		$this->config->load('fabtotum');
		$this->load->library('serial');
		$this->load->helper(array('file', 'fabtotum'));
		//write lock file
		write_file($this->config->item('lock'), '', 'w+');
		//load default settings
		$defaultHardwareSettings = loadSettings();
		
		//init serial class
		$this->serial->deviceSet($this->config->item('serial_port'));
		$this->serial->confBaudRate($this->config->item('serial_baud'));
		$this->serial->confParity("none");
		$this->serial->confCharacterLength(8);
		$this->serial->confStopBits(1);
		$this->serial->confFlowControl("none");
		$this->serial->deviceOpen();
		
		//get hawrdware id version
		$this->serial->sendMessage("M763".PHP_EOL);
		$read = $this->serial->readPort();
		$hardwareId = trim(str_replace('ok', '', $read));
		
		//rise probe (security action)
		$this->serial->sendMessage("M402".PHP_EOL);
		$read = $this->serial->readPort();
		
		//alive machine
		$this->serial->sendMessage("M728".PHP_EOL);
		$read = $this->serial->readPort();
		
		//set ambient colors
		$this->serial->sendMessage('M701 S'.$defaultHardwareSettings['color']['r'].PHP_EOL); //red
		$this->serial->sendMessage('M702 S'.$defaultHardwareSettings['color']['g'].PHP_EOL); //green 
		$this->serial->sendMessage('M703 S'.$defaultHardwareSettings['color']['b'].PHP_EOL); //blue
		
		//set safety door open: enable/disable warnings
		$this->serial->sendMessage('M732 S'.$defaultHardwareSettings['safety']['door'].PHP_EOL);
		
		//set collision-warning enable/disable warnings
		$this->serial->sendMessage('M734 S'.$defaultHardwareSettings['safety']['collision-warning'].PHP_EOL);
		
		//set homing preferences
		$this->serial->sendMessage('M714 S'.$defaultHardwareSettings['switch'].PHP_EOL);
		
		//load head
		$head = loadHead();
		//set head settings
		$this->serial->sendMessage($head['pid'].PHP_EOL); //set head pid
		$this->serial->sendMessage('M793 S'.$head['fw_id'].PHP_EOL); //set installed head
		$this->serial->sendMessage('M500' . PHP_EOL); //save settings
		//close serial
		$this->serial->deviceClose();
		
		$this->load->library('hardware', array('id' => $defaultHardwareSettings['settings_type'] == 'custom' ? 'custom' : $hardwareId));
		//$this->load->library('hardware', array('id' => $hardwareId));
		$this->hardware->run();
		
		//remove lock file
		unlink($this->config->item('lock'));
		echo 'bootstrap done'.PHP_EOL;
	}
	
			
 }
 
?>
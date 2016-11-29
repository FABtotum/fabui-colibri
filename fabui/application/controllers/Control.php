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
		//load helper
		$this->load->model('Tasks', 'tasks');
		$this->load->helper('fabtotum_helper');
		emergency();
	}
	
	/**
	 * reset controller board
	 */
	public function resetController()
	{
		//load helpers
		$this->load->helper('fabtotum_helper');
		$this->output->set_content_type('application/json')->set_output(json_encode(resetController()));
	}
	
	/**
	 * @param string {activate | deactivate}
	 */
	function setRecovery($mode)
	{
		$this->load->helper('fabtotum_helper');
		$this->output->set_content_type('application/json')->set_output(json_encode(setRecovery($mode)));
	}
	
	/**
	 * init hardware settings and configs (OBSOLETE)
	 */
	/*public function hardwareBootstrap()
	{
		log_message('debug', __METHOD__.' - Start');
		//load config, libraries, helpers
		$this->config->load('fabtotum');
		$this->load->library('serial');
		$this->load->helper(array('file', 'fabtotum_helper'));
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
		$this->serial->serialflush();
		
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
		log_message('debug', __METHOD__.' - setted ambient lights: '.$defaultHardwareSettings['color']['r'].'-'.$defaultHardwareSettings['color']['g'].'-'.$defaultHardwareSettings['color']['b']);
		
		//set safety door open: enable/disable warnings
		$this->serial->sendMessage('M732 S'.$defaultHardwareSettings['safety']['door'].PHP_EOL);
		log_message('debug', __METHOD__.' - setted safety door: '.$defaultHardwareSettings['safety']['door']);
		
		//set collision-warning enable/disable warnings
		$this->serial->sendMessage('M734 S'.$defaultHardwareSettings['safety']['collision_warning'].PHP_EOL);
		log_message('debug', __METHOD__.' - setted collision-warning: '.$defaultHardwareSettings['safety']['collision_warning']);
		
		//set homing preferences
		$this->serial->sendMessage('M714 S'.$defaultHardwareSettings['switch'].PHP_EOL);
		log_message('debug', __METHOD__.' - setted homing preferences: '.$defaultHardwareSettings['switch']);
		
		//load head
		$head = loadHead();
		//set head settings
		$this->serial->sendMessage($head['pid'].PHP_EOL); //set head pid
		$this->serial->sendMessage('M793 S'.$head['fw_id'].PHP_EOL); //set installed head
		$this->serial->sendMessage('M500' . PHP_EOL); //save settings
		log_message('debug', __METHOD__.' - Setted installed head: '.$head['name'] );
		//close serial
		$this->serial->deviceClose();
		
		$this->load->library('hardware', array('id' => $defaultHardwareSettings['settings_type'] == 'custom' ? 'custom' : $hardwareId));
		//$this->load->library('hardware', array('id' => $hardwareId));
		$this->hardware->run();
		log_message('debug', __METHOD__.' - Run hardware: '.$this->hardware->getId());
		//remove lock file
		if(file_exists($this->config->item('lock'))) unlink($this->config->item('lock'));
		log_message('debug', __METHOD__.' - End');
	}*/
	
	/**
	 *  set secure from error alert
	 */
	public function setSecure($mode)
	{
		//load fabtotum config
		$this->config->load('fabtotum');
		//load serial class
		$this->load->library('serial');
		
		//init serial class
		$this->serial->deviceSet($this->config->item('serial_port'));
		$this->serial->confBaudRate($this->config->item('serial_baud'));
		$this->serial->confParity("none");
		$this->serial->confCharacterLength(8);
		$this->serial->confStopBits(1);
		$this->serial->confFlowControl("none");
		$this->serial->deviceOpen();
		$this->serial->serialflush();
		
		//if($mode == false) $this->serial->sendMessage('M731'.PHP_EOL);
		$this->serial->sendMessage('M999'.PHP_EOL.'M728'.PHP_EOL);
		//$this->serial->sendMessage('M728'.PHP_EOL);
		
		$this->serial->serialflush();
		$this->serial->deviceClose();
		
		//if is called from fabui
		if($this->input->is_ajax_request()){
			$this->output->set_content_type('application/json')->set_output(json_encode(true));
		}
	}
	
	/**
	 * Task flow control used by javascript ajax calls.
	 * @param $action {abort | pause | resume...}
	 * @param $value action related value 
	 */
	public function taskAction($action, $value = '')
	{
		$this->load->helper('fabtotum_helper');
		
		/*switch($action)
		{
			case "abort":
				$response = abort();
				break;
			case "pause":
				$response = pause();
				break;
			case "resume":
				$response = resume();
				break;
			default:
			
		}*/
		$response = $action($value);
		
		$this->output->set_content_type('application/json')->set_output(json_encode($response));
	}
 }
 
?>

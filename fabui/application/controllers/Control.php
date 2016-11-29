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
		$this->output->set_content_type('application/json')->set_output(json_encode($action($value)));
	}
 }
 
?>

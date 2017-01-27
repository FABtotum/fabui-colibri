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
	
	
	public function ws_fallback()
	{
		$method = $this->input->method(true);
		
		$response = array("type" => "unknown", "data" => "");

		if($method == "GET")
		{
			$request = $this->input->get("data");
			$requestData = json_decode($request, true);
			
			$this->load->config('fabtotum');
			
			// notify.json
			$notify = array(
				'data' => file_get_contents( $this->config->item('trace') ),
				'type' => 'trace'
			);
			
			// task_monitor.json
			$task = array(
				'data' => json_decode( file_get_contents( $this->config->item('task_monitor') ), true),
				'type' => 'trace'
			);
			
			// trace
			$trace = array(
				'data' => file_get_contents( $this->config->item('trace') ),
				'type' => 'trace'
			);
			
			// read usb status
			$usb = array(
				'data' => array('status' => getUsbStatus(), 'alert' => false),
				'type' => 'usb'
				);
			
			$response['type'] = 'poll';
			$response['data'] = array(
				'notify' => $notify,
				'trace' => $trace,
				'task' => $task,
				'usb' => $usb
			);
		} 
		else if($method == "POST")
		{
			$request = $this->input->post("data");
			$requestData = json_decode($request, true);

			if(isset($requestData['function'])){
				$function       = $requestData['function'];
				$functionParams = isset($requestData['params']) ? $requestData['params'] : '';
				
				switch($function)
				{
					case "serial": {
						
						$method      = $functionParams['method'];
						$methodParam = $functionParams['value'];
						$methodStamp = $functionParams['stamp'];
						
						unset($functionParams['method']);
						unset($functionParams['value']);
						unset($functionParams['stamp']);
						
						$this->load->library('JogFactory', $functionParams, 'jogFactory');
						$jogFactory = $this->jogFactory;
						
						if(method_exists($jogFactory, $method)){ //if method exists than do it
							$response['data'] = $jogFactory->$method($methodParam, $methodStamp);
							$response['type'] = $jogFactory->getResponseType();
						}
					}
					break;
					
					case "fabBusy": {
						$response['data'] = array('message' => 'FABtotum is busy');
						$response['type'] = 'alert';
					}
					break;
					
					case "usbInserted": {
						$response['type'] = 'usb';
						//set true if usb file exists
						$this->config->load('fabtotum');
						$response['data'] = array('status' => file_exists($this->config->item('usb_file')), 'alert' => false);
					}
					
				}
			}
			
		}
		
		$this->output->set_content_type('application/json')->set_output(json_encode($response));
	}
	
 }
 
?>

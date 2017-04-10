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
		$this->session->settings = loadSettings();
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
		//if is called from fabui
		if($this->input->is_ajax_request()){
			$this->load->helper('fabtotum_helper');
			setSecure($mode);
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
			$this->load->helper('fabtotum_helper');
			$this->load->helper('os_helper');
			
			// notify.json
			$notify = array(
				'data' => json_decode( file_get_contents( $this->config->item('notify_file') ), true),
				'type' => 'trace'
			);
			
			// task_monitor.json
			$task = array(
				'data' => json_decode( file_get_contents( $this->config->item('task_monitor') ), true),
				'type' => 'trace'
			);
			
			// trace
			$trace = array(
				'data' => file_get_contents($this->config->item('trace')),
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
				'trace'  => $trace,
				'task'   => $task,
				'usb'    => $usb
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
	/**
	 * get settings
	 */
	public function getSettings()
	{
		$this->load->helpers('fabtotum_helper');
		$settings = loadSettings();
		$this->output->set_content_type('application/json')->set_output(json_encode($settings));
	}
	
	/**
	 * 
	 */
	public function runningTasks()
	{
		
		$this->load->model('Tasks', 'tasks');
		$this->load->model('Files', 'files');
		
		$tasks = $this->tasks->getRunning();
		
		if($tasks)
		{
			/* used to define gettext versions of those words for task status*/
			$_running    = _("running");
			$_paused     = _("paused");
			$_aborted    = _("aborted");
			$_aborting   = _("aborting");
			$_completed  = _("completed");
			$_completing = _("completing");
			/* do not remove lines above */
			$task_status = _($tasks['status']);
			
			$task_type       = $tasks['type'];
			$task_controller = $tasks['controller'];
			$task_file_id    = $tasks['id_file'];
			
			$task_url = $task_controller;
			if($task_type)
				$task_url .= '/' . $task_type;
			
			$task_filename = '';
			
			if($task_file_id != ""){
				$file = $this->files->get($task_file_id, 1);
				if($file){
					if($task_type == 'scan')
						$task_filename = _("Being generated") . '...';
					else
						$task_filename = $file['client_name'];
				}
			}
			
			$task_label = _(ucfirst($task_type)).' '._("task");
			
			switch($task_type){
				case 'pid_tune':
					$task_label = 'PID tune';
					$task_url   = $task_controller.'/nozzle-pid-tune';
					break;
				case 'update':
					$task_label = 'Update';
					$task_url   = $task_controller;
					break;
			}
			
			echo '<ul class="notification-body">';
				echo '
					<li>
						<span class="padding-10 unread">
							<em class=" padding-5 no-border-radius  pull-left margin-right-5 ">
								<i class="fa fa-tablet fa-2x "></i>
							</em>
							<span>
								<strong><a class="display-normal" href="#'.$task_url.'">'.$task_label.' <i class="font-xs txt-color-orangeDark">('.$task_status.')</i></a></strong>
								<p>'.$task_filename.'</p>
							</span>
						</span>
					</li>
				';
			echo '</ul>';
		}
		else
		{
			echo '
				<div class="alert alert-transparent">
					<h4 class="text-center">'._("No running tasks").'</h4>
				</div>
			';
		}

	}
	
	/**
	 * 
	 */
	public function notifications()
	{
		echo '
				<div class="alert alert-transparent">
					<h4>Click a button to show messages here</h4>
					This blank page message helps protect your privacy, or you can show the first message here automatically.
				</div>
				<i class="fa fa-lock fa-4x fa-border"></i>
			';
	}
 }
 
?>

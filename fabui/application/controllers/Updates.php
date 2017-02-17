<?php
/**
 * 
 * @author Krios Mane
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 *  
 */
 defined('BASEPATH') OR exit('No direct script access allowed');
 
 class Updates extends FAB_Controller {
 	
 	protected $runningTask = false;
 	
 	function __construct(){
 		parent::__construct();
 		if(!$this->input->is_cli_request()){
 			$this->load->model('Tasks', 'tasks');
 			//$this->tasks->truncate();
 			$this->runningTask = $this->tasks->getRunning('update');
 		}
 	}
 	
 	public function index()
 	{
 		//load helpers
 		$this->load->helper('layout');
 		$this->load->library('smart');
 		
 		$data = array();
 		$data['runningTask'] = $this->runningTask;
 		
 		$widgetOptions = array(
 				'sortable' => false, 'fullscreenbutton' => true,'refreshbutton' => false,'togglebutton' => false,
 				'deletebutton' => false, 'editbutton' => false, 'colorbutton' => false, 'collapsed' => false
 		);
 		
 		$widget = $this->smart->create_widget($widgetOptions);
 		$widget->id = 'updates-widget';
 		$widget->class = 'well';
 		
 		$widget->body   = array('content' => $this->load->view('updates/index/widget', $data, true ));
 		
 		//$this->content = $this->load->view('updates/index/widget', $data, true );
 		$this->content  = $widget->print_html(true);
 		$this->addCSSInLine('<style>.table-forum tr td>i {padding-left:0px};.checkbox{margin-top:0px !important;}</style>');
 		$this->addJsInLine($this->load->view('updates/index/js','', true));
 		$this->addCssFile('/assets/css/updates/style.css');
 		$this->addJSFile('/assets/js/plugin/bootstrap-progressbar/bootstrap-progressbar.min.js'); //datatable
 		$this->view();
 	}
	
	/**
	 * get update status (local and remote)
	 */
	function updateStatus()
	{
		//load helpers, config
		$this->load->helper('update_helper');
		$this->load->helper('file');
		$this->config->load('fabtotum');
		//get remote bundles status
		$bundlesStatus = getUpdateStatus();
		write_file($this->config->item('updates_json_file'), json_encode($bundlesStatus));
		$this->output->set_content_type('application/json')->set_output(json_encode($bundlesStatus));
	}
	/**
	 * start update
	 */
	function startUpdate()
	{
		//load helpers
		$this->load->helpers('fabtotum_helper');
		//load model
		$this->load->model('Tasks', 'tasks');
		//get data from post
		$data = $this->input->post();
		$bundles = $this->input->post('bundles');
		$firmware = $data['firmware'];
		$boot = $data['boot'];
		
		//add task record to db
		$taskData = array(
			'user'       => $this->session->user['id'],
			'controller' => 'updates',
			'type'       => 'update',
			'status'     => 'running',
			'start_date' => date('Y-m-d H:i:s')
		);
		$taskId   = $this->tasks->add($taskData);
		
		$updateArgs = array(
				'-T' => $taskId,
		);
		if($bundles)  $updateArgs['-b'] = implode(',', $bundles);
		if($firmware == "true") $updateArgs['--firmware'] = '';
		if($boot == "true") $updateArgs['--boot'] = '';

		startPyScript('update.py', $updateArgs, true, true);
		$this->output->set_content_type('application/json')->set_output(json_encode(array('start' => true, 'id_task' => $taskId)));
	}
	
	/**
	 * 
	 */
	public function notifications()
	{
		/*$this->load->model('Tasks', 'tasks');
		
		$tasks = $this->tasks->getRunning();
		
		if(!$tasks)
		{
			echo '<ul class="notification-body">';
				echo '
					<li>
						<span class="padding-10 unread">
							<em class=" padding-5 no-border-radius  pull-left margin-right-5 ">
								<i class="fa fa-tablet fa-2x "></i>
							</em>
							<span>
								<strong><a class="display-normal" href="#">FABUI <i class="font-xs txt-color-orangeDark">beta</i></a>  is out!</strong>
								<a href="" class="btn btn-xs btn-primary margin-top-5"><i class="fa fa-refresh"></i> Update now!</a>
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
		}*/
	}

 }
 
?>

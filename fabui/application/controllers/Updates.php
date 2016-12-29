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
 	}
 	
 	/*
	public function index()
	{
		if(!$this->input->is_cli_request()){
			$this->load->model('Tasks', 'tasks');
			//$this->tasks->truncate();
			$this->runningTask = $this->tasks->getRunning('updates');
		}
		
		//load libraries, helpers, model, config
		$this->load->library('smart');
		$this->load->helper('layout');
		$data = array();
		$data['runningTask'] = $this->runningTask;
		
		//main page widget
		$widgetOptions = array(
				'sortable' => false, 'fullscreenbutton' => true,'refreshbutton' => false,'togglebutton' => false,
				'deletebutton' => false, 'editbutton' => false, 'colorbutton' => false, 'collapsed' => false
		);
		
		$widget = $this->smart->create_widget($widgetOptions);
		$widget->id = 'updates-widget';
		$widget->header = array('icon' => 'fa-refresh', "title" => "<h2>Updates</h2>");
		$widget->body   = array('content' => $this->load->view('updates/index/widget', $data, true ));
		$this->content  = $widget->print_html(true);
		
		$this->addCSSInLine('<style>.table-forum tr td>i {padding-left:0px};.checkbox{margin-top:0px !important;}</style>');
		$this->addJsInLine($this->load->view('updates/index/js','', true));
		
		$this->view();
	}*/
 	
 	public function index()
 	{
 		if(!$this->input->is_cli_request()){
 			$this->load->model('Tasks', 'tasks');
 			//$this->tasks->truncate();
 			$this->runningTask = $this->tasks->getRunning('updates');
 		}

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
	 * get bundles status
	 */
	function bundleStatus()
	{
		//load helpers
		$this->load->helper('update_helper');
		//get remote bundles status
		$bundlesStatus = getBundlesStatus();
		echo json_encode($bundlesStatus);
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
		$bundles = $data['bundles'];
		
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
				'-b' => implode(',', $bundles)
		);
		startPyScript('update.py', $updateArgs, true, true);
		$this->output->set_content_type('application/json')->set_output(json_encode(array('start' => true, 'id_task' => $taskId)));
	}
			
 }
 
?>
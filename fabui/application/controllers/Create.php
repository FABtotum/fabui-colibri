<?php
/**
 * 
 * @author Krios Mane
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */
 defined('BASEPATH') OR exit('No direct script access allowed');
 
 class Create extends FAB_Controller {
 	
	protected $runningTask = false;
	
	function __construct()
	{
		parent::__construct();
		if(!$this->input->is_cli_request()){ //avoid this form command line
			//check if there's a running task
			//load libraries, models, helpers
			$this->load->model('Tasks', 'tasks');
			//$this->tasks->truncate();
			$this->runningTask = $this->tasks->getRunning();
		}
	}
	
	//controller router
	public function index($type = 'print'){
		
		if($this->runningTask){
			$method = 'do'.ucfirst($this->runningTask['type']);
			if(method_exists($this, $method)) $this->$method();
			else redirect('dashboard');
		}else{
			switch($type){
				case 'mill':
					$this->doMill();
					break;
				case 'print':
					$this->doPrint();
					break;
				default:
					$this->doPrint();
			}
		}
	}
	
	//print controller function
	public function doPrint()
	{
		//load libraries, helpers, model
		$this->load->library('smart');
		$this->load->helper('form');
		//data
		$data = array();
		$data['type']      = 'print';
		$data['printType'] = 'additive';
		$data['runningTask'] = $this->runningTask;
		$data['zHeightOptions'] = array('0.1' => '0.1', '0.01' => '0.01');
		//if there's no running task don't load all steps
		if(!$this->runningTask){
			$data['step1']  = $this->load->view('create/wizard/step1', $data, true );
			$data['step2']  = $this->load->view('create/wizard/additive_step', $data, true );	
		}
		//wizard
		$data['step3']  = $this->load->view('create/wizard/step3', $data, true );
		$data['step4']  = $this->load->view('create/wizard/step4', $data, true );
		$data['wizard'] = $this->load->view('create/wizard/main',  $data, true );
		
		//main page widget
		$widgetOptions = array(
			'sortable'     => false, 'fullscreenbutton' => true,  'refreshbutton' => false, 'togglebutton' => false,
			'deletebutton' => false, 'editbutton'       => false, 'colorbutton'   => false, 'collapsed'    => false
		);
		
		//$widgeFooterButtons = $this->smart->create_button('Save', 'primary')->attr(array('id' => 'save'))->attr('data-action', 'exec')->icon('fa-save')->print_html(true);

		$widget         = $this->smart->create_widget($widgetOptions);
		$widget->id     = 'main-widget-print';
		$widget->header = array('icon' => 'icon-fab-print', "title" => "<h2>Print</h2>");
		$widget->body   = array('content' => $this->load->view('create/main_widget', $data, true ), 'class'=>'fuelux');
		
		//add css files
		$this->addCssFile('/assets/css/create/style.css');
		//add javascript dependencies
		$this->addJSFile('/assets/js/plugin/fuelux/wizard/wizard.min.old.js'); //wizard
		if(!$this->runningTask){ //if task is running these filee are not needed
			$this->addJSFile('/assets/js/plugin/datatables/jquery.dataTables.min.js'); //datatable
			$this->addJSFile('/assets/js/plugin/datatables/dataTables.colVis.min.js'); //datatable
			$this->addJSFile('/assets/js/plugin/datatables/dataTables.tableTools.min.js'); //datatable
			$this->addJSFile('/assets/js/plugin/datatables/dataTables.bootstrap.min.js'); //datatable
			$this->addJSFile('/assets/js/plugin/datatable-responsive/datatables.responsive.min.js'); //datatable */
		}
		$this->addJSFile('/assets/js/plugin/flot/jquery.flot.cust.min.js'); //datatable
		$this->addJSFile('/assets/js/plugin/flot/jquery.flot.resize.min.js'); //datatable
		$this->addJSFile('/assets/js/plugin/flot/jquery.flot.fillbetween.min.js'); //datatable
		$this->addJSFile('/assets/js/plugin/flot/jquery.flot.time.min.js'); //datatable
		$this->addJSFile('/assets/js/plugin/flot/jquery.flot.tooltip.min.js'); //datatable
		
		$this->addJsInLine($this->load->view('create/js', $data, true)); 
		$this->content = $widget->print_html(true);
		$this->view();
	}
	
	//mill controller function()
	public function doMill()
	{
		$this->view();
	}
	
	/**
	 * @param type (additive, subtractive)
	 * @return json object for dataTables plugin
	 * get all files
	 */
	public function getFiles($type = 'additive')
	{
		//load libraries, models, helpers
		$this->load->model('Files', 'files');
		$files  = $this->files->getForCreate($type);
		$aaData = $this->dataTableFormat($files);
		$this->output->set_content_type('application/json')->set_output(json_encode(array('aaData' => $aaData)));
	}
	
	/**
	 * @param type (print, mill)
	 * @return json object for dataTables plugin
	 * get recent printed files
	 */
	public function getRecentFiles($type = 'print')
	{
		//load libraries, models, helpers
		$this->load->model('Tasks', 'tasks');
		$files  = $this->tasks->getLastCreations($type);
		$aaData = $this->dataTableFormat($files);
		$this->output->set_content_type('application/json')->set_output(json_encode(array('aaData' => $aaData)));
	}
	
	/**
	 * @param $data (list)
	 * return array data for dataTable pluguin
	 */
	private function dataTableFormat($data)
	{
		//load text helper
		$this->load->helper('text_helper');
		$aaData = array();
		foreach($data as $file){ 
			$td0 = '<label class="radio"><input type="radio" name="create-file" value="'.$file['id_file'].'"><i></i></label>';
			$td1 = '<i></i><span class="hidden-xs">'.$file['orig_name'].'</span><span class="hidden-md hidden-sm hidden-lg">'.ellipsize($file['orig_name'], 35).'</span>';
			$td2 = '<i class="fa fa-folder-open"></i> <span class="hidden-xs">'.$file['name'].'</span><span class="hidden-md hidden-sm hidden-lg">'.ellipsize($file['name'], 35).'</span>';
			$td3 = $file['id_file'];
			$td4 = $file['id_object'];
			$aaData[] = array($td0, $td1, $td2, $td3, $td4);
		}
		return $aaData;
	}
	
	/**
	 * @param $type (print or mill) 
	 * Start print or mill
	 */
	public function startCreate($type = 'print')
	{
		$postData = $this->input->post(); //home_all
		
		switch($type){
			case 'print':
				$this->startPrint($postData); // go to start print function
				break;
			case 'mill':
				$this->startMill($postData); // go to start mill function
				break;
		}
	}

	public function test($id)
	{
		$data = array();
		$data['idFile'] = $id;
		$this->startPrint($data);
	}

	/**
	 * @param $data (POST DATA)
	 * start print task
	 */
	//~ private function startPrint($data)
	private function startPrint($data)
	{
		//startPrint
		
		//load helpers
		$this->load->helpers('fabtotum_helper');
		
		$this->load->model('Files', 'files');
		$fileToCreate = $this->files->get($data['idFile'], 1);
		$temperatures = readInitialTemperatures($fileToCreate['full_path']);
		if($temperatures == false){
			$this->output->set_content_type('application/json')->set_output(json_encode(array('start' => false, 'message' => 'File not found')));
			return;
		}
		
		$homAllResult = doMacro('home_all');
		if($homAllResult['response'] == false){
			$this->output->set_content_type('application/json')->set_output(json_encode(array('start' => false, 'message' => $homAllResult['message'])));
			return;
		}
		
		$startPrintResult = doMacro('start_additive', '', [ $temperatures['extruder'], $temperatures['bed'] ] );
		if($startPrintResult['response'] == false){
			$this->output->set_content_type('application/json')->set_output(json_encode(array('start' => false, 'message' => $startPrintResult['message'], 'trace'=>$startPrintResult['trace'], 'error' => $startPrintResult['reply'])));
			return;
		}
		
		//get object record
		$object = $this->files->getObject($fileToCreate['id']);
		//ready to print
		//add record to DB
		$this->load->model('Tasks', 'tasks');
		$taskData = array(
			'user'       => $this->session->user['id'],
			'controller' => 'create',
			'type'       => 'print',
			'status'     => 'running',
			'id_file'    => $data['idFile'],
			'id_object'  => $object['id'],
			'start_date' => date('Y-m-d H:i:s')
		);
		$taskId   = $this->tasks->add($taskData);
		$userID   = $this->session->user['id'];
		
		//start print
		$printArgs = array(
						'-T' => $taskId, 
						'-F' => $fileToCreate['full_path']
						);
		startScript('py/print.py', $printArgs);
		
		$this->output->set_content_type('application/json')->set_output(json_encode(array('start' => true, 'id_task' => $taskId, 'temperatures' => $temperatures)));
	}
	/**
	 * @param $data (POST DATA)
	 * start mill task
	 */
	private function startMill($data)
	{
		//TODO
	}
	
	/**
	 *  abort task
	 */
	public function abort($taskId)
	{
		$this->load->helper('fabtotum_helper');
		//abort
		$response = abort();
		$this->output->set_content_type('application/json')->set_output(json_encode($response));
		//TODO 
	}
	
	/**
	 * complete task
	 */
	public function complete($taskID)
	{
		//update db status
		//$this->load->model('Tasks', 'tasks');
		//$this->tasks->update($taskID, array('status' => 'completed', 'finish_date' => date('Y-m-d H:i:s')));
		//$this->output->set_content_type('application/json')->set_output(json_encode(array(true)));
	}
	
	/**
	 *  exec action from print/mill control panel
	 */
	public function action($action, $value = '')
	{
		$this->load->helper('fabtotum_helper');
		$action($value);
		$this->output->set_content_type('application/json')->set_output(json_encode(array(true)));
	}		
 }
 
?>

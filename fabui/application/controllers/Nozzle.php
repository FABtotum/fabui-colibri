<?php
/**
 * 
 * @author Krios Mane
 * @author Daniel Kesler
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */

defined('BASEPATH') OR exit('No direct script access allowed');
 
class Nozzle extends FAB_Controller {
	/**
	 * 
	 */
	function __construct()
	{	
		parent::__construct();
		session_write_close(); //avoid freezing page
	}
	/**
	 * 
	 */
	public function index($type = 'length')
	{
		switch($type){
			case 'length':
				$this->doHeightCalibration();
				break;
		}
	}
	/**
	 * height calibration controller
	 */
	public function doHeightCalibration()
	{
		//load libraries, helpers, model
		$this->load->library('smart');
		$this->load->helper('form');
		$this->load->helper('fabtotum_helper');
		$this->config->load('fabtotum');
		$extPath = $this->config->item('ext_path');
		
		//data
		$data = array();
		$data['warning'] = false;
		
		$head_info = getInstalledHeadInfo();
		
		if($head_info['nozzle_offset'] == 0)
			$data['warning'] = true;
		
		//main page widget
		$widgetOptions = array(
			'sortable'     => false, 'fullscreenbutton' => true,  'refreshbutton' => false, 'togglebutton' => false,
			'deletebutton' => false, 'editbutton'       => false, 'colorbutton'   => false, 'collapsed'    => false
		);
		
		$widget         = $this->smart->create_widget($widgetOptions);
		$widget->id     = 'main-widget-feeder-calibration';
		$widget->header = array('icon' => 'icon-fab-print', "title" => "<h2>Nozzle Height Calibration</h2>");
		$widget->body   = array('content' => $this->load->view('nozzle/height_widget', $data, true ), 'class'=>'fuelux');
		
		$this->addJsInLine($this->load->view('nozzle/height_js', $data, true));
		$this->content = $widget->print_html(true);
		$this->view();
	}
	/**
	 * 
	 */
	public function getOffset()
	{
		$this->load->helper('fabtotum_helper');
		$info = getInstalledHeadInfo();
		$this->output->set_content_type('application/json')->set_output(
				json_encode( array('nozzle_offset' => $info['nozzle_offset']) )
			);
	}
	/**
	 * 
	 */
	public function overrideOffset($override_by)
	{
		$this->load->helper('fabtotum_helper');
		
		$info = getInstalledHeadInfo();
		$old_nozzle_offset = $info['nozzle_offset'];
		$new_nozzle_offset = $old_nozzle_offset + $override_by;
		
		$info['nozzle_offset'] = $new_nozzle_offset;
		$result = saveInfoToInstalledHead($info);
		
		$this->output->set_content_type('application/json')->set_output(
				json_encode( array(
					'nozzle_offset' => $new_nozzle_offset,
					'old_nozzle_offset' => $old_nozzle_offset,
					'over' => $override_by) )
			);
	}
	/**
	 * 
	 */
	public function calibrateHeight()
	{
		$this->load->helper('fabtotum_helper');
		$_result = doMacro('measure_nozzle_offset');
		
		$this->output->set_content_type('application/json')->set_output(
			json_encode( array(
				'nozzle_z_offset'     => $_result['reply']['nozzle_z_offset'],
				) )
			);
	}
	/**
	 * 
	 */
	public function prepare()
	{
		$this->load->helper('fabtotum_helper');
		$offset = doMacro('measure_probe_offset');
		$result = doMacro('measure_nozzle_prepare');
		$this->output->set_content_type('application/json')->set_output(json_encode( $offset ));
	}
	/**
	 * 
	 */
	public function pidtune()
	{
		$this->load->library('smart');
		$this->load->helper('form');
		$this->load->helper('fabtotum_helper');
		$this->load->helper('plugin_helper');
		
		$this->load->model('Tasks', 'tasks');
		//$this->tasks->truncate();
		$runningTask = $this->tasks->getRunning();
		
		
		$data = array();
		$data['installed_head'] = getInstalledHeadInfo();
		$data['runningTask'] = $this->tasks->getRunning('maintenance');
		
		$data['task'] = 'stopped';
		
		$widgetOptions = array(
				'sortable'     => false, 'fullscreenbutton' => true,  'refreshbutton' => false, 'togglebutton' => false,
				'deletebutton' => false, 'editbutton'       => false, 'colorbutton'   => false, 'collapsed'    => false
		);
		
		$widgeFooterButtons = $this->smart->create_button(_('Save'), 'primary')->attr(array('id' => 'save'))->attr('data-action', 'exec')->icon('fa-save')->print_html(true);
		
		
		$widgeFooterButtons = $this->smart->create_button(_('Start'), 'default')->attr(array('id' => 'autotune'))->attr('data-action', 'start')->icon('fa-play')->print_html(true)
		.' '.$this->smart->create_button(_('Save'), 'primary')->attr(array('id' => 'save'))->icon('fa-save')->print_html(true);
		
		
		$widget         = $this->smart->create_widget($widgetOptions);
		$widget->id     = 'main-widget-nozzle-pidtune';
		$widget->header = array('icon' => 'fa-thermometer-three-quarters', "title" => "<h2>PID Tune</h2>");
		$widget->body   = array('content' => $this->load->view('nozzle/pidtune/widget', $data, true ), 'class'=>'no-padding', 'footer'=>$widgeFooterButtons);
		
		$this->addJSFile('/assets/js/plugin/flot/jquery.flot.cust.min.js'); //datatable
		$this->addJSFile('/assets/js/plugin/flot/jquery.flot.resize.min.js'); //datatable
		$this->addJSFile('/assets/js/plugin/flot/jquery.flot.fillbetween.min.js'); //datatable
		$this->addJSFile('/assets/js/plugin/flot/jquery.flot.time.min.js'); //datatable
		$this->addJSFile('/assets/js/plugin/flot/jquery.flot.tooltip.min.js'); //datatable
		
		
		$this->addJSFile('/assets/js/plugin/fuelux/wizard/wizard.min.old.js'); //wizard
		$this->addJsInLine($this->load->view('nozzle/pidtune/js', $data, true));
		$this->content = $widget->print_html(true);
		$this->view();
	}
	/**
	 * 
	 */
	public function startPidTune()
	{
		$postData = $this->input->post();
		$this->load->helpers('fabtotum_helper');
		//create db record
		$this->load->model('Tasks', 'tasks');
		$taskData = array(
			'user'       => $this->session->user['id'],
			'controller' => 'maintenance',
			'type'       => 'pid_tune',
			'status'     => 'running',
			'start_date' => date('Y-m-d H:i:s')
		);
		
		$taskID = $this->tasks->add($taskData);
		$taskArgs = array(
			'-T' => $taskID,
			'-t' => $postData['temperature'],
			'-c' => $postData['cycle']
		);
		resetTaskMonitor();
		startPyScript('autotune.py', $taskArgs);
		$this->output->set_content_type('application/json')->set_output(json_encode( array('start'=> true, 'args' => $taskArgs) ));
	}
	/**
	 * 
	 */
	public function savePIDValues()
	{
		$postData = $this->input->post();
		$this->load->helpers('fabtotum_helper');
		$installedHeadInfo        = getInstalledHeadInfo();
		$installedHeadInfo['pid'] = 'M301 P'.$postData['p'].' I'.$postData['i'].' D'.$postData['d'];
		saveInfoToInstalledHead($installedHeadInfo);
		resetController();
		$this->output->set_content_type('application/json')->set_output(json_encode( $installedHeadInfo ));
	}
	
	
}
 
?>

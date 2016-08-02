<?php
/**
 * 
 * @author Krios Mane
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */
 defined('BASEPATH') OR exit('No direct script access allowed');
 
 class Scan extends FAB_Controller {
 	
	public function index()
	{
		//load libraries, helpers, model
		$this->load->library('smart');
		$this->load->model('ScanConfiguration', 'scanconfiguration');
		//data
		$data = array();
		$data['scanModes'] = $this->scanconfiguration->getModes();
		$data['scanQualities'] = $this->scanconfiguration->getQualities();
		$data['modesForDropdown'] = $this->scanconfiguration->getModesForDropdown();
		$data['step1']  = $this->load->view('scan/wizard/step1', $data, true );
		$data['step2']  = $this->load->view('scan/wizard/step2', null, true );
		$data['wizard'] = $this->load->view('scan/wizard/main',  $data, true );
		//main page widget
		$widgetOptions = array(
				'sortable'     => false, 'fullscreenbutton' => true,  'refreshbutton' => false, 'togglebutton' => false,
				'deletebutton' => false, 'editbutton'       => false, 'colorbutton'   => false, 'collapsed'    => false
		);
		$widget         = $this->smart->create_widget($widgetOptions);
		$widget->id     = 'main-widget-scan';
		$widget->header = array('icon' => 'icon-fab-scan', "title" => "<h2>Scan</h2>");
		$widget->body   = array('content' => $this->load->view('scan/main_widget', $data, true ), 'class'=>'fuelux');
		$this->content = $widget->print_html(true);
		
		$this->addJSFile('/assets/js/plugin/fuelux/wizard/wizard.min.js'); //wizard
		$this->addJSFile('/assets/js/plugin/jquery-validate/jquery.validate.min.js'); //validator
		$this->addCssFile('/assets/css/scan/style.css');
		$this->addJsFile('/assets/js/scan/scan.js');
		$this->addJsInLine($this->load->view('scan/js', $data, true));
		
		$this->view(); 
	}
	/**
	 * @param int scan mode id
	 */
	public function getScanModeInfo($scanModeId)
	{
		//load libraries, helpers, model
		$this->load->model('ScanConfiguration', 'scanconfiguration');
		$scan = $this->scanconfiguration->getModeById($scanModeId);
		$this->output->set_content_type('application/json')->set_output($scan['values']);
	}
	/**
	 * @param int scan mode id
	 */
	public function getScanModeSettings($scanModeId)
	{
		//load models, libraries, helpers
		$this->load->model('ScanConfiguration', 'scanconfiguration');
		$this->load->model('Objects', 'objects');
		$this->load->helper('form');
		$scanMode = $this->scanconfiguration->getModeById($scanModeId);
		$methodName = $scanMode['name'].'Settings';
		$data['objectsForDropdown'] = $this->objects->getObjectsorDropdown();
		$data['content'] = $this->$methodName();
		$this->load->view('scan/settings/default', $data);
	}
	/**
	 * return settings form for sweep mode
	 */
	public function sweepSettings()
	{
		//TODO
		return $this->load->view('scan/settings/sweep', null, true);
	}
	/**
	 * return settings form for rotating mode
	 */
	public function rotatingSettings(){
		//TODO
		//load models, libraries, helpers
		return $this->load->view('scan/settings/rotating', null, true);
	}
	/**
	 * return settings form for probing mode
	 */
	public function probingSettings(){
		//TODO
		return $this->load->view('scan/settings/probing', null, true);
	}
	/**
	 * return settings form for photogrammetry mode
	 */
	public function photogrammetrySettings(){
		//TODO
		return $this->load->view('scan/settings/photogrammetry', null, true);
	}
	
			
 }
 
?>
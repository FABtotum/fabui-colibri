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
		$data['probingQualities'] = $this->scanconfiguration->getProbingQualities(); 
		$data['modesForDropdown'] = $this->scanconfiguration->getModesForDropdown();
		$data['step1']  = $this->load->view('scan/wizard/step1', $data, true );
		$data['step2']  = $this->load->view('scan/wizard/step2', null, true );
		$data['step3']  = $this->load->view('scan/wizard/step3', null, true );
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
		$this->addJSFile('/assets/js/plugin/cropper/cropper.js'); //validator
		$this->addCssFile('/assets/js/plugin/cropper/cropper.min.css');
		$this->addCssFile('/assets/css/scan/style.css');
		$this->addJsFile('/assets/js/scan/scan.js');
		$this->addJsInLine($this->load->view('scan/js', $data, true),true);
		$this->addJsInLine('<script type="text/javascript">initScanPage();</script>');
		
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
		$data['label'] = json_decode($scanMode['values'], true)['info']['name'];
		$data['suggestedObjectName'] = json_decode($scanMode['values'], true)['info']['name'].' - Object name';
		$data['suggestedFileName'] = json_decode($scanMode['values'], true)['info']['name'].' - File name';
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
	/**
	 * return get ready instructions
	 */
	public function getReady($scanModeId)
	{
		//load models, libraries, helpers
		$this->load->model('ScanConfiguration', 'scanconfiguration');
		$scanMode = $this->scanconfiguration->getModeById($scanModeId);
		$methodName = $scanMode['name'].'Instructions';
		$data['content'] = method_exists ($this, $methodName) ? $this->$methodName() : '';
		$this->load->view('scan/instructions/default', $data);
	}
	/**
	 * return instructions for rotating mode
	 */
	public function rotatingInstructions()
	{
		//TODO
		return $this->load->view('scan/instructions/rotating', null, true);
	}
	/**
	 * return instructions for sweep mode
	 */
	public function sweepInstructions()
	{
		//TODO
		return $this->load->view('scan/instructions/sweep', null, true);
	}
	/**
	 * return instructions for probing mode
	 */
	public function probingInstructions()
	{
		//TODO
		return $this->load->view('scan/instructions/probing', null, true);
	}
	/**
	 * return instructions for photogrammetry mode
	 */
	public function photogrammetryInstructions()
	{
		//TODO
		return $this->load->view('scan/instructions/photogrammetry', null, true);
	}
	/**
	 * prepare scan macro
	 */
	public function prepareScan()
	{
		//load helpers
		$this->load->helpers('fabtotum_helper');
		$checkPreScanResult = doMacro('check_pre_scan');
		$this->output->set_content_type('application/json')->set_output(json_encode(array('response' => $checkPreScanResult['response'], 'trace' => $checkPreScanResult['trace'])));
	}
	/**
	 * start scan
	 */
	public function startScan($scanModeId)
	{
		//load models, libraries, helpers
		$this->load->model('ScanConfiguration', 'scanconfiguration');
		$scanMode = $this->scanconfiguration->getModeById($scanModeId);
		$methodName = $scanMode['name'].'Start';
		$params = $this->input->post();
		if(method_exists ($this, $methodName)) $this->$methodName($params);
	}
	/**
	 * start sweep mode scan task
	 */
	public function sweepStart($params)
	{
		//load helpers
		$this->load->helpers('fabtotum_helper');
		//preparing printer
		$checkPreScanResult = doMacro('check_pre_scan');
		if($checkPreScanResult['response'] == false){
			$this->output->set_content_type('application/json')->set_output(json_encode(array('start' => false, 'trace' => $checkPreScanResult['trace'])));
			return;
		}
		$sScanResult = doMacro('s_scan');
		if($sScanResult['response'] == false){
			$this->output->set_content_type('application/json')->set_output(json_encode(array('start' => false, 'trace' => $sScanResult['trace'])));
			return;
		}
		//starting task
		$taskArgs = array(
			'-s' => $params['slices'],
			'-i' => $params['iso'],
			'-b' => $params['start'],
			'-e' => $params['end'],
			'-w' => $params['width'],
			'-h' => $params['height']
		);
		startScan('s_scan.py', $taskArgs);
		$this->output->set_content_type('application/json')->set_output(json_encode(array('start' => true, 'params'=>$params)));
	}
	/**
	 * start probing mode scan task
	 */
	public function probingStart()
	{
		//load helpers
		$this->load->helpers('fabtotum_helper');
		//preparing printer
		$checkPreScanResult = doMacro('check_pre_scan');
		if($checkPreScanResult['response'] == false){
			$this->output->set_content_type('application/json')->set_output(json_encode(array('start' => false, 'trace' => $checkPreScanResult['trace'])));
			return;
		}
		$sScanResult = doMacro('p_scan');
		if($sScanResult['response'] == false){
			$this->output->set_content_type('application/json')->set_output(json_encode(array('start' => false, 'trace' => $sScanResult['trace'])));
			return;
		}
		$this->output->set_content_type('application/json')->set_output(json_encode(array('start' => true)));
	}		
 }
 
?>
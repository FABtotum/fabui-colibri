<?php
/**
 * 
 * @author Krios Mane
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */
 defined('BASEPATH') OR exit('No direct script access allowed');
 
 class Settings extends FAB_Controller {
 	
	public function hardware(){
			
		//load libraries, helpers, model, config
		$this->load->library('smart');
		$this->load->helper('fabtotum_helper');
		$this->load->helper('form');
		$this->config->load('fabtotum');
		//load settings (default or customs)
		if(!file_exists($this->config->item('default_settings'))){ // if default settings file doesn't exits, create it
			createDefaultSettings();
 		}
		if(!file_exists($this->config->item('custom_settings'))){
			copy($this->config->item('default_settings'), $this->config->item('custom_settings'));
		}
		$data['defaultSettings'] = json_decode(file_get_contents($this->config->item('default_settings')), true);
		$data['customSettings']  = json_decode(file_get_contents($this->config->item('custom_settings')), true);
		$data['yesNoOptions'] = array('1' => 'Yes', '0' => 'No');
		$data['customizeActionsOptions'] = array('none' => 'None', 'shutdown' => 'Shutdown');
		//main page widget
		$widgetOptions = array(
			'sortable' => false, 'fullscreenbutton' => true,'refreshbutton' => false,'togglebutton' => false,
			'deletebutton' => false, 'editbutton' => false, 'colorbutton' => false, 'collapsed' => false
		);
		$headerToolbar = '
			<ul class="nav nav-tabs pull-right">
				<li class="active"><a data-toggle="tab" href="#hardware-tab"> Hardware</a></li>
				<li><a data-toggle="tab" href="#safety-tab"> Safety</a></li>
				<li><a data-toggle="tab" href="#homing-tab"> Homing</a></li>
				<li><a data-toggle="tab" href="#customized-actions-tab"> Customized actions</a></li>
				<li><a data-toggle="tab" href="#print-tab"> Print</a></li>
				<li><a data-toggle="tab" href="#milling-tab"> Milling</a></li>
				<li><a data-toggle="tab" href="#lighting-tab"> Lighting</a></li>
			</ul>';
		$widgeFooterButtons = $this->smart->create_button('Save', 'primary')->attr(array('id' => 'save'))->attr('data-action', 'exec')->icon('fa-save')->print_html(true);
							  
		$widget         = $this->smart->create_widget($widgetOptions);
		$widget->id     = 'hardware-settings-widget';
		$widget->header = array('icon' => 'fa-cog', "title" => "<h2>Hardware</h2>", 'toolbar'=>$headerToolbar);
		$widget->body   = array('content' => $this->load->view('settings/hardware_widget', $data, true ), 'class'=>'no-padding', 'footer'=>$widgeFooterButtons);
		
		$this->addJsInLine($this->load->view('settings/hardware_js', $data, true));
		//$this->addCSSInLine('<style type="text/css">.custom_settings{display:none !important;}</style>'); 
		$this->content = $widget->print_html(true);
		$this->view();
	}

	/**
	 * save settings 
	 */
	public function saveSettings()
	{
		//get data from post
		$postData = $this->input->post();
		//load libraries, helpers, model, config
		$this->load->helpers('utility_helper');
		$this->load->helpers('fabtotum_helper');
		//create settings array
		$settingsToSave = arrayFromPost($postData);
		if($postData['settings_type'] == 'default'){ //don't override those vaules for default settings
			unset($settingsToSave['e']);
			unset($settingsToSave['a']);
			unset($settingsToSave['feeder']); 
			unset($settingsToSave['invert_x_endstop_logic']);
		}
		//load settings
		$loadedSettings = loadSettings($postData['settings_type']);
		$newSettings = array_replace ($loadedSettings, $settingsToSave);
		saveSettings($newSettings, $postData['settings_type']);
		if($postData['settings_type'] == 'custom'){
			$defaultSettings = loadSettings('default');
			saveSettings(array_replace($defaultSettings, array('settings_type' => 'custom')), 'default');
		}
		//update settings on session
		$this->session->settings = $newSettings;
	}
	
	/***
	 * 
	 */
	public function ethernet()
	{
		//TODO
	}
	
	/***
	 * 
	 */
	public function wifi()
	{
		//TODO
	}
	
	/***
	 * 
	 */
	public function dnssd()
	{
		//TODO
	}
	
	/***
	 * 
	 */
	public function raspicam()
	{
		//TODO
	}
	
 }
 
?>
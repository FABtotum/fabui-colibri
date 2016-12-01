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
 	
	public function index()
	{
		//load libraries, helpers, model, config
		$this->load->library('smart');
		$this->load->helper('layout');
		
		$data = array();
		//$data['bundlesStatus'] = getBundlesStatus();
		
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
		
		$this->addJsInLine($this->load->view('updates/index/js','', true));
		
		$this->view();
	}
	
	/**
	 * get bundles status
	 */
	function bundleStatus()
	{
		$this->load->helper('update_helper');
		$bundlesStatus = getBundlesStatus();
		
		echo json_encode($bundlesStatus);
	}
			
 }
 
?>
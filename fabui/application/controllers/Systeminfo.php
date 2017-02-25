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
 
class SystemInfo extends FAB_Controller {

	public function index()
	{
		//load librarire, helpers, config
		$this->load->library('smart');
		$this->load->helper('fabtotum_helper');
		$this->load->helper('update_helper');
		$this->load->helper('os_helper');
		$this->config->load('fabtotum');
		//
		$data = json_decode(startPyScript('systeminfo.py', '', false), true);
		$data['bundles'] = getLocalBundles();
		$data['versions'] = array();
		$macroResponse = doMacro('version');
		if($macroResponse['response']){
			$data['versions'] = $macroResponse['reply'];
		}
		$data['installed_head'] = getInstalledHeadInfo();
		
		$widgetOptions = array(
				'sortable'     => false, 'fullscreenbutton' => true,  'refreshbutton' => false, 'togglebutton' => false,
				'deletebutton' => false, 'editbutton'       => false, 'colorbutton'   => false, 'collapsed'    => false
		);
		$widget     = $this->smart->create_widget($widgetOptions);
		$widget->id = 'main-widget-systeminfo';
		$widget->header = array('icon' => 'fa-info-circle', "title" => "<h2>System Info</h2>");
		$widget->body   = array('content' => $this->load->view('systeminfo/widget', $data, true ), 'class'=>'');
		$this->content = $widget->print_html(true);
		$this->view();
	}
}
 
?>

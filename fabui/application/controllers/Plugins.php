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
 
 class Plugins extends FAB_Controller {
 	
	public function index()
	{
		//load libraries, helpers, model
		$this->load->library('smart');
		$this->load->helper('form');
		$this->load->helper('plugin_helper');
		
		//data
		$data = array();
		$data['installed_plugins'] = getInstalledPlugins();
        $this->content = json_encode($data);
		//~ $widgetOptions = array(
			//~ 'sortable'     => false, 'fullscreenbutton' => true,  'refreshbutton' => false, 'togglebutton' => false,
			//~ 'deletebutton' => false, 'editbutton'       => false, 'colorbutton'   => false, 'collapsed'    => false
		//~ );
		
		//~ $widgeFooterButtons = '';

		//~ $widget         = $this->smart->create_widget($widgetOptions);
		//~ $widget->id     = 'main-widget-head-installation';
		//~ $widget->header = array('icon' => 'fa-toggle-down', "title" => "<h2>Plugins</h2>");
		//~ $widget->body   = array('content' => $this->load->view('plugins/main_widget', $data, true ), 'class'=>'no-padding', 'footer'=>$widgeFooterButtons);

		//~ $this->addJsInLine($this->load->view('plugins/js', $data, true));
		//~ $this->content = $widget->print_html(true);
		$this->view();
	}

 }
 
?>

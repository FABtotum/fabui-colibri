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
 
class Head extends FAB_Controller {

	public function index()
	{
		//load libraries, helpers, model
		$this->load->library('smart');
		$this->load->helper('form');
		$this->load->helper('fabtotum_helper');
		//data
		$data = array();

		$_units = loadSettings();
		$heads  = loadHeads();
		
		$data['units'] = $_units;
		$data['heads'] = $heads;

		$heads_list = array();
		$heads_list[] = array('head_shape' => '---');
		
		foreach($heads as $head => $val)
		{
			$heads_list[] = array($head => $val['name']);
		}
		
		$heads_list[] = array('more_heads' => 'Get more heads');
		$data['heads_list'] = $heads_list;
		
		$data['head'] = isset($_units['hardware']['head']['type']) ? $_units['hardware']['head']['type'] : 'head_shape';
		
/*
		if (isset($_units['settings_type']) && $_units['settings_type'] == 'custom') {
			$_units = json_decode(file_get_contents($this -> config -> item('fabtotum_custom_config_units', 'fabtotum')), TRUE);
		}
*/
		//main page widget
		$widgetOptions = array(
			'sortable'     => false, 'fullscreenbutton' => true,  'refreshbutton' => false, 'togglebutton' => false,
			'deletebutton' => false, 'editbutton'       => false, 'colorbutton'   => false, 'collapsed'    => false
		);
		
		$widgeFooterButtons = '<span style="margin-right:10px;"><i class="fa fa-warning"></i> Before clicking "Install", make sure the head is properly locked in place </span>' .
							   $this->smart->create_button('Install', 'primary')->attr(array('id' => 'set-head'))->icon('fa-wrench')->print_html(true);
		
		$widget         = $this->smart->create_widget($widgetOptions);
		$widget->id     = 'main-widget-head-installation';
		$widget->header = array('icon' => 'fa-toggle-down', "title" => "<h2>Heads</h2>");
		$widget->body   = array('content' => $this->load->view('head/install', $data, true ), 'class'=>'no-padding', 'footer'=>$widgeFooterButtons);

		$this->addJsInLine($this->load->view('head/install_js', $data, true));
		$this->content = $widget->print_html(true);
		$this->view();
	}

}
 
?>

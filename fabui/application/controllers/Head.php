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

	public function index($type = 'install')
	{
		switch($type){
			case 'install':
				$this->doInstall();
				break;
			case 'add':
				$this->doAdd();
				break;
		}
	}
	
	public function doInstall()
	{
		//load libraries, helpers, model
		$this->load->library('smart');
		$this->load->helper('form');
		$this->load->helper('fabtotum_helper');
		//data
		$data = array();

		$_units = loadSettings();
		if (isset($_units['settings_type']) && $_units['settings_type'] == 'custom') {
			$_units = loadSettings( $_units['settings_type'] );
		}
		
		$heads  = loadHeads();
		
		$data['units'] = $_units;
		$data['heads'] = $heads;

		$heads_list = array();
		$heads_list['head_shape'] = '---';

		
		foreach($heads as $head => $val)
		{
			$heads_list[$head] = $val['name'];
		}
		
		$heads_list['more_heads'] = 'Get more heads';
		$data['heads_list'] = $heads_list;
		
		$data['head'] = isset($_units['hardware']['head']) ? $_units['hardware']['head'] : 'head_shape';

		$headerToolbar = '';
		
		//main page widget
		$widgetOptions = array(
			'sortable'     => false, 'fullscreenbutton' => true,  'refreshbutton' => false, 'togglebutton' => false,
			'deletebutton' => false, 'editbutton'       => false, 'colorbutton'   => false, 'collapsed'    => false
		);
		
		$widgeFooterButtons = '<span style="margin-right:10px;"><i class="fa fa-warning"></i> Before clicking "Install", make sure the head is properly locked in place </span>' .
							   $this->smart->create_button('Install', 'primary')->attr(array('id' => 'set-head'))->icon('fa-wrench')->print_html(true);
		
		$widget         = $this->smart->create_widget($widgetOptions);
		$widget->id     = 'main-widget-head-installation';
		$widget->header = array('icon' => 'fa-toggle-down', "title" => "<h2>Heads</h2>", 'toolbar'=>$headerToolbar);
		$widget->body   = array('content' => $this->load->view('head/install', $data, true ), 'class'=>'no-padding', 'footer'=>$widgeFooterButtons);

		$this->addJsInLine($this->load->view('head/install_js', $data, true));
		$this->content = $widget->print_html(true);
		$this->view();
	}
	
	public function doAdd()
	{
		$this->view();
	}
	
	public function setHead($new_head)
	{
		$this->load->helper('fabtotum_helper');
		$heads  = loadHeads();

		$_data = loadSettings();
		$settings_type = $_data['settings_type'];
		if (isset($_data['settings_type']) && $_data['settings_type'] == 'custom') {
			$_data = loadSettings( $_data['settings_type'] );
		}

		$head_info = $heads[$new_head];
		$pid	   = $head_info['pid'];
		$fw_id	   = $head_info['fw_id'];
		$mode	   = $head_info['working_mode'];
		$th_idx	   = $head_info['thermistor_index'];
		$max_temp  = $head_info['max_temp'];
		$offset    = $head_info['probe_offset'];

		if ($pid != '') {
			doGCode( array($pid, 'M500') );
		}
		
		if( intval($max_temp) > 25 ) {
			doGCode( array('M801 S'.$max_temp) );
		}
		
		doGCode( array('M800 S'.$th_idx, 'M450 S'.$mode) );
		
		if($offset != "0") {
			doGCode( array('M710 S'.$offset) );
		}
		
		doGCode( array('M793 S'.$fw_id, 'M500', 'M999', 'G4 P500', 'M728') );

		$_data['hardware']['head'] = $new_head;
		
		saveSettings($_data, $settings_type);

		$this->output->set_content_type('application/json')->set_output(json_encode( $head_info ));
	}

}
 
?>

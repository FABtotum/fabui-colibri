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
		$heads_list['head_shape'] = '---';

		
		foreach($heads as $head => $val)
		{
			$heads_list[$head] = $val['name'];
		}
		
		$heads_list['more_heads'] = 'Get more heads';
		$data['heads_list'] = $heads_list;
		
		$data['head'] = isset($_units['hardware']['head']) ? $_units['hardware']['head'] : 'head_shape';

		$headerToolbar = '<div class="widget-toolbar" role="menu">
		<a class="btn btn-success settings-action" data-action="add" href=""><i class="fa fa-plus"></i> '._("Add new head").' </a>
		</div>';
		
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

		$this->addJSFile('/assets/js/plugin/jquery-validate/jquery.validate.min.js'); //validator
		$this->addJSFile('/assets/js/plugin/inputmask/jquery.inputmask.bundle.js');
		$this->addJSFile('/assets/js/plugin/FileSaver.min.js');
		$this->addJsInLine($this->load->view('head/install_js', $data, true));
		$this->addCssFile('/assets/css/head/style.css');
		$this->content = $widget->print_html(true);
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
		/*$pid	   = $head_info['pid'];
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
		
		
		
		if($offset != "0") {
			doGCode( array('M710 S'.$offset) );
		}
		
		doGCode( array('M793 S'.$fw_id, 'M500', 'M999', 'G4 P500', 'M728') );

		doGCode( array('M800 S'.$th_idx, 'M450 S'.$mode) );*/
		doMacro('install_head', '', [$new_head]);

		$_data['hardware']['head'] = $new_head;
		
		saveSettings($_data, $settings_type);

		$this->output->set_content_type('application/json')->set_output(json_encode( $head_info ));
	}

	public function removeHead($head)
	{
		$this->load->helper('fabtotum_helper');
		$result = removeHeadInfo($head);
		$this->output->set_content_type('application/json')->set_output(json_encode( $result ));
	}
	
	public function saveHead($head_filename)
	{
		$this->load->helper('fabtotum_helper');
		
		$info = $this->input->post();
		$result = saveHeadInfo($info, $head_filename);
		
		$this->output->set_content_type('application/json')->set_output(json_encode( $result ));
	}
}
 
?>

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
 
class Feeder extends FAB_Controller {
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
	public function index($type = 'calibrate')
	{
		switch($type){
			case 'calibrate':
				$this->doCalibration();
				break;
			case 'engage':
				$this->doEngage();
				break;
			case 'profiles':
				$this->doProfiles();
				break;
		}
	}

	public function doProfiles()
	{
		//load libraries, helpers, model
		$this->load->library('smart');
		$this->load->helper('form');
		$this->load->helper('fabtotum_helper');
		$this->config->load('fabtotum');
		
		//data
		$data = array();
		
		$_units = loadSettings();
		$data['units'] = $_units;
		
		$feeders = loadFeeders();
		$data['feeders'] = $feeders;

		foreach($feeders as $feeder => $val)
		{
			$feeder_list[$feeder] = $val['name'];
		}
		
		//$heads_list['more_heads'] = 'Get more heads';
		$data['feeder_list'] = $feeder_list;
		
		$data['feeder'] = isset($_units['hardware']['feeder']) ? $_units['hardware']['feeder'] : 'built_in_feeder';

		$headerToolbar = '<div class="widget-toolbar" role="menu">
		<a class="btn btn-success settings-action" data-action="add" href=""><i class="fa fa-plus"></i> '._("Add new feeder").' </a>
		</div>';
		
		$widgeFooterButtons = $this->smart->create_button('Configure', 'primary')->attr(array('id' => 'set-feeder'))->icon('fa-wrench')->print_html(true);
		
		//main page widget
		$widgetOptions = array(
			'sortable'     => false, 'fullscreenbutton' => true,  'refreshbutton' => false, 'togglebutton' => false,
			'deletebutton' => false, 'editbutton'       => false, 'colorbutton'   => false, 'collapsed'    => false
		);
		
		$widget         = $this->smart->create_widget($widgetOptions);
		$widget->id     = 'main-widget-feeder-calibration';
		$widget->header = array('icon' => 'fa-bars', "title" => "<h2>Feeder profiles</h2>", 'toolbar'=>$headerToolbar);
		$widget->body   = array('content' => $this->load->view('feeder/profiles/widget', $data, true ), 'class'=>'no-padding', 'footer'=>$widgeFooterButtons);
		
		$this->addJSFile('/assets/js/plugin/jquery-validate/jquery.validate.min.js'); //validator
		$this->addJSFile('/assets/js/plugin/inputmask/jquery.inputmask.bundle.js');
		$this->addJSFile('/assets/js/plugin/FileSaver.min.js');
		$this->addCssFile('/assets/css/feeder/style.css');

		$this->addJsInLine($this->load->view('feeder/profiles/js', $data, true));
		$this->content = $widget->print_html(true);
		$this->view();
	}

	public function doCalibration()
	{
		//load libraries, helpers, model
		$this->load->library('smart');
		$this->load->helper('form');
		$this->load->helper('fabtotum_helper');
		$this->config->load('fabtotum');
		
		//data
		$data = array();
		
		$tmp = doMacro('read_eeprom');
		$data['eeprom'] = $tmp['reply'];
		
		//main page widget
		$widgetOptions = array(
			'sortable'     => false, 'fullscreenbutton' => true,  'refreshbutton' => false, 'togglebutton' => false,
			'deletebutton' => false, 'editbutton'       => false, 'colorbutton'   => false, 'collapsed'    => false
		);
		
		$widget         = $this->smart->create_widget($widgetOptions);
		$widget->id     = 'main-widget-feeder-calibration';
		$widget->header = array('icon' => 'icon-fab-print', "title" => "<h2>Step Calibration</h2>");
		$widget->body   = array('content' => $this->load->view('feeder/calibration/widget', $data, true ), 'class'=>'fuelux');
		
		$this->addJsInLine($this->load->view('feeder/calibration/js', $data, true));
		$this->content = $widget->print_html(true);
		$this->view();
	}
	/**
	 * 
	 */
	public function doEngage()
	{
		//load libraries, helpers, model
		$this->load->library('smart');
		$this->load->helper('form');
		//data
		$data = array();
		
		//main page widget
		$widgetOptions = array(
			'sortable'     => false, 'fullscreenbutton' => true,  'refreshbutton' => false, 'togglebutton' => false,
			'deletebutton' => false, 'editbutton'       => false, 'colorbutton'   => false, 'collapsed'    => false
		);
		
		$widget         = $this->smart->create_widget($widgetOptions);
		$widget->id     = 'main-widget-feeder-engage';
		$widget->header = array('icon' => 'fa-hand-o-right', "title" => "<h2>Engage Feeder</h2>");
		$widget->body   = array('content' => $this->load->view('feeder/engage/widget', $data, true ), 'class'=>'fuelux');
		
		$this->addJsInLine($this->load->view('feeder/engage/js', $data, true));
		$this->content = $widget->print_html(true);
		$this->view();
	}
	/**
	 * 
	 */
	public function extrude($filament_to_extrude)
	{
		$this->load->helpers('fabtotum_helper');
		$json_data = doMacro('extrude', null, $filament_to_extrude );
		$this->output->set_content_type('application/json')->set_output(json_encode( $json_data ));
	}
	/**
	 * 
	 */
	public function engage()
	{
		$this->load->helpers('fabtotum_helper');
		$json_data = doMacro('engage_feeder');
		$this->output->set_content_type('application/json')->set_output(json_encode( $json_data ));
	}
	/**
	 * 
	 */
	public function changeStep($new_step)
	{
		$this->load->helpers('fabtotum_helper');
		
		$result = doMacro('change_step', null, $new_step);
		
		$response = array();
		$response['new_step'] = $new_step;
		$response['response'] = $result['response'];
		
		$feeder = getInstalledFeederInfo();
		$feeder['steps_per_unit'] = $new_step;
		saveInfoToInstalledFeeder($feeder);
		
		$this->output->set_content_type('application/json')->set_output(json_encode( $response ));
	}
	/**
	 * 
	 */
	public function calculateStep($actual_step, $filament_to_extrude, $filament_extruded)
	{
		$this->load->helpers('fabtotum_helper');
		
		$new_step_value = floatval($actual_step) / ( floatval($filament_extruded) / floatval($filament_to_extrude)) ;
		
		$result = doMacro('change_step', null, $new_step_value);
		
		$feeder = getInstalledFeederInfo();
		$feeder['steps_per_unit'] = $new_step_value;
		saveInfoToInstalledFeeder($feeder);
		
		$response = array();
		$response['new_step'] = $new_step_value;
		$response['old_step']            = $actual_step;
		$response['filament_to_extrude'] = $filament_to_extrude;
		$response['filament_extruded']   = $filament_extruded;
		$response['response'] = $result['response'];
	
		$this->output->set_content_type('application/json')->set_output(json_encode( $response ));
	}
	/**
	 * 
	 */
	public function setFeeder($new_feeder)
	{
		$this->load->helper('fabtotum_helper');
		$feeders  = loadFeeders();

		$_data = loadSettings();
		doMacro('install_feeder', '', [$new_feeder]);

		$_data['hardware']['feeder'] = $new_feeder;
		
		saveSettings($_data);

		$this->output->set_content_type('application/json')->set_output(json_encode( array("name" => $new_feeder) ));
	}
	/**
	 * 
	 */
	public function removeFeeder($feeder)
	{
		$this->load->helper('fabtotum_helper');
		$result = removeFeederInfo($feeder);
		$this->output->set_content_type('application/json')->set_output(json_encode( $result ));
	}
	/**
	 * 
	 */
	public function saveFeeder($feeder_filename)
	{
		$this->load->helper('fabtotum_helper');
		$info = $this->input->post();
		$result = saveFeederInfo($info, $feeder_filename);
		
		$this->output->set_content_type('application/json')->set_output(json_encode( $result ));
	}
	/**
	 * 
	 */
	public function factoryReset($feeder_filename)
	{
		$this->load->helper('fabtotum_helper');
		$result = restoreFeederFactorySettings($feeder_filename);
		
		$_data = loadSettings();
		doMacro('install_feeder', '', [$_data['hardware']['feeder']]);
		
		$this->output->set_content_type('application/json')->set_output(json_encode( $result ));
	}

}
 
?>

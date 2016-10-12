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

	public function index($type = 'calibrate')
	{
		switch($type){
			case 'calibrate':
				$this->doCalibration();
				break;
			case 'engage':
				$this->doEngage();
				break;
		}
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
		$widget->body   = array('content' => $this->load->view('feeder/calibration_widget', $data, true ), 'class'=>'fuelux');
		
		$this->addJsInLine($this->load->view('feeder/calibration_js', $data, true));
		$this->content = $widget->print_html(true);
		$this->view();
	}
	
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
		$widget->header = array('icon' => 'icon-fab-print', "title" => "<h2>Engage Feeder</h2>");
		$widget->body   = array('content' => $this->load->view('feeder/engage_widget', $data, true ), 'class'=>'fuelux');
		
		$this->addJsInLine($this->load->view('feeder/engage_js', $data, true));
		$this->content = $widget->print_html(true);
		$this->view();
	}
	
	public function extrude($filament_to_extrude)
	{
		$this->load->helpers('fabtotum_helper');
		$json_data = doMacro('extrude', null, $filament_to_extrude );
		$this->output->set_content_type('application/json')->set_output(json_encode( $json_data ));
	}
	
	public function engage()
	{
		$this->load->helpers('fabtotum_helper');
		$json_data = doMacro('engage_feeder');
		$this->output->set_content_type('application/json')->set_output(json_encode( $json_data ));
	}
	
	public function changeStep($new_step)
	{
		$this->load->helpers('fabtotum_helper');
		
		$result = doMacro('change_step', null, $new_step);
		
		$response = array();
		$response['new_step'] = $new_step;
		$response['response'] = $result['response'];
		$this->output->set_content_type('application/json')->set_output(json_encode( $response ));
	}
	
	public function calculateStep($actual_step, $filament_to_extrude, $filament_extruded)
	{
		$this->load->helpers('fabtotum_helper');
		
		$new_step_value = floatval($actual_step) / ( floatval($filament_extruded) / floatval($filament_to_extrude)) ;
		
		$result = doMacro('change_step', null, $new_step_value);
		
		$response = array();
		$response['new_step'] = $new_step_value;
		$response['old_step']            = $actual_step;
		$response['filament_to_extrude'] = $filament_to_extrude;
		$response['filament_extruded']   = $filament_extruded;
		$response['response'] = $result['response'];
	
		$this->output->set_content_type('application/json')->set_output(json_encode( $response ));
	}

}
 
?>

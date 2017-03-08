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
 
class Nozzle extends FAB_Controller {
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
	public function index($type = 'length')
	{
		switch($type){
			case 'length':
				$this->doHeightCalibration();
				break;
		}
	}
	/**
	 * height calibration controller
	 */
	public function doHeightCalibration()
	{
		//load libraries, helpers, model
		$this->load->library('smart');
		$this->load->helper('form');
		$this->load->helper('fabtotum_helper');
		$this->config->load('fabtotum');
		$extPath = $this->config->item('ext_path');
		
		//data
		$data = array();
		
		//main page widget
		$widgetOptions = array(
			'sortable'     => false, 'fullscreenbutton' => true,  'refreshbutton' => false, 'togglebutton' => false,
			'deletebutton' => false, 'editbutton'       => false, 'colorbutton'   => false, 'collapsed'    => false
		);
		
		$widget         = $this->smart->create_widget($widgetOptions);
		$widget->id     = 'main-widget-feeder-calibration';
		$widget->header = array('icon' => 'icon-fab-print', "title" => "<h2>Nozzle Height Calibration</h2>");
		$widget->body   = array('content' => $this->load->view('nozzle/height_widget', $data, true ), 'class'=>'fuelux');
		
		$this->addJsInLine($this->load->view('nozzle/height_js', $data, true));
		$this->content = $widget->print_html(true);
		$this->view();
	}
	/**
	 * 
	 */
	/*public function getLength()
	{
		$this->load->helper('fabtotum_helper');
		$_result = doMacro('read_eeprom');
		$probe_length = $_result['reply']['probe_length'];
		$this->output->set_content_type('application/json')->set_output(
				json_encode( array('probe_length' => $probe_length) )
			);
	}*/
	/**
	 * 
	 */
	public function overrideOffset($override_by)
	{
		$this->load->helper('fabtotum_helper');
		//$_result = doMacro('read_eeprom');
		//$old_probe_lenght = $_result['reply']['probe_length'];
		//$new_probe_lenght = abs($old_probe_lenght) - $override_by;
		
		// override probe value
		//doGCode('M710 S'.$new_probe_lenght );
		
		$info = getInstalledHeadInfo();
		
		$old_nozzle_offset = $info['nozzle_offset'];
		$new_nozzle_offset = $old_nozzle_offset + $override_by;
		
		$info['nozzle_offset'] = $new_nozzle_offset;
		$result = saveInfoToInstalledHead($info);
		
		$this->output->set_content_type('application/json')->set_output(
				json_encode( array(
					'nozzle_offset' => $new_nozzle_offset,
					'old_nozzle_offset' => $old_nozzle_offset,
					'over' => $override_by) )
			);
	}
	/**
	 * 
	 */
	public function calibrateHeight()
	{
		$this->load->helper('fabtotum_helper');
		$_result = doMacro('measure_nozzle_offset');
		
		$this->output->set_content_type('application/json')->set_output(
			json_encode( array(
				'nozzle_z_offset'     => $_result['reply']['nozzle_z_offset'],
				) )
			);
	}
	/**
	 * 
	 */
	public function prepare()
	{
		$this->load->helper('fabtotum_helper');
		$offset = doMacro('measure_probe_offset');
		$result = doMacro('measure_nozzle_prepare');
		$this->output->set_content_type('application/json')->set_output(json_encode( $offset ));
	}
}
 
?>

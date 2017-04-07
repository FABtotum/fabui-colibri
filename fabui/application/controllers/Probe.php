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
 
class Probe extends FAB_Controller {
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
			case 'angle':
				$this->doAngleCalibration();
				break;
		}
	}

	// angle calibration controller
	public function doAngleCalibration()
	{
		//load libraries, helpers, model
		$this->load->library('smart');
		$this->load->helper('form');
		$this->load->helper('fabtotum_helper');
		$this->config->load('fabtotum');
		$extPath = $this->config->item('ext_path');
		
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
		$widget->header = array('icon' => 'icon-fab-print', "title" => "<h2>". _("Probe Angle Calibration")."</h2>");
		$widget->body   = array('content' => $this->load->view('probe/angle_widget', $data, true ), 'class'=>'fuelux');
		
		$this->addJsInLine($this->load->view('probe/angle_js', $data, true));
		$this->content = $widget->print_html(true);
		$this->view();
	}
	
	/**
	 * @param $value servo angle value
	 * @return json object true
	 * open probe and store angle value
	 */
	public function open($value = 132)
	{
		$this->load->helper('fabtotum_helper');
		doGCode( array('M711 S'.$value, 'M401') );
		$this->output->set_content_type('application/json')->set_output(
			json_encode( array(true) )
			);
	}
	
	/**
	 * @param $value servo angle value
	 * @return json object true
	 * close probe and store angle value
	 */
	public function close($value = 26)
	{
		$this->load->helper('fabtotum_helper');
		doGCode( array('M712 S'.$value, 'M402') );
		$this->output->set_content_type('application/json')->set_output(
			json_encode( array(true) )
			);
	}
	/**
	 * 
	 */
	public function getLength()
	{
		$this->load->helper('fabtotum_helper');
		$_result = doMacro('read_eeprom');
		$probe_length = $_result['reply']['probe_length'];
		$this->output->set_content_type('application/json')->set_output(
				json_encode( array('probe_length' => $probe_length) )
			);
	}

}
 
?>

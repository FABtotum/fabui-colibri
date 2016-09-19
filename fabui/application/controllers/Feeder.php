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
		$widget->id     = 'main-widget-4th-axis';
		$widget->header = array('icon' => 'icon-fab-print', "title" => "<h2>Engage Feeder</h2>");
		$widget->body   = array('content' => $this->load->view('feeder/engage_widget', $data, true ), 'class'=>'fuelux');
		
		$this->content = $widget->print_html(true);
		$this->view();
	}

}
 
?>

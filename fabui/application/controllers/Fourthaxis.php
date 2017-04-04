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
 
class FourthAxis extends FAB_Controller {

	public function index()
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
		$widget->header = array('icon' => 'fa-arrows-h', "title" => "<h2>"._("Engage 4th Axis")."</h2>");
		$widget->body   = array('content' => $this->load->view('fourthaxis/main_widget', $data, true ), 'class'=>'fuelux');
		
		$this->addJsInLine($this->load->view('fourthaxis/js', $data, true));
		$this->content = $widget->print_html(true);
		$this->view();
	}
	
	public function engage($time)
	{
		// load jog factory class
		$this->load->helpers('fabtotum_helper');
		$response = doMacro('engage_4axis');
		$this->output->set_content_type('application/json')->set_output(json_encode( $response ));
	}

}
 
?>

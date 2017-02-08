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
 
class Bed extends FAB_Controller {

    public function index()
    {
		//load libraries, helpers, model
		$this->load->library('smart');
		$this->load->helper('form');
		$this->load->helper('fabtotum_helper');

		$data = array();
		
		//main page widget
		$widgetOptions = array(
			'sortable'     => false, 'fullscreenbutton' => true,  'refreshbutton' => false, 'togglebutton' => false,
			'deletebutton' => false, 'editbutton'       => false, 'colorbutton'   => false, 'collapsed'    => false
		);
		
		$widget         = $this->smart->create_widget($widgetOptions);
		$widget->id     = 'main-widget-bed-calibration';
		$widget->header = array('icon' => 'icon-fab-print', "title" => "<h2>Bed Calibration</h2>");
		$widget->body   = array('content' => $this->load->view('bed/calibration_widget', $data, true ), 'class'=>'fuelux');
		
		$this->addJsInLine($this->load->view('bed/calibration_js', $data, true));
		$this->content = $widget->print_html(true);
		$this->view();
    }
    
	public function calibrate($time, $num_probes, $skip_homing)
	{ 
		$this->load->helpers('fabtotum_helper');
		$this->config->load('fabtotum');
		
		$arguments = array(
				'-n' => $num_probes //number of probes
		);
		if($skip_homing) $arguments['-s'] = '';
		startPyScript('manual_bed_leveling.py', $arguments, false);
		$task_monitor = $this->config->item('task_monitor');
		$monitor = json_decode(file_get_contents($task_monitor), true);
		
		$data = array();
		$data['_response'] = $monitor;
		$content = $this->load->view('bed/calibration_results', $data, true );
		
		$html = $content;
		
		//$json_data = array(true);
		$this->output->set_content_type('application/json')->set_output(json_encode( array('html' => $html) ));
	}

}
 
?>

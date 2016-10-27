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
 
class History extends FAB_Controller {

    public function index()
    {
		//load libraries, helpers, model
		$this->load->library('smart');
		$this->load->helper('form');
		$this->load->helper('fabtotum_helper');

		$data = array();
		$data['start_date'] = date('d/m/Y', strtotime('today - 30 days'));
		$data['end_date'] = date('d/m/Y', strtotime('today'));
        
		//main page widget
		$widgetOptions = array(
			'sortable'     => false, 'fullscreenbutton' => true,  'refreshbutton' => false, 'togglebutton' => false,
			'deletebutton' => false, 'editbutton'       => false, 'colorbutton'   => false, 'collapsed'    => false
		);
		
		$widget         = $this->smart->create_widget($widgetOptions);
		$widget->id     = 'main-widget-bed-calibration';
		$widget->header = array('icon' => 'icon-fab-print', "title" => "<h2>History</h2>");
		$widget->body   = array('content' => $this->load->view('history/main_widget', $data, true ), 'class'=>'fuelux');
		
		$this->addJsInLine($this->load->view('history/main_js', $data, true));
		$this->content = $widget->print_html(true);
		$this->view();
    }
 

}
 
?>

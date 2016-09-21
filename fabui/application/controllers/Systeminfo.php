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
 
function seconds_to_time($seconds) {
	$dtF = new DateTime("@0");
	$dtT = new DateTime("@$seconds");
	return $dtF -> diff($dtT) -> format('%ad %hh %im');
}
 
class SystemInfo extends FAB_Controller {

    public function index()
    {
		$this->load->library('smart');
		$this->load->helper('fabtotum_helper');
		$this->load->helper('os_helper');
		
		$this->config->load('fabtotum');
		$extPath = $this->config->item('ext_path');
		
		$json_data = doCommandLine('python', $extPath.'/py/systeminfo.py');
		$data = json_decode($json_data, true);
		
		$widgetOptions = array(
			'sortable'     => false, 'fullscreenbutton' => true,  'refreshbutton' => false, 'togglebutton' => false,
			'deletebutton' => false, 'editbutton'       => false, 'colorbutton'   => false, 'collapsed'    => false
		);
		
		$widget         = $this->smart->create_widget($widgetOptions);
		$widget->id     = 'main-widget-systeminfo';
		$widget->header = array('icon' => 'fa-info-circle', "title" => "<h2>System Info</h2>");
		$widget->body   = array('content' => $this->load->view('systeminfo/widget', $data, true ), 'class'=>'fuelux');

		$this->addJSFile('/assets/js/plugin/bootstrap-progressbar/bootstrap-progressbar.min.js'); //datatable
		$this->addJsInLine($this->load->view('systeminfo/js', $data, true)); 
		$this->content = $widget->print_html(true);
		$this->view();
    }
    
    public function test()
    {
		
	}

}
 
?>

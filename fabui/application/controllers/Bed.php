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
	public function index()
	{
		//load libraries, helpers, model
		$this->load->library('smart');
		$this->load->helper('form');
		$this->load->helper('fabtotum_helper');
	
		$data = array();
	
		//main page widget
		$widgetOptions = array(
				'sortable'         => false,
				'fullscreenbutton' => true,
				'refreshbutton'    => false,
				'togglebutton'     => false,
				'deletebutton'     => false,
				'editbutton'       => false,
				'colorbutton'      => false,
				'collapsed'        => false
		);
	
		$data['steps'] = array(
				array(
					'title'   => _("Start"),
					'name'    => 'start',
					'content' => $this->load->view( 'bed/wizard/start', $data, true ),
					'active'  => true
				),
				array(
					'title'   => _("Calibrate"),
					'name'    => 'calibrate',
					'content' => $this->load->view( 'bed/wizard/calibrate', $data, true ),
				)
		);
	
		$headerToolbar = '
		<div class="widget-toolbar" role="menu">
			<a id="get-more-beds" class="btn btn-default no-ajax" target="_blank" href="http://store.fabtotum.com/"><i class="fa fa-cart-plus"></i> <span class="hidden-xs">'._("Get more beds").'</span> </a>
		</div>';
	
		$widget = $this->smart->create_widget($widgetOptions);
		$widget->id = 'main-widget-bed-calibration';
	
		$widget->header = array('icon' => 'fa-arrows-h', "title" => "<h2>" . _("Bed calibration") . "</h2>", 'toolbar'=>$headerToolbar);
		$widget->body   = array('content' => $this->load->view('std/task_wizard', $data, true ),'class'=>'fuelux');
		$this->content = $widget->print_html(true);
	
		$this->addJSFile('/assets/js/plugin/fuelux/wizard/wizard.min.old.js'); //wizard
		$this->addJsInLine($this->load->view( 'std/task_wizard_js',   $data, true));
		$this->addJsInLine($this->load->view('bed/js', $data, true));
	
		$this->view();
	}
    /**
     * 
     */
	public function calibrate($num_probes, $skip_homing)
	{ 
		$this->load->helpers('fabtotum_helper');
		$this->config->load('fabtotum');
		
		resetTaskMonitor();
		resetTrace();
		
		$arguments = array(
				'-n' => $num_probes //number of probes
		);
		if($skip_homing) $arguments['-s'] = '';
		
		startPyScript('manual_bed_leveling.py', $arguments, false, true);
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

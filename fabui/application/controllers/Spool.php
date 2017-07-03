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
 
class Spool extends FAB_Controller {
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
		$data['settings'] = loadSettings();
		$data['head']     = getInstalledHeadInfo();
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
		
		$data['filamentsOptions'] = array('pla' => 'PLA', 'abs' => 'ABS', 'nylon' => 'Nylon');
		
		$data['steps'] = array(
				array(
				 'title'   => _("Choose mode"),
				 'name'    => 'choose',
				 'content' => $this->load->view( 'spool/wizard/step1', $data, true ),
				 'active'  => true
			    ),
				array(
				 'title'   => _("Filament"),
				 'name'    => 'filament',
				 'content' => $this->load->view( 'spool/wizard/step2', $data, true )
			    ),
				array(
				 'title'   => _("Get ready"),
				 'name'    => 'get_ready',
				 'content' => $this->load->view( 'spool/wizard/step3', $data, true )
			    ),
				array(
				 'title'   => _("Finish"),
				 'name'    => 'finish',
				 'content' => $this->load->view( 'spool/wizard/step4', $data, true )
			    )
			);
		
		$headerToolbar = '
		<div class="widget-toolbar" role="menu">
			<a class="btn btn-default no-ajax" target="_blank" href="http://store.fabtotum.com/"><i class="fa fa-cart-plus"></i> <span class="hidden-xs">'._("Get more filaments").'</span> </a>
		</div>';
		
		
		$widget = $this->smart->create_widget($widgetOptions);
		$widget->id = 'main-widget-spool-management';
		
		$widget->header = array('icon' => 'fabui-spool-vert', "title" => "<h2>" . _("Spool management") . "</h2>", 'toolbar'=>$headerToolbar);
		$widget->body   = array('content' => $this->load->view('std/task_wizard', $data, true ),'class'=>'fuelux');
		
		$this->content = $widget->print_html(true);
		$this->addJSFile('/assets/js/plugin/fuelux/wizard/wizard.min.old.js'); //wizard
		$this->addJsInLine($this->load->view( 'std/task_wizard_js',   $data, true));
		$this->addJsInLine($this->load->view('spool/js', $data, true));
		$this->view();
	}
	/**
	 * 
	 */
	public function load($filament_type = 'pla', $task_running = 0)
	{
		$this->load->helpers('fabtotum_helper');
		$filament = getFilament($filament_type);
		$result = doMacro('load_spool', '', [$filament['temperatures']['extrusion'], $task_running]);
		if($result['response'] == 'success'){
			setFilament($filament_type, true);
		}
		$this->output->set_content_type('application/json')->set_output(json_encode($result));
	}
	/**
	 * 
	 */
	public function preUnload()
	{
		$this->load->helpers('fabtotum_helper');
		$result = doMacro('pre_unload_spool');
		$this->output->set_content_type('application/json')->set_output(json_encode($result));
	}
	/**
	 * 
	 */
	public function unload($filament_type = 'pla', $task_running = 0)
	{
		$this->load->helpers('fabtotum_helper');
		$filament = getFilament($filament_type);
		
		$resultPreUnLoad = doMacro('pre_unload_spool', '', [$filament['temperatures']['extrusion']]);
		
		if($resultPreUnLoad['response'] != 'success'){
			$this->output->set_content_type('application/json')->set_output(json_encode(array('start' => false, 'message' => $resultPreUnLoad['message'])));
			return;
		}
		$resultUnload = doMacro('unload_spool', '', [$task_running]);
		
		if($resultUnload['response'] == 'success'){
			setFilament($filament_type, false);
		}
		
		$this->output->set_content_type('application/json')->set_output(json_encode($resultUnload));
	}
	
}
 
?>

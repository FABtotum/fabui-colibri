<?php
/**
 * 
 * @author Krios Mane
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */
 defined('BASEPATH') OR exit('No direct script access allowed');
 
 class Jog extends FAB_Controller {
 	
 	
	/**
	 *
	 */
	function __construct()
	{
		parent::__construct();
		session_write_close(); //avoid freezing page
	}
 	
	public function index(){
			
		//load libraries, helpers, model, config
		$this->load->library('JogFactory', null, 'jogFactory');
		$this->load->library('smart');
		$this->load->helper('fabtotum_helper');
		$this->load->helper('text');
		
		$widgetOptions = array(
			'sortable' => false, 'fullscreenbutton' => true,'refreshbutton' => false,'togglebutton' => false,
			'deletebutton' => false, 'editbutton' => false, 'colorbutton' => false, 'collapsed' => false
		); 
		
		$data = array();
		$data['gcodes'] = loadGCodeInfo();
		$data['settings'] = loadSettings();
	
		
		$data['haveHead'] = isHeadInPlace();
		$data['haveBed'] = isBedInPlace();
		$data['shortcuts'] = $this->jogFactory->getShortcuts();
		$data['headPrintSupport'] = canHeadSupport("print");
		$data['headFanSupport'] = canHeadSupport("fan");
		$data['headMillSupport'] = canHeadSupport("mill");
		$data['headLaserSupport'] = canHeadSupport("laser");
		
		$info = getInstalledHeadInfo();
		if( isset($info['max_temp']) ) {
			$data['extruder_max'] = $info['max_temp'];
		}
		$data['head_min_temp'] = isset($info['min_temp']) ? $info['min_temp'] : 175;
		
		$widget = $this->smart->create_widget($widgetOptions);
		$widget->id     = 'jog-widget';
		$widget->header = array('icon' => 'fabui-jog', "title" => "<h2>"._("Jog")."</h2>");
		$widget->body   = array('content' => $this->load->view('jog/widget', $data, true ), 'class'=>'');
		
		$this->addJsInLine($this->load->view('jog/jog_js', $data, true));
		//css
		$this->addCssFile('/assets/css/jog/style.css');
		$this->addCssFile('/assets/css/std/jogcontrols.css');
		$this->addCssFile('/assets/css/std/jogtouch.css');
		$this->addJsFile('/assets/js/std/jogtouch.js');
		
		$this->addJsFile('/assets/js/plugin/knob/jquery.knob.min.js');
		$this->addJsFile('/assets/js/jquery.textcomplete.min.js');
		
		$this->content = $widget->print_html(true);
		$this->view();
	}
	
	/**
	 * @param (POST) - jogfactory params
	 * @return (json) reponse from serial
	 * exec serial command
	 */
	public function exec()
	{
		$data = $this->input->post();
		$this->config->load('fabtotum');
		
		//prepare JogFactory init args
		$method      = $data['method'];
		$methodParam = $data['value'];
		
		//load jog factory class
		$this->load->library('JogFactory', $data, 'jogFactory');
		
		if(method_exists($this->jogFactory, $method)){ //if method exists than do it
			$messageData = $this->jogFactory->$method($methodParam);
			$messageType = $this->jogFactory->getResponseType();
			$this->output->set_content_type('application/json')->set_output(json_encode(array('type' => $messageType, 'data' =>$messageData)));
		}
	}
	/**
	 * clear jog response file
	 */
	public function clear()
	{
		$this->load->helper('fabtotum_helper');
		clearJogResponse();
		$this->output->set_content_type('application/json')->set_output(json_encode(array(true)));
	}
	
	public function test()
	{
		$data = $this->input->post();
		$this->config->load('fabtotum');
		
		//prepare JogFactory init args
		$method      = 'manualDataInput';
		$methodParam = '!firmware';
		
		//load jog factory class
		$this->load->library('JogFactory', $data, 'jogFactory');
		
		if(method_exists($this->jogFactory, $method)){ //if method exists than do it
			$messageData = $this->jogFactory->$method($methodParam);
			$messageType = $this->jogFactory->getResponseType();
			$this->output->set_content_type('application/json')->set_output(json_encode(array('type' => $messageType, 'data' =>$messageData)));
		}
	}	
 }
 
?>

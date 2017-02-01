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
 	
	public function index(){
			
		//load libraries, helpers, model, config
		$this->load->library('smart');
		$this->load->helper('fabtotum_helper');
		
		$widgetOptions = array(
			'sortable' => false, 'fullscreenbutton' => true,'refreshbutton' => false,'togglebutton' => false,
			'deletebutton' => false, 'editbutton' => false, 'colorbutton' => false, 'collapsed' => false
		); 
		
		$data = array();
		$data['gcodes'] = loadGCodeInfo();
		$data['haveHead'] = isHeadInPlace();
		$data['haveBed'] = isBedInPlace();
		$data['headPrintSupport'] = canHeadSupport("print");
		$data['headFanSupport'] = canHeadSupport("fan");
		$data['headMillSupport'] = canHeadSupport("mill");
		$data['headLaserSupport'] = canHeadSupport("laser");
		
		$widget = $this->smart->create_widget($widgetOptions);
		$widget->id     = 'jog-widget';
		$widget->header = array('icon' => 'icon-fab-jog', "title" => "<h2>Jog Panel</h2>");
		$widget->body   = array('content' => $this->load->view('jog/widget', $data, true ), 'class'=>'');
		
		$this->addJsInLine($this->load->view('jog/js', $data, true));
		//css
		$this->addCssFile('/assets/css/jog/style.css');
		$this->addCssFile('/assets/css/std/jogcontrols.css');
		$this->addCssFile('/assets/css/std/jogtouch.css');
		$this->addJsFile('/assets/js/std/jogtouch.js');
		
		//~ $this->addJsFile('/assets/js/plugin/knob/jquery.knob.min.js');
		$this->addJsFile('/assets/js/std/jquery.knob.js');
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
		$this->load->helper('fabtotum_helper');
		$info = isBedinPlace();
		$this->output->set_content_type('application/json')->set_output(json_encode($info));
	}	
 }
 
?>

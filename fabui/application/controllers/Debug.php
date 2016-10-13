<?php
/**
 * 
 * @author Krios Mane
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */
 defined('BASEPATH') OR exit('No direct script access allowed');
 
 class Debug extends FAB_Controller {
 	
	public function index()
	{
		//load libraries, helpers, model, config
		$this->load->library('smart');
		
		$widgetOptions = array(
				'sortable' => false, 'fullscreenbutton' => true,'refreshbutton' => false,'togglebutton' => false,
				'deletebutton' => false, 'editbutton' => false, 'colorbutton' => false, 'collapsed' => false
		);
		
		$headerToolbar = '
			<ul class="nav nav-tabs pull-right">
				<li class="active"><a data-toggle="tab" href="#monitor-tab"> task_monitor.json</a></li>
				<li><a data-toggle="tab" href="#temperatures-tab"> temperatures.json</a></li>
				<li><a data-toggle="tab" href="#notify-tab"> notify.json</a></li>
				<li><a data-toggle="tab" href="#trace-tab"> trace</a></li>
			</ul>';
		$data = array();
		$widget = $this->smart->create_widget($widgetOptions);
		$widget->id     = 'debug-widget';
		$widget->header = array('icon' => 'fa-bug', "title" => "<h2>Debug panel</h2>", 'toolbar'=>$headerToolbar);
		$widget->body   = array('content' => $this->load->view('debug/index', null, true ), 'class'=>'');
		$this->content = $widget->print_html(true);
		
		$this->addJSFile('/assets/js/plugin/jsonview/jquery.jsonview.min.js'); //datatable */
		$this->addCssFile('/assets/js/plugin/jsonview/jquery.jsonview.min.css'); //datatable */
		$this->addJsInLine($this->load->view('debug/js', null, true));
		$this->addCSSInLine('<style>hr{margin-top:0px;margin-bottom:0px;}#trace{overflow:auto; height:500px;}</style>'); 
		
		$this->debugLayout();
	}
	
	public function test()
	{
		$this->load->helpers('fabtotum_helper');
		$homAllResult = doMacro('start_additive');
		var_dump($homAllResult);
	}
	
 }
 
?>

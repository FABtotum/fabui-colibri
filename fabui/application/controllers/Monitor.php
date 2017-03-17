<?php
/**
 * 
 * @author Krios Mane
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */
 defined('BASEPATH') OR exit('No direct script access allowed');
 
 class Monitor extends FAB_Controller {
 	
	public function index()
	{
		//load libraries, helpers, model, config
		$this->load->library('smart');
		
		$data = array();
		
		$data['units'] = array('169.254.1.2', '169.254.1.3', '169.254.1.4', '169.254.1.5', '169.254.1.6');
		
		$headerToolbar = '<ul class="nav nav-tabs pull-right"><li class="active"><a data-toggle="tab" href="#dashboard-tab"><i class="fa fa-dashboard"></i> Control</a></li>';
		$count = 1;
		foreach($data['units'] as $unit){
			$headerToolbar .= '<li><a id="unit-'.$count.'-tab-link" class="unit-link" data-toggle="tab" href="#unit-'.$count.'"> <i class="fa fa-ban"></i> Core '.$count.' </a></li>';
			$count++;
		}
		$headerToolbar .= '</ul>';
		
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
		
		$widget = $this->smart->create_widget($widgetOptions);
		$widget->id     = 'debug-widget';
		$widget->header = array('icon' => 'fa-sitemap', "title" => "<h2></h2>", 'toolbar'=>$headerToolbar);
		$widget->body   = array('content' => $this->load->view('monitor/index', $data, true ), 'class'=>'no-padding');
		$this->content = $widget->print_html(true);
		$this->addJsInLine($this->load->view('monitor/js', $data, true));
		$this->addCSSInLine("<style>iframe {border:0px !important;}</style>");
		$this->addJSFile('/assets/js/plugin/jsonview/jquery.jsonview.min.js'); //datatable */
		$this->addCssFile('/assets/js/plugin/jsonview/jquery.jsonview.min.css'); //datatable */
		$this->addCssInLine($this->load->view('monitor/css', $data, true));
		
		$this->debugLayout();
	}
	
 }
 
?>

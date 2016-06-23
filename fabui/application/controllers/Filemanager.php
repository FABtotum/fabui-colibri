<?php
/**
 * 
 * @author Krios Mane
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */
 defined('BASEPATH') OR exit('No direct script access allowed');
 
 class Filemanager extends FAB_Controller {
 	
	public function index()
	{
		//load libraries, helpers, model, config
		$this->load->library('smart');
		
		//main page widget
		$widgetOptions = array(
			'sortable' => false, 'fullscreenbutton' => true,'refreshbutton' => false,'togglebutton' => false,
			'deletebutton' => false, 'editbutton' => false, 'colorbutton' => false, 'collapsed' => false
		);
		
		$headerToolbar = '<div class="widget-toolbar" role="menu"><a href="'.site_url('filemanager/new-object').'"> Add new object </a></div>';
		
		$widget = $this->smart->create_widget($widgetOptions);
		$widget->id = 'file-manager-widget';
		$widget->header = array('icon' => 'fa-folder-open', "title" => "<h2>File Manager</h2>", 'toolbar'=>$headerToolbar);
		$widget->body   = array('content' => $this->load->view('filemanager/index/widget', '', true ), 'class'=>'no-padding');
		$this->content  = $widget->print_html(true);
		
		//add needed scripts
		$this->addJSFile('/assets/js/plugin/datatables/jquery.dataTables.min.js'); //datatable
		$this->addJSFile('/assets/js/plugin/datatables/dataTables.colVis.min.js'); //datatable
		$this->addJSFile('/assets/js/plugin/datatables/dataTables.tableTools.min.js'); //datatable
		$this->addJSFile('/assets/js/plugin/datatables/dataTables.bootstrap.min.js'); //datatable
		$this->addJSFile('/assets/js/plugin/datatable-responsive/datatables.responsive.min.js'); //datatable */
		$this->addJsInLine($this->load->view('filemanager/index/js','', true));
		
		$this->view();
	}
	
	/**
	 * add new object and files page
	 */
	public function newObject()
	{
		//TODO
		//load libraries, helpers, model, config
		$this->load->library('smart');
		
		$widgetOptions = array(
			'sortable' => false, 'fullscreenbutton' => true,'refreshbutton' => false,'togglebutton' => false,
			'deletebutton' => false, 'editbutton' => false, 'colorbutton' => false, 'collapsed' => false
		);
		
		$widgeFooterButtons = $this->smart->create_button('Save', 'primary')->attr(array('id' => 'save'))->attr('data-action', 'exec')->icon('fa-save')->print_html(true);
		
		$widget = $this->smart->create_widget($widgetOptions);
		$widget->id = 'file-manager-add-object-widget';
		$widget->header = array('icon' => 'fa-folder-open', "title" => "<h2>Add new object</h2>", 'toolbar'=>'');
		$widget->body   = array('content' => $this->load->view('filemanager/add/widget', '', true ), 'class'=>'', 'footer'=>$widgeFooterButtons);
		$this->content  = $widget->print_html(true);
		
		
		//add needed scripts
		$this->addJSFile('/assets/js/plugin/dropzone/dropzone.min.js'); //dropzpone
		$this->addJSFile('/assets/js/plugin/jquery-validate/jquery.validate.min.js'); //validator
		$this->addJsInLine($this->load->view('filemanager/add/js','', true));
		
		$this->view();
	}
	
	/**
	 * 
	 */
	public function saveObject()
	{
		//TODO
		print_r($this->input->post());
	}
	
	/**
	 * 
	 */
	public function uploadFile()
	{
		//TODO
	}


			
 }
 
?>
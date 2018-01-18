<?php
/**
 * 
 * @author Krios Mane
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */
 defined('BASEPATH') OR exit('No direct script access allowed');
 
 class Backup extends FAB_Controller {
 	
	public function index(){
	    
	    //load librarire, helpers, config
	    $this->load->library('smart');
	    $this->config->load('fabtotum');
	    $this->load->helper(array('fabtotum_helper', 'update_helper', 'os_helper', 'date_helper'));
	    
	    $widgetOptions = array(
	        'sortable'     => false, 'fullscreenbutton' => true,  'refreshbutton' => false, 'togglebutton' => false,
	        'deletebutton' => false, 'editbutton'       => false, 'colorbutton'   => false, 'collapsed'    => false
	    );
	    
	    $widget         = $this->smart->create_widget($widgetOptions);
	    $widget->id     = 'main-widget-systeminfo';
	    $widget->header = array('icon' => 'icon-electronics-089', "title" => "<h2>"._("Backup or restore your files")."</h2>");
	    $widget->body   = array('content' => $this->load->view('backup/widget', null, true ), 'class'=>'');
	    
	    $this->addJsInLine($this->load->view('backup/js', null, true));
	    
	    $this->content  = $widget->print_html(true);
	    $this->view();
	    
	}
	
	/**
	 * 
	 */
	public function doBackup()
	{
	    $data = $this->input->post();
	}
 }
?>

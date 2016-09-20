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

	public function index($type = 'load')
	{
		$this->load->library('smart');
		$this->load->helper('form');
		$this->config->load('fabtotum');
        
		//main page widget
		$widgetOptions = array(
			'sortable' => false, 'fullscreenbutton' => true,'refreshbutton' => false,'togglebutton' => false,
			'deletebutton' => false, 'editbutton' => false, 'colorbutton' => false, 'collapsed' => false
		);

		$data = array();

		$headerToolbar = ''; //'<div class="alert alert-info animated fadeIn"><h4 class="text-center"><i class="fa fa-shopping-cart"></i> <a target="_blank" href="https://store.fabtotum.com/eu/store/filaments.html?from=fabui">Get new filaments!</a></h4></div>';
		$widgeFooterButtons = '';
		//$this->smart->create_button('Save', 'primary')->attr(array('id' => 'save'))->attr('data-action', 'exec')->icon('fa-save')->print_html(true);

		$widget         = $this->smart->create_widget($widgetOptions);
		$widget->id     = 'maintenance-spool-widget';
		$widget->header = array('icon' => 'fa-cog', "title" => "<h2>Spool</h2>", 'toolbar'=>$headerToolbar);
		$widget->body   = array('content' => $this->load->view('spool/main_widget', $data, true ), 'class'=>'no-padding', 'footer'=>$widgeFooterButtons);
		
		$this->addJsInLine($this->load->view('spool/js', $data, true));
		$this->content = $widget->print_html(true);
		$this->view();
	}

	public function load()
	{
		
	}

	public function preUnload()
	{
		$this->load->helpers('fabtotum_helper');
		
		result = doMacro('pre_unload_spool');
		
		//~ result = array(true);
		
		$this->output->set_content_type('application/json')->set_output(json_encode(result));
	}

	public function unload()
	{
		//$this->output->set_content_type('application/json')->set_output(json_encode( $head_info ));
	}
}
 
?>

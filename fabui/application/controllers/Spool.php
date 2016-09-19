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
		$this->load->helper('fabtotum_helper');
		$this->load->helper('form');
		$this->config->load('fabtotum');
        
		//main page widget
		$widgetOptions = array(
			'sortable' => false, 'fullscreenbutton' => true,'refreshbutton' => false,'togglebutton' => false,
			'deletebutton' => false, 'editbutton' => false, 'colorbutton' => false, 'collapsed' => false
		);

		$data['myvar'] = False;

		$headerToolbar = '';
		$widgeFooterButtons = $this->smart->create_button('Save', 'primary')->attr(array('id' => 'save'))->attr('data-action', 'exec')->icon('fa-save')->print_html(true);

		$widget         = $this->smart->create_widget($widgetOptions);
		$widget->id     = 'maintenance-spool-widget';
		$widget->header = array('icon' => 'fa-cog', "title" => "<h2>Spool</h2>", 'toolbar'=>$headerToolbar);
		$widget->body   = array('content' => $this->load->view('maintenance/spool_widget', $data, true ), 'class'=>'no-padding', 'footer'=>$widgeFooterButtons);
		
		//~ $this->add JsInLine($this->load->view('maintenance/spool_js', $data, true));
		//~ //$this->addCSSInLine('<style type="text/css">.custom_settings{display:none !important;}</style>'); 
		$this->content = $widget->print_html(true);
		$this->view();
	}

}
 
?>

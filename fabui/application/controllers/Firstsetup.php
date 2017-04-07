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
 
class FirstSetup extends FAB_Controller {

    //~ public function index()
    //~ {
		//~ $this->view();
	//~ }
	
    public function index()
    {
		//load libraries, helpers, model
		$this->load->library('smart');
		$this->load->helper('form');

		$widgetOptions = array(
			'sortable'     => false, 'fullscreenbutton' => true,  'refreshbutton' => false, 'togglebutton' => false,
			'deletebutton' => false, 'editbutton'       => false, 'colorbutton'   => false, 'collapsed'    => false
		);
		
		$data = array();
		
		$widgeFooterButtons = '';

		$widget         = $this->smart->create_widget($widgetOptions);
		$widget->id     = 'main-widget-firstsetup';
		$widget->header = array('icon' => 'icon-fab-print', "title" => "<h2>"._("First setup")."</h2>");
		$widget->body   = array('content' => $this->load->view('firstsetup/widget', $data, true ), 'class'=>'fuelux');
		
		$this->addJsInLine($this->load->view( 'firstsetup/js', $data, true));
		
		
		//~ $this->addJSFile('/assets/js/plugin/circleMenu/jQuery.circleMenu.js');
		//$this->addJSFile('/assets/js/plugin/ferroMenu/jquery.ferro.ferroMenu-1.2.3.min.js');
		//$this->addCssFile('/assets/js/plugin/ferroMenu/jquery.ferro.ferroMenu.css');
		$this->addCssFile('/assets/css/firstsetup/style.css');
		
		$this->content = $widget->print_html(true);
		$this->view();
    }

}
 
?>

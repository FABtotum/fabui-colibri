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

    public function index()
    {
    	/*
		//load libraries, helpers, model
		$this->load->library('smart');
		$this->load->helper('form');

		//data
		$data = array();
		$data['step1']  = $this->load->view('firstsetup/wizard/step1', $data, true );
		$data['step2']  = $this->load->view('firstsetup/wizard/step2', $data, true );
		$data['step3']  = $this->load->view('firstsetup/wizard/step3', $data, true );
		$data['step4']  = $this->load->view('firstsetup/wizard/step4', $data, true );
		$data['step5']  = $this->load->view('firstsetup/wizard/step5', $data, true );
		$data['step6']  = $this->load->view('firstsetup/wizard/step6', $data, true );
		$data['wizard']  = $this->load->view('firstsetup/wizard/main', $data, true );

		//main page widget
		$widgetOptions = array(
			'sortable'     => false, 'fullscreenbutton' => true,  'refreshbutton' => false, 'togglebutton' => false,
			'deletebutton' => false, 'editbutton'       => false, 'colorbutton'   => false, 'collapsed'    => false
		);
		*/
		$this->view();
    }

	public function new_index()
	{
		$this->load->library('smart');
		$this->load->helper('form');
		$this->load->helper('fabtotum_helper');
		
		$data = array();
		
		$data['head_in_place'] = isHeadInPlace();
		$data['head_info'] = getInstalledHeadInfo();
		
		$data['steps'] = array(
				array(
				 'title'   => _("Head"),
				 'name'    => 'head',
				 'content' => $this->load->view( 'firstsetup/wizard/install_head', $data, true ),
				 'active'  => true
			    ),
				array(
				 'title'   => _("Bed"),
				 'name'    => 'bed',
				 'content' => $this->load->view( 'firstsetup/wizard/empty', $data, true )
			    ),
				array(
				 'title'   => _("Nozzle"),
				 'name'    => 'nozzle',
				 'content' => $this->load->view( 'firstsetup/wizard/empty', $data, true )
			    ),
				array(
				 'title'   => _("Spool"),
				 'name'    => 'spool',
				 'content' => $this->load->view( 'firstsetup/wizard/empty', $data, true )
			    ),
				array(
				 'title'   => _("Feeder"),
				 'name'    => 'feeder',
				 'content' => $this->load->view( 'firstsetup/wizard/empty', $data, true )
			    ),
				array(
				 'title'   => _("Test"),
				 'name'    => 'test',
				 'content' => $this->load->view( 'firstsetup/wizard/empty', $data, true )
			    ),
				array(
				 'title'   => _("Finish"),
				 'name'    => 'finish',
				 'content' => $this->load->view( 'firstsetup/wizard/empty', $data, true )
			    ),
			);
		
		$widgetOptions = array(
			'sortable'     => false, 'fullscreenbutton' => true,  'refreshbutton' => false, 'togglebutton' => false,
			'deletebutton' => false, 'editbutton'       => false, 'colorbutton'   => false, 'collapsed'    => false
		);
		
		$widgeFooterButtons = '';

		$widget         = $this->smart->create_widget($widgetOptions);
		$widget->id     = 'main-widget-firstsetup';
		$widget->header = array('icon' => 'icon-fab-print', "title" => "<h2>"._("First setup")."</h2>");
		$widget->body   = array('content' => $this->load->view('std/task_wizard', $data, true ), 'class'=>'fuelux', 'footer'=>$widgeFooterButtons);

		$this->addJSFile('/assets/js/plugin/fuelux/wizard/wizard.min.old.js'); //wizard
		$this->addJsInLine($this->load->view( 'std/task_wizard_js',   $data, true));
		
		$this->addJsInLine($this->load->view( 'firstsetup/js', $data, true));


		$this->content = $widget->print_html(true);
		$this->view();
	}
}
 
?>

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

}
 
?>

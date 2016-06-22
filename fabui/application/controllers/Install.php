<?php
/**
 * 
 * @author Krios Mane
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */
 defined('BASEPATH') OR exit('No direct script access allowed');
 
 class Install extends FAB_Controller {
 	
	public function index()
	{
		//TODO
		$this->content = $this->load->view('install/wizard', null, true );
		$this->addJsInLine($this->load->view('install/js', '', true));
		//add js file
		$this->addJSFile('/assets/js/plugin/bootstrap-wizard/jquery.bootstrap.wizard.min.js'); //wizard
		$this->addCSSInLine('<style> #main {margin-left:0px !important;}</style>');
		//show page
		$this->installLayout();
	}
			
 }
 
?>
<?php
/**
 * 
 * @author Krios Mane
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */
 defined('BASEPATH') OR exit('No direct script access allowed');
 
 class Lock extends FAB_Controller {
 	
	public function index()
	{	
	    $this->addCssFile('/assets/css/lockscreen.min.css');
	    $this->addCSSInLine("<style>#main{margin-left:0px !important;} .page-footer{padding-left:10px !important;}</style>");
		$this->content = $this->load->view('lock/index', null, true );
		$this->lockLayout();
	}
 }
 
?>

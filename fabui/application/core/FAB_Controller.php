<?php
/**
 * 
 * @author Krios Mane
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */
 if (!defined('BASEPATH')) exit('No direct script access allowed');
 
 class FAB_Controller extends CI_Controller {
 	
	protected $layoutDefaultFolder = 'layout/default';
	protected $layoutLogin         = 'layout/login';
	protected $layoutAjax          = 'layout/ajax';
	protected $template            = array();
	protected $content             = ''; //
	protected $js                  = array();  //js scripts
	protected $jsInLine            = ''; //inline javascript code 
	protected $cssInline           = ''; //inline css style
	protected $css                 = array(); //css files inclusion
	protected $menu                = array();
	protected $isAjax              = false;
	
	function __construct()
    {
        parent::__construct(); //CI father CLASS
		
		if( ! $this->input->is_cli_request()) { // if is not a command line call
			$this->isAjax = $this->input->is_ajax_request();
			//check if user is logged
			//if not redirect to login page
			//load needed libraries, helpers
			$this->load->library(array('session', 'parser'));
			$this->load->helper(array('url', 'layout'));
			$this->load->database();
			if($this->session->loggedIn == false && get_class($this) != 'Login'){
				$this->load->helper('url');	
				redirect('login/index');	
			}
			if(!$this->isAjax){ //for ajax request no need to load menu
				//load menu
				$this->config->load('menu');
				$this->menu = $this->config->item('menu');
			}
		}
    }
	
	/*
	 * Default view
	 */ 
 	public function view()
 	{
 		if($this->isAjax){ //go to ajax view 
 			$this->ajaxView();
			return;
 		}
		
		$data['jsScripts'] = jScriptsInclusion($this->js);
		$data['jsInLine']  = $this->jsInLine;
		$data['cssInLine'] = $this->cssInline;
		$this->template['head']    = $this->load->view($this->layoutDefaultFolder.'/head', $data, true);
		$this->template['top']     = $this->load->view($this->layoutDefaultFolder.'/top', $data, true);
		$this->template['sidebar'] = $this->load->view($this->layoutDefaultFolder.'/sidebar', array('menu'=> buildMenu($this->menu)), true);
		$this->template['ribbon']  = $this->load->view($this->layoutDefaultFolder.'/ribbon', $data, true);
		$this->template['footer']  = $this->load->view($this->layoutDefaultFolder.'/footer', $data, true);
		$this->template['scripts'] = $this->load->view($this->layoutDefaultFolder.'/scripts', $data, true);
		$this->template['content'] = $this->content;
		
		$this->parser->parse($this->layoutDefaultFolder.'/structure', $this->template);
 	}
	
	/**
	 * Ajax view (just load only content and javascripts files)
	 */
	public function ajaxView()
	{
		
		$data['jsScripts'] = ajaxJScriptsInclusion($this->js);
		$data['jsInLine']  = ajaxJSInline($this->jsInLine, count($this->js) == 0);
		$this->template['cssInLine'] = $this->cssInline;
		$this->template['content'] = $this->content;
		$this->template['scripts'] = $this->load->view($this->layoutAjax.'/scripts', $data, true);
		$this->parser->parse($this->layoutAjax.'/structure', $this->template);
		
	}
	
	/**
	 * Login layout page view
	 */
	public function loginLayout($mode = 'login'){
		
		$data = array();
		$data['jsScripts'] = jScriptsInclusion($this->js);
		$data['jsInLine'] = $this->jsInLine;
		$data['mode'] = $mode;
		
		$this->template['head']    = $this->load-> view($this->layoutLogin.'/head', $data, true);
		$this->template['top']     = $this->load-> view($this->layoutLogin.'/top', $data, true);
		$this->template['scripts'] = $this->load-> view($this->layoutLogin.'/scripts', $data, true);
		$this->template['content'] = $this->content;
		$this->parser->parse($this->layoutLogin.'/structure', $this->template);
	}
	
	/*
	 * add js script file
	 */
	public function addJSFile($js)
	{
		$this->js[] = $js;
	}
	
	/*
	 * add inline javascript
	 */
	public function addJsInLine($js)
	{
		$this->jsInLine .= $js;
	}
	
	/**
	 * add inlineCSS
	 */
	public function addCSSInLine($css)
	{
		$this->cssInline .= $css;
	}
 }
 
?>
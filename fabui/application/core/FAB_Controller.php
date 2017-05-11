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
	protected $layoutInstall       = 'layout/install';
	protected $layoutRestore       = 'layout/restore';
	protected $layoutDebug         = 'layout/debug';
	protected $layoutLock          = 'layout/lock';
	protected $template            = array();
	protected $content             = ''; //
	protected $js                  = array();  //js scripts
	protected $jsInLine            = ''; //inline javascript code
	protected $jsInLineTop         = ''; //inline javascript to add on top, just for variable declarations
	protected $cssInline           = ''; //inline css style
	protected $css                 = array(); //css files inclusion
	protected $menu                = array();
	protected $isAjax              = false;
	protected $noSessionNeeded     = array('Login', 'Install', 'Control');
	
	public $js_mandatory  = array();
	public $css_mandatory = array();
	
	function __construct()
    {
		/**
		 * Enable CORS (Cross Origin Resource Sharing) (for example: redirect to a new eht ip address)
		 * @todo : improve cors management
		 */
        parent::__construct(); //CI father CLASS
        
		if( ! $this->input->is_cli_request()) { // if is not a command line call
			$this->isAjax = $this->input->is_ajax_request();
			//check if user is logged
			//if not redirect to login page
			//load needed libraries, helpers
			$this->load->library(array('session', 'parser', 'database'));
			$this->load->helper(array('url', 'layout', 'language', 'update', 'cookie'));
			$this->load->database();
			
			/**
			 * get and define FABUI version
			 */
			$fabuiBundle = getLocalBundle('fabui');
			define('FABUI_VERSION', $fabuiBundle['version']);
			
			if($this->session->loggedIn == false && !in_array(get_class($this), $this->noSessionNeeded)){
				if($this->isAjax){
					$this->output->set_status_header(403, 'Invalid session');
					exit();
				}else{
					if(!verify_keep_me_logged_in_cookie())
						redirect('login/out');
				}		
			}
			loadTranslation();
			if(!$this->isAjax){ //for ajax request no need to load menu
				//load menu
				$this->load->helper('utility_helper');
				$this->config->load('menu');
				$this->config->load('layout');
				
				$this->menu = $this->config->item('menu');
				
				$javascript = $this->config->item('javascript');
				$css        = $this->config->item('css');
				
				$this->js_mandatory = $javascript['mandatory'];
				$this->css_mandatory = $css['mandatory'];
				
				unset($javascript);
				unset($css);
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
		
 		$this->load->helper('fabtotum_helper');
 		
		$data['jsScripts']      = jScriptsInclusion($this->js);
		$data['cssFiles']       = cssFilesInclusion($this->css);
		$data['jsInLine']       = $this->jsInLine;
		$data['jsInlineTop']    = $this->jsInLineTop;
		$data['cssInLine']      = $this->cssInline;
		$data['translations']   = $this->load->view('layout/translations_js', null, true);
		$data['tours']          = $this->load->view('layout/tours_js', array('available_tours' => getTours()), true);
		$data['ga_property_id'] = $this->config->config['ga_property_id'];
		$data['heads']          = loadHeads();
		
		$this->template['head']    = $this->load->view($this->layoutDefaultFolder.'/head',    $data, true);
		$this->template['top']     = $this->load->view($this->layoutDefaultFolder.'/top',     $data, true);
		$this->template['sidebar'] = $this->load->view($this->layoutDefaultFolder.'/sidebar', array('menu'=> buildMenu($this->menu)), true);
		$this->template['ribbon']  = $this->load->view($this->layoutDefaultFolder.'/ribbon',  $data, true);
		$this->template['footer']  = $this->load->view($this->layoutDefaultFolder.'/footer',  $data, true);
		$this->template['ga']      = $this->load->view('layout/ga',                           $data, true);
		$this->template['scripts'] = $this->load->view($this->layoutDefaultFolder.'/scripts', $data, true);
		$this->template['content'] = $this->content;
		
		$this->parser->parse($this->layoutDefaultFolder.'/structure', $this->template);
 	}
	
	/**
	 * Ajax view (just load only content and javascripts files)
	 */
	public function ajaxView()
	{
		
		$data['jsScripts']   = ajaxJScriptsInclusion($this->js);
		$data['jsInLine']    = ajaxJSInline($this->jsInLine, count($this->js) == 0);
		$data['jsInlineTop'] = $this->jsInLineTop;
		$this->template['topScripts']  = $this->load->view($this->layoutAjax.'/top_scripts', $data, true);
		$this->template['cssFiles']    = cssFilesInclusion($this->css, true); 
		$this->template['cssInLine']   = $this->cssInline;
		$this->template['content']     = $this->content;
		$this->template['scripts']     = $this->load->view($this->layoutAjax.'/scripts', $data, true);
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
		$data['translations'] = $this->load->view('layout/translations_js', null, true);
		
		$this->template['head']    = $this->load-> view($this->layoutLogin.'/head', $data, true);
		$this->template['top']     = $this->load-> view($this->layoutLogin.'/top', $data, true);
		$this->template['scripts'] = $this->load-> view($this->layoutLogin.'/scripts', $data, true);
		$this->template['content'] = $this->content;
		$this->parser->parse($this->layoutLogin.'/structure', $this->template);
	}
	
	/**
	 * Install layout page view
	 */
	public function installLayout()
	{
		$data = array();
		$data['jsScripts'] = jScriptsInclusion($this->js);
		$data['jsInLine']  = $this->jsInLine;
		$data['cssInLine'] = $this->cssInline;
		$data['cssFiles']  = cssFilesInclusion($this->css);
		$data['translations'] = $this->load->view('layout/translations_js', null, true);
		
		$this->template['head']    = $this->load-> view($this->layoutInstall.'/head',    $data, true);
		//$this->template['top']     = $this->load-> view($this->layoutInstall.'/top',     $data, true);
		$this->template['scripts'] = $this->load-> view($this->layoutInstall.'/scripts', $data, true);
		$this->template['content'] = $this->content;
		$this->parser->parse($this->layoutInstall.'/structure', $this->template);
	}
	
	/**
	 * Restore layout page view
	 */
	public function restoreLayout()
	{
		$data = array();
		$data['jsScripts'] = jScriptsInclusion($this->js);
		$data['jsInLine'] = $this->jsInLine;
		$data['cssInLine'] = $this->cssInline;
		
		$this->template['head']    = $this->load-> view($this->layoutRestore.'/head',    $data, true);
		//$this->template['top']     = $this->load-> view($this->layoutRestore.'/top',     $data, true);
		$this->template['scripts'] = $this->load-> view($this->layoutRestore.'/scripts', $data, true);
		$this->template['content'] = $this->content;
		$this->parser->parse($this->layoutRestore.'/structure', $this->template);
	}
	
	/**
	 * 
	 */
	public function debugLayout()
	{
		$data = array();
		$data['jsScripts'] = jScriptsInclusion($this->js);
		$data['cssFiles']  = cssFilesInclusion($this->css);
		$data['jsInLine']  = $this->jsInLine;
		$data['cssInLine'] = $this->cssInline;
		$this->template['head']     = $this->load-> view($this->layoutDebug.'/head', $data, true);
		$this->template['top']     = $this->load-> view($this->layoutDebug.'/top', $data, true);
		$this->template['scripts'] = $this->load-> view($this->layoutDebug.'/scripts', $data, true);
		$this->template['footer']  = $this->load->view($this->layoutDebug.'/footer', $data, true);
		$this->template['content'] = $this->content;
		$this->parser->parse($this->layoutDebug.'/structure', $this->template);
	}
	
	/**
	 * 
	 */
	public function lockLayout()
	{
		$data = array();
		$data['jsScripts'] = jScriptsInclusion($this->js);
		$data['cssFiles']  = cssFilesInclusion($this->css);
		$data['jsInLine']  = $this->jsInLine;
		$data['cssInLine'] = $this->cssInline;
		$this->template['head'] = $this->load-> view($this->layoutLock.'/head', $data, true);
		$this->template['top']     = $this->load-> view($this->layoutLock.'/top', $data, true);
		$this->template['scripts'] = $this->load-> view($this->layoutLock.'/scripts', $data, true);
		$this->template['footer']  = $this->load->view($this->layoutLock.'/footer', $data, true);
		$this->template['content'] = $this->content;
		$this->parser->parse($this->layoutLock.'/structure', $this->template);
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
	public function addJsInLine($js, $top = false)
	{
		if(!$top)
			$this->jsInLine .= $js;
		else
			$this->jsInLineTop .= $js; //just for variable declarations
	}
	
	/*
	 * add css file
	 */
	public function addCssFile($css)
	{
		$this->css[] = $css;
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

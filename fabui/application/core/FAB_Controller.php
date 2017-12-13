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
	protected $layoutPopup         = 'layout/popup';
	protected $layoutEmail         = 'layout/email';
	protected $template            = array();
	protected $content             = ''; //
	protected $is_ajax_request     = false;
	protected $noSessionNeeded     = array('Login', 'Install', 'Control', 'Myfabtotum');
	/*************************************
	 * 
	 ************************************/
	public $js            = array();  //js scripts
	public $jsInLine      = ''; //inline javascript code
	public $jsInLineTop   = ''; //inline javascript to add on top, just for variable declarations
	public $cssInline     = ''; //inline css style
	public $css           = array(); //css files inclusion
	public $menu          = array();
	public $fab_app_init  = true;
	public $js_mandatory  = array();
	public $css_mandatory = array();
	public $meta_tags = array(
		'description' => 'FABtotum User Web Interface',
		'author'      => 'FABtotum Development Team',
		'viewport'    => 'width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no',
		'robots'      => 'noindex,nofollow',
		'theme-color' => '#2196F3',
		'apple-mobile-web-app-capable' => "yes",
		'apple-mobile-web-app-status-bar-style' => "black"
	);
	/***
	 * 
	 * 
	 */
	function __construct()
    {
		/**
		 * Enable CORS (Cross Origin Resource Sharing) (for example: redirect to a new eht ip address)
		 * @todo : improve cors management
		 */
        parent::__construct(); //CI father CLASS
        
        /**
         *  BEHAVIOUR FOR STANDARD HTTP REQUEST (GET/POST) 
         */
		if( ! $this->input->is_cli_request()) {
			
			$this->is_ajax_request = $this->input->is_ajax_request();
			$this->load->library(array('session', 'parser'));
			$this->load->helper(array('url', 'layout', 'language', 'update', 'cookie', 'os_helper'));
			$this->load->database();
			
			/**
			 * get and define FABUI version
			 */
			$fabuiBundle = getLocalBundle('fabui');
			define('FABUI_VERSION', $fabuiBundle['version']);
			
			/**
			 * check if user is logged
			 * if not logged and is an ajax call set output header to 403 (Forbidden)
			 * if not logged but fabkml exists and is valid init session
			 * then redirec to login
			 * 
			 */
			if($this->session->loggedIn == false && !in_array(get_class($this), $this->noSessionNeeded)){
				if($this->is_ajax_request){
					$this->output->set_status_header(403, 'Invalid session');
					exit();
				}else{
					if(!verify_keep_me_logged_in_cookie())
						redirect('login/out');
				}		
			}
			
			//load translation
			loadTranslation();
			
			if(!$this->is_ajax_request){ //for ajax request no need to load menu
				//load menu
				$this->load->helper('utility_helper');
				$this->config->load('menu');
				$this->config->load('layout');
				
				$this->menu = $this->config->item('menu');
				
				$javascript = $this->config->item('javascript');
				$css        = $this->config->item('css');
				
				$this->js_mandatory  = $javascript['mandatory'];
				$this->css_mandatory = $css['mandatory'];
				
				unset($javascript);
				unset($css);
			}
		}
    }
	
	/**
	 * Default view
	 */ 
 	public function view()
 	{
 		if($this->is_ajax_request){ //go to ajax view 
 			$this->ajaxView();
			return;
 		}
		
 		$this->load->helper('fabtotum_helper');
 		
		$data['translations']   = $this->load->view('layout/translations_js', null, true);
		$data['tours']          = $this->load->view('layout/tours_js', array('available_tours' => getTours()), true);
		$data['ga_property_id'] = $this->config->config['ga_property_id'];
		$data['heads']          = loadHeads();
		$data['lang']           = getCurrentLanguage();
		
		
		$this->template['head']    = $this->load->view($this->layoutDefaultFolder.'/head',    $data, true);
		$this->template['top']     = $this->load->view($this->layoutDefaultFolder.'/top',     $data, true);
		$this->template['sidebar'] = $this->load->view($this->layoutDefaultFolder.'/sidebar', null,  true);
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
		$data = array();
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
		$this->fab_app_init = false;
		
		$this->addJsInLine('<script type="text/javascript">loginLogOut();</script>');
		$data['mode'] = $mode;
		$data['translations'] = $this->load->view('layout/translations_js', null, true);
		
		$this->template['head']    = $this->load-> view($this->layoutDefaultFolder.'/head', $data, true);
		$this->template['top']     = $this->load-> view($this->layoutLogin.'/top', $data, true);
		$this->template['scripts'] = $this->load-> view($this->layoutDefaultFolder.'/scripts', $data, true);
		$this->template['content'] = $this->content;
		$this->parser->parse($this->layoutLogin.'/structure', $this->template);
	}
	
	/**
	 * Install layout page view
	 */
	public function installLayout()
	{
		$data = array();
		$this->fab_app_init        = false;
		$data['translations']      = $this->load->view('layout/translations_js', null, true);
		$this->template['head']    = $this->load-> view($this->layoutDefaultFolder.'/head',    $data, true);
		$this->template['scripts'] = $this->load-> view($this->layoutDefaultFolder.'/scripts', $data, true);
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
		$this->template['scripts'] = $this->load-> view($this->layoutRestore.'/scripts', $data, true);
		$this->template['content'] = $this->content;
		$this->parser->parse($this->layoutRestore.'/structure', $this->template);
	}
	/***
	 * 
	 */
	public function debugLayout()
	{
		$this->load->helper('fabtotum_helper');
		$data['heads']             = loadHeads();
		$data['translations']      = $this->load->view('layout/translations_js', null, true);
		$this->template['head']    = $this->load->view($this->layoutDefaultFolder.'/head',    $data, true);
		$this->template['top']     = $this->load->view($this->layoutDefaultFolder.'/top',     $data, true);
		$this->template['scripts'] = $this->load->view($this->layoutDefaultFolder.'/scripts', $data, true);
		$this->template['footer']  = $this->load->view($this->layoutDefaultFolder.'/footer',  $data, true);
		$this->template['content'] = $this->content;
		$this->parser->parse($this->layoutDebug.'/structure', $this->template);
	}
	/***
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
	
	/**
	 * 
	 */
	public function popupLayout()
	{
		$data = array();
		$this->fab_app_init = false;
		
		$data['ga_property_id'] = $this->config->config['ga_property_id'];
		$data['translations']   = $this->load->view('layout/translations_js', null, true);
		
		$this->template['head']    = $this->load->view($this->layoutDefaultFolder.'/head',    $data, true);
		$this->template['scripts'] = $this->load->view($this->layoutDefaultFolder.'/scripts', $data, true);
		$this->template['ga']      = $this->load->view('layout/ga',                           $data, true);
		$this->template['content'] = $this->content;
		$this->parser->parse($this->layoutPopup.'/structure', $this->template);
	}
	
	/**
	 * 
	 */
	public function layoutEmail($return=false)
	{
	    $data['cssInLine'] = $this->cssInline;
	    
	    $this->template['head'] = $this->load->view($this->layoutEmail.'/head', $data, true);
	    $this->template['content'] = $this->content;
	    
	    return $this->parser->parse($this->layoutEmail.'/structure', $this->template, $return);
	}
	
	/***
	 * add js script file to include
	 * @param string $js
	 */
	public function addJSFile($js)
	{
		$this->js[] = $js;
	}
	
	/***
	 * add inline javascript code
	 * @param string $js
	 * @param boolean $top = false (default false, include at the bottom, else in the top)
	 */
	public function addJsInLine($js, $top = false)
	{
		if(!$top)
			$this->jsInLine .= $js;
		else
			$this->jsInLineTop .= $js; //just for variable declarations
	}
	/***
	 * add css file to inlcude
	 * @param string $css
	 */
	public function addCssFile($css)
	{
		$this->css[] = $css;
	}
	/**
	 * add inline CSS style
	 * @param $string $css 
	 */
	public function addCSSInLine($css)
	{
		$this->cssInline .= $css;
	}
	/**
	 * set html meta tag
	 * @param string $name meta tag name
	 * @param string $value meta taga value
	 */
	public function setMetaTag($name, $value)
	{
		$this->meta_tags[$name] = $value;
	}
	
 }
 
?>

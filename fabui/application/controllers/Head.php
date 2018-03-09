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
 
class Head extends FAB_Controller {
	
    const PRISM_ID = 8; 
    
    /**
     *
     */
    function __construct()
    {
        parent::__construct();
        session_write_close(); //avoid freezing page
    }
   
    
	/**
	 * 
	 */
	public function setHead($new_head)
	{
		$this->load->helper(array('fabtotum_helper'));
		
		
		
		
		$heads = loadHeads();
		$_data = loadSettings();
		
		$head_info = $heads[$new_head];
		$_data['hardware']['head'] = $new_head;
		
		if($head_info['fw_id'] != self::PRISM_ID){
		    setSecure();
		    //resetController();
		}
		
		
		if($head_info['fw_id'] == self::PRISM_ID){
		    doMacro('clear_errors');
		}
		
		doMacro('install_head', '', [$new_head]);
		
		if(in_array('feeder', $head_info['capabilities']))
		{
			$_data['hardware']['feeder'] = $new_head;
			doMacro('install_feeder', '', [$new_head]);
		}
		else if(in_array('4thaxis', $head_info['capabilities']))
		{
			$_data['hardware']['feeder'] = $new_head;
			doMacro('install_4thaxis', '', [$new_head]);
		}
		else
		{
			if( isFeederInHead($_data['hardware']['feeder']) )
			{
				$_data['hardware']['feeder'] = 'built_in_feeder';
				doMacro('install_feeder', '', ['built_in_feeder']);
			}
		}
		
		saveSettings($_data);
		// reset totumduino
		/*
		if($head_info['fw_id'] != self::PRISM_ID){
		  //resetController();
		}
		*/
		
		//reload myfabtotum
		reload_myfabtotum();
		
		$this->output->set_content_type('application/json')->set_output(json_encode( $head_info ));
	}
	/**
	 * 
	 */
	public function removeHead($head)
	{
		$this->load->helper('fabtotum_helper');
		$result = removeHeadInfo($head);
		$this->output->set_content_type('application/json')->set_output(json_encode( $result ));
	}
	/**
	 * 
	 */
	public function saveHead($head_filename)
	{
		$this->load->helper('fabtotum_helper');
		$info = $this->input->post();
		$result = saveHeadInfo($info, $head_filename, false);
		$this->output->set_content_type('application/json')->set_output(json_encode( $result ));
	}
	/**
	 * 
	 */
	public function factoryReset($head_filename)
	{
		$this->load->helper('fabtotum_helper');
		$result = restoreHeadFactorySettings($head_filename);
		$this->output->set_content_type('application/json')->set_output(json_encode( $result ));
	}
	/**
	 * 
	 */
	public function index(){
		$this->load->library('smart');
		$this->load->helper(array('form', 'fabtotum_helper'));
		
		$data = array();
		
		$data['heads'] = loadHeads();
		
		$data['installed_head'] = getInstalledHeadInfo();
		$data['capabilities'] = array(
			'*'      => _("All"),
			'.print' => _("3D Printing (FDM)"),
		    '.sla'   => _("3D Printing (SLA)"),
			'.mill'  => _("Milling"),
			'.laser' => _("Laser"),
			'.scan'  => _("Scan")
		);
		
		$data['working_modes_options'] = array(
		    '0' => "Hybrid",
		    '1' => "FFF",
		    '2' => "Laser",
		    '3' => "CNC",
		    '4' => "Scan",
		    '4' => "SLA"
		);
		
		/**
		 * temporary
		 */
		$data['working_modes'] = array(
		    'Hybrid' => 0,
		    'FFF'    => 1,
		    'Laser'  => 2,
		    'CNC'    => 3,
		    'Scan'   => 4,
		    'SLA'    => 4
		);
		
		
		$data['thermistors'] = array(
		    '0' => 'Fabtotum',
		    '1' => 'Standard 100k'
		);
		
		$headerToolbar = '
		<div class="widget-toolbar" role="menu">
			<a class="btn btn-success settings-action" data-action="add" href=""><i class="fa fa-plus"></i> '._("Add new head/module").' </a>
			<a class="btn btn-default no-ajax" target="_blank" href="http://store.fabtotum.com/"><i class="fa fa-cart-plus"></i> <span class="hidden-xs">'._("Get more heads & modules").'</span> </a>
		</div>';
		
		//main page widget
		$widgetOptions = array(
				'sortable'     => false, 'fullscreenbutton' => true,  'refreshbutton' => false, 'togglebutton' => false,
				'deletebutton' => false, 'editbutton'       => false, 'colorbutton'   => false, 'collapsed'    => false
		);
		
		$widget         = $this->smart->create_widget($widgetOptions);
		$widget->id     = 'main-widget-heads-modules-installation';
		$widget->class = '';
		$widget->header = array('icon' => 'fabui-head-2', "title" => "<h2>"._("Heads & Modules")."</h2>", 'toolbar'=>$headerToolbar);
		$widget->body   = array('content' => $this->load->view('head/index', $data, true ), 'class'=>'');
		
		$this->addJsInLine($this->load->view('head/js', $data, true));
		
		/**
		 * include scripts
		 */
		$this->addCssFile('/assets/css/head/style.css');
		$this->addJSFile('/assets/js/plugin/jquery-validate/jquery.validate.min.js'); //validator
		$this->addJSFile('/assets/js/plugin/inputmask/jquery.inputmask.bundle.js');
		$this->addJSFile('/assets/js/plugin/FileSaver.min.js');
		$this->addCssFile('/assets/js/plugin/OwlCarousel2-2.2.1/owl.carousel.min.css');
		$this->addCssFile('/assets/js/plugin/OwlCarousel2-2.2.1/owl.theme.default.css');
		$this->addJSFile('/assets/js/plugin/OwlCarousel2-2.2.1/owl.carousel.min.js');
		$this->addJSFile('/assets/js/plugin/OwlCarousel2-2.2.1/plugins/jquery.owl-filter.js');
		
		$this->content = $widget->print_html(true);
		$this->view();
		
	}
}
 
?>

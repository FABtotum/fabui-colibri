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
		
		
		$data['prism_disabled'] = false;
		
		$data['prism_module_instractions'] = array(
		    1 => array(
		        'image' => '/assets/img/head/prism/prism_install_1.png',
		        'title' => _('Clean the unit'),
		        'description' => _('Turn off the FAbtotum Personal fabricator and with no power connected remove any module installed (heads and build plate).<br><br> Make sure no dust or debris is in the way and clean thoroughly the inside.'),
		    ),
		    2 => array(
		        'image' => '/assets/img/head/prism/prism_install_2.png',
		        'title' => _('Prepare the mounting plate'),
		        'description' => _('The PRISM module requires a locking plate to be fixed and aligned with the FABtotum Personal Fabricator bottom plate.<br><br>
                                    Adhesion is provided by industrial-grade double side adhesive.<br>
                                    To lock it in place, clean the bottom of the FABtotum personal fabricator and make sure there is no dirt or debris.<br><br>
                                    Remove the protective plastic from the adhesive masking tape provided and prepare to position the plate'),
		    ),
		    3 => array(
		        'image' => '/assets/img/head/prism/prism_install_3.png',
		        'title' => _('Position the locking plate'),
		        'description' => _('Position the mounting plate as shown, make sure it is aligned with the front side of the bottom of the FABtotum unit.<br><br>
                                    The metal mounting plate does not interfere with the normal operations and should not be removed after using PRISM (e.g when switching back to FDM printing or Milling).'),
		    ),
		    4 => array(
		        'image' => '/assets/img/head/prism/prism_install_4.png',
		        'title' => _('Attach the power connector'),
		        'description' => _('Change the power connector with the provided one
                                    Prism takes its power from the FAbtotum personal fabricator 24V 6A DC build plate connector inside the unit itself.<br>
                                    To avoid cable swaps each time prism is used, a new cable is provided with the PRISM module.<br>
                                    This cable should not be removed when using the FABtotum in another working mode (e.g Milling, Printing) as it does not interfere with normal operations unless conductive debris is produced (conductive filaments, aluminum dust from milling).
                                    In this last case you may switch to the old configuration and always clean the unit after each job, especially in the connector area.<br>
                                    Remove the power connector (1) and connect it to the female part of the provided PRISM power cable (2). Connect the male lead of the PRISM power cable to the original connection slot (1). The DC plug of the PRISM power cable is now ready to be plugged in the PRISM module body.'),
		    ),
		    5 => array(
		        'image' => '/assets/img/head/prism/prism_install_6.png',
		        'title' => _(' Place the PRISM module Body'),
		        'description' => _('Place the prism module body on the lock plate<br>
                                    Make sure no cleareance or other foreign object is present and that all pins are aligned.<br>
                                    Notice: practice caution as the PRISM module body is equipped with magnets to lock in place.<br>
                                    Position the PRISM module body aligning it with the positioning pins on the lock plate.<br>
                                    The PRISM body will be magnetically attracted to the lock plate and stick to the unit bottom.'),
		    ),
		    6 => array(
		        'image' => '/assets/img/head/prism/prism_install_5.png',
		        'title' => _('Connect the DC power plug'),
		        'description' => _('Connect the power jack on Prism. Make sure the cable is straight and untangled.<br>
                                      You can do this when the PRISM module body is not placed in the FABtotum unit.<br>
                                    You are now ready to set up PRISM for 3D printing'),
		   )
		);
		
		
		$data['max_prism_connection_attempts'] = 5;
		
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

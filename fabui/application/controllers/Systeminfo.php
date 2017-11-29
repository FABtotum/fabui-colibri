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
 
class SystemInfo extends FAB_Controller {

	public function index()
	{
		//load librarire, helpers, config
		$this->load->library('smart');
		$this->config->load('fabtotum');
		$this->load->helper(array('fabtotum_helper', 'update_helper', 'os_helper', 'date_helper'));
		
		//
		$data = json_decode(startPyScript('systeminfo.py', '', false), true);
		
		$data['bundles']           = getLocalBundles();
		$data['serial_number']     = getSerialNumber();
		$data['unit_color']        = getUnitColor();
		$data['host_name']         = getHostName();
		$data['avahi_description'] = getAvahiServiceName();
		
		//versions macro
		$data['versions'] = array();
		$macroResponse = doMacro('version');
		if($macroResponse['response']){
			$data['versions'] = $macroResponse['reply'];
		}
		//eeprom macro
		$data['eeprom'] = array();
		$macroResponse = doMacro('read_eeprom');
		if($macroResponse['response']){
			$data['eeprom'] = $macroResponse['reply'];
		}
		
		$data['installed_head'] = getInstalledHeadInfo();
		
		$data['current_timezone'] = trim(shell_exec('cat /etc/timezone'));
		
		
		$widgetOptions = array(
				'sortable'     => false, 'fullscreenbutton' => true,  'refreshbutton' => false, 'togglebutton' => false,
				'deletebutton' => false, 'editbutton'       => false, 'colorbutton'   => false, 'collapsed'    => false
		);
		
		$headerToolbar = '<div class="widget-toolbar" role="menu">
		<a rel="tooltip" data-title="'._("Enter recovery mode").'" id="recovery-button" class="btn btn-default" href="javascript:void(0);"><i class="fa fa-heartbeat "></i> <span class="hidden-xs">'._("Recovery").'</span> </a>
		</div>';	
		
		$widget     = $this->smart->create_widget($widgetOptions);
		$widget->id = 'main-widget-systeminfo';
		$widget->header = array('icon' => 'fa-info-circle', "title" => "<h2>System Info</h2>", 'toolbar' => $headerToolbar);
		$widget->body   = array('content' => $this->load->view('systeminfo/widget', $data, true ), 'class'=>'');
		$this->content = $widget->print_html(true);
		
		$extra_css = $this->session->user['role'] != 'administrator' ? 'cursor:default;' : 'cursor:pointer;';
		
		//$this->addJsFile('/assets/js/plugin/x-editable/moment.min.js');
		//$this->addJsFile('/assets/js/plugin/x-editable/x-editable.min.js');
		$this->addJSFile('/assets/js/plugin/jquery-validate/jquery.validate.min.js');
		$this->addJSFile('/assets/js/plugin/masked-input/jquery.maskedinput.min.js');
		$this->addCSSInLine('<style> @media (min-width: 768px){ .big dt {width:300px !important;} .big dd {margin-left:310px !important;} } .edit-field{border-bottom: dashed 1px #0088cc;'.$extra_css.'}</style>');
		$this->addJsInLine($this->load->view('systeminfo/js', null, true));
		$this->view();
	}
}
 
?>

<?php
/**
 * 
 * @author Krios Mane
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */
 defined('BASEPATH') OR exit('No direct script access allowed');
 
class Settings extends FAB_Controller {
	
	/***
	 *  Settings - Hardware page
	 */
	public function hardware(){
			
		//load libraries, helpers, model, config
		$this->load->library('smart');
		$this->load->helper('fabtotum_helper');
		$this->load->helper('form');
		$this->config->load('fabtotum');
		//load settings (default or customs)
		if(!file_exists($this->config->item('default_settings'))){ // if default settings file doesn't exits, create it
			createDefaultSettings();
 		}
		if(!file_exists($this->config->item('custom_settings'))){
			copy($this->config->item('default_settings'), $this->config->item('custom_settings'));
		}
		$data['defaultSettings'] = json_decode(file_get_contents($this->config->item('default_settings')), true);
		
		if($data['defaultSettings']['settings_type'] == 'custom')
		{
			$data['defaultSettings']  = json_decode(file_get_contents($this->config->item('custom_settings')), true);
			$data['defaultSettings']['settings_type'] = 'custom';
		}
		
		$data['customSettings']  = json_decode(file_get_contents($this->config->item('custom_settings')), true);
		$data['yesNoOptions'] = array('1' => 'Yes', '0' => 'No');
		$data['customizeActionsOptions'] = array('none' => 'None', 'shutdown' => 'Shutdown');
		//main page widget
		$widgetOptions = array(
			'sortable' => false, 'fullscreenbutton' => true,'refreshbutton' => false,'togglebutton' => false,
			'deletebutton' => false, 'editbutton' => false, 'colorbutton' => false, 'collapsed' => false
		);
		$headerToolbar = '
			<ul class="nav nav-tabs pull-right">
				<li class="active"><a data-toggle="tab" href="#hardware-tab"> Hardware</a></li>
				<li><a data-toggle="tab" href="#safety-tab"> Safety</a></li>
				<li><a data-toggle="tab" href="#homing-tab"> Homing</a></li>
				<li><a data-toggle="tab" href="#customized-actions-tab"> Customized actions</a></li>
				<li><a data-toggle="tab" href="#print-tab"> Print</a></li>
				<li><a data-toggle="tab" href="#milling-tab"> Milling</a></li>
				<li><a data-toggle="tab" href="#lighting-tab"> Lighting</a></li>
			</ul>';
		$widgeFooterButtons = $this->smart->create_button('Save', 'primary')->attr(array('id' => 'save'))->attr('data-action', 'exec')->icon('fa-save')->print_html(true);
							  
		$widget         = $this->smart->create_widget($widgetOptions);
		$widget->id     = 'hardware-settings-widget';
		$widget->header = array('icon' => 'fa-cog', "title" => "<h2>Hardware</h2>", 'toolbar'=>$headerToolbar);
		$widget->body   = array('content' => $this->load->view('settings/hardware_widget', $data, true ), 'class'=>'no-padding', 'footer'=>$widgeFooterButtons);
		
		$this->addJsInLine($this->load->view('settings/hardware_js', $data, true));
		//$this->addCSSInLine('<style type="text/css">.custom_settings{display:none !important;}</style>'); 
		$this->content = $widget->print_html(true);
		$this->view();
	}

	/**
	 * save settings 
	 */
	public function saveSettings()
	{
		//get data from post
		$postData = $this->input->post();
		//load libraries, helpers, model, config
		$this->load->helpers('utility_helper');
		$this->load->helpers('fabtotum_helper');
		//create settings array
		$settingsToSave = arrayFromPost($postData);
		if($postData['settings_type'] == 'default'){ //don't override those vaules for default settings
			unset($settingsToSave['e']);
			unset($settingsToSave['a']);
			unset($settingsToSave['feeder']); 
			unset($settingsToSave['invert_x_endstop_logic']);
		}
		//load settings
		$loadedSettings = loadSettings($postData['settings_type']);
		$newSettings = array_replace ($loadedSettings, $settingsToSave);
		saveSettings($newSettings, $postData['settings_type']);
		if($postData['settings_type'] == 'custom'){
			$defaultSettings = loadSettings('default');
			saveSettings(array_replace($defaultSettings, array('settings_type' => 'custom')), 'default');
		}
		//update settings on session
		$this->session->settings = $newSettings;
		//reload configuration settings
		resetController();
		$this->output->set_content_type('application/json')->set_output(true);
	}
	
	/**
	 * scan wifi networks
	 * @return json all scanned networks
	 */
	public function scanWifi($interface = 'wlan0')
	{
		//load helpers
		$this->load->helper('os_helper');
		$nets = scanWlan($interface);
		$this->output->set_content_type('application/json')->set_output(json_encode($nets));
	}
	
	public function network()
	{
		$postData = $this->input->post();
		
		//load libraries, helpers, model, config
		$this->load->library('smart');
		$this->load->helper('fabtotum_helper');
		$this->load->helper('os_helper');
		$this->load->helper('form');
		$this->config->load('fabtotum');

		$data = array();
		$data['yesNoOptions'] = array('1' => 'Yes', '0' => 'No');
		$data['current_hostname'] = getHostName();
		$data['current_name'] = getAvahiServiceName();
		
		$ifaces_data = getInterfaces();
		
		//main page widget
		$widgetOptions = array(
			'sortable' => false, 'fullscreenbutton' => true,'refreshbutton' => false,'togglebutton' => false,
			'deletebutton' => false, 'editbutton' => false, 'colorbutton' => false, 'collapsed' => false
		);
		
		$tabs_title = '';
		$is_active = 'active';
		$if_idx = array('eth' => 1, 'wlan' => 1);
		$if_number = array('eth' => 0, 'wlan' => 0);
		foreach($ifaces_data as $iface => $info)
		{
			if(array_key_exists('wireless', $info) )
				$if_number['wlan'] += 1;
			else
				$if_number['eth'] += 1;
			
		}
		
		$tabs_content = '';
		
		$interfaces = array();
		
		foreach($ifaces_data as $iface => $info)
		{
			/* Convert <ip>/<prefix> format to <ip> & <netmask> */
			if( $info['ipv4_address'] != '' )
			{
				$info['netmask_address'] = cidr2NetmaskAddr($info['ipv4_address']);
				$tmp = explode('/', $info['ipv4_address'])[0];
				$info['ipv4_address'] = $tmp;
			}
			else
			{
				$info['ipv4_address'] = '0.0.0.0';
				$info['netmask_address'] = '255.255.255.0';
				$info['gateway'] = '0.0.0.0';
			}
			
			$interfaces[$iface] = array('do_scan' => false);
			
			if(array_key_exists('wireless', $info) )
			{
				$interfaces[$iface]['do_scan'] = true;
				
				if($info['wireless']['can_be_ap'] == 'yes')
					$wifiModes = array('static' => 'Static', 'dhcp' => 'Automatic (DHCP)', 'static-ap' => 'Access Point');
				else
					$wifiModes = array('static' => 'Static', 'dhcp' => 'Automatic (DHCP)');
				
				if(!isset($info['wireless']['bssid']) && $info['address_mode'] == 'static')
				{
					$info['address_mode'] = 'dhcp';
				}
				
				$info['wireless']['bssid'] = isset($info['wireless']['bssid']) ? $info['wireless']['bssid'] : "";
				$info['wireless']['psk'] = isset($info['wireless']['psk']) ? $info['wireless']['psk'] : "";
				
				$info['wireless']['ssid'] = isset($info['wireless']['ssid']) ? $info['wireless']['ssid'] : "";
				$info['wireless']['passphrase'] = isset($info['wireless']['passphrase']) ? $info['wireless']['passphrase'] : "";
				
				if(isset($info['wireless']['mode']))
				{
					if( $info['wireless']['mode'] == 'accesspoint' )
					{
						$info['address_mode'] = 'static-ap';
						$interfaces[$iface]['do_scan'] = false;
					}
				}
				
				$if_type = 'wlan';
				$title = 'Wireless';
				$tab_data = array(
					'iface' => $iface,
					'info' => $info,
					'addressModeWiFi' => $wifiModes
				);
				$tabs_content .= $this->load->view('settings/wireless_tab', $tab_data, true );
			}
			else
			{
				$if_type = 'eth';
				$title = 'Ethernet';
				$tab_data = array(
					'iface' => $iface,
					'info' => $info,
					'addressModeEth' => array('static' => 'Static', 'dhcp' => 'Automatic (DHCP)')
				);
				$tabs_content .= $this->load->view('settings/ethernet_tab', $tab_data, true );
			}
			
			if( $if_number[$if_type] > 1)
				$title .= ' ('.$if_idx[$if_type].')';
			$if_idx[$if_type] += 1;
			
			$tabs_title .= '<li data-net-type="'.$if_type.'" data-attribute="'.$iface.'" class="tab '.$is_active.'"><a data-toggle="tab" href="'.$iface.'-tab"> '.$title.'</a></li>';
			$is_active = '';
		}
		$data['iface_tabs'] = $tabs_content;
		$data['interfaces'] = $interfaces;
		
		$tabs_title .= '<li data-attribute="dnssd" class="tab"><a data-toggle="tab" href="#dnssd-tab"> DNS-SD</a></li>';
		
		$headerToolbar = '<ul class="nav nav-tabs pull-right">' . $tabs_title .'</ul>';
		
		$widgeFooterButtons = $this->smart->create_button('Scan', 'primary')->attr(array('id' => 'scanButton', 'style' => 'display:none'))->attr('data-action', 'exec')->icon('fa-save')->print_html(true)
						 .' '.$this->smart->create_button('Save', 'primary')->attr(array('id' => 'saveButton'))->attr('data-action', 'exec')->icon('fa-save')->print_html(true);
		
		$widget         = $this->smart->create_widget($widgetOptions);
		$widget->id     = 'network-settings-widget';
		$widget->header = array('icon' => 'fa-globe', "title" => "<h2>Network settings</h2>", 'toolbar'=>$headerToolbar);
		$widget->body   = array('content' => $this->load->view('settings/network_widget', $data, true ), 'class'=>'no-padding', 'footer'=>$widgeFooterButtons);
		
		$this->addJsInLine($this->load->view('settings/network_js', $data, true));
		$this->addJSFile('/assets/js/plugin/jquery-validate/jquery.validate.min.js'); //validator
		$this->addJSFile('/assets/js/plugin/inputmask/jquery.inputmask.bundle.js');	
		//$this->addCSSInLine('<style type="text/css">.custom_settings{display:none !important;}</style>'); 
		$this->content = $widget->print_html(true);
		$this->view();
	}
	
	public function saveNetworkSettings($action = 'connect')
	{
		//get data from post
		$this->load->helper('os_helper');
		$postData = $this->input->post();
		$result = true;
		
		switch($postData['net_type'])
		{
			case "eth":
				$address = $postData['ipv4'];
				$netmask = $postData['netmask'];
				$gateway = $postData['gateway'];
				$mode = $postData['address-mode'];
				$iface = $postData['active'];
				configureEthernet($iface, $mode, $address, $netmask, $gateway);
				break;
			case "wlan":
				if($action == 'connect')
				{
					$address = $postData['ipv4'];
					$netmask = $postData['netmask'];
					$gateway = $postData['gateway'];
					$mode = $postData['address-mode'];
					$iface = $postData['active'];
					$ap_ssid = $postData['ap-ssid'];
					$ap_pass = $postData['ap-password'];
					$hidden_ssid = $postData['hidden-ssid'];
					$hidden_pass = $postData['hidden-passphrase'];
					$psk = $postData['hidden-psk'];
					
					if($mode == 'static-ap')
					{
						$ssid = $ap_ssid;
						$password = $ap_pass;
					}
					else
					{
						$ssid = $hidden_ssid;
						$password = $hidden_pass;
					}
					configureWireless($iface, $ssid, $password, $psk, $mode, $address, $netmask, $gateway);
				}
				else if($action == 'disconnect')
				{
					$iface = $postData['active'];
					disconnectFromWireless($iface);
				}
				break;
			case "dnssd":
				$hostname = $postData['dnssd-hostname'];
				$name = $postData['dnssd-name'];
				// TODO: error handling
				setHostName($hostname, $name);
				break;
			default:
				$result = false;
		}
		
		$this->output->set_content_type('application/json')->set_output($result);
	}

 }
 
?>

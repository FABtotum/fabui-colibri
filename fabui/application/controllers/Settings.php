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
    /**
     *
     */
    function __construct()
    {
        parent::__construct();
        session_write_close(); //avoid freezing page
    }
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
		if(!file_exists($this->config->item('settings'))){ // if default settings file doesn't exits, create it
			createDefaultSettings();
 		}
		$data['defaultSettings'] = json_decode(file_get_contents($this->config->item('settings')), true);
		$data['unitType'] = getUnitType();
		
		$data['yesNoOptions'] = array('1' => 'Yes', '0' => 'No');
		$data['customizeActionsOptions'] = array('none' => 'None', 'shutdown' => 'Shutdown');
		$data['printCalibrationPreferenceOptions'] = array('homing' => _('Simple homing'), 'auto_bed_leveling' => _('Auto bed leveling'));
		//main page widget
		$widgetOptions = array(
			'sortable' => false, 'fullscreenbutton' => true,'refreshbutton' => false,'togglebutton' => false,
			'deletebutton' => false, 'editbutton' => false, 'colorbutton' => false, 'collapsed' => false
		);
		$headerToolbar = '
			<ul class="nav nav-tabs pull-right">
				<li class="active"><a data-toggle="tab" href="#hardware-tab"> '._('Hardware').'</a></li>
				<li><a data-toggle="tab" href="#safety-tab"> '._('Safety').'</a></li>
				<li><a data-toggle="tab" href="#homing-tab"> '._('Homing').'</a></li>
				<li><a data-toggle="tab" href="#print-tab"> '._('Print').'</a></li>
				<li><a data-toggle="tab" href="#lighting-tab"> '._('Lighting').'</a></li>
			</ul>';
		$widgeFooterButtons = $this->smart->create_button(_('Save'), 'primary')->attr(array('id' => 'save'))->attr('data-action', 'exec')->icon('fa-save')->print_html(true);
							  
		$widget         = $this->smart->create_widget($widgetOptions);
		$widget->id     = 'hardware-settings-widget';
		$widget->header = array('icon' => 'fa-cog', "title" => "<h2>"._('Hardware')."</h2>", 'toolbar' => $headerToolbar);
		$widget->body   = array('content' => $this->load->view('settings/hardware_widget', $data, true ), 'class'=>'no-padding', 'footer'=>$widgeFooterButtons);
		
		$this->addJSInLine($this->load->view('settings/hardware_js', $data, true));
		$this->addCSSInLine($this->load->view('settings/hardware_css', $data, true));
		
		$this->content = $widget->print_html(true);
		$this->view();
	}
	
	/**
	 * set ambient color
	 */
	public function setColor()
	{
		$postData = $this->input->post();
		$this->load->helpers('fabtotum_helper');
		
		$red   = $postData['red'];
		$green = $postData['green'];
		$blue  = $postData['blue'];
		
		$result = doMacro('set_ambient_color', '', array($red, $green, $blue));
		
		$this->output->set_content_type('application/json')->set_output(json_encode( array($result) ));
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
		$this->load->helpers('myfabtotum_helper');
		//create settings array
		$settingsToSave = arrayFromPost($postData);
		
		$settingsToSave['hardware']['camera']['available'] = $settingsToSave['hardware']['camera']['available'] == 1;
		$settingsToSave['scan']['available']               = $settingsToSave['scan']['available'] == 1;
		$settingsToSave['feeder']['available']             = $settingsToSave['feeder']['available'] == 1;
		$settingsToSave['feeder']['engage']                = $settingsToSave['feeder']['engage'] == 1;
		
		if($postData['settings_type'] == 'default'){ //don't override those vaules for default settings
			unset($settingsToSave['e']);
			unset($settingsToSave['a']);
			unset($settingsToSave['feeder']); 
			unset($settingsToSave['custom-invert_x_endstop_logic']);
		}
		//load settings
		$loadedSettings = loadSettings();
		$newSettings = array_replace_recursive($loadedSettings, $settingsToSave);
		saveSettings($newSettings);
		
		//update settings on session
		$this->session->settings = $newSettings;
		//reload configuration settings
		reload_myfabtotum();
		resetController();
		$this->output->set_content_type('application/json')->set_output(json_encode(loadSettings()));
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
	
	public function network($preSelectedInterface = 'eth0')
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
		$wifiChannels = array(
			'1'  => _('Channel'). ' 1 (2412 MHz)',
			'2'  => _('Channel'). ' 2 (2417 MHz)',
			'3'  => _('Channel'). ' 3 (2422 MHz)',
			'4'  => _('Channel'). ' 4 (2427 MHz)',
			'5'  => _('Channel'). ' 5 (2432 MHz)',
			'6'  => _('Channel'). ' 6 (2437 MHz)',
			'7'  => _('Channel'). ' 7 (2442 MHz)',
			'8'  => _('Channel'). ' 8 (2447 MHz)',
			'9'  => _('Channel'). ' 9 (2452 MHz)',
			'10' => _('Channel').' 10 (2457 MHz)',
			'11' => _('Channel').' 11 (2462 MHz)');
			
		$data['current_hostname']     = getHostName();
		$data['current_name']         = getAvahiServiceName();
		$data['preSelectedInterface'] = $preSelectedInterface;
		
		$ifaces_data = getInterfaces();
		
		
		
		writeNetworkInfo($ifaces_data);
		
		//main page widget
		$widgetOptions = array(
			'sortable' => false, 'fullscreenbutton' => true,'refreshbutton' => false,'togglebutton' => false,
			'deletebutton' => false, 'editbutton' => false, 'colorbutton' => false, 'collapsed' => false
		);
		
		$tabs_title = '';
		$if_idx = array('eth' => 1, 'wlan' => 1);
		$if_number = array('eth' => 0, 'wlan' => 0);
		foreach($ifaces_data as $iface => $info)
		{
			if(!is_array($info)) continue;
			
			if(array_key_exists('wireless', $info) )
				$if_number['wlan'] += 1;
			else
				$if_number['eth'] += 1;
			
		}
		
		$tabs_content = '';
		
		$interfaces = array();
		
		
		foreach($ifaces_data as $iface => $info)
		{
			if(!is_array($info)) continue;
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
				$info['netmask_address'] = '255.255.0.0';
				$info['gateway'] = '0.0.0.0';
			}
			
			$interfaces[$iface] = array('do_scan' => false);
			
			if(isset($info['address_mode']))
			{
				if($info['address_mode'] == "manual")
				{
					$info['address_mode'] == "static";
				}
			}
			
			if(array_key_exists('wireless', $info) )
			{
				$interfaces[$iface]['do_scan'] = true;
				
				if($info['wireless']['can_be_ap'] == 'yes')
					$wifiModes = array('static' => _("Static"), 'dhcp' => _("Automatic (DHCP)"), 'static-ap' => _("Access Point"), 'disabled' => _("Disable") );
				else
					$wifiModes = array('static' => _("Static"), 'dhcp' => _("Automatic (DHCP)"), 'disabled' => _("Disable") );
				
				if(!isset($info['wireless']['bssid']) && $info['address_mode'] == 'static')
				{
					$info['address_mode'] = 'dhcp';
				}
				
				if(isset($info['wireless']['support_ap_channel']))
				{
					$info['wireless']['support_ap_channel'] = 'no';
				}
				
				if(isset($info['wireless']['support_ap_custom_address']))
				{
					$info['wireless']['support_ap_custom_address'] = 'no';
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
				
				if( $info['address_mode'] == 'manual' )
				{
					$info['address_mode'] = 'disabled';
				}
				
				if( $info['address_mode'] == 'disabled' )
				{
					$interfaces[$iface]['do_scan'] = false;
				}
				
				if(!isset($info['wireless']['channel']))
				{
					$info['wireless']['channel'] = 1;
				}
				
				$if_type = 'wlan';
				$title = '<span class="hidden-xs"> '._("Wireless").' </span>';
				$icon = '<i class="fa fa-wifi"></i> ';
				$tab_data = array(
					'iface' => $iface,
					'info' => $info,
					'addressModeWiFi' => $wifiModes,
					'wifiChannels' => $wifiChannels,
					'active' => $iface == $preSelectedInterface ? 'active' : ''
				);
				$tabs_content .= $this->load->view('settings/wireless_tab', $tab_data, true );
			}
			else
			{
				$if_type = 'eth';
				$title = '<span class="hidden-xs"> '. _('Ethernet').'</span>';
				$icon = '<i class="icon-communication-088"></i> ';
				$tab_data = array(
					'iface' => $iface,
					'info' => $info,
					'addressModeEth' => array('static' => 'Static', 'dhcp' => _('Automatic (DHCP)')),
					'active' => $iface == $preSelectedInterface ? 'active' : ''
				);
				$tabs_content .= $this->load->view('settings/ethernet_tab', $tab_data, true );
			}
			
			if( $if_number[$if_type] > 1)
				$title .= ' ('.$if_idx[$if_type].')';
			$if_idx[$if_type] += 1;
			
			$is_active = $iface == $preSelectedInterface ? 'active' : '';
			
			$tabs_title .= '<li data-net-type="'.$if_type.'" data-attribute="'.$iface.'" class="tab '.$is_active.'"><a data-toggle="tab" href="'.$iface.'-tab">'.$icon.'  '.$title.'</a></li>';
			//$is_active = '';
		}
		$data['iface_tabs'] = $tabs_content;
		$data['interfaces'] = $interfaces;
		$data['preSelectedInterface'] = $preSelectedInterface;
		
		$dns = getDNS();
		$data['dns'] = getDNS();
		
		
		$dnssdActive = $preSelectedInterface == 'dnssd' ? 'active': '';
		$tabs_title .= '<li data-attribute="dnssd" class="tab '.$dnssdActive.' "><a data-toggle="tab" href="#dnssd-tab"> '._('DNS-SD').'</a></li>';
		
		$dnsActive = $preSelectedInterface == 'dns' ? 'active': '';
		$tabs_title .= '<li data-attribute="dns" class="tab '.$dnsActive.' "><a data-toggle="tab" href="#dns-tab"> '._('DNS').'</a></li>';
		
		//$sshActive = $preSelectedInterface == 'ssh' ? 'active': '';
		//$tabs_title .= '<li data-attribute="ssh" class="tab '.$sshActive.' "><a data-toggle="tab" href="#ssh-tab"> '._('SSH').'</a></li>';
		
		$headerToolbar = '<ul class="nav nav-tabs network-tabs pull-right">' . $tabs_title .'</ul>';
		
		/*
		$widgeFooterButtons = $this->smart->create_button(_('Hidden wifi'), 'default hiddenWifiButton')->attr(array('style' => 'display:none'))->attr('data-action', 'exec')->icon('fa-user-secret')->print_html(true)
						 .' '.$this->smart->create_button(_('Scan'), 'primary')->attr(array('id' => 'scanButton', 'style' => 'display:none'))->attr('data-action', 'exec')->icon('fa-search')->print_html(true)
						 .' '.$this->smart->create_button(_('Save'), 'primary')->attr(array('id' => 'saveButton'))->attr('data-action', 'exec')->icon('fa-save')->print_html(true);
		*/
		$widgeFooterButtons = $this->smart->create_button(_('Scan'), 'primary')->attr(array('id' => 'scanButton', 'style' => 'display:none'))->attr('data-action', 'exec')->icon('fa-search')->print_html(true)
						      .' '.$this->smart->create_button(_('Save'), 'primary')->attr(array('id' => 'saveButton'))->attr('data-action', 'exec')->icon('fa-save')->print_html(true);
		
		
		$widget         = $this->smart->create_widget($widgetOptions);
		$widget->id     = 'network-settings-widget';
		$widget->header = array('icon' => 'fa-globe', "title" => "<h2 class='hidden-xs'>"._('Network settings')."</h2>", 'toolbar'=>$headerToolbar);
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
		$this->load->helper(array('os_helper', 'social_helper', 'shop_helper'));
		$this->load->helper('myfabtotum_helper');
		$postData = $this->input->post();
		$result = true;
		$net_type = $postData['net_type'];
		switch($net_type)
		{
			case "eth":
				$address = $postData['ipv4'];
				$netmask = $postData['netmask'];
				$gateway = $postData['gateway'];
				$mode = $postData['address-mode'];
				$iface = $postData['active'];
				configureEthernet($iface, $mode, $address, $netmask, $gateway);
				storeNetworkSettings($net_type, $iface, $mode, $address, $netmask, $gateway);
				
				//update social feeds
				downloadAllFeeds();
				downloadAllFilamentsFeeds();
				break;
			case "wlan":
				if($action == 'connect')
				{
					$address     = isset($postData['ipv4'])    ? $postData['ipv4']    : '0.0.0.0';
					$netmask     = isset($postData['netmask']) ? $postData['netmask'] : '255.255.0.0';
					$gateway     = isset($postData['gateway']) ? $postData['gateway'] : '0.0.0.0';
					$mode        = $postData['address-mode'];
					$iface       = $postData['active'];
					$ap_ssid     = $postData['ap-ssid'];
					$ap_pass     = $postData['ap-password'];
					$ap_channel  = isset($postData['ap-channel']) ? $postData['ap-channel'] : 1;
					$hidden_ssid = $postData['hidden-ssid'];
					$hidden_pass         = $postData['hidden-passphrase'];
					$psk                 = $postData['hidden-psk'];
					$connectToHiddenWifi = isset($postData['connect-to-hidden-wifi']) && ($postData['connect-to-hidden-wifi'] == 'true');
					
					if($mode == 'static-ap')
					{
						$ssid = $ap_ssid;
						$password = $ap_pass;
						$psk = '';
					}
					else
					{
						$ssid = $hidden_ssid;
						$password = $hidden_pass;
					}
					
					if($connectToHiddenWifi){
						$ssid = $postData['hiddenWifiSsid'];
						$password = $postData['hiddenWifiPassword'];
					}
					
					configureWireless($iface, $ssid, $password, $psk, $mode, $address, $netmask, $gateway, $ap_channel);
					storeNetworkSettings($net_type, $iface, $mode, $address, $netmask, $gateway, $ssid, $password, $psk);
					//update social feeds
					downloadAllFeeds();
					downloadAllFilamentsFeeds();
				}
				else if($action == 'disconnect')
				{
					$iface = $postData['active'];
					disconnectFromWireless($iface);
				}
				break;
			case "dnssd":
				$hostname = str_replace(" ", "", trim($postData['dnssd-hostname']));
				$name     = trim($postData['dnssd-name']);
				// TODO: error handling
				setHostName($hostname, $name);
				storeNetworkSettings($net_type, '', '', '', '', '', '', '', '', $hostname, $name);
				$this->load->model('Configuration', 'configuration');
				$this->configuration->store('unit_name', $hostname);
				break;
			case "dns":
				// TODO
				$dns = $postData['dns'];
				configureDNS($dns);
			default:
				$result = false;
		}
		writeNetworkInfo();
		reload_myfabtotum();
		//$this->output->set_content_type('application/json')->set_output(json_encode($result));
		$this->output->set_content_type('application/json')->set_output(json_encode(getInterfaces())); 
	}
	
	
	public function getNetworkInfo()
	{
		$this->load->helper('os_helper');
		$data['interfaces'] = getInterfaces();
		$data['internet'] = false;
		if(isset($data['interfaces']['wlan0']['wireless']['ssid'])){
			$data['internet'] = isInternetAvaialable();
		}
		$this->output->set_content_type('application/json')->set_output(json_encode($data));
	}
	
 }
 
?>

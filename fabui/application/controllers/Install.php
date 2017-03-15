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
		$this->config->load('fabtotum');
		$restore_file = $this->config->item('restore_file');

		if( file_exists($restore_file) )
		{
			$this->restoreView();
		}
		else
		{
			$this->installView();
		}
	}
	
	private function installView()
	{
		$this->load->helper('date_helper');
		$this->load->helper('language_helper');
		$this->load->helper('fabtotum_helper');
		//TODO
		$this->content = $this->load->view('install/wizard', null, true );
		$this->addJsInLine($this->load->view('install/js', '', true));
		//add js file
		$this->addJSFile('/assets/js/plugin/masked-input/jquery.maskedinput.min.js');
		$this->addJSFile('/assets/js/plugin/bootstrap-wizard/jquery.bootstrap.wizard.min.js'); //wizard
		$this->addJSFile('/assets/js/plugin/moment/moment.min.js'); //moment
		$this->addJSFile('/assets/js/plugin/tzdetection/jstz.min.js'); //timezonedetection
		$this->addCSSFile('/assets/css/install/custom.css');
		//show page
		$this->installLayout();
	}
	
	private function restoreView()
	{
		$this->load->helper('date_helper');
		//TODO
		$this->content = $this->load->view('restore/wizard', null, true );
		$this->addJsInLine($this->load->view('restore/js', '', true));
		//add js file
		$this->addJSFile('/assets/js/plugin/bootstrap-wizard/jquery.bootstrap.wizard.min.js'); //wizard
		$this->addJSFile('/assets/js/plugin/moment/moment.min.js'); //moment
		$this->addJSFile('/assets/js/plugin/tzdetection/jstz.min.js'); //timezonedetection
		$this->addCSSInLine('<style> #main {margin-left:0px !important;}</style>');
		//show page
		$this->restoreLayout();
	}
	
	public function test()
	{
		$this->load->model('Configuration', 'configuration');
		
		$raw = $this->configuration->load('network', '{}');
		$network_settings = json_decode($raw, true);
		 
		if( array_key_exists('interfaces', $network_settings) )
		{
			foreach($network_settings['interfaces'] as $iface => $data)
			{
				switch($data['net_type'])
				{
					case "eth":
						print_r($data);
						//~ configureEthernet($data['iface'], $data['mode'], $data['address'], $data['netmask'], $data['gateway']);
						//echo $iface .", ". $data['mode'].", ". $data['address'].", ". $data['netmask'].", ". $data['gateway'];
						break;
					case "wlan":
						//~ configureWireless($data['iface'], $data['ssid'], $data['password'], $data['psk'], $data['mode'], $data['address'], $data['netmask'], $data['gateway']);
						//echo $iface.", ". $data['ssid'].", ". $data['password'].", ". $data['psk'].", ". $data['mode'].", ". $data['address'].", ". $data['netmask'].", ". $data['gateway'];
						print_r($data);
						break;
				}
			}
			
			//~ if( array_key_exists('hostname', $network_settings) && array_key_exists('description', $network_settings))
			//~ {
				//~ setHostName($network_settings['hostname'], $network_settings['description']);
			//~ }
		}
	}
	
	public function doRestore()
	{
		// load libraries, models, helpers
		$this->load->model('Configuration', 'configuration');
		$this->load->helper('os_helper');
		$postData = $this->input->post();
		
		// Restore timezone
		$query = $this->configuration->get( array('key' => 'timezone') );
		if($query)
		{
			setTimeZone($query[0]['value']);
		}
		
		if(!$this->input->post('user_files'))
		{
			/*
			 * Clear user files from filesystem and database
			 * and install samples instead.
			 **/
			$this->db->truncate('sys_obj_files');
			$this->db->truncate('sys_objects');
			$this->db->truncate('sys_files');
			
			shell_exec('rm -rf /mnt/userdata/uploads/*');
		}
		
		if(!$this->input->post('hardware_settings'))
		{
			/*
			 * Remove previous hardware settings (default and custom) and copy fresh ones to userdata
			 * */
			// Copy a fresh copy of default_settings
			shell_exec('cp /var/lib/fabui/settings/settings.json /mnt/userdata/settings/settings.json');
			shell_exec('sudo chown 33.33 /mnt/userdata/settings/settings.json');
		}
		// else keep the previous settings
		
		// Create links to settings on userdata partition
		shell_exec('rm -f /var/lib/fabui/settings/settings.json');
		shell_exec('ln -s /mnt/userdata/settings/settings.json /var/lib/fabui/settings/settings.json');

		if(!$this->input->post('task_history'))
		{
			/*
			 * Flush task table
			 * */
			$this->db->truncate('sys_tasks');
		}
		
		if(!$this->input->post('network_settings'))
		{
			$this->configuration->store('network', '{"interfaces" : {}, "hostname" : "fabtotum", "description" : "Fabtotum Personal Fabricator 3D Printer" }');
		}
		else
		{
			$raw = $this->configuration->load('network', '{}');
			$network_settings = json_decode($raw, true);
			 
			if( array_key_exists('interfaces', $network_settings) )
			{
				foreach($network_settings['interfaces'] as $iface => $data)
				{
					switch($data['net_type'])
					{
						case "eth":
							configureEthernet($iface, $data['mode'], $data['address'], $data['netmask'], $data['gateway']);
							break;
						case "wlan":
							configureWireless($iface, $data['ssid'], $data['password'], $data['psk'], $data['mode'], $data['address'], $data['netmask'], $data['gateway']);
							break;
					}
				}
				
				if( array_key_exists('hostname', $network_settings) && array_key_exists('description', $network_settings))
				{
					setHostName($network_settings['hostname'], $network_settings['description']);
				}
			}
		}
		
		if(!$this->input->post('head_settings'))
		{
			/*
			 * Remove previous head settings and copy fresh ones to userdata
			 * */
			shell_exec('rm -rf /mnt/userdata/heads/*');
			shell_exec('cp /var/lib/fabui/heads/* /mnt/userdata/heads');
		}
		else
		{
			shell_exec('rm -rf /var/lib/fabui/heads');
			shell_exec('ln -s /mnt/userdata/heads /var/lib/fabui');
		}
		
		if(!$this->input->post('feeder_settings'))
		{
			/*
			 * Remove previous feeder settings and copy fresh ones to userdata
			 * */
			shell_exec('rm -rf /mnt/userdata/feeders/*');
			shell_exec('cp /var/lib/fabui/feeders/* /mnt/userdata/feeders');
		}
		else
		{
			shell_exec('rm -rf /var/lib/fabui/feeders');
			shell_exec('ln -s /mnt/userdata/feeders /var/lib/fabui');
		}
		
		
		if(!$this->input->post('plugins'))
		{
			/*
			 * Remove installed plugins and flush sys_plugins
			 * */
			$this->db->truncate('sys_plugins');
			shell_exec('rm -rf /mnt/userdata/plugins/*');
		}
		
		//set system date (first time internet is not available)
		setSystemDate($postData['browser-date']);
		//delete AUTOINSTALL
		$this->deleteAutoInstallFile();
		//delete RESTORE
		$this->deleteRestoreFile();
		redirect('login');
	}
	
	/**
	 * do first install
	 */
	public function doInstall()
	{
		//database is installed during bundle creation
		
		//load libraries, models, helpers
		$this->load->model('User', 'user');
		$this->load->model('Configuration', 'configuration');
		$this->load->helper('os_helper');
		//load configs
		$this->config->load('fabtotum');
		
		//get data from post	
		$postData = $this->input->post();
		
		//if is first install
		if(file_exists($this->config->item('autoinstall_file'))){
			//set system date (first time internet is not available)
			setSystemDate($postData['browser-date']);
			unset($postData['browser-date']);
			//set time zone
			setTimeZone($postData['timezone']);
			$this->configuration->store('timezone',      $postData['timezone']);
			$this->configuration->store('serial_number', $postData['serial_number']);
			$this->configuration->store('unit_name',     $postData['unit_name']);
		}
		
		$language = $postData['language'];
		
		unset($postData['timezone']);
		unset($postData['passwordConfirm']);
		unset($postData['terms']);
		unset($postData['confirmPassword']);
		unset($postData['language']);
		unset($postData['browser-date']);
		unset($postData['serial_number']);
		unset($postData['unit_name']);
		//set user account data
		$userData = $postData;
		$userData['session_id'] = $this->session->session_id;
		$userData['role']       = 'administrator';
		$userData['settings']   = json_encode(array('language' => $language ));
		$userData['password']   = md5($userData['password']);
		//ADD USER ACCOUNT
		$newUserID = $this->user->add($userData);
		//Install samples
		$this->installSamples($newUserID);
		//delete AUTOINSTALL
		$this->deleteAutoInstallFile();
		redirect('login');
	}
	
	/**
	 * install gcode samples
	 */
	public function installSamples($userID)
	{
		$this->load->model('Objects', 'objects');
		$this->load->helpers('upload_helper');
		
		$samples_path = '/usr/share/fabui/recovery/';
		$samples_import = '/usr/share/fabui/recovery/import.json';
		
		if(file_exists($samples_import))
		{
			$samples = json_decode( file_get_contents($samples_import), true);
			foreach($samples['objects'] as $object)
			{
				$data = array();
				$data['name'] = $object['name'];
				$data['description'] = $object['description'];
				$data['user'] = $userID;
				$data['date_insert'] = date('Y-m-d H:i:s');
				$data['date_update'] = date('Y-m-d H:i:s');
				
				$objectID = $this->objects->add($data);
				$fileIDs = array();
				
				foreach($object['files'] as $file)
				{
					$file_note = $file['note'];
					$file_fullpath = $samples_path . $file['path'];
					$fileID = uploadFromFileSystem($file_fullpath, $file_note);
					$fileIDs[] = $fileID;
				}
				
				$this->objects->addFiles($objectID, $fileIDs);
			}
		}
	}
	
	public function deleteAutoInstallFile()
	{
		//load configs
		$this->config->load('fabtotum');
		$autoinstall_file = $this->config->item('autoinstall_file');
		//delete file if exists
		if(file_exists($autoinstall_file)){
			unlink($autoinstall_file);
		}
	}
	
	public function deleteRestoreFile()
	{
		//load configs
		$this->config->load('fabtotum');
		$restore_file = $this->config->item('restore_file');
		//delete file if exists
		if(file_exists($restore_file)){
			unlink($restore_file);
		}
	}
	
 }
 
?>

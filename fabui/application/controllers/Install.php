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
		$database_file = $this->config->item('database');
		$database_filename = basename($database_file);
		$userdata_path = $this->config->item('userdata_path');
		
		$database_final_path = $userdata_path . basename($database_file);
		
		if( file_exists($database_final_path) )
		{
			$this->restoreView();
		}
		else
		{
			$this->installView();
		}
		
		//$this->restoreView();
	}
	
	private function installView()
	{
		$this->load->helper('date_helper');
		//TODO
		$this->content = $this->load->view('install/wizard', null, true );
		$this->addJsInLine($this->load->view('install/js', '', true));
		//add js file
		$this->addJSFile('/assets/js/plugin/bootstrap-wizard/jquery.bootstrap.wizard.min.js'); //wizard
		$this->addJSFile('/assets/js/plugin/moment/moment.min.js'); //moment
		$this->addJSFile('/assets/js/plugin/tzdetection/jstz.min.js'); //timezonedetection
		$this->addCSSInLine('<style> #main {margin-left:0px !important;}</style>');
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
	
	public function doRestore()
	{
		// load libraries, models, helpers
		$this->load->model('Configuration', 'configuration');
		$this->load->helper('os_helper');
		
		// get data from post
		$postData = $this->input->post();
		
		// Install database
		$this->installDatabase(true);
		
		/*
		 * Restore timezone from sys_configuration
		 * */
		
		print_r($postData);
		//Array ( [browser-date] => 2016-11-23 16:46:04 [user_files] => on [hw_settings] => on [task_history] => on [head_settings] => on [network_settings] => on [plugins] => on )
		if(!$postData['user_files'])
		{
			/*
			 * Clear user files from filesystem and database
			 * and install samples instead.
			 **/
			//~ echo "clear existing files from filesystem".PHP_EOL;
			$this->db->truncate('sys_obj_files');
			$this->db->truncate('sys_objects');
			$this->db->truncate('sys_files');
			
			shell_exec('rm -rf /mnt/userdata/uploads/*');
		}
		
		if(!$postData['hw_settings'])
		{
			/*
			 * Remove previous hardware settings (default and custom) and copy fresh ones to userdata
			 * */
			shell_exec('rm -rf /mnt/userdata/settings/*');
			
			// Copy a fresh copy of default_settings
			shell_exec('cp /var/lib/fabui/settings/default_settings.json /mnt/userdata/settings/default_settings.json');
			shell_exec('rm /var/lib/fabui/settings/default_settings.json');
			shell_exec('ln -s /mnt/userdata/settings/default_settings.json /var/lib/fabui/settings/default_settings.json');
			
			// Copy a fresh copy of custom_settings
			shell_exec('cp /var/lib/fabui/settings/default_settings.json /mnt/userdata/settings/custom_settings.json');
			shell_exec('rm /var/lib/fabui/settings/custom_settings.json');
			shell_exec('ln -s /mnt/userdata/settings/custom_settings.json /var/lib/fabui/settings/default_settings.json');
		}
		
		if(!$postData['task_history'])
		{
			/*
			 * Flush task table
			 * */
			$this->db->truncate('sys_tasks');
		}
		
		if(!$postData['network_settings'])
		{
			/*
			 * Remove network settings from sys_configuration
			 * */
		}
		
		if(!$postData['head_settings'])
		{
			/*
			 * Remove previous head settings and copy fresh ones to userdata
			 * */
			 shell_exec('rm -rf /mnt/userdata/heads/*');
		}
		
		if(!$postData['plugins'])
		{
			/*
			 * Remove installed plugins and flush sys_plugins
			 * */
			$this->db->truncate('sys_plugins');
			shell_exec('rm -rf /mnt/userdata/plugins/*');
		}
		
		//set system date (first time internet is not available)
		//setSystemDate($postData['browser-date']);
		//delete AUTOINSTALL
		//$this->deleteAutoInstallFile();
		//redirect('login');
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
		
		//get data from post	
		$postData = $this->input->post();
		print_r($postData);
		//set system date (first time internet is not available)
		setSystemDate($postData['browser-date']);
		unset($postData['browser-date']);
		//set time zone
		setTimeZone($postData['timezone']);
		$this->configuration->store('timezone', $postData['timezone']);
		
		unset($postData['timezone']);
		unset($postData['passwordConfirm']);
		unset($postData['terms']);
		unset($postData['confirmPassword']);
		//set user account data
		$userData = $postData;
		$userData['session_id'] = $this->session->session_id;
		$userData['settings'] = '{}';
		$userData['password'] = md5($userData['password']);
		//ADD USER ACCOUNT
		$newUserID = $this->user->add($userData);
		//Install samples
		$this->installSamples($newUserID);
		//Install database
		$this->installDatabase();
		//delete AUTOINSTALL
		$this->deleteAutoInstallFile();
		redirect('login');
	}
	
	/**
	 * Install database.
	 */
	public function installDatabase($restore = false)
	{
		$this->config->load('fabtotum');
		
		$restore = false;
		$database_file = $this->config->item('database');
		$database_filename = basename($database_file);
		$userdata_path = $this->config->item('userdata_path');
		
		$database_final_path = $userdata_path . basename($database_file);
		
		if( $restore )
		{
			/*
			 * Database exists on the userdata partition.
			 * Remove the fresh database from /var/lib/fabui and make
			 * a soft-link to the one on userdata.
			 * */
			
			shell_exec('sudo rm '.$database_file);
			shell_exec('ln -s '.$database_final_path.' '.$database_file);
		}
		else
		{
			/* 
			 * Database does not exist on userdata partition
			 * move the one in /var/lib/fabui to /mnt/userdata
			 * and create a soft-link so that it looks as if it's still there
			 */
			shell_exec('sudo mv '.$database_file.' '.$database_final_path);
			shell_exec('ln -s '.$database_final_path.' '.$database_file);
			shell_exec('sudo chown www-data.www-data '.$database_final_path);
		}
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
		//delete file if exists
		if(file_exists(unlink($this->config->item('autoinstall_file')))){
			unlink($this->config->item('autoinstall_file'));
		}
	}
	
 }
 
?>

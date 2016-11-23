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
		$this->load->helper('date_helper');
		//TODO
		$this->content = $this->load->view('install/wizard', null, true );
		$this->addJsInLine($this->load->view('install/js', '', true));
		//add js file
		$this->addJSFile('/assets/js/plugin/bootstrap-wizard/jquery.bootstrap.wizard.min.js'); //wizard
		$this->addJSFile('/assets/js/plugin/moment/moment.min.js'); //moment
		$this->addCSSInLine('<style> #main {margin-left:0px !important;}</style>');
		//show page
		$this->installLayout();
	}
	
	/**
	 * do first install
	 */
	public function doInstall()
	{
		//database is installed during bundle creation
		
		//load libraries, models, helpers
		$this->load->model('User', 'user');
		$this->load->helper('os_helper');
		
		//get data from post	
		$postData = $this->input->post();
		print_r($postData);
		//set system date (first time internet is not available)
		setSystemDate($postData['browser-date']);
		unset($postData['browser-date']);
		//set time zone
		setTimeZone($postData['timezone']);
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
		//Install database
		installDatabase($newUserID);
		//Install samples
		$this->installSamples($newUserID);
		//delete AUTOINSTALL
		$this->deleteAutoInstallFile();
		redirect('login');
	}
	
	/**
	 * Install database.
	 */
	public function installDatabase($userID)
	{
		$this->config->load('fabtotum');
		
		$restore = false;
		$database_file = $this->config->item('database');
		$database_filename = basename($database_file);
		$userdata_path = $this->config->item('userdata_path');
		
		$database_final_path = $userdata_path . basename($database_file);
		//var_dump($database_file);
		//var_dump($userdata_path);
		
		if( file_exists($database_final_path) )
		{
			$restore = true;
			//shell_exec('sudo cp '.$database_file.' /tmp');
			
			//~ shell_exec('sudo mv '.$database_file.' '.$database_final_path);
			shell_exec('sudo mv '.$database_file.' /tmp');
			shell_exec('sudo ln -s '.$database_final_path.' '.$database_file);
			
			shell_exec('sudo mv '.$database_file.' /tmp');
		}
		else
		{
			shell_exec('sudo mv '.$database_file.' '.$database_final_path);
			shell_exec('sudo ln -s '.$database_final_path.' '.$database_file);
		}*/
		
		/* Move database to userdata partition */
		//shell_exec('sudo mv '.$database_file.' '.$database_final_path);
		//shell_exec('sudo ln -s '.$database_final_path.' '.$database_file);
		
		//~ if($restore)
		//~ {
			//~ $this->restoreUserFiles($userID);
		//~ }
	}
	
	/**
	 * Restore user files from existing database.
	 */
	private function restoreUserFiles($userID)
	{
		
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

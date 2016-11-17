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
		//TODO
		//$this->config->load('fabtotum');
		$samples = json_decode( file_get_contents('/usr/share/fabui/recovery/import.json'), true);
		foreach($samples['objects'] as $object)
		{
			echo $object['name'] . '<br>';
			foreach($object['files'] as $file)
			{
				echo '* ' . $file['name'] . ' [' . $file['note'] . ']<br>';
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

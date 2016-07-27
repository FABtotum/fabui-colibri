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
		//TODO
		$this->content = $this->load->view('install/wizard', null, true );
		$this->addJsInLine($this->load->view('install/js', '', true));
		//add js file
		$this->addJSFile('/assets/js/plugin/bootstrap-wizard/jquery.bootstrap.wizard.min.js'); //wizard
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
		/*
		if(!$this->installDefaultDatabase()){
			show_error('Can\'t install default database');
		}*/
	
		//load libraries, models, helpers
		$this->load->model('User', 'user');
		
		//get data from post	
		$postData = $this->input->post();
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
		$this->installSamples();
		//delete AUTOINSTALL
		$this->deleteAutoInstallFile();
		redirect('login');
	}
	
	/**
	 * install default database, sql file stored in ./recovery/sql/fabtotum-default.sqlite3
	 */
	public function installDefaultDatabase()
	{
		//load configs
		$this->config->load('fabtotum');
		//install comand
		return shell_exec('/usr/bin/sqlite3 '.$this->config->item('database').' <  ./recovery/sql/fabtotum.sqlite3') == '' ;
	}
	
	/**
	 * install gcode samples
	 */
	public function installSamples()
	{
		//TODO
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
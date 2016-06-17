<?php
/**
 * 
 * @author Krios Mane
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */
 defined('BASEPATH') OR exit('No direct script access allowed');
 
 class Login extends FAB_Controller {
 	
	public function index(){
		$this->content = $this->load->view(strtolower(get_class($this)).'/login_form', '', true);
		$this->addJsInLine($this->load->view(strtolower(get_class($this)).'/login_js', '', true));
		$this->viewLogin();
	}
	
	//do login
	public function doLogin()
	{
		if($this->input->method() == 'get') redirect('login'); //accessible only passing trough the login form
		$postData = $this->input->post();
		if(count($postData) == 0) redirect('login'); //if post data is empty
		
		if(isset($postData['remember'])){ // remember user?
			unset($postData['remember']);
		}
		$postData['password'] = md5($postData['password']);
		//load libraries, models, helpers
		$this->load->model('User', 'user');
		$user = $this->user->get($postData, 1);
		if($user == false){ //if user dosen't exists
			//TO DO add flash message
			redirect('login');
		}
		//create valid session for fabui
		$this->session->loggedIn = true;
		$this->session->user = $user;
		//load hardware settings
		$this->load->helpers('fabtotum_helper');
		$hardwareSettings = loadSettings('default');
		if($hardwareSettings['settings_type'] == 'custom') $hardwareSettings = loadSettings('custom');
		//save hardware settings on session
		$this->session->settings = $hardwareSettings;
		redirect('dashboard');
	}
	
	//log out
	public function out()
	{
		//destroy session and redirect to login	
		$this->session->loggedIn = false; 
		$this->session->unset_userdata('user');
		$this->session->unset_userdata('settings');
		redirect('login');
	}
 }
 
?>
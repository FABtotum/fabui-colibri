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
		
		$data['alert'] = $this->session->flashdata('alert');
		$this->content = $this->load->view('login/login_form', $data, true);
		$this->addJsInLine($this->load->view('login/login_js', '', true));
		$this->addJSFile('/assets/js/plugin/moment/moment.min.js'); //moment
		$this->loginLayout();
	}
	
	//do login
	public function doLogin()
	{
		if($this->input->method() == 'get') redirect('login'); //accessible only passing trough the login form
		$postData = $this->input->post();
		if(count($postData) == 0) redirect('login'); //if post data is empty
		
		if(isset($postData['remember'])){ // remember user?
			unset($postData['remember']);
			//TODO
		}
		if(isset($postData['browser-date'])){
			$this->load->helpers('os_helper');
			if(!isInternetAvaialable()){
				setSystemDate($postData['browser-date']);
			}
			unset($postData['browser-date']);
		}
		
		$postData['password'] = md5($postData['password']);
		//load libraries, models, helpers
		$this->load->model('User', 'user');
		$user = $this->user->get($postData, 1);
		$user['settings'] = json_decode($user['settings'], true);
		if($user == false){ //if user doesn't exists
			//TO DO add flash message
			redirect('login');
		}
		//create valid session for fabui
		$this->session->loggedIn = true;
		$this->session->user = $user;
		//load hardware settings
		$this->load->helpers('fabtotum_helper');
		$hardwareSettings = loadSettings('default');
		//save hardware settings on session
		$this->session->settings = $hardwareSettings;
		redirect('#dashboard');
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
	
	/**
	 * add new account page
	 */
	public function newAccount()
	{
		$this->content = $this->load->view('login/register_form', '', true);
		$this->addJsInLine($this->load->view('login/register_js', '', true));
		$this->loginLayout('register');
	}
	
	/**
	 * craete new account (post from register form)
	 */
	public function doNewAccount()
	{
		if($this->input->method() == 'get') redirect('login'); //accessible only passing trough the login form
		$postData = $this->input->post();
		if($postData['terms'] != 'on') redirect('login'); //You must agree with Terms and Conditions
		if($postData['passwordConfirm'] != $postData['password']) redirect('login'); //passwords needs to be equals
		//unset unuseful data
		unset($postData['passwordConfirm']);
		unset($postData['terms']);
		$postData['session_id'] = $this->session->session_id;
		$postData['settings'] = '{}';
		$postData['password'] = md5($postData['password']);
		//load libraries, models, helpers
		$this->load->model('User', 'user');
		$newUserID = $this->user->add($postData);
		$this->session->set_flashdata('alert', array('type' => 'alert-success', 'message'=> '<i class="fa fa-fw fa-check"></i>New user created successfully' ));
		redirect('login');
		
	}
	
	/**
	 * reset user password
	 */
	public function resetPassword()
	{
		//TODO
	}
 }
 
?>

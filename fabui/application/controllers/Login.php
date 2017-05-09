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
		
		//load configs
		$this->config->load('fabtotum');
		if(file_exists($this->config->item('autoinstall_file'))){
			redirect('install');
		}
		verify_keep_me_logged_in_cookie();
		$data['alert'] = $this->session->flashdata('alert');
		$this->load->helper('os_helper');
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
		
		$remember = false;
		if(isset($postData['remember'])){ // remember user?
			$remember= $postData['remember'] == 'on';
			unset($postData['remember']);
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
		
		if($user == false){ //if user doesn't exists
			//TO DO add flash message
			$this->session->mark_as_flash('alert');
			$this->session->set_flashdata('alert', array('type' => 'alert-danger', 'message'=> '<i class="fa fa-fw fa-warning"></i> '._("Please check your email or password") ));
			redirect('login');
		}
		
		if($remember == true){ //keep me logged in
			set_keep_me_looged_in_cookie($postData['email'], $postData['password']);
		}
		
		
		$user['settings'] = json_decode($user['settings'], true);
		if(!isset($user['settings']['language'])) $user['settings']['language'] = 'en_US';
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
		delete_cookie("fabkml");
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
		$this->load->helper('fabtotum_helper');
		$this->load->helper('os_helper');
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
		$this->session->set_flashdata('alert', array('type' => 'alert-success', 'message'=> '<i class="fa fa-fw fa-check"></i> '._("New user created successfully") ));
		redirect('login');
		
	}
	
	/**
	 * reset user password
	 */
	public function resetPassword($token)
	{
		$this->load->model('User', 'user');
		
		$user = $this->user->getByToken($token);
		if($user)
		{
			$this->load->helper('fabtotum_helper');
			$this->load->helper('os_helper');
			
			$data = array();
			$data['user'] = $user;
			$data['token'] = $token;
			
			$this->content = $this->load->view('login/reset_form', $data, true);
			$this->addJsInLine($this->load->view('login/reset_js', $data, true));
			$this->loginLayout('reset');
		}
		else
		{
			redirect('login');
		}
	}
	
	public function doReset()
	{
		$this->load->model('User', 'user');
		
		$token = $this->input->post('token');
		$new_password = $this->input->post('password');
		
		$user = $this->user->getByToken($token);
		if($user)
		{
			$user_settings = json_decode($user['settings'], true);
			$user_settings['token'] = '';
			
			$data_update['password'] = md5($new_password);
			$data_update['settings'] = json_encode($user_settings);
			
			$this->user->update($user['id'], $data_update);
			
			$_SESSION['new_reset'] = true;
		}
		redirect('login');
	}
	
	public function sendResetEmail()
	{
		$email = $this->input->post('email');
		
		$this->load->helper('fabtotum_helper');
		$this->load->model('User', 'user');

		$response = array();
		$response['user'] = false;
		$response['sent'] = false;
		
		$user = $this->user->getByEmail($email);
		if($user)
		{
			$response['user'] = true;
		}
		
		$sent = send_password_reset($email);
		$response['sent'] = $sent;
		
		$this->output->set_content_type('application/json')->set_output(json_encode($response));
	}
	
 }
 
?>

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
		$this->verifyCookie();
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
			$this->load->library('encrypt');
			$encryptData = array(
				'fab',
				$this->input->ip_address(),
				$this->input->server('SERVER_ADDR'),
				$postData['email']
			);
			$cookieName  = 'fabkml';
			$cookieValue = $this->encrypt->encode(implode(':',$encryptData)).':'.$postData['password'];
			$expire = (86400*7); //7days
			$this->input->set_cookie($cookieName, $cookieValue, $expire);
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
		$this->load->helper('cookie');
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
		//TODO
		
		$result = false;
		
		$user = $this->user->getByToken($token);
		if($user)
		{
			
		}
		
		$this->output->set_content_type('application/json')->set_output(json_encode($result));
	}
	
	public function sendResetEmail()
	{
		$email = '';
		$this->load->helper('fabtotum_helper');
		$result = send_password_reset($email);
		$this->output->set_content_type('application/json')->set_output(json_encode($result));
	}
	
	/**
	 * verify cookie credentials
	 * redirect to dashboard if cookie and credentials exist
	 */
	public function verifyCookie()
	{
		if($this->input->cookie('fabkml')){
			$this->load->library('encrypt');
			
			$cookieValueExploded = explode(':', $this->input->cookie('fabkml'));
			$userInfo = $this->encrypt->decode($cookieValueExploded[0]);
			$password = $cookieValueExploded[1];
			$userInfoExploed = explode(':', $userInfo);
			
			if($userInfoExploed[0] == 'fab' && 
			   $userInfoExploed[1] == $this->input->ip_address() && 
			   $userInfoExploed[2] == $this->input->server('SERVER_ADDR')){
				
			   	$this->load->model('User', 'user');
			   	$user = $this->user->get(array('email'=>$userInfoExploed[3], 'password'=>$password), 1);
			   	
			   	if($user){
			   		$user['settings'] = json_decode($user['settings'], true);
			   		if(!isset($user['settings']['language'])) $user['settings']['language'] = 'en_US';
			   		$this->session->loggedIn = true;
			   		$this->session->user = $user;
			   		//load hardware settings
			   		$this->load->helpers('fabtotum_helper');
			   		$hardwareSettings = loadSettings('default');
			   		//save hardware settings on session
			   		$this->session->settings = $hardwareSettings;
			   		redirect('#dashboard');
			   	}	
			}
		}
	}
 }
 
?>

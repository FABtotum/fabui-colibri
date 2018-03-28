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
		
		$fabid = $this->input->get('fabid');
		$data['alert'] = $this->session->flashdata('alert');
		$data['fabid'] = $fabid == 'no' ? false : true;
		$this->load->helper('os_helper');
		$this->content = $this->load->view('login/login_form', $data, true);
		$this->addJsInLine($this->load->view('login/login_js', $data, true));
		$this->addJSFile('/assets/js/plugin/jquery-validate/jquery.validate.min.js');
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
			//if(!isInternetAvaialable()){
			//setSystemDate($postData['browser-date']);
			//}
			unset($postData['browser-date']);
		}
		$postData['password'] = md5($postData['password']);
		//load libraries, models, helpers
		$this->load->model('User', 'user');
		$user = $this->user->get($postData, 1);
		
		//print_r($postData);
		
		//print_r($user); exit();
		
		if($user == false){ //if user doesn't exists
			//TO DO add flash message
			$this->session->mark_as_flash('alert');
			$this->session->set_flashdata('alert', array('type' => 'alert-danger', 'message'=> '<i class="fa fa-fw fa-warning"></i> '._("Please check your email or password") ));
			redirect('login?fabid=no');
		}
		
		if($remember == true){ //keep me logged in
			set_keep_me_looged_in_cookie($postData['email'], $postData['password']);
		}
		
		//update user last login column
		$update_data['last_login'] = $last_login;
		$update_data['session_id'] = $this->session->session_id;
		$this->user->update($user['id'], $update_data);
		
		$user['settings'] = json_decode($user['settings'], true);
		$user['last_login'] = $last_login;
		
		//load hardware settings
		$this->load->helper(array('fabtotum_helper', 'language_helper'));
		$hardwareSettings = loadSettings();
		
		$this->session->set_userdata('user', $user);
		$this->session->set_userdata('loggedIn', true);
		
		reload_myfabtotum();
		//save hardware settings on session
		$this->session->set_userdata('settings', $hardwareSettings);
		redirect('#dashboard');
	}
	
	/**
	 * 
	 */
	public function fabid()
	{
	    /**
	     * 
	     */
	    $data  = $this->input->post();
	    $fabid = $data['fabid'];
	    
	    /**
	     * 
	     */
	    $redirect      = '';
	    $alert_type    = '';
	    $alert_message = '';
	    
	    /**
	     * load helpers
	     */
	    $this->load->helper(array('fabtotum_helper', 'language_helper', 'os_helper'));
	    
	    /**
	     * check if printer is connected to internet 
	     */
	    if(isInternetAvaialable()){
	        /**
	         * init myfabotutum client
	         */
	        $init['fabid'] = $fabid;
	        $this->load->library('MyFabtotumClient', $init,  'myfabtotumclient');
	        
	        /**
	         * get if im the owner of the printer
	         */
	        $owner = $this->myfabtotumclient->im_owner();
	        
	        /**
	         * check if fabid exists
	         */
	        if($this->myfabtotumclient->is_fabid_registered()){
	            /**
	             *  check if fabid is authorized to use this printer
	             */
	            if($this->myfabtotumclient->can_use_local_printer()){
	                /**
	                 *  load users model
	                 */
	                $this->load->model('User', 'user');
	                $user = $this->user->getByFABID($fabid);
	            
	                /**
	                 * check if there's already a user with the same e-mail as fabid
	                 * if true then the users should be the same user (it happens after first install if user didnt connect to my.fabtotum.com before
	                 * clicking to "install"). This avoid to have duplicate users
	                 */

	                if(!$user){
	                    $userEmail = $this->user->getByEmail($fabid);
	                    if($userEmail) {
	                        $user = $userEmail;
	                    }
	                }   
	                
	                /**
	                 * if user exists
	                 */
	                if($user){
	                    
	                    /**
	                     * update user's info
	                     */
	                    $settings = json_decode($user['settings'], true);
	                    $settings['fabid'] = array(
	                        'email' => $fabid,
	                        'logged_in' => true
	                    );
	                    $update_data['last_login'] = date('Y-m-d H:i:s');
	                    $update_data['session_id'] = $this->session->session_id;
	                    $update_data['settings']   = json_encode($settings);
	                    /**
	                     * if user is printer's owner he should be also an administrator
	                     */
	                    if($owner == true){
	                        $update_data['role'] = 'administrator';
	                    }
	                    
	                    $this->user->update($user['id'], $update_data);
	                    $user['settings'] = $update_data['settings'];
	                    
	                }else{
	                    
	                    /**
	                     * user doesn't exists
	                     * crate a new "GUEST" user
	                     */
	                    $settings = array(
	                        'fabid' => array(
	                            'email'     => $fabid,
	                            'logged_in' => true
	                        ),
	                        'notifications' => array(
	                            'tasks' => array(
	                                'finish' => false,
	                                'pause' => false
	                            )
	                        )
	                    );
	                    $user['email']      = $fabid;
	                    $user['first_name'] = 'Guest';
	                    $user['last_name']  = 'Guest';
	                    $user['role']       = $owner == true ? 'administrator' : 'user';
	                    $user['session_id'] = $this->session->session_id;
	                    $user['last_login'] = date('Y-m-d H:i:s');
	                    $user['settings']   = json_encode($settings);
	                    /**
	                     * insert new user row
	                     */
	                    $new_user_id = $this->user->add($user);
	                    $user['id'] = $new_user_id;
	                }
	               
	                /**
	                 * create valid session for fabui
	                 */
	                $user['settings'] = json_decode($user['settings'], true);
	                
	                $this->session->set_userdata('user',     $user);
	                $this->session->set_userdata('loggedIn', true);
	                /**
	                 * reload myfabtotum background daemon
	                 */
	                reload_myfabtotum();
	                /**
	                 *  LOGIN OK
	                 */
	                
	                $redirect      = '#dashboard';
	                $alert_type    = '';
	                $alert_message = '';
	                
	            }else{
	                /**
	                 * fabid not authorized to use this printer
	                 * LOGIN KO
	                 */
	                $redirect      = 'login/?fabid=no';
	                $alert_type    = 'alert-danger';
	                $alert_message = '<i class="fa fa-fw fa-exclamation-triangle"></i> '._("You don't have the permission for this printer");
	            }
	        }else{
	            /**
	             * fabid doesn't exists or invalid
	             * LOGIN KO
	             */
	            $redirect      = 'login/?fabid=no';
	            $alert_type    = 'alert-danger';
	            $alert_message = '<i class="fa fa-fw fa-exclamation-triangle"></i> '._("FABID doesn't exists");
	            
	        }
	    }else{
	        /**
	         * printer not connected to internet
	         * LOGIN KO
	         */
	        $redirect      = 'login/?fabid=no';
	        $alert_type    = 'alert-danger';
	        $alert_message = '<i class="fa fa-fw fa-exclamation-triangle"></i> '._("Internet connection not available. Sign-in to your local access and then connect to internet");
	    }
	    
	    /**
	     * 
	     */
	    $this->session->set_flashdata('alert', array('type' => $alert_type,  'message'=> $alert_message));
	    redirect($redirect);
	}
	
	/*
	 * 
	 * 
	 */
	public function out()
	{
		delete_cookie("fabkml", $this->input->server('HTTP_HOST'));
		//destroy session and redirect to login
		$this->session->set_userdata('loggedIn', false);
		$this->session->unset_userdata('user');
		$this->session->unset_userdata('settings');
		redirect('login/?fabid=no');
	}
	
	/**
	 * add new account page
	 */
	public function newAccount()
	{
		$this->load->helper('fabtotum_helper');
		$this->load->helper('os_helper');
		$this->content = $this->load->view('login/register_form', '', true);
		$this->addJSFile('/assets/js/plugin/jquery-validate/jquery.validate.min.js');
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
		$postData['role']     = 'user';
		$postData['password'] = md5($postData['password']);
		//load libraries, models, helpers
		$this->load->model('User', 'user');
		$newUserID = $this->user->add($postData);
		$this->session->set_flashdata('alert', array('type' => 'alert-success', 'message'=> '<i class="fa fa-fw fa-check"></i> '._("New user created successfully") ));
		redirect('login/?fabid=no');
		
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
			$this->addJSFile('/assets/js/plugin/jquery-validate/jquery.validate.min.js');
			$this->loginLayout('reset');
		}
		else
		{
			redirect('login?fabid=no');
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
		redirect('login?fabid=no');
	}
	
	public function sendResetEmail($email = '')
	{
	    if($email == ''){
	        
	        $email = $this->input->post('email');
	    }
	    
	    $email = urldecode($email);
		$this->load->helper('fabtotum_helper');
		$this->load->model('User', 'user');

		$response = array();
		$response['user'] = false;
		$response['sent'] = false;
		
		$user = $this->user->getByEmail($email);
		if($user)
		{
			$response['user'] = true;
			
			
			$uid        = $user['id'];
			$first_name = $user['first_name'];
			$last_name  = $user['last_name'];
			
			$token = md5($uid . '-' . $email . '-' . time());
			
			$user_settings = json_decode($user['settings'], true);
			$user_settings['token'] = $token;
			
			$data_update['settings'] = json_encode($user_settings);
			$this->user->update( $uid, $data_update);
			$complete_url = site_url().'login/resetPassword/'.$token;
			
			$key_unicode = "\xF0\x9F\x94\x91";
			$subject = $key_unicode.' '. _("Password Reset");
			
			$data['user'] = $user;
			$data['complete_url']  = $complete_url;
			
			$this->content = $this->load->view('std/email/reset_password', $data, true );
			$page = $this->layoutEmail(true);
			
			$sent = send_via_noreply($user['email'], $user['first_name'], $user['last_name'],  $subject, $page);
		}
		
		//$sent = send_password_reset($email);
		$response['sent'] = $sent;
		
		$this->output->set_content_type('application/json')->set_output(json_encode($response));
	}
	
 }
 
?>

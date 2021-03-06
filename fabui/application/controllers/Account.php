<?php
/**
 * 
 * @author Krios Mane
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */
 defined('BASEPATH') OR exit('No direct script access allowed');
 
 class Account extends FAB_Controller {
 	
	public function index(){
		
		//load libraries, helpers, model, config
		$this->load->library('smart');
		$this->load->helper(array('fabtotum_helper', 'language_helper', 'form'));
		$this->config->load('fabtotum');
		
		//reload user info
		$this->load->model('User', 'user');
		$user = $this->user->get($this->session->user['id'], 1);
		$user['settings'] = json_decode($user['settings'], true);

		$this->session->set_userdata('user', $user);
		//print_r($this->session->user);
		//print_r($user);
		//$this->session->user = $user;
		
		
		
		$data['user'] = $user;
		$data['has_image'] = isset($user['settings']['image']['url']) && $user['settings']['image']['url'] != '';
		$data['fabid_active'] = $this->config->item('fabid_active') == 1;
		
		//main page widget
		$widgetOptions = array(
				'sortable' => false, 'fullscreenbutton' => false,'refreshbutton' => false,'togglebutton' => false,
				'deletebutton' => false, 'editbutton' => false, 'colorbutton' => false, 'collapsed' => false
		);
		
		$headerToolbar = '
			<ul class="nav nav-tabs pull-right">
				<li class="active"><a data-toggle="tab" href="#account-tab"> ' . _("Edit profile"). '</a></li>
				<li><a data-toggle="tab" href="#password-tab"> '._("Change password").'</a></li>
                <li><a data-toggle="tab" href="#notifications-tab"> '._("Notification settings").'</a></li>
			</ul>';
		
		$widgeFooterButtons = $this->smart->create_button( _("Save"), 'primary')->attr(array('id' => 'save'))->attr('data-action', 'exec')->icon('fa-save')->print_html(true);
		
		$widget         = $this->smart->create_widget($widgetOptions);
		$widget->id     = 'user-widget';
		$widget->header = array('icon' => 'fa-user', "title" => "<h2>". _("Your account"). "</h2>", 'toolbar'=>$headerToolbar);
		$widget->body   = array('content' => $this->load->view('account/widget', $data, true ), 'class'=>'', 'footer'=>$widgeFooterButtons);
		
		$data['widget'] = $widget->print_html(true);
		
		$this->addJSFile('/assets/js/plugin/jquery-validate/jquery.validate.min.js');
		$this->addJSInLine($this->load->view('account/js', $data, true));
		
		$this->content = $this->load->view('account/index', $data, true );
		$this->view();
	}
	/**
	 * 
	 * 
	 */
	public function saveUser($userID, $data = array())
	{
	    
		//load libraries, helpers, model, config
		$this->load->helper(array('utility_helper', 'fabtotum_helper'));
		$this->load->model('User', 'user');
		
		$user = $this->user->get($userID, 1);
		
		$user['settings'] = json_decode($user['settings'], true);
		
		if(empty($data)){
			if($this->input->method() == 'post'){
				$requesData = $this->input->post();
			}else{
				$requesData = $this->input->get();
			}	
			$data = arrayFromPost($requesData);
		}
		
		/**
		 * if an image is uploaded
		 */
		if($_FILES['profile-image']['name'] != ''){
		    /**
		     * load helper, configs
		     */
		    $this->load->helper(array('file_helper', 'file'));
		    $this->config->load('upload');
		    /**
		     *  get file extension to save the file in the correct directory
		     */
		    $fileExtension = getFileExtension($_FILES['profile-image']['name']);
		    /**
		     * preaprea configs for upload library
		     * crate folder extension if doesn't exist
		     */
		    $upload_path        = $this->config->item('upload_path');
		    $folder_destination = $upload_path . $fileExtension . '/';
		    if(!file_exists($folder_destination)) createFolder($folder_destination);
		    
		    /**
		     * init upload library
		     */
		    $config['upload_path']      = $upload_path.$fileExtension;
		    $config['file_ext_tolower'] = true;
		    $config['remove_spaces']    = true;
		    $config['allowed_types']    = 'gif|jpg|png';  
		    $this->load->library('upload', $config);
		    /**
		     * do upload
		     */
		    if($this->upload->do_upload('profile-image')) {
		        /**
		         * retrieve data from upload
		         */
		        $upload_data = $this->upload->data();
		        
		        /**
		         * remove existing image
		         */
		        if(isset($user['settings']['image']['full_path']) && $user['settings']['image']['full_path'] != ''){
		            unlink($user['settings']['image']['full_path']);
		        }
		        $user['settings']['image'] = array(
		            'full_path' => $upload_data['full_path'],
		            'url' => '/uploads/'.$fileExtension.'/'.$upload_data['file_name']
		        );
		    }
		    
		}else{
		   /**
		    * nothing for now
		    */
		}
		
		$update = array_replace_recursive($user, $data);
		
		//preare settings for db insert
		if(isset($update['settings'])){
			$update['settings'] = json_encode($update['settings']);
		}
		
		//update user db
		$this->user->update($userID, $update);
		//get all user info
		$user = $this->user->get($userID, 1);
		$user['settings'] = json_decode($user['settings'], true);
		
		//$this->session->user = $user;
		$this->session->set_userdata('user', $user);
		
		//$this->output->set_content_type('application/json')->set_output(json_encode( $this->session->user ));
		redirect("#account");
		
	}
	
	/**
	 * save new password
	 */
	public function saveNewPassword($userId = '')
	{
	    $this->load->model('User', 'user');
	    
	    if($userId == ''){
	        $userId = $this->session->user['id'];
	    }
	    
	    $user = $this->user->get($userId, 1);
	    $data = $this->input->post();
	    
	    if(!$user){
	        $result['status'] = false;
	        $result['message'] = _("Invalid user");
	        $this->output->set_content_type('application/json')->set_output(json_encode($result));
	    }
	    
	    if(md5($data['old_password']) != $user['password']){
	        $result['status'] = false;
	        $result['message'] = _("Old password was not recognized");
	        $this->output->set_content_type('application/json')->set_output(json_encode($result));
	    }
	    
	    $updateData['password'] =  md5($data['new_password']);
	    //update user new password
	    $this->user->update($userId, $updateData);
	    
	    $result['status'] = true;
	    $result['message'] = _("Your password has been changed successfully");
	    $this->output->set_content_type('application/json')->set_output(json_encode($result));
	    
	}
	
	/**
	 * 
	 */
	public function saveNotifications($userId = '')
	{
	    //load libraries, helpers, model, config
	    $this->load->helper(array('utility_helper', 'fabtotum_helper'));
	    $this->load->model('User', 'user');
	    
	    if($userId == ''){
	        $userId = $this->session->user['id'];
	    }
	    
	    $user = $this->user->get($userId, 1);
	    
	    $settings = json_decode($user['settings'], true);
	    
	    $postData = $this->input->post();
	    $data     = arrayFromPost($postData);
	    
	    $settings['notifications'] = $data;
	    
	    //update session
	    $user['settings'] = $settings;
	    $this->session->set_userdata('user', $user);
	    
	    
	    $updateData['settings'] =  json_encode($settings);
	    //update notifications db record
	    $this->user->update($userId, $updateData);
	    
	    $result['status'] = true;
	    $result['message'] = _("Notifications settings has been changed successfully");
	    $this->output->set_content_type('application/json')->set_output(json_encode($result));
	    
	    
	}
 }
 
?>

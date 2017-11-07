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
		//$this->session->user = $user;
		$data['user'] = $user;
		$data['fabid_active'] = $this->config->item('fabid_active') == 1;
		
		//main page widget
		$widgetOptions = array(
				'sortable' => false, 'fullscreenbutton' => false,'refreshbutton' => false,'togglebutton' => false,
				'deletebutton' => false, 'editbutton' => false, 'colorbutton' => false, 'collapsed' => false
		);
		
		$headerToolbar = '
			<ul class="nav nav-tabs pull-right">
				<li class="active"><a data-toggle="tab" href="#account-tab"> ' . _("Account"). '</a></li>
				<li><a data-toggle="tab" href="#settings-tab"> '._("Settings").'</a></li>
			</ul>';
		
		$widgeFooterButtons = $this->smart->create_button( _("Save"), 'primary')->attr(array('id' => 'save'))->attr('data-action', 'exec')->icon('fa-save')->print_html(true);
		
		$widget         = $this->smart->create_widget($widgetOptions);
		$widget->id     = 'user-widget';
		$widget->header = array('icon' => 'fa-user', "title" => "<h2>". _("Your account"). "</h2>");
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
		
		if(empty($data)){
			if($this->input->method() == 'post'){
				$requesData = $this->input->post();
			}else{
				$requesData = $this->input->get();
			}	
			$data = arrayFromPost($requesData);
		}
		
		//if locale
		if(isset($data['settings']['locale'])){
			$hwSettings = loadSettings();
			$hwSettings['locale'] = $data['settings']['locale']; //set locale to settings.json
			saveSettings($hwSettings);
		}
		
		//preare settings for db insert
		if(isset($data['settings'])){
			$data['settings'] = json_encode($data['settings']);
		}
		
		//update user db
		$this->user->update($userID, $data);
		//get all user info
		$user = $this->user->get($userID, 1);
		$user['settings'] = json_decode($user['settings'], true);
		
		//$this->session->user = $user;
		$this->session->set_userdata('user', $user);
		
		$this->output->set_content_type('application/json')->set_output(json_encode( $this->session->user ));
		
	}
 }
 
?>

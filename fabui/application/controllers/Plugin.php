<?php
/**
 * 
 * @author Krios Mane
 * @author Daniel Kesler
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */
 defined('BASEPATH') OR exit('No direct script access allowed');
 
 class Plugin extends FAB_Controller {
 	
 	public function online()
 	{
		$this->index('online');
	}
 	
	public function index($tab = 'installed')
	{
		//load libraries, helpers, model
		$this->load->library('smart');
		$this->load->helper('form');
		$this->load->helper('plugin_helper');

		$postData = $this->input->post();

		$data = array();
		$data['installed_plugins'] = getInstalledPlugins();
		
		$data['user'] = $this->session->user;
		
		unset($data['user']['session_id']);
		unset($data['user']['id']);
		unset($data['user']['password']);
		unset($data['user']['settings']);
		
		$widgetOptions = array(
			'sortable'     => false, 'fullscreenbutton' => true,  'refreshbutton' => false, 'togglebutton' => false,
			'deletebutton' => false, 'editbutton'       => false, 'colorbutton'   => false, 'collapsed'    => false
		);
		
		$widgeFooterButtons = '';
		
		$is_installed_active = ($tab=='installed')?"active":"";
		$is_online_active = ($tab=='online')?"active":"";
		
		$data['online_is_active'] = $is_online_active;
		$data['installed_is_active'] = $is_installed_active;
		
		$headerToolbar = '
			<ul class="nav nav-tabs pull-right">
				<li class="'.$is_installed_active.'"><a data-toggle="tab" href="#installed-tab"> '._("Installed").'</a></li>
				<li class="'.$is_online_active.'"><a data-toggle="tab" href="#online-tab"> '._("Online").'</a></li>
				<li><a data-toggle="tab" href="#add-new-tab"><i class="fa fa-upload""></i> '._("Upload").'</a></li>
				<li><a data-toggle="tab" href="#create-new-tab"><i class="fa fa-file-code-o" aria-hidden="true"></i> '._("Create New").'</a></li>
			</ul>
			
			';
		
		$widget         = $this->smart->create_widget($widgetOptions);
		$widget->id     = 'main-widget-head-installation';
		$widget->header = array('icon' => 'fa-plug', "title" => "<h2>"._("Plugins")."</h2>", 'toolbar'=>$headerToolbar);
		$widget->body   = array('content' => $this->load->view('plugin/main_widget', $data, true ), 'class'=>'no-padding', 'footer'=>$widgeFooterButtons);

		$this->addJSFile('/assets/js/plugin/jquery-validate/jquery.validate.min.js'); //validator
		$this->addJSFile('/assets/js/plugin/inputmask/jquery.inputmask.bundle.js');
		$this->addJsInLine($this->load->view('plugin/js', $data, true));
		$this->content = $widget->print_html(true);
		$this->view();
	}
	
	public function doUpload()
	{
		//load helpers
		$this->load->helper('file');
		$this->load->helper('fabtotum');
		$this->load->helper('plugin_helper');
		$this->load->helper('utility_helper');
		
		$upload_config['upload_path']   = '/tmp/fabui/';
		$upload_config['allowed_types'] = 'zip';
		
		$this->load->library('upload', $upload_config);
		
		if($this->upload->do_upload('plugin-file')){ //do upload
			$github = false;
			$upload_data = $this->upload->data();
		
			//check if is a master file from github
			if(strpos($upload_data['orig_name'], '-master') !== false) {	
				$github = true;
				//rename file
				shell_exec('sudo mv '.$upload_data['full_path'].' '.str_replace('-master', '', $upload_data['full_path']));
				//update values
				$upload_data['file_name']   = str_replace('-master', '', $upload_data['file_name']);
				$upload_data['full_path']   = str_replace('-master', '', $upload_data['full_path']);
				$upload_data['raw_name']    = str_replace('-master', '', $upload_data['raw_name']);
				$upload_data['client_name'] = str_replace('-master', '', $upload_data['client_name']);
			}
			$result = managePlugin('install', $upload_data['full_path']);
			$data['result'] = $result;
			$data['installed'] = endsWith($result, "ok\n") == 1;
			$data['file_name'] = $upload_data['file_name'];
		}else{
			$data['error'] = strip_tags($this->upload->display_errors());
		}
		$this->output->set_content_type('application/json')->set_output(json_encode($data));
		
	}
	
	public function manage($action, $plugin)
	{
		$this->load->model('Plugins', 'plugins');
		$this->load->helper('plugin_helper');
		
		$installed_plugins = getInstalledPlugins();
		$allowed_actions = array('remove', 'activate', 'deactivate', 'update');
		$result = false;
		
		if( array_key_exists($plugin, $installed_plugins) || ($action == 'update')  )
		{
			$this->content  = json_encode($action);
			if( in_array($action, $allowed_actions) )
			{
				managePlugin($action, $plugin);
				$result = true;
			}
		}
		
		$this->output->set_content_type('application/json')->set_output(json_encode($result));
	}
	
	public function download($slug = '')
	{
		if($slug == '')
			return;
		
		$this->load->library('zip');
		$this->config->load('fabtotum');
		$tmpPath = $this->config->item('temp_path');
		$plugin_dir = $tmpPath.$slug;
		
		$this->zip->read_dir($plugin_dir, false, $tmpPath);
		
		//~ shell_exec('rm -rf '.$plugin_dir);
		
		$this->zip->download($slug.'.zip');
	}
	
	public function create()
	{
		//get data from post
		$postData = $this->input->post();
		
		$this->load->helper('plugin_helper');
		$this->load->helpers('utility_helper');
		$this->load->helpers('fabtotum_helper');
		$this->load->library('zip');
		
		//create settings array
		$plugin_meta = arrayFromPost($postData['meta']);
		
		$result = array();
		
		$plugin_dir = createPlugin($plugin_meta);
		
		$result['success'] = true;
		$result['meta'] = $plugin_meta;
		$result['slug'] = $plugin_meta['plugin']['slug'];

		/*$filename = realpath($filename);
		$ext = pathinfo($filename, PATHINFO_EXTENSION);
		$_file_name = 'raspicam.'.$ext;
		$_data      = file_get_contents($filename);
		
		
		force_download($filename, NULL);*/
		
		$this->output->set_content_type('application/json')->set_output(json_encode($result));
	}

	public function getOnline()
	{
		$this->load->helper('plugin_helper');
		$this->output->set_content_type('application/json')->set_output(json_encode(getOnlinePlugins()));
	}

 }
 
?>

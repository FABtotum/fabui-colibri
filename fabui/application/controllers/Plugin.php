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
 	
	public function index()
	{
		//load libraries, helpers, model
		$this->load->library('smart');
		$this->load->helper('form');
		$this->load->helper('plugin_helper');
		
		//~ //data
		$data = array();
		$data['installed_plugins'] = getInstalledPlugins();
		
		$widgetOptions = array(
			'sortable'     => false, 'fullscreenbutton' => true,  'refreshbutton' => false, 'togglebutton' => false,
			'deletebutton' => false, 'editbutton'       => false, 'colorbutton'   => false, 'collapsed'    => false
		);
		
		$widgeFooterButtons = '';
		
		$headerToolbar = '<div class="widget-toolbar" role="menu">
		<a class="btn btn-success" href="plugin/upload"><i class="fa fa-plus"></i> Add New Plugin </a>
		</div>';
		
		$widget         = $this->smart->create_widget($widgetOptions);
		$widget->id     = 'main-widget-head-installation';
		$widget->header = array('icon' => 'fa-toggle-down', "title" => "<h2>Plugins</h2>", 'toolbar'=>$headerToolbar);
		$widget->body   = array('content' => $this->load->view('plugin/main_widget', $data, true ), 'class'=>'no-padding', 'footer'=>$widgeFooterButtons);

		$this->addJsInLine($this->load->view('plugin/js', $data, true));
		$this->content = $widget->print_html(true);
		$this->view();
	}
	
	public function upload()
	{
		$this->load->library('smart');
		$this->load->helper('form');
		$this->load->helper('plugin_helper');
		
		$data = array();
		
		//main page widget
		$widgetOptions = array(
			'sortable' => false, 'fullscreenbutton' => true,'refreshbutton' => false,'togglebutton' => false,
			'deletebutton' => false, 'editbutton' => false, 'colorbutton' => false, 'collapsed' => false
		);
		
		$widgeFooterButtons = '';
		
		$headerToolbar = '<div class="widget-toolbar" role="menu">
		<a class="btn btn-default" href="plugin"><i class="fa fa-arrow-left"></i> Back </a>
		</div>';
		
		$widget         = $this->smart->create_widget($widgetOptions);
		$widget->id     = 'plugin-upload-widget';
		$widget->header = array('icon' => 'fa-toggle-down', "title" => "<h2>Upload plugin</h2>", 'toolbar'=>$headerToolbar);
		$widget->body   = array('content' => $this->load->view('plugin/upload_widget', $data, true ), 'class'=>'no-padding', 'footer'=>$widgeFooterButtons);
		
		$this->addJsInLine($this->load->view('plugin/upload_js', $data, true));
		$this->content = $widget->print_html(true);
		$this->view();
	}
	
	public function doUpload()
	{
		//load helpers
		$this->load->helper('file');
		$this->load->helper('fabtotum');
		$this->load->helper('plugin_helper');
		
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
			managePlugin('install', $upload_data['full_path']);
			$data['installed'] = true;
			$data['file_name'] = $upload_data['file_name'];
		
			//shell_exec('sudo rm -rvf '.$upload_data['full_path']);
			//shell_exec('sudo rm -rvf '.$upload_data['file_path'].$upload_data['raw_name']);
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
		$allowed_actions = array('remove', 'activate', 'deactivate');
		
		if( array_key_exists($plugin, $installed_plugins) )
		{
			$this->content  = json_encode($action);
			if( in_array($action, $allowed_actions) )
			{
				managePlugin($action, $plugin);
				
				switch($action)
				{
					case 'activate':
						$this->plugins->activate($plugin);
						break;
					case 'remove':
					case 'deactivate':
						$this->plugins->deactivate($plugin);
						break;
				}
			}
		}
		
		$this->output->set_content_type('application/json')->set_output(json_encode(true));
	}

 }
 
?>

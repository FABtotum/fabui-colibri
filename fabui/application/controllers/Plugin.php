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
		<a class="btn btn-success" href="plugin/add"><i class="fa fa-plus"></i> Add New Plugin </a>
		</div>';
		
		$widget         = $this->smart->create_widget($widgetOptions);
		$widget->id     = 'main-widget-head-installation';
		$widget->header = array('icon' => 'fa-toggle-down', "title" => "<h2>Plugins</h2>", 'toolbar'=>$headerToolbar);
		$widget->body   = array('content' => $this->load->view('plugin/main_widget', $data, true ), 'class'=>'no-padding', 'footer'=>$widgeFooterButtons);

		$this->addJsInLine($this->load->view('plugin/js', $data, true));
		$this->content = $widget->print_html(true);
		$this->view();
	}
	
	public function add()
	{
		$this->content = 'test';
		
		$this->view();
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
				$this->content =  managePlugin($action, $plugin);
				
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
		
		redirect('plugin');
	}

 }
 
?>

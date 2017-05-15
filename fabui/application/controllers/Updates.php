<?php
/**
 * 
 * @author Krios Mane
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 *  
 */
 defined('BASEPATH') OR exit('No direct script access allowed');
 
 class Updates extends FAB_Controller {
 	
 	protected $runningTask = false;
 	
 	function __construct(){
 		parent::__construct();
 		if(!$this->input->is_cli_request()){
 			$this->load->model('Tasks', 'tasks');
 			//$this->tasks->truncate();
 			$this->runningTask = $this->tasks->getRunning('update');
 		}
 	}
 	
 	public function index()
 	{
 		//load helpers
 		$this->load->helper('layout');
 		$this->load->library('smart');
 		
 		$data = array();
 		$data['runningTask'] = $this->runningTask;
 		
 		$widgetOptions = array(
 				'sortable' => false, 'fullscreenbutton' => true,'refreshbutton' => false,'togglebutton' => false,
 				'deletebutton' => false, 'editbutton' => false, 'colorbutton' => false, 'collapsed' => false
 		);
 		
 		$widget = $this->smart->create_widget($widgetOptions);
 		$widget->id = 'updates-widget';
 		$widget->class = 'well';
 		
 		$widget->body   = array('content' => $this->load->view('updates/index/widget', $data, true ));
 		
 		//$this->content = $this->load->view('updates/index/widget', $data, true );
 		$this->content  = $widget->print_html(true);
 		$this->addCSSInLine('<style>.table-forum tr td>i {padding-left:0px};.checkbox{margin-top:0px !important;}</style>');
 		$this->addJsInLine($this->load->view('updates/index/js','', true));
 		$this->addCssFile('/assets/css/updates/style.css');
 		$this->addJSFile('/assets/js/plugin/bootstrap-progressbar/bootstrap-progressbar.min.js'); //datatable
 		$this->addJSFile('/assets/js/plugin/showdown/showdown.min.js');
 		$this->view();
 	}
	
	/**
	 * get update status (local and remote)
	 */
	function updateStatus()
	{
		//load helpers, config
		$this->load->helper('update_helper');
		$this->load->helper('file');
		$this->config->load('fabtotum');
		//get remote bundles status
		$bundlesStatus = getUpdateStatus();
		write_file($this->config->item('updates_json_file'), json_encode($bundlesStatus));
		$this->output->set_content_type('application/json')->set_output(json_encode($bundlesStatus));
	}
	/**
	 * start update
	 */
	function startUpdate()
	{
		//load helpers
		$this->load->helpers('fabtotum_helper');
		//load model
		$this->load->model('Tasks', 'tasks');
		//get data from post
		$data = $this->input->post();
		$bundles = $this->input->post('bundles');
		$firmware = $data['firmware'];
		$boot = $data['boot'];
		
		//add task record to db
		$taskData = array(
			'user'       => $this->session->user['id'],
			'controller' => 'updates',
			'type'       => 'update',
			'status'     => 'running',
			'start_date' => date('Y-m-d H:i:s')
		);
		$taskId   = $this->tasks->add($taskData);
		
		$updateArgs = array(
				'-T' => $taskId,
		);
		if($bundles)  $updateArgs['-b'] = implode(',', $bundles);
		if($firmware == "true") $updateArgs['--firmware'] = '';
		if($boot == "true") $updateArgs['--boot'] = '';

		startPyScript('update.py', $updateArgs, true, true, 'update_task.log');
		$this->output->set_content_type('application/json')->set_output(json_encode(array('start' => true, 'id_task' => $taskId)));
	}
	
	/**
	 * 
	 */
	public function notifications()
	{
		//load helpers, config
		$this->load->helper('update_helper');
		$this->config->load('fabtotum');
		$updatesJSON = json_decode(file_get_contents($this->config->item('updates_json_file')), true);
		
		if($updatesJSON['update']['available']){
			
			$bundleNumber = $updatesJSON['update']['bundles'];
			$pluginNumber = $updatesJSON['update']['plugins'];
			$html  = '<ul class="notification-body">';
			
			if($bundleNumber > 0){
				$html .= '<li>';
				$html .= '<span class="padding-10">';
				$html .= '<em class="badge padding-5 no-border-radius bg-color-blueLight pull-left margin-right-5"><i class="fa fa-refresh fa-fw fa-2x"></i></em>';
				$text = $bundleNumber == 1 ? _("1 new update is available") : $bundleNumber.' '. _("new updates are available");
				$html .= '<span> '.$text.' <a class="display-normal" href="'.site_url('#updates').'"><strong>'._("Update now").'</strong></a> </span>';
				$html .= '</span>';
				$html .= '</li>';
			}
			if($pluginNumber > 0){
				$html .= '<li>';
				$html .= '<span class="padding-10">';
				$html .= '<em class="badge padding-5 no-border-radius bg-color-blueLight pull-left margin-right-5"><i class="fa fa-refresh fa-fw fa-2x"></i></em>';
				$text = $pluginNumber == 1 ? _("1 new plugin update is available") : $pluginNumber.' '. _("new plugin updates are available");
				$html .= '<span> '.$text.' <a class="display-normal" href="'.site_url('#plugin/online').'"><strong>'._("Update now").'</strong></a> </span>';
				$html .= '</span>';
				$html .= '</li>';
			}
			
			if($updatesJSON['update']['firmware']){
				$html .= '<li>';
				$html .= '<span class="padding-10">';
				$html .= '<em class="badge padding-5 no-border-radius  bg-color-green pull-left margin-right-5"><i class="fa fa-microchip fa-fw fa-2x"></i></em>';
				$text = _("A new firmware update is available");
				$html .= '<span> '.$text.' <a class="display-normal" href="'.site_url('#updates').'"><strong>'._("Update now").'</strong></a> </span>';
				$html .= '</span>';
				$html .= '</li>';
			}
			
			if($updatesJSON['update']['boot']){
				$html .= '<li>';
				$html .= '<span class="padding-10">';
				$html .= '<em class="badge padding-5 no-border-radius  bg-color-pink pull-left margin-right-5"><i class="fa fa-rocket fa-fw fa-2x"></i></em>';
				$text = _("A new boot update is available");
				$html .= '<span> '.$text.' <a class="display-normal" href="'.site_url('#updates').'"><strong>'._("Update now").'</strong></a> </span>';
				$html .= '</span>';
				$html .= '</li>';
			}
			
			$html .= '</ul>';
			echo $html;
			
		}else{
			echo '<div class="alert alert-transparent">
					<h4 class="text-center">'._("Great! Your FABtotum Personal Fabricator is up to date").'</h4>
				</div>';
		}
	}
	
	function getChangelog($subtype, $name = '', $version = '')
	{
		$this->load->helper('os_helper');
		$this->config->load('fabtotum');
		$bundlesEndpoint = $this->config->item('colibri_endpoint').getArchitecture();
		$fwEndpoint      = $this->config->item('firmware_endpoint').'fablin/atmega1280/';
		
		switch($subtype)
		{
			case "bundle":
				$changelog_url = $bundlesEndpoint.'/bundles/'.$name.'/changelog.json';
				$content = getRemoteFile($changelog_url, true);
				$json = json_decode($content, 1);
				if($json)
				{
					if( array_key_exists($version, $json) )
					{
						echo $json[$version];
					}
				}
				else
				{
					echo "No data";
				}
				break;
			case "boot":
				$changelog_url = $bundlesEndpoint.'/boot/changelog.json';
				$content = getRemoteFile($changelog_url, true);
				$json = json_decode($content, 1);
				if($json)
				{
					if( array_key_exists($version, $json) )
					{
						echo $json[$version];
					}
				}
				else
				{
					echo "No data";
				}
				break;
			case "firmware":
				$changelog_url = $fwEndpoint.'/latest/changelog.txt';
				break;
			default:
				return;
		}
	}

 }
 
?>

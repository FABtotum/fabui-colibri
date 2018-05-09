<?php
/**
 * 
 * @author FABteam
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */
defined('BASEPATH') or exit('No direct script access allowed');

class Cam2 extends FAB_Controller
{
	/**
	 */
	function __construct()
	{
		parent::__construct();
		session_write_close(); // avoid freezing page
	}

	/**
	 */
	public function index()
	{
		$controller_base = 'cam2';
		
		$this->load->library('smart');
		$this->load->helper(array(
			'form',
			'fabtotum_helper',
			'language_helper',
			'os_helper',
			'cam_helper',
			'utility_helper'
		));
		
		$language = getCurrentLanguage();
		$this->load->config('cam');
		
		$data = array();
		
		$widgetOptions = array(
			'sortable' => false,
			'fullscreenbutton' => true,
			'refreshbutton' => false,
			'togglebutton' => false,
			'deletebutton' => false,
			'editbutton' => false,
			'colorbutton' => false,
			'collapsed' => false
		);
		
		$headerToolbar = '
			<ul class="nav nav-tabs pull-right">
				<li class="active"><a data-toggle="tab" href="#cam-apps-tab"> ' . _('Applications') . '</a></li>
				<li><a data-toggle="tab" href="#cam-history-tab"> ' . _('History') . '</a></li>
				<li><a data-toggle="tab" href="#subscriptions-tab"> ' . _('Subscriptions') . '</a></li>
			</ul>';
		
		$widgeFooterButtons = '';
		
		// init vars
		$data['internet'] = isInternetAvaialable();
		$data['isFabid'] = $this->_isFabid();
		$data['subscription_exists'] = subscription_exists();
		$data['installed_head'] = getInstalledHeadInfo();
		
		$laser_heads = loadLaserHeads();
		foreach ($laser_heads as $head) {
			$data["laser_heads"][$head['fw_id']] = $head['name'];
		}
		
		// cam-server
		$data['cam'] = array();
		$data['cam']['groups'] = array(
			0 => 'laser',
			1 => 'milling',
			2 => 'prism',
			3 => 'fdm'
		);
		
		$data['cam']['apps'] = array();
		
		if ($data['subscription_exists']) {
			$data['subscription_code'] = load_subscription();
			
			$init = array();
			$init['subscription'] = $data['subscription_code']['code'];
			
			if( $data['isFabid'] )
			{
				$init['fabid'] = $this->_fabId();
				$this->load->library('ApiFabtotumClient', $init,  'apifabtotum');
				$data['cam']['apps'] = $this->apifabtotum->apps;
				
				foreach($data['cam']['apps'] as $appId => $app)
				{
					foreach($app['config'] as $idx => $cfg)
					{
						$cfg_file = $this->config->item('userdata_path') . '/cam/apps/' . $app['id'] . '/config/' . $cfg['id'] . '.json';
						$cfg_data = json_decode(file_get_contents($cfg_file), true);
						$data['cam']['apps'][$appId]['config'][$idx]['data'] = flatten_array($cfg_data);
					}
				}
			}
			
		}
		
		// tabs
		$data['apps_tab'] = $this->load->view($controller_base . '/apps_tab', $data, true);
		$data['history_tab'] = $this->load->view($controller_base . '/history_tab', $data, true);
		$data['subscriptions_tab'] = $this->load->view($controller_base . '/subscriptions_tab', $data, true);
		
		$widget = $this->smart->create_widget($widgetOptions);
		$widget->id = 'main-widget-head-installation';
		$widget->header = array(
			'icon' => 'fabui-edit-file',
			"title" => "<h2>CAM toolbox <i>beta</i></h2>",
			'toolbar' => $headerToolbar
		);
		$widget->body = array(
			'content' => $this->load->view($controller_base . '/main_widget', $data, true),
			'class' => ''
		); // 'footer'=>$widgeFooterButtons
		
		//~ $this->addJsInLine($this->load->view('cam/help/inputs', $data, true));
		$this->addJsInLine($this->load->view($controller_base . '/subscription_js', $data, true));
		$this->addJsInLine($this->load->view($controller_base . '/js', $data, true));
		$this->content = $widget->print_html(true);
		$this->addCssFile('/assets/css/cam/style.css');
		// javascript assets
		$this->addJSFile('/assets/js/plugin/dropzone/dropzone.js'); // dropzpone
		$this->addJSFile('/assets/js/plugin/ion-slider/ion.rangeSlider.min.js');
		$this->addJSFile('/assets/js/plugin/spectrum/spectrum.js');
		$this->addCssFile('/assets/js/plugin/spectrum/spectrum.css');
		$this->addJSFile('/assets/js/plugin/moment/moment.min.js');
		$this->addJSFile('/assets/js/plugin/jquery-validate/jquery.validate.min.js');
		
		$this->addCssFile('/assets/js/plugin/OwlCarousel2-2.2.1/owl.carousel.min.css');
		$this->addCssFile('/assets/js/plugin/OwlCarousel2-2.2.1/owl.theme.default.css');
		$this->addJSFile('/assets/js/plugin/OwlCarousel2-2.2.1/owl.carousel.min.js');
		
		$this->addJSFile('/assets/js/jquery-sortable.js');
		
		$this->view();
	}
	
	/**
	 * 
	 */
	private function _isFabid()
	{
		return (isset($this->session->user['settings']['fabid']['logged_in']) && ($this->session->user['settings']['fabid']['logged_in'] == true));
	}
	
	private function _fabId()
	{
		if( ($this->session->user['settings']['fabid']['logged_in'] !== null)
			&& ($this->session->user['settings']['fabid']['logged_in'] == true) )
		{
			return $this->session->user['settings']['fabid']['email'];
		}
		
		return null;
	}
	
	/**
	 */
	public function subscription($action, $code = "")
	{
		$this->load->helper(array('cam_helper'));
		
		switch ($action) {
			case 'active':
				$output = active_subscription($code);
				break;
			case 'remove':
				$output = remove_subscription();
				break;
		}
		$this->output->set_content_type('application/json')->set_output($output);
	}
	
	public function applications($group = "")
	{
		$this->load->helper(array(
			'cam_helper'
		));
		
		$this->load->library('ApiFabtotumClient');
		
		$apps = $this->apifabtotumclient->getApplications();

		$this->output->set_content_type('application/json')->set_output(json_encode($apps));
	}
	
	/**
	 */
	public function upload($appId = null)
	{
		// load helpers
		$this->load->helper(array(
			'file_helper',
			'file',
			'fabtotum_helper',
			'cam_helper'
		));
		// load config
		$this->config->load('fabtotum');
		$this->config->load('cam');
		
		// prepare destination folder
		$upload_path = $this->config->item('temp_path') . '/uploads/';
		if (! file_exists($upload_path))
			createFolder($upload_path);
		
		$upload_path = $this->config->item('temp_path') . '/uploads/cam/';
		if (! file_exists($upload_path))
			createFolder($upload_path);
		
		$upload_path.= $this->session->user['id'];
		if (! file_exists($upload_path))
			createFolder($upload_path);
			
		shell_exec('rm -rf ' . $upload_path . '/*');
		
		$allowed_types = '';
		$max_filesize = 8192;
		//*
		$this->load->library('ApiFabtotumClient');
		$apps = $this->apifabtotumclient->apps;
		$application = null;
		foreach($apps as $app_idx => $app)
		{
			if($app['id'] == $appId)
			{
				$application = $app;
				$allowed_types = str_replace('.', '', $app['accepts']);
				$max_filesize = $app['max_filesize'];
			}
		}
		//*
		
		// config load upload library
		$config['upload_path']      = $upload_path;
		$config['file_ext_tolower'] = true;
		$config['remove_spaces']    = true;
		$config['allowed_types']    = $allowed_types;//$this->config->item('laser_allowed_types');
		$config['max_size']         = $max_filesize; //$this->config->item('upload_max_file_size');
		
		$this->load->library('upload', $config);
		
		// do upload
		if ($this->upload->do_upload('file')) {
			$data = $this->upload->data();
			$data['upload'] = true;
			$data['url']    = '/temp/uploads/' . $data['file_name'];
			$data['url']    = str_replace('/tmp/fabui/', '/temp/', $data['full_path']);
			
			// get file info
			$data['info']   = get_img_extra_info($data['full_path']);
			if(empty($data['info'])){
				unlink($data['full_path']);
				unset($data);
				$data['error']  = _("File not valid, unable to process it");
				$data['upload'] = false;
			}
			
		} else {
			$data['error']  = $this->upload->display_errors();
			$data['upload'] = false;
		}
		$data['allowed'] = $allowed_types;
		$this->output->set_content_type('application/json')->set_output(json_encode($data));
	}
	
	private function resolve_oneof_property($schemas, $property_path)
	{
		foreach($schemas as $schema)
		{
			if(isset($schema['properties']))
			{
				$schema['type'] = 'object';
				$r = $this->get_property_schema($schema, $property_path);
				if($r) return $r;
			}
		}
		
		return null;
	}

	private function get_property_schema($schema, $property_path)
	{
		if(!isset($schema['type']))
			return null;
			
		if( $schema['type'] == 'object' && 
			(!isset($schema['properties']) && !isset($schema['oneOf']) ) )
			return null;
			
		if( $schema['type'] == 'array' && !isset($schema['item']) )
			return null;
			
		$p = explode('.', $property_path);
		$property_name = $p[0];
		
		if(count($p) == 1)
		{
			if(isset($schema['properties'][$property_name]))
				return $schema['properties'][$property_name];
			elseif(isset($schema['oneOf']))
			{
				//echo "OneOf #1".PHP_EOL;
				return $this->resolve_oneof_property($schema['oneOf'], $property_name);
			}
		}
		elseif(count($p) > 1)
		{
			if(isset($schema['properties'][$property_name]))
			{
				
				if(isset($schema['properties'][$property_name]['item']))
				{
					unset($p[0]);
					unset($p[1]);
					$new_path = implode('.', $p);
					return $this->get_property_schema($schema['properties'][$property_name]['item'], $new_path);
				}
				
				unset($p[0]);
				$new_path = implode('.', $p);
				return $this->get_property_schema($schema['properties'][$property_name], $new_path);
			}
			elseif(isset($schema['oneOf']))
			{
				//echo "OneOf #2".PHP_EOL;
				return $this->resolve_oneof_property($schema['oneOf'], $property_name);
			}
		}
		
		return null;
	}

	private function get_property_ui_element($schema, $ui, $children = 1, $prefix = 'camfield')
	{
		$property_path = $ui['field'];
		$label = $ui['label'];

		$property_schema = $this->get_property_schema($schema, $property_path);
		
		$content = '';
		$default = null;
		$min_value = 0;
		$max_value = 100;
		$step_value = 1;
		
		if(isset($property_schema['default']))
			$default = $property_schema['default'];
		
		if(isset($ui['default']))
			$default = $ui['default'];
			
		if(isset($property_schema['minimum']))
			$min_value = $property_schema['minimum'];
			
		if(isset($property_schema['maximum']))
			$max_value = $property_schema['maximum'];
			
		if(isset($property_schema['multipleOf']))
			$step_value = $property_schema['multipleOf'];
		
		$field_name = $prefix . '-' . str_replace('.', '-', $property_path);
		$field_id = $field_name;
		$field_class = '';
		$field_popover = '';
		
		if($children == 1)
		{
			$content .= '<section>';
		}
		else
		{
			$col_val = int(12 / $children);
			$content .= '<section class="col col-'.$col_val.'">';
		}
		
		if(isset($property_schema['enum']))
		{
			$content .= '<label class="select">';
			$content .= '<select data-type="'.$property_schema['type'].'" name="'.$field_name.'" id="'.$field_id.'"';
			if($field_class) ' class="'.$field_class.'" ';
			$content .= '>';
			foreach($property_schema['enum'] as $idx => $value)
			{
				$value_label = $value;
				if(isset($ui['enum']))
				{
					$value_label = $ui['enum'][$value];
				}
				$content .= '<option value="'.$value.'"';
				if($value == $default)
					$content .= ' selected';
				$content .= '>'.$value_label.'</option>';
			}
			$content .= '</select>';
			$content .= '<i></i></label>';
		}
		else
		{
			
			switch($property_schema['type'])
			{
				case 'integer':
					if(!$default) $default = 0;
					$content .= '<label class="input">';
					$content .= '<span class="icon-prepend">'.$label.'</span>';
					$content .= '<input data-type="integer" type="number" name="'.$field_name.'" id="'.$field_id.'"';
					if($field_class) ' class="'.$field_class.'" ';
					$content .= ' value="'.$default.'"';
					$content .= ' min="'.$min_value.'"';
					$content .= ' max="'.$max_value.'"';
					$content .= ' step="'.$step_value.'"';
					$content .= '>';
					break;
				case 'number':
					if(!$default) $default = 0.0;
					$content .= '<label class="input">';
					$content .= '<span class="icon-prepend">'.$label.'</span>';
					$content .= '<input data-type="number" type="number" name="'.$field_name.'" id="'.$field_id.'"';
					if($field_class) ' class="'.$field_class.'" ';
					$content .= ' value="'.$default.'"';
					$content .= ' min="'.$min_value.'"';
					$content .= ' max="'.$max_value.'"';
					$content .= ' step="'.$step_value.'"';
					$content .= '>';
					break;
				case 'string':
					if(!$default) $default = '';
					break;
				case 'boolean':
					if(!$default) $default = false;
					$content .= '<label class="checkbox">';
					$content .= '<input data-type="boolean" type="checkbox" name="'.$field_name.'" id="'.$field_id.'"';
					if($field_class) ' class="'.$field_class.'" ';
					$content .= '><i></i><span>'.$label.'</span>';
					break;
			}
			$content .= '</label>';
		}
		
		$content .= '</section>';
		
		return $content;
	}
	
	public function ui($appId)
	{
		// load helpers
		$this->load->helper(array(
			'file_helper',
			'file',
			'fabtotum_helper',
			'cam_helper'
		));
		// load config
		$this->config->load('fabtotum');
		$this->config->load('cam');
		
		$app_path = $this->config->item('userdata_path') . '/cam/apps/' . $appId;
		
		$ui = json_decode(file_get_contents($app_path . '/ui.json'), true);
		$schema = json_decode(file_get_contents($app_path . '/schema.json'), true);
		
		$tab_title = '<ul id="camConfigTab" class="nav nav-tabs bordered">';
		$tab_content .= '<div id="camConfigTabContent" class="tab-content padding-10">';
		$active = 'active';
		foreach($ui['tabs'] as $tabIdx => $tab)
		{
			$href = 'cam-'. strtolower($tab['title']) .'-tab';
			$tab_title .= '<li class="'.$active.'"><a href="#'.$href.'" data-toggle="tab">'._($tab['title']).'</a></li>';
			
			$tab_content .= '<div class="tab-pane fade in '.$active.'" id="'.$href.'">';
			$tab_content .= '<div class="smart-form">';
			$tab_content .= '<fieldset>'; 

			foreach($tab['rows'] as $rowIdx => $row)
			{
				if(count($row) == 1)
				{
					//$tab_content .= '<div class="row margin-top-10 margin-bottom-10">';
				}
				
				foreach($row as $element)
				{
					$tab_content .= $this->get_property_ui_element($schema, $element, count($row));
				}
				
				if(count($row) == 1)
				{
					//$tab_content .= '</div>';
				}
			}
			$tab_content .= '</fieldset>'; 
			$tab_content .= '</div>';
			
			$active = '';
		}
		$tab_title .= '</ul>';
		$tab_content .= '</div>';
		
		$hidden_content = '';
		$prefix = 'camfield';
		
		foreach($ui['hidden'] as $fieldIdx => $el)
		{
			$field_name = str_replace('.', '-', $el['field']);
			$field_id = $prefix . '-' . $field_name;
			$field_value = '';
			if(isset($el['value']))
				$field_value = $el['value'];
			$el_schema = $this->get_property_schema($schema, $el['field']);
			$field_type = $el_schema['type'];
			$hidden_content .= '<input type="hidden" name="'.$field_id.'" id="'.$field_id.'" value="'.$field_value.'" data-type="'.$field_type.'" >';
		}
		
		$output = $hidden_content . $tab_title . $tab_content;

		$this->output->set_output($output);
	}
	
	public function generate($appId)
	{
		// load helpers
		$this->load->helper(array(
			'file_helper',
			'file',
			'fabtotum_helper',
			'cam_helper'
		));
		// load config
		$this->config->load('fabtotum');
		$this->config->load('cam');
		
		$data = $this->input->post();
		$response = array();
		$config = $data['config'];
		$appName = $data['app_name'];
		
		$this->load->library('ApiFabtotumClient');
		$cam = $this->apifabtotumclient;
		//$apps = $this->apifabtotumclient->apps;
		
		$accepts = array();
		foreach($cam->apps as $id => $app)
		{
			if($id == $appId)
			{
				$accepts = explode('|', $app['accepts']);
				break;
			}
		}
		
		//$taskId = $this->apifabtotumclient->newTask($appName);
		$taskId = 16;
		
		$files = $cam->getFiles($taskId);
		
		$app_path = $this->config->item('userdata_path') . 'cam/apps/' . $appId;
		
		$task_path = $app_path . '/task';
		if (! file_exists($task_path))
			createFolder($task_path);
		
		$task_path = $task_path . '/' . $taskId;
		if (! file_exists($task_path))
			createFolder($task_path);
		
		$input_path = $task_path . '/input';
		if (! file_exists($input_path))
			createFolder($input_path);
		
		$config_path = $task_path . '/config';
		if (! file_exists($config_path))
			createFolder($config_path);
		
		$upload_path = $this->config->item('temp_path') . '/uploads/cam/' . $this->session->user['id'];
		
		$configFilename = $config_path . '/config.json';
		//~ write_file($configFilename, json_encode($config, JSON_PRETTY_PRINT) );
		write_file($configFilename, $config );
		
		// Check for uploaded files
		$inputId = 0;
		$configId = 0;
		foreach($files as $fileIdx => $file)
		{
			if($file['type'] == 'CONFIG')
			{
				$configId = $file['id'];
			}
			elseif($file['type'] == 'INPUT')
			{
				$inputId = $file['id'];
			}
		}
		
		// Upload CONFIG files
		if($configId)
		{
			// update existing
			//$cam->updateFile($taskId, $configId, $configFilename);
		}
		else
		{
			// upload new
			//$cam->uploadConfigFile($taskId, $configFilename);
		}
		
		// Upload INPUT files
		if(!$inputId)
		{
			// upload new
			$inputFilename = false;
			foreach (glob( $upload_path . "/*.*") as $filename) {
				#echo "$filename size " . filesize($filename) . "\n";
				$file_parts = pathinfo($filename);
				$ext = '.' . $file_parts['extension'];
				if(in_array($ext, $accepts))
				{
					$inputFilename = $filename;
					break;
				}
			}
			if($inputFilename)
			{
				$inputId = $cam->uploadInputFile($taskId, $inputFilename);
				unlink($inputFilename);
			}
		}
		
		$cam->startTask($taskId);
		
		$response['appId'] = $appId;
		$response['taskId'] = $taskId;
		$response['files'] = $files;
		$response['configId'] = $configId;
		$response['inputId'] = $inputId;
		$response['accepts'] = $accepts;
		$response['config'] = $config;
		
		$this->output->set_content_type('application/json')->set_output(json_encode($response));
	}
	
	public function status($taskId)
	{
		// load helpers
		$this->load->helper(array(
			'file_helper',
			'file',
			'fabtotum_helper',
			'cam_helper'
		));
		// load config
		$this->config->load('fabtotum');
		$this->config->load('cam');
		
		$response = array();
		
		$this->load->library('ApiFabtotumClient');
		$cam = $this->apifabtotumclient;
		
		$task = $cam->getTask($taskId);
		$task['files'] = array();
		
		if($task['status'] == 'FINISHED')
		{
			$files = $cam->getFiles($taskId);
			$task['files'] = $files;
		}
		
		$this->output->set_content_type('application/json')->set_output(json_encode($task));
	}
	
}

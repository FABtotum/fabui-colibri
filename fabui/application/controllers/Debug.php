<?php
/**
 * 
 * @author Krios Mane
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */
 defined('BASEPATH') OR exit('No direct script access allowed');
 
 class Debug extends FAB_Controller {
 	
	public function index()
	{
		//load libraries, helpers, model, config
		$this->load->library('smart');
		$this->config->load('fabtotum');
		
		$widgetOptions = array(
				'sortable' => false, 'fullscreenbutton' => true,'refreshbutton' => false,'togglebutton' => false,
				'deletebutton' => false, 'editbutton' => false, 'colorbutton' => false, 'collapsed' => false
		);
		
		$headerToolbar = '
			<ul class="nav nav-tabs pull-right">
				<li class="active"><a data-toggle="tab" href="#monitor-tab"> task_monitor.json</a></li>
				<li><a data-toggle="tab" href="#temperatures-tab"> temperatures.json</a></li>
				<li><a data-toggle="tab" href="#notify-tab"> notify.json</a></li>
				<li><a data-toggle="tab" href="#trace-tab"> trace</a></li>
				<li><a data-toggle="tab" href="#settings-tab"> settings</a></li>
				<li><a data-toggle="tab" href="#json-rpc-tab"> json-rpc</a></li>
			</ul>';
		$data = array();
		$data['settings'] = file_get_contents($this->config->item('settings'));
		$widget = $this->smart->create_widget($widgetOptions);
		$widget->id     = 'debug-widget';
		$widget->header = array('icon' => 'fa-bug', "title" => "<h2>Debug panel</h2>", 'toolbar'=>$headerToolbar);
		$widget->body   = array('content' => $this->load->view('debug/index', $data, true ), 'class'=>'');
		$this->content = $widget->print_html(true);
		
		$this->addJSFile('/assets/js/plugin/jsonview/jquery.jsonview.min.js'); //datatable */
		$this->addCssFile('/assets/js/plugin/jsonview/jquery.jsonview.min.css'); //datatable */
		
		$this->addJSFile('/assets/js/plugin/jsoneditor/jquery.jsoneditor.min.js'); //datatable */
		$this->addCssFile('/assets/js/plugin/jsoneditor/jsoneditor.css'); //datatable */
		
		$this->addJsInLine($this->load->view('debug/js', $data, true));
		$this->addCSSInLine('<style>#main{margin-left:0px !important;} hr{margin-top:0px;margin-bottom:0px;}#trace{overflow:auto; height:500px;}</style>'); 
		
		$this->debugLayout();
	}
	
	public function test()
	{
		$this->load->helpers('plugin_helper');
		$this->load->helpers('upload_helper');
		$this->config->load('upload');
		#$tmp = allowedTypesToDropzoneAcceptedFiles( $this->config->item('allowed_types') );
		$tmp = getFileActionList('drl');
		var_dump($tmp);
	}
	
	public function gcodeviewer()
	{
		$this->content = $this->load->view('filemanager/file/preview/index', null, true );
		$this->addJSFile('/assets/js/plugin/gcode-viewer/lib/modernizr.custom.93389.js');
		$this->addJSFile('/assets/js/plugin/gcode-viewer/lib/jquery-1.7.1.min.js');
		$this->addJSFile('/assets/js/plugin/gcode-viewer/lib/sugar-1.2.4.min.js');
		$this->addJSFile('/assets/js/plugin/gcode-viewer/lib/three.js');
		$this->addJSFile('/assets/js/plugin/gcode-viewer/lib/TrackballControls.js');
		
		$this->addJSFile('/assets/js/plugin/gcode-viewer/js/ShaderExtras.js');
		$this->addJSFile('/assets/js/plugin/gcode-viewer/js/postprocessing/EffectComposer.js');
		$this->addJSFile('/assets/js/plugin/gcode-viewer/js/postprocessing/MaskPass.js');
		$this->addJSFile('/assets/js/plugin/gcode-viewer/js/postprocessing/RenderPass.js');
		$this->addJSFile('/assets/js/plugin/gcode-viewer/js/postprocessing/ShaderPass.js');
		$this->addJSFile('/assets/js/plugin/gcode-viewer/js/postprocessing/BloomPass.js');
		
		$this->addJSFile('/assets/js/plugin/gcode-viewer/js/Stats.js');
		$this->addJSFile('/assets/js/plugin/gcode-viewer/js/dat.gui.min.js');

		
		$this->addJSFile('/assets/js/plugin/gcode-viewer/gcode_model.js');
		$this->addJSFile('/assets/js/plugin/gcode-viewer/gcode_parser.js');
		$this->addJSFile('/assets/js/plugin/gcode-viewer/gcode_interpreter.js');
		$this->addJSFile('/assets/js/plugin/gcode-viewer/gcode_importer.js');
		$this->addJSFile('/assets/js/plugin/gcode-viewer/gcode_renderer.js');
		
		$this->addCssFile('/assets/js/plugin/gcode-viewer/lib/bootstrap.min.css');
		
		$this->addJsInLine($this->load->view('filemanager/file/preview/renderer_js', null, true));
		$this->addJsInLine($this->load->view('filemanager/file/preview/ui_js', null, true));
		
		$this->view();
	}
	
	public function jsonrpc($method)
	{
		//load jog factory class
		//$init['url'] = 'https://my.fabtotum.com/myfabtotum/default/call/jsonrpc2';
		//$this->load->library('JsonRPC', $init, 'jsonRPC');
		
		$this->load->helpers('os_helper');
		$this->load->helpers('fabtotum_helper');
		$this->load->helpers('myfabtotum_helper');
		
		//$params['serialno']   = getSerialNumber();
		//$params['mac']        = getMACAddres();
		//$params['apiversion'] = 1;
		
		
		$result_codes[200]  = 'SERVICE_SUCCESS';
		$result_codes[401]  = 'SERVICE_UNAUTHORIZED';
		$result_codes[403]  = 'SERVICE_FORBIDDEN';
		$result_codes[500]  = 'SERVICE_SERVER_ERROR';
		$result_codes[1001] = 'SERVICE_INVALID_PARAMETER';
		$result_codes[1002] = 'SERVICE_ALREADY_REGISTERED';
		$result_codes[1003] = 'SERVICE_PRINTER_UNKNOWN';
		
		
		
		switch($method){
			case 'fab_register_printer':
				//$params['fabid'] = 'fabtest@fabtotum.com';
			    $result = fab_register_printer($this->session->user['email']);
				break;
			case 'fab_info_update':
				$result = fab_info_update();
				break;
			case 'fab_polling':
				$result = fab_polling();
				break;
			case 'fab_is_printer_registered':
				$result = fab_is_printer_registered();
				break;
			case 'fab_my_printers_list':
			    $result = fab_my_printers_list($this->session->user['email']);
			    break;
		}
		
		if(is_array($result)){
			if(isset($result['status_code'])){
				$result['status_description'] = $result_codes[$result['status_code']];
			}
		}else{
		    if(is_object($result))
		        $result = $result->getMessage();
		}
		
		$this->output->set_content_type('application/json')->set_output(json_encode(array('method'=>$method, 'result'=>$result)));
	}
	/**
	 * 
	 */
	function saveSettingsJson()
	{
		$json = $this->input->post('json');
		$this->load->helpers('fabtotum_helper');
		
		$json['hardware']['bed']['enable'] = $json['hardware']['bed']['enable'] == 'true';
		$json['feeder']['show']            = $json['feeder']['show'] == 'true';
		$json['filament']['inserted']      = $json['filament']['inserted'] == 'true';
		
		saveSettings($json);
		resetController();
		$this->output->set_content_type('application/json')->set_output(json_encode(loadSettings()));
		
	}
	
	/**
	 * 
	 */
	public function deshape()
	{
	    
	    $this->load->helpers(array('api_helper', 'myfabtotum_helper'));
	    
	    $config['server'] = 'http://myfabdev.tk/deshape/';
	    $config['token']  = fab_authenticate('*******', '***********');
	    
	    $this->load->library('Deshape', $config);
	    
	    $data['project'] = array(
	        'project_name' => 'Marvin Test',
	        'project_description' => 'This is Marvin. He is the symbol of the 3D Printing movement. Marvin\'s core ethos is about community, creativity, social change, and problem solving. He\'s determined to revolutionize the way we make things through 3D Printing',
	        'visibility' => 'PUBLIC',
	        'categories' => array('Design'),
	        'parts' => array (
	            array(
	                'part_name' => 'marvin_test',
	                'part_description' => 'No description',
	                'price' => 0,
	                'part_creation_tool' => 'Printing Head Pro',
	                'part_quantity' => 1,
	                'ordinal_number' => 1,
	                'part_files' => array (
	                    array(
	                        'title' => 'marvin_test',
	                        'file_type' => 'STL',
	                        'file_data' => base64_encode(file_get_contents('/mnt/bigtemp/fabui/marvin_test.STL')),
	                        'file_name' => 'marvin_test.STL'
	                    )
	                )
	            )
	        )
	    );
	    
	    //$project = $this->deshape->create_project($data);
	    
	    $projects = $this->deshape->get_project_image(273);
	    
	    print_r($projects);
	    
	    
	    //$projects = $this->deshape->get_single_project(211);
	    
	    //print_r($projects);
	    
	    //print_r($this->deshape);
	    
	    /*
	    $this->load->helpers(array('api_helper', 'myfabtotum_helper'));
	    
	    //$projects_full = deshape_list_projects_full();
	    
	    
	    $args['project'] = array(
	        'project_name' => 'Cane',
	        'project_description' => 'logo CANE',
	        'visibility' => 'PUBLIC',
	        'categories' => array('Design'),
	        'parts' => array (
	            array(
	                'part_name' => 'Cane',
	                'part_description' => 'Unica parte',
	                'price' => 0,
	                'part_creation_tool' => 'Printing Head Pro',
	                'part_quantity' => 1,
	                'ordinal_number' => 1,
	                'part_files' => array (
	                    array(
	                        'title' => 'logo_cane',
	                        'file_type' => 'STL', 
	                        'file_data' => base64_encode(file_get_contents('/mnt/bigtemp/fabui/CANE.stl')),
	                        'file_name' => 'CANE.stl'
	                    )
	                )
	            )
	        )
	    );
	    
	    // $project       = deshape_create_project($args);  // ok
	     $projects_full = deshape_list_projects_full();   // ok
	    // $projects_short = deshape_list_projects_short(); // ok
	    
	    //edit project id=211
	    $args['project'] = array(
	        'project_id' => 211,
	        'project_name' => 'Nome progetto modificato',
	        'project_description' => 'Descrizione progetto modificato',
	        'visibility' => 'PUBLIC'
	    );
	    
	    // $project_edited = deshape_edit_project($args); // ok
	    
	    $part = array(
	        'part_name' => 'Cane nuova parte',
	        'part_description' => 'seconda parte aggiunta',
	        'price' => 10,
	        'part_creation_tool' => 'Printing Head Pro',
	        'part_quantity' => 2,
	        'ordinal_number' => 1,
	        'part_files' => array (
	            array(
	                'title' => 'logo_cane_aggiunto',
	                'file_type' => 'STL',
	                'file_data' => base64_encode(file_get_contents('/mnt/bigtemp/fabui/CANE.stl')),
	                'file_name' => 'CANE.stl'
	            )
	        )
	    );
	    
	    //$added_part = deshape_add_part(211, $part); // ok - note : [response] il file della parte aggiunta viene aggiunta nell'elenco "part_files" di tutte le parti
	    
	    
	    $remove_part = deshape_remove_part(211, 210); // ko - BAD REQUEST
	   
	    //print_r($project);
	    print_r($projects_full);
	    
	    //print_r($project);
	    //print_r($projects_short);
	    
	    //print_r($project_edited);
	    
	    //print_r($added_part);
	    
	    //print_r($remove_part);
	     * 
	     * 
	     */
	}
 }
 
?>

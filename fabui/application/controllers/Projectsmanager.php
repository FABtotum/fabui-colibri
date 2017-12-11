<?php
/**
 * 
 * @author Krios Mane
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */
 defined('BASEPATH') OR exit('No direct script access allowed');
 
class Projectsmanager extends FAB_Controller {
	
	/**
	 * show objects page
	 **/
	public function index()
	{
		//load libraries, helpers, model, config
		$this->load->library('smart');
		$this->load->helper('layout');
		$this->config->load('fabtotum');
		$data['alert'] = $this->session->flashdata('alert'); //show message if is present
		
		//main page widget
		$widgetOptions = array(
			'sortable' => false, 'fullscreenbutton' => true,'refreshbutton' => false,'togglebutton' => false,
			'deletebutton' => false, 'editbutton' => false, 'colorbutton' => false, 'collapsed' => false
		);
		
		//$installSamplesButton = '';
		
		//if(file_exists($this->config->item('samples_file'))){
		$installSamplesButton = '<button id="install-samples" class="btn bg-color-magenta txt-color-white"><i class="fa fa-cubes"></i> <span class="hidden-xs">'._("Install samples").'</span> </button>';
		//}
		
		
		$headerToolbar = '<div class="widget-toolbar" role="menu">
		<a class="btn btn-success" href="projectsmanager/add-project"><i class="fa fa-plus"></i> <span class="hidden-xs">'._("Add new project").'</span> </a>
		<button class="btn btn-danger bulk-button" data-action="delete"><i class="fa fa-trash"></i> <span class="hidden-xs">'._("Delete").'</span> </button>
		<button class="btn btn-info bulk-button" data-action="download"><i class="fa fa-download"></i> <span class="hidden-xs">'._("Download").'</span> </button>
		'.$installSamplesButton.'
		</div>';
		
		$widget = $this->smart->create_widget($widgetOptions);
		$widget->id = 'file-manager-widget';
		$widget->header = array('icon' => 'fa-cubes', "title" => "<h2>"._("Projects")."</h2>", 'toolbar'=>$headerToolbar);
		$widget->body   = array('content' => $this->load->view('projectsmanager/index/widget', $data, true ), 'class'=>'no-padding');
		$this->content  = $widget->print_html(true);
		
		
		//add needed scripts
		$this->addJSFile('/assets/js/plugin/datatables/jquery.dataTables.min.js'); //datatable
		$this->addJSFile('/assets/js/plugin/datatables/dataTables.colVis.min.js'); //datatable
		$this->addJSFile('/assets/js/plugin/datatables/dataTables.tableTools.min.js'); //datatable
		$this->addJSFile('/assets/js/plugin/datatables/dataTables.bootstrap.min.js'); //datatable
		$this->addJSFile('/assets/js/plugin/datatable-responsive/datatables.responsive.min.js'); //datatable */
		$this->addJsInLine($this->load->view('projectsmanager/index/js','', true));
		$this->view();
	}
	
	/**
	 * show project page with info and files
	 */
	public function project($objectId)
	{
		if($objectId == '') redirect('projectsmanager');
		
		//load libraries, helpers, model, config
		$this->load->library('smart');
		//load db model
		$this->load->model('Objects', 'objects');
		$data['object'] = $this->objects->get($objectId, 1);
		
		if($data['object']) // if object existss
		{
			$widgetOptions = array(
				'sortable' => false, 'fullscreenbutton' => true,'refreshbutton' => false,'togglebutton' => false,
				'deletebutton' => false, 'editbutton' => false, 'colorbutton' => false, 'collapsed' => false
			);
			
			$data['isOwner'] = $this->session->user['id'] == $data['object']['user'];
			$widgeFooterButtons = '';
			$deleteTopButton = '';
			$addFilesTopButton = '';
			
			if($data['isOwner']){
				$deleteTopButton = '<button class="btn btn-danger bulk-button" data-action="delete"><i class="fa fa-trash"></i> <span class="hidden-xs">'._("Delete").'</span> </button>';
				$addFilesTopButton = '<a class="btn btn-success" href="projectsmanager/add-file/'.$objectId.'"><i class="fa fa-plus"></i> <span class="hidden-xs">'._("Add files").'</span> </a>';	
				$widgeFooterButtons = $this->smart->create_button("<span class='hidden-xs'>"._("Save")."</span>", 'primary')->attr(array('id' => 'save-object'))->attr('data-action', 'exec')->icon('fa-save')->print_html(true);
			}
			
			$headerToolbar = '<div class="widget-toolbar" role="menu">
			<a class="btn btn-default" href="projectsmanager"><i class="fa fa-arrow-left"></i> <span class="hidden-xs">'._("Back").'</span> </a> '.
			$addFilesTopButton.' '.$deleteTopButton.'
			<button class="btn btn-info bulk-button" data-action="download"><i class="fa fa-download"></i> <span class="hidden-xs">'._("Download").'</span> </button>
			</div>';
			
			$widget = $this->smart->create_widget($widgetOptions);
			$widget->id = 'file-manager-edit-object-widget';
			$widget->header = array('icon' => 'fa-cubes', "title" => "<h2>"._("Edit project")."</h2>", 'toolbar'=>$headerToolbar);
			$widget->body   = array('content' => $this->load->view('projectsmanager/edit/widget', $data, true ), 'class'=>'', 'footer'=>$widgeFooterButtons);
			$this->content  = $widget->print_html(true);
			
			//add needed scripts
			$this->addJSFile('/assets/js/plugin/datatables/jquery.dataTables.min.js'); //datatable
			$this->addJSFile('/assets/js/plugin/datatables/dataTables.colVis.min.js'); //datatable
			$this->addJSFile('/assets/js/plugin/datatables/dataTables.tableTools.min.js'); //datatable
			$this->addJSFile('/assets/js/plugin/datatables/dataTables.bootstrap.min.js'); //datatable
			$this->addJSFile('/assets/js/plugin/datatable-responsive/datatables.responsive.min.js'); //datatable */
			$this->addJsInLine($this->load->view('projectsmanager/edit/js',$data['object'], true));
			$this->addCSSInLine('<style>.dropdown-menu {min-width: auto !important;}</style>');
			
			$this->view();
		}
		else
		{
			redirect('projectsmanager');
		}
	}
	
	/**
	 * show file actions view
	 */
	public function file($fileID, $what = 'index')
	{
		if($fileID == '') redirect('projectsmanager');
		
		//load db model
		$this->load->model('Files', 'files');
		$file = $this->files->get($fileID, 1);
		
		if($file) // if file existss
		{
			switch($what)
			{
				case "index":
					$this->fileView($fileID);
					return;
				case "stats":
					$this->fileStats($fileID);
					return;
				case "preview":
					// TODO: select appropriate viewer for additive/milling gcode
					$this->fileGCodeViewer($fileID);
					return;
				default:
					// just continue
			}
		}
		else
		{
			redirect('projectsmanager');
		}
	}
	
	private function fileView($fileId)
	{
		//load libraries, helpers, model, config
		$this->load->library('smart');
		$this->load->helper('fabtotum_helper');
		$this->load->helper('projects_manager_helper');
		$this->config->load('upload');
		//load db model
		$this->load->model('Files', 'files');
		$make_action = get_make_default_action($fileId);	
		$data['file'] = $this->files->get($fileId, 1);
		
		$data['upload_path'] = $this->config->item('upload_path');
		$data['is_editable'] = True;
		
		$data['printables_files'] = array('.gc', '.gcode', '.nc');
		$data['preview_files'] = array('.gc', '.gcode', '.stl');
		
		// additive
		$data['dimesions'] = '';
		$data['filament'] = '';
		$data['estimated_time'] = '';
		$data['number_of_layers'] = '';
			
		$data['object'] = $this->files->getObject($fileId);
		
		$objectId = $data['object']['id'];
		$data['isOwner'] = $this->session->user['id'] == $data['object']['user'];
		
		$attributes = json_decode($data['file']['attributes'], true);
		
		$data['dimesions'] = '-';
		$data['filament'] = '-';
		$data['number_of_layers'] = '-';
		$data['estimated_time'] = '-';
		
		if(is_array($attributes) and $attributes)
		{
		    
		    if(isset($attributes['dimensions'])){
		    
			     $dimensions = $attributes['dimensions'];
			     $x = number_format($dimensions['x'], 2, '.', '');
			     $y = number_format($dimensions['y'], 2, '.', '');
			     $z = number_format($dimensions['z'], 2, '.', '');
			     $data['dimesions'] = $x . ' x ' . $y . ' x ' . $z . ' mm';
		    }
		    if(isset($attributes['filament'])) {
		        $data['filament'] = number_format($attributes['filament'], 2, '.', '') . ' mm';
		    }
		    
		    if(isset($attributes['number_of_layers'])){
		        $data['number_of_layers'] = $attributes['number_of_layers'];
		    }
		    
		    if(isset($attributes['estimated_time'])){
		        $data['estimated_time'] = $attributes['estimated_time'];
		    }
			
		    
		}
		else
		{
			if( $data['file']['print_type'] == 'additive' && $attributes != 'Processing' )
			{
				//startPyScript('gcode_analyzer.py', array($fileId), true);
				/**
				 * @todo improve script - disabled temporary
				 */
			}
		}
		
		$widgetOptions = array(
			'sortable' => false, 'fullscreenbutton' => true,'refreshbutton' => false,'togglebutton' => false,
			'deletebutton' => false, 'editbutton' => false, 'colorbutton' => false, 'collapsed' => false
		);
		
		$widgeFooterButtons= '';
		$deleteTopButton = '';
		
		if($data['isOwner'] == true){
			$widgeFooterButtons = '<button class="btn btn-default pull-left" type="button" id="load-content"><i class="fa fa-angle-double-down"></i> view content </button>
				<label class="checkbox-inline" style="padding-top:0px;">
				 <input type="checkbox" class="checkbox" disabled="disabled" id="also-content">
				 <span>Save content also </span>
			</label>' .$this->smart->create_button(_("Save"), 'primary')->attr(array('id' => 'save'))->attr('data-action', 'exec')->icon('fa-save')->print_html(true);
			$deleteTopButton = '<button class="btn btn-danger button-action" data-action="delete"><i class="fa fa-trash"></i> <span class="hidden-xs">'._("Delete").'</span> </button>';
		}else{
			$widgeFooterButtons = '<button class="btn btn-default" type="button" id="load-content"><i class="fa fa-angle-double-down"></i> view content </button>';
		}
		
		
		$make_button = '';
		if(!empty($make_action)){
			$make_button  = '<a class="btn btn-success" href="'.$make_action['url'].'"><i class="fa fa fa-rotate-90 fa-play"></i> <span class="hidden-xs">'.$make_action['title'].'</span> </a>';
		}
		
		$headerToolbar = '<div class="widget-toolbar" role="menu">
		<a class="btn btn-default" href="projectsmanager/project/'.$objectId.'"><i class="fa fa-arrow-left"></i> <span class="hidden-xs">'._("Back").'</span> </a>
		 '.$make_button.'
		 <a class="btn btn-info" href="projectsmanager/file/'.$fileId.'/stats"><i class="fa fa-area-chart"></i> <span class="hidden-xs">'._("Stats").'</span> </a> '.
		$deleteTopButton.'
		<button class="btn btn-info button-action" data-action="download"><i class="fa fa-download"></i> <span class="hidden-xs">'._("Download").'</span> </button>
		</div>';
		
		$widget = $this->smart->create_widget($widgetOptions);
		$widget->id = 'file-manager-edit-object-widget';
		$widget->header = array('icon' => 'fa-cube', "title" => "<h2>"._("File view")."</h2>", 'toolbar'=>$headerToolbar);
		$widget->body   = array('content' => $this->load->view('projectsmanager/file/view/widget', $data, true ), 'class'=>'', 'footer'=>$widgeFooterButtons);
		$this->content  = $widget->print_html(true);
		
		//add css files
		$this->addCssFile('/assets/css/projectsmanager/style.css');
		//add needed scripts
		$this->addJSFile('/assets/js/plugin/ace/src-min/ace.js'); // editor
		$this->addJSFile('/assets/js/plugin/datatables/jquery.dataTables.min.js'); //datatable
		$this->addJSFile('/assets/js/plugin/datatables/dataTables.colVis.min.js'); //datatable
		$this->addJSFile('/assets/js/plugin/datatables/dataTables.tableTools.min.js'); //datatable
		$this->addJSFile('/assets/js/plugin/datatables/dataTables.bootstrap.min.js'); //datatable
		$this->addJSFile('/assets/js/plugin/datatable-responsive/datatables.responsive.min.js'); //datatable */
		$this->addJsInLine($this->load->view('projectsmanager/file/view/js', $data, true));
		
		$this->view();
	}
	
	private function getStatistics($file, $start_date, $end_date){
		
		$this -> load -> model('tasks');
		$stats = $this->tasks->getFileStats($file, $start_date, $end_date);
		
		//print_r($stats); 
		
		$temp_stats = array();
		$durations_stats = array();
		//construct stats
		foreach($stats as $stat){
			
			if(!isset($temp_stats[$stat['date']])) $temp_stats[$stat['date']] = array();
			if(!isset($temp_stats[$stat['date']][$stat['status']])) $temp_stats[$stat['date']][$stat['status']] = 0;
			
			$temp_stats[$stat['date']][$stat['status']] += $stat['total']; 
			
			if(!isset($durations_stats[$stat['status']])) $durations_stats[$stat['status']] = array();
			$durations_stats[$stat['status']][] = $stat['total_time'];
		}

		
		
		$statistics = array();
		
		foreach($temp_stats as $day => $content){
			
			$temp = array('period'=>$day);
			
			foreach($content as $status => $total){
				
				$temp[$status] = $total;
				
			}	
			array_push($statistics, $temp);
			
		}
		
		return  array ('statistics' =>$statistics, 'durations' => $durations_stats);		
		
	}
	
	function getFileTasksForTable($fileId){
		
		$params = $this->input->get();
		
		$this->load->helper('date_helper');
		$this->load->model('Tasks', 'tasks');
		
		$tasks = $this->tasks->getFileTasks($fileId, $params);
		
		$options = array(
			'completed' => array('label' => 'Completed', 'color' => '#7e9d3a'),
			'aborted'   => array('label' => 'Aborted',   'color' => '#FF9F01'),
			'terminated'   => array('label' => 'Terminated',   'color' => '#a90329')
		);
		
		$aaData = array();

		foreach ($tasks as $task) {
			
			$td_0 = date('d/m/Y H:i:s', strtotime($task['finish_date']));
			$td_1 = $options[$task['status']]['label'];
			$td_2 = $task['duration'];
			$td_3 = $task['status'];
			
			$aaData[] = array($td_0, $td_1, $td_2, $td_3);
		}
		
		echo json_encode(array('aaData' => $aaData));
		
	}
	
	function getJsonStatsData($file, $start_date, $end_date)
	{
		$this->load->helper('utility_helper');
				
		$start_date = date('d/m/Y', ($start_date/1000));
		$end_date   = date('d/m/Y', ($end_date/1000));
		
		$options = array(
			'completed' => array('label' => 'Completed', 'color' => '#7e9d3a'),
			'aborted'   => array('label' => 'Aborted',   'color' => '#FF9F01'),
			'terminated'   => array('label' => 'Terminated',   'color' => '#a90329')
		);
		
		$stats =  $this->getStatistics($file, $start_date, $end_date);
		$statistics = $stats['statistics'];
		$durations = $stats['durations'];
		
		
		if(count($statistics)<=0){
			echo json_encode(array('line'=>array(), 'donut'=>array(), 'tasks'=>array(), 'total_tasks'=>0, 'total_duration' => 0, 'durations' => array(), 'bars'=>''), JSON_NUMERIC_CHECK );
			return;
		}
		
		$totals = array();
		$status_keys = array();
		foreach($statistics as  $val){
			
			foreach($val as $key => $c){			
				if($key != 'period'){
					
					if(!in_array($key, $status_keys)) array_push($status_keys, $key);
							
					if(!isset($totals[$key])) $totals[$key] = 0;
					$totals[$key] += $c;
				}
			}
		}
		
		$donut_data = array();
		$total_duration_temp = array();
		$total_durations = array();
	
		foreach($options as $status => $attributes){
			
			$temp_tot = isset($totals[$status]) ? $totals[$status] : 0;
			@$value = number_format(($temp_tot / array_sum($totals))*100, 1, '.', ' ');
			$temp = array('value' => $value, 'label'=>$options[$status]['label']);
			array_push($donut_data,  $temp);
			
			//echo $status;
			if(count($durations) > 0 && isset($durations[$status])){
				$total_duration_temp[] = sumTimes($durations[$status]);
				$total_durations[$status] = sumTimes($durations[$status]);
			}
			
		}
		
		$total_duration = sumTimes($total_duration_temp);
		
		
		$html_bars = '<div>';
		foreach($options as $status => $attributes){
			if(isset($totals[$status])){
				$html_bars .= '<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12"><span class="text">'.$attributes['label'].'<span class="pull-right">'.$totals[$status].'/ '.array_sum($totals).'</span></span></div>';
				$html_bars .= '<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12"><span class="text"><span class="pull-right">'.$total_durations[$status].'/ '.$total_duration.' </span></span></div>';
				$html_bars .= '<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12"><div class="progress"><div class="progress-bar" style="width:'.(($totals[$status]/array_sum($totals))*100).'%; background-color:'.$attributes['color'].' !important;"></div></div></div>';
			}
		}
		$html_bars .= '</div>';
		
		
		echo json_encode(array('line'=>$statistics, 'donut'=>$donut_data, 'tasks' => $totals, 'total_tasks' => array_sum($totals), 'total_duration' => $total_duration, 'durations'=>$total_durations, 'bars'=>$html_bars), JSON_NUMERIC_CHECK );
	}
	
	private function fileStats($fileId)
	{
		//load libraries, helpers, model, config
		$this->load->library('smart');
		$this->load->helper('fabtotum_helper');
		$this->load->helper('utility_helper');
		//load db model
		$this->load->model('Files', 'files');
		$data['file'] = $this->files->get($fileId, 1);
		//$file = $this->files->get($fileId, 1);
		//$data['file'] = $file;
		
		$data['object'] = $this->files->getObject($fileId);
		$objectId = $data['object']['id'];
		
		$data['start_date'] = !isset($params['start_date']) ? date('d/m/Y',strtotime('today - 30 days')) : $params['start_date'];
		$data['end_date']   = !isset($params['end_date'])  ? date('d/m/Y',strtotime('today')) : $params['end_date'];

		$status = array();
		
		$stats = $this->getStatistics($fileId, $data['start_date'], $data['end_date']);
		
		$data['statistics'] = $stats['statistics'];
		$data['durations'] = $stats['durations'];
		
		$data['totals'] = array();
		
		foreach($data['statistics'] as  $val){
			
			foreach($val as $key => $c){			
				if($key != 'period'){

					if(!isset($data['totals'][$key])) $data['totals'][$key] = 0;
					$data['totals'][$key] += $c;
				}
			}
		}

		$data['options'] = array(
			'completed' => array('label' => 'Completed', 'color' => '#7e9d3a'),
			'aborted'   => array('label' => 'Aborted',   'color' => '#FF9F01'),
			'terminated'   => array('label' => 'Terminated',   'color' => '#a90329')
		);
		
		
		$data['labels'] = array();
		$data['line_colors'] = array();
		$data['donut_data'] = array();
		$data['status_keys'] = array();
		$data['total_durations'] = array();
		
		
		foreach($data['options'] as $status => $attributes){
			
			array_push($data['labels'], $data['options'][$status]['label']);
			array_push($data['line_colors'], $data['options'][$status]['color']);
			array_push($data['status_keys'], $status);
			
			//if(count($data['statistics'])>0){

				$temp_tot = isset($data['totals'][$status]) ? $data['totals'][$status] : 0;
				@$value = number_format(($temp_tot / array_sum($data['totals']))*100, 1, '.', ' ');
				
				$temp = array('value' => $value, 'label'=>$data['options'][$status]['label']);
				array_push($data['donut_data'],  $temp);
			
			//}
			
			if(count($data['durations']) > 0 && isset($data['durations'][$status])){
				$data['total_durations'][] = sumTimes($data['durations'][$status]);
			}
			
		}
		
		
		$data['total_durations'] = sumTimes($data['total_durations']);
		
		$widgetOptions = array(
			'sortable' => false, 'fullscreenbutton' => true,'refreshbutton' => false,'togglebutton' => false,
			'deletebutton' => false, 'editbutton' => false, 'colorbutton' => false, 'collapsed' => false
		);
		
		$widgeFooterButtons = '';
		
		$headerToolbar = '<div class="widget-toolbar" role="menu">
		<a class="btn btn-default" href="projectsmanager/file/'.$fileId.'"><i class="fa fa-arrow-left"></i> <span class="hidden-xs">'._("Back").'</span> </a>
		</div>';
		
		$widget = $this->smart->create_widget($widgetOptions);
		$widget->id = 'file-manager-edit-object-widget';
		$widget->header = array('icon' => 'fa-area-chart', "title" => "<h2>"._("File statistics")."</h2>", 'toolbar'=>$headerToolbar);
		$widget->body   = array('content' => $this->load->view('projectsmanager/file/stats/widget', $data, true ), 'class'=>'', 'footer'=>$widgeFooterButtons);
		$this->content  = $widget->print_html(true);
		
		//add css files
		$this->addCssFile('/assets/css/projectsmanager/style.css');
		//add needed scripts
		$this->addJSFile('/assets/js/plugin/datatables/jquery.dataTables.min.js'); //datatable
		$this->addJSFile('/assets/js/plugin/datatables/dataTables.colVis.min.js'); //datatable
		$this->addJSFile('/assets/js/plugin/datatables/dataTables.tableTools.min.js'); //datatable
		$this->addJSFile('/assets/js/plugin/datatables/dataTables.bootstrap.min.js'); //datatable
		$this->addJSFile('/assets/js/plugin/datatable-responsive/datatables.responsive.min.js'); //datatable */
		
		// datepicker
		$this->addJSFile('/assets/js/plugin/bootstrap-datepicker/moment.min.js');
		$this->addJSFile('/assets/js/plugin/bootstrap-datepicker/daterangepicker.min.js');
		$this->addCSSFile('/assets/js/plugin/bootstrap-datepicker/daterangepicker.css');
		
		// charts
		$this->addJSFile('/assets/js/plugin/morris/raphael.min.js');
		$this->addJSFile('/assets/js/plugin/morris/morris.min.js');
		
		$this->addJsInLine($this->load->view('projectsmanager/file/stats/js', $data, true));
		
		$this->view();
	}
	
	private function fileGCodeViewer($fileID)
	{
		$this->load->model('Files', 'files');
		$file = $this->files->get($fileID, 1);
		
		switch($file['print_type'])
		{
			case "additive":
				$this->gcodeviewer($fileID);
				break;
			default:
				echo _("Not supported");
		}
	}
	
	private function gcodeviewer($fileID = '')
	{
		//load libraries, helpers, model, config
		$this->load->library('smart');
		$this->load->helper('fabtotum_helper');
		$this->load->model('Files', 'files');

		$data = array();
		
		$file = $this->files->get($fileID, 1);
		$this->config->load('upload');
		$upload_path = $this->config->item('upload_path');
		
		$url = 'http://'.$_SERVER['HTTP_HOST'].str_replace($upload_path, '/uploads/', $file['file_path'].urlencode($file['file_name']))."?t=".time();
		
		$data['gcode_url'] = $url;
		
		$widgetOptions = array(
			'sortable' => false, 'fullscreenbutton' => true,'refreshbutton' => false,'togglebutton' => false,
			'deletebutton' => false, 'editbutton' => false, 'colorbutton' => false, 'collapsed' => false
		);
		
		$widgeFooterButtons = '';
		
		$headerToolbar = '';
		
		$widget = $this->smart->create_widget($widgetOptions);
		$widget->id = 'file-manager-edit-object-widget';
		$widget->header = array('icon' => 'fa-cube', "title" => "<h2>"._("gCodeViewer")."</h2>", 'toolbar'=>$headerToolbar);
		$widget->body   = array('content' => $this->load->view('projectsmanager/file/gcodeviewer/index', $data, true ), 'class'=>'', 'footer'=>$widgeFooterButtons);
		$this->content  = $widget->print_html(true);

		$this->addCssFile('/assets/css/projectsmanager/gcodeviewer/lib/codemirror.css');
		$this->addCssFile('/assets/css/projectsmanager/gcodeviewer/style.css');
		
		$this->addJsInLine($this->load->view('projectsmanager/file/gcodeviewer/js', $data, true));
		
		//~ $this->addJSFile('/assets/js/libs/jquery-2.1.1.min.js');
		$this->addJSFile('/assets/js/libs/jquery-ui-1.10.3.min.js');
		
		$this->addJSFile('/assets/js/plugin/gcodeviewer/lib/codemirror.js');
		$this->addJSFile('/assets/js/plugin/gcodeviewer/lib/mode_gcode/gcode_mode.js');
		$this->addJSFile('/assets/js/plugin/gcodeviewer/lib/three.js');
		$this->addJSFile('/assets/js/plugin/gcodeviewer/lib/bootstrap.js');
		$this->addJSFile('/assets/js/plugin/gcodeviewer/lib/modernizr.custom.09684.js');
		$this->addJSFile('/assets/js/plugin/gcodeviewer/lib/TrackballControls.js');
		$this->addJSFile('/assets/js/plugin/gcodeviewer/lib/zlib.min.js');
		$this->addJSFile('/assets/js/plugin/gcodeviewer/ui.js');
		$this->addJSFile('/assets/js/plugin/gcodeviewer/gCodeReader.js');
		$this->addJSFile('/assets/js/plugin/gcodeviewer/renderer.js');
		$this->addJSFile('/assets/js/plugin/gcodeviewer/analyzer.js');
		$this->addJSFile('/assets/js/plugin/gcodeviewer/renderer3d.js');
		
		
		
		$this->view();
	}
	
	public function checkUSB()
	{
		$result = array('inserted' => false, 'content' => '');
		
		$this->load->helper('fabtotum_helper');
		$tree = json_decode(startPyScript('usb_browser.py', '', false, true), true);
		
		if (sizeof($tree) > 0) {
			$result['inserted'] = true;
			
			$content = '<div class="tree smart-form"><ul>';
			
			//folders
			foreach ($tree as $folder) {
				
				if(substr($folder, -1) == '/' && $folder[0] != '.'){
					$content .= '<li><span data-loaded="false" data-folder="' . $folder . '"><i class="fa fa-lg fa-folder-open"></i> ' . rtrim(str_replace("/media/", '', $folder), '/') . '</span><ul></ul></li>';
				}
			}

			//files
			foreach ($tree as $folder) {
				
				if(substr($folder, -1) != '/' && $folder[0] != '.'){
					$content .= '<li><span><label class="checkbox inline-block"><input type="checkbox" name="checkbox-inline" value="'.$folder.'"><i></i> '.$folder.'</label></span></li>';
				}
			}
			
			
			$content .= '</ul></div>';
			$result['content'] = $content;
		}
		
		
		
		$this->output->set_content_type('application/json')->set_output( json_encode($result) );
	}
	
	public function getFileTree()
	{
		$folder = $this->input->post('folder');
		$this->load->helper('fabtotum_helper');
		$tree = json_decode(startPyScript('usb_browser.py', $folder, false, true), true);
		$this->output->set_content_type('application/json')->set_output( json_encode(array('tree' => $tree)) );
	}
	
	public function init_folder_tree()
	{
		$this->load->helper('fabtotum_helper');
		$folder_tree = json_decode(startPyScript('usb_browser.py', '', false, true), true);
		
		echo "<ul>";
			foreach($folder_tree as $folder) {
			
				echo '<li><span data-loaded="false" data-folder="'.$folder.'"><i class="fa fa-lg fa-folder-open"></i> '. rtrim(str_replace("/run/media/", '', $folder), '/'). '</span>';
				echo "<ul></ul>";
				echo "</li>";
			}
		echo "</ul>";
		
		
		//$this->output->set_content_type('application/json')->set_output($result);
	}
	
	/**
	 * add new object and files page
	 */
	public function newProject()
	{
		//TODO
		//load libraries, helpers, model, config
		$this->load->library('smart');
		$this->load->helper('upload_helper');
		
		$data = array();
		
		//load configs
		$this->config->load('upload');
		$data['accepted_files'] = allowedTypesToDropzoneAcceptedFiles( $this->config->item('allowed_types') );
		
		$data['folder_tree'] = array();
		
		$widgetOptions = array(
			'sortable' => false, 'fullscreenbutton' => true,'refreshbutton' => false,'togglebutton' => false,
			'deletebutton' => false, 'editbutton' => false, 'colorbutton' => false, 'collapsed' => false
		);
		
		$widgeFooterButtons = $this->smart->create_button(_("Save"), 'primary')->attr(array('id' => 'save'))->attr('data-action', 'exec')->icon('fa-save')->print_html(true);
		
		$headerToolbar = '<div class="widget-toolbar" role="menu">
		<a class="btn btn-danger" href="projectsmanager"><i class="fa fa-arrow-left"></i> <span class="hidden-xs">'._("Cancel").'</span> </a>
		</div>';
		
		$widget = $this->smart->create_widget($widgetOptions);
		$widget->id = 'file-manager-add-object-widget';
		$widget->header = array('icon' => 'fa-cubes', "title" => "<h2>"._("Add new project")."</h2>", 'toolbar'=>$headerToolbar);
		$widget->body   = array('content' => $this->load->view('projectsmanager/add/widget', $data, true ), 'class'=>'', 'footer'=>$widgeFooterButtons);
		$this->content  = $widget->print_html(true);
		
		//add needed scripts
		$this->addJSFile('/assets/js/plugin/dropzone/dropzone.js'); //dropzpone
		$this->addJSFile('/assets/js/plugin/jquery-validate/jquery.validate.min.js'); //validator
		$this->addJSFile('/assets/js/controllers/projectsmanager/usb.js');
		$this->addJsInLine($this->load->view('projectsmanager/add/js',$data, true));
		
		$this->view();
	}
	
	/**
	 * add files to object page
	 */
	public function newFile($objectID)
	{
		//TODO
		//load libraries, helpers, model, config
		$this->load->library('smart');
		
		$data = array('object_id' => $objectID);
		$this->config->load('upload');
		$this->load->helpers('upload_helper');
		$data['accepted_files'] = allowedTypesToDropzoneAcceptedFiles( $this->config->item('allowed_types') );
		//~ var_dump($data);
		
		$data['folder_tree'] = array();
		
		$widgetOptions = array(
			'sortable' => false, 'fullscreenbutton' => true,'refreshbutton' => false,'togglebutton' => false,
			'deletebutton' => false, 'editbutton' => false, 'colorbutton' => false, 'collapsed' => false
		);
		
		$widgeFooterButtons = $this->smart->create_button(_("Save"), 'primary')->attr(array('id' => 'save'))->attr('data-action', 'exec')->icon('fa-save')->print_html(true);
		
		$headerToolbar = '<div class="widget-toolbar" role="menu">
		<a class="btn btn-danger" href="projectsmanager/project/'.$objectID.'"><i class="fa fa-arrow-left"></i> '._("Cancel").' </a>
		</div>';
		
		$widget = $this->smart->create_widget($widgetOptions);
		$widget->id = 'file-manager-add-object-widget';
		$widget->header = array('icon' => 'fa-cube', "title" => "<h2>"._("Add new file")."</h2>", 'toolbar'=>$headerToolbar);
		$widget->body   = array('content' => $this->load->view('projectsmanager/file/add/widget', $data, true ), 'class'=>'', 'footer'=>$widgeFooterButtons);
		$this->content  = $widget->print_html(true);
		
		//add needed scripts
		$this->addJSFile('/assets/js/plugin/dropzone/dropzone.js'); //dropzpone
		$this->addJSFile('/assets/js/plugin/jquery-validate/jquery.validate.min.js'); //validator
		$this->addJSFile('/assets/js/controllers/projectsmanager/usb.js');
		$this->addJsInLine($this->load->view('projectsmanager/file/add/js', $data, true));
		
		$this->view();
	}
	
	
	/**
	 * @return get all objects for table view
	 */
	public function getUserObjects()
	{
		//load db model
		$this->load->model('Objects', 'objects');
		//retrieve objetcs
		$objects = $this->objects->getUserObjects($this->session->user['id']);
		//crate response for datatable
		$aaData = array();
		foreach($objects as $object){
			$temp = array();
			$isOwner = $object['user'] == $this->session->user['id'];
			$temp[] = '<label class="checkbox-inline"><input data-attribute-owner="'.$isOwner.'" type="checkbox" id="check_'.$object['id'].'" name="checkbox-inline" class="checkbox"><span></span> </label>';
			$temp[] = '<i class="fa fa-cubes"></i> <a href="projectsmanager/project/'.$object['id'].'">'.$object['name'].'</a>';
			$temp[] = $object['description'];
			
			$date_inserted = date('d/m/Y', strtotime($object['date_insert']));
			
			$temp[] = $date_inserted;
			$temp[] = $object['num_files'];
			$aaData[] = $temp;
		}
		$this->output->set_content_type('application/json')->set_output(json_encode(array('aaData' => $aaData)));
	}
	
	
	/**
	 * 
	 */
	public function saveObject($objectID = '')
	{
		$this->load->helpers('upload_helper');
		
		$data = $this->input->post();
		$files = explode(',', $data['files']); //explode files id
		unset($data['files']);
		
		$usb_files = explode(',', $this->input->post('usb_files'));
		unset($data['usb_files']);
		
		//load db model
		$this->load->model('Objects', 'objects');
		$data['user'] = $this->session->user['id'];
		//~ $data['date_insert'] = date('Y-m-d H:i:s');
		$data['date_update'] = date('Y-m-d H:i:s');
		
		$redirectTo = '#projectsmanager';
		//add object record
		if(!$objectID)
		{
			$data['date_insert'] = date('Y-m-d H:i:s');
			$objectID = $this->objects->add($data);
		}
		else
		{
			$redirectTo = '#projectsmanager/project/' . $objectID;
		}
		
		// if files are presents add them to the object
		if(count($files) > 0)
		{ 
			$this->objects->addFiles($objectID, $files);
		}
		
		// if usb files are present add them to the object
		if(count($usb_files) > 0)
		{
			$usb_files_id = array();
			foreach ($usb_files as $file)
			{
				if ($file != '')
				{
					// preppend removable media path
					$file = '/run/media/' . $file;
					array_push($usb_files_id, uploadFromFileSystem($file) );
				}
			}
			$this->objects->addFiles($objectID, $usb_files_id);
		}
		
		
		$this->session->set_flashdata('alert', array('type' => 'alert-success', 'message'=> '<i class="fa fa-fw fa-check"></i> '._("Object has been added") ));
		
		redirect($redirectTo);
	}
	
	
	public function updateFile()
	{
		$data = $this->input->post();
		$this->load->model('Files', 'files');

		$response['success'] = true;
		$response['message'] = '';

		if($data)
		{
			$fileId = $data['file_id'];
			$file_path = $data['file_path'];
			
			if( $this->input->post('file_content') )
			{
				$file_content = urldecode($data['file_content']);
				file_put_contents($file_path, $file_content);
			}
			
			$new_data = array(
				'client_name'	=> urldecode($data['name']),
				'note'			=> urldecode($data['note']),
				'file_size'		=> filesize($file_path),
				'update_date'	=> date('Y-m-d H:i:s')
			);
			
			$this->files->update($fileId, $new_data);
		}
		else
		{
			$response['success'] = false;
			$response['message'] = _("No POST data");
		}
		
		// TODO: exec gcode_analyzer.py 
		
		$this->output->set_content_type('application/json')->set_output(json_encode($response));
	}
	
	/**
	 * update object attributes 
	 */
	public function updateObject()
	{
		$data = $this->input->post();
		$this->load->model('Objects', 'objects');

		$objectID = $data['object_id'];
		
		$this->load->model('Objects', 'objects');
		$object = $this->objects->get($objectID, 1);
		
		if($object) // if object existss
		{
			$new_data = array(
				'name' 			=> $data['name'],
				'description'	=> $data['description'],
				'public' 		=> $data['public'],
				'date_update'	=> date('Y-m-d H:i:s')
			);
			
			$this->objects->update($objectID, $new_data);
			$response['success'] = true;
		}
		else
		{
			$response['success'] = false;
		}
		
		$this->output->set_content_type('application/json')->set_output(json_encode($response));
	}
	
	/*
	 * delete object and all associated files
	 */
	public function deleteObjects()
	{
		// TODO: error handling
		$this->load->model('Objects', 'objects');
		$this->load->model('Files', 'files');
		
		$response['success'] = true;
		$response['message'] = '';
		
		$ids = $this->input->post("ids");
		
		if(is_array($ids)){
			foreach($ids as $objectID)
			{
				$files = $this->files->getByObject($objectID);
				
				$fileIDs = array();
				foreach($files as $file)
				{
					$fileID = $file['id'];
					$fileIDs[] = $fileID;
					
					$file = $this->files->get($fileID, True);
					shell_exec('sudo rm '.$file['full_path']);
					$this->files->delete( $fileID );
				}
				
				$this->objects->deleteFiles($objectID, $fileIDs);
				$this->objects->delete( $objectID );
			}
		}
		
		$this->output->set_content_type('application/json')->set_output(json_encode( $response ));
	}
	
	/*
	 * delete file
	 */
	public function deleteFiles()
	{
		// TODO: error handling
		$this->load->model('Objects', 'objects');
		$this->load->model('Files', 'files');
		
		$response['success'] = true;
		$response['message'] = '';
		
		$ids = $this->input->post("ids");
		foreach($ids as $fileID)
		{
			$objectID = $this->files->getObject($fileID)['id'];
			$this->objects->deleteFiles($objectID, $fileID);
			$file = $this->files->get($fileID, True);
			shell_exec('sudo rm '.$file['full_path']);
			$this->files->delete($fileID);
		}
		$this->output->set_content_type('application/json')->set_output(json_encode( $response ));
	}
	
	/**
	 * upload file
	 */
	public function uploadFile()
	{
		// TODO
		// load helpers
		$this->load->helper('file_helper');
		$this->load->helper('file');
		$this->load->helper('fabtotum_helper');
		// get file extension to save the file in the correct directory
		$fileExtension = getFileExtension($_FILES['file']['name']);
		//load configs
		$this->config->load('upload');
		$upload_path = $this->config->item('upload_path');
		// preaprea configs for upload library
		// crate folder extension if doesn't exist
		$folder_destination = $upload_path . $fileExtension . '/';
		if(!file_exists($folder_destination))
			createFolder($folder_destination);
			
		// load upload library
		$config['upload_path']      = $upload_path.$fileExtension;
		$config['allowed_types']    = $this->config->item('allowed_types');
		$config['file_ext_tolower'] = true ; 
		$config['remove_spaces']    = true ;
		$config['encrypt_name']     = true;
		$this->load->library('upload', $config);
		if($this->upload->do_upload('file')) { //do upload
			//load db model
			$this->load->model('Files', 'files');
			$data = $this->upload->data();
			$file_name = md5(uniqid(mt_rand())) . '.' . $fileExtension;
			$data['file_path'] = $folder_destination;
			$data['full_path'] = $folder_destination . $data['file_name'];
			$data['raw_name'] = str_replace('.'.$fileExtension, '', $file_name);
			$data['insert_date'] = date('Y-m-d H:i:s');
			$data['update_date'] = date('Y-m-d H:i:s');
			$data['note'] = '';
			$data['attributes'] = '{}';
			$data['print_type'] = checkManufactoring($data['full_path']);
			$fileId = $this->files->add($data);
			$response['upload'] = true;
			$response['fileId'] = $fileId;
			$response['type'] = $data['print_type'];
			if( $data['print_type'] == 'additive' )
			{
				//startPyScript('gcode_analyzer.py', array($fileId), true);
				/**
				 * @todo improve script - disabled temporary
				 */
			}
		}else{
			$response['upload'] = false;
			$response['message'] = $this -> upload -> display_errors();
		}
		$this->output->set_content_type('application/json')->set_output(json_encode($response));
	}
	
	private function generateActionDropdown($default_action, $builtin_actions, $plugin_actions)
	{
		$html = '<div class="btn-group">
					<button data-action="'.site_url($default_action['url']).'"type="button" class="btn btn-xs btn-success file-action"><i class="fa '.$default_action['icon'].'"></i> <span class="hidden-xs">'.$default_action['title'].'</span> </button>';
		
		if( count($plugin_actions) > 0 || count($builtin_actions) > 0 )
		{
			$html .= '
			<button type="button" class="btn btn-xs btn-success dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
			<span class="caret"></span>
			<span class="sr-only">Toggle Dropdown</span>
			</button>
			<ul class="dropdown-menu">';
			
			foreach($builtin_actions as $a)
			{
				$html .= '<li><a class="file-action" data-action="'.$a['url'].'"><i class="fa '.$a['icon'].'"></i> <span class="hidden-xs">'.$a['title'].'</span> </a></li>';
			}
			
			if(count($plugin_actions) > 0)
			{
				$html .= '<li role="separator" class="divider"></li>';
				foreach($plugin_actions as $a)
				{
					$html .= '<li><a class="file-action" data-action="'.$a['url'].'"><i class="fa '.$a['icon'].'"></i> <span class="hidden-xs">'.$a['title'].'</span> </a></li>';
				}
			}
			
			$html .= '</ul>';
		}
		
		$html .= '</div>';
		
		return $html;
	}
	
	/**
	 * Return a menu formated action button with dropdown menu for more supported actions.
	 */
	private function getFileActionDropdown($fileID)
	{
		//load db model
		$this->load->helpers('plugin_helper');
		$this->load->model('Files', 'files');
		$file = $this->files->get($fileID, 1);
		$file_ext = ltrim($file['file_ext'], '.');
		
		$builtin_actions = array();
		
		if($file['print_type'] == 'additive')
		{
			$builtin_actions[] = array(
				"title" => _("Print"),
				"icon" => "fa-rotate-90 fa-play",
				"url" => "#make/print/".$fileID
			);
			$default_action = $builtin_actions[0];
		}
		else if($file['print_type'] == 'subtractive')
		{
			$builtin_actions[] = array(
				"title" => _("Mill"),
				"icon" => "fa-rotate-90 fa-play",
				"url" => "#make/mill/".$fileID
			);
			$default_action = $builtin_actions[0];
		}

		$builtin_actions[] = array(
				"title" => _("Download"),
				"icon" => "fa-download",
				"url" => "projectsmanager/download/file/".$fileID
			);
			
		if( $file['print_type'] == 'additive' or $file['print_type'] == 'subtractive' )
		{
			$builtin_actions[] = array(
				"title" => _("Preview"),
				"icon" => "fa-eye",
				"url" => "#projectsmanager/file/".$fileID."/preview"
			);
			$builtin_actions[] = array(
				"title" => _("Stats"),
				"icon" => "fa-area-chart",
				"url" => "#projectsmanager/file/".$fileID."/stats"
			);
		}
		
		$plugin_actions = array();
		
		$actions = getFileActionList($file_ext, $file['print_type']);
		foreach($actions as $action)
		{
			$plugin_actions[] = array(
				'title' => $action['title'],
				'icon' => $action['icon'],
				'url' => '#'.str_replace('$1', $fileID, $action['url'] )
			);
		}
		
		if( count($builtin_actions) == 1) // There are no builtin actions except "Download"
		{
			if( count($plugin_actions) > 0 ) // Plugins provide an actions
			{
				$default_action = $plugin_actions[0];
				
				unset($plugin_actions[0]);
			}
			else
			{
				$default_action = $builtin_actions[0];
				unset($builtin_actions[0]);
			}
		}
		else // There are some built
		{
			unset($builtin_actions[0]);
		}
		
		// Generate action buttons
		return $this->generateActionDropdown($default_action, $builtin_actions, $plugin_actions);
	}
	
	/**
	 * @param (int) object id
	 * @return all files associated to the object
	 */
	public function getFiles($objectID)
	{
		//load db model
		$this->load->model('Files', 'files');
		//retrieve objetcs
		$files = $this->files->getByObject($objectID);
		//crate response for datatable
		$aaData = array();
		foreach($files as $file){
			$temp = array();
			$temp[] = '<label class="checkbox-inline"><input type="checkbox" id="check_'.$file['id'].'" name="checkbox-inline" class="checkbox"><span></span> </label>';
			$temp[] = '<a href="projectsmanager/file/'.$file['id'].'">'.str_replace($file['file_ext'], '', $file['client_name']).'</a>';
			$temp[] = $file['print_type'];
			$temp[] = $file['note'];
			
			$date_inserted = date('d/m/Y', strtotime($file['insert_date']));
			
			$temp[] = $date_inserted;
			$temp[] = $this->getFileActionDropdown($file['id']);
			//$temp[] = $object['num_files'];
			$aaData[] = $temp;
		}
		$this->output->set_content_type('application/json')->set_output(json_encode(array('aaData' => $aaData)));
	}
	
	public function download($type, $list)
	{
		/** LOAD HELPER */
		$this->load->model('Files', 'files');
		$this->load->model('Objects', 'objects');
		$this->load->helper('download');
		$this->load->helper('utility_helper');
		$this->load->library('zip');
		$this->config->load('fabtotum');
		
		if ($type == 'file')
		{
			$files = explode('-', $list);
			if (count($files) > 0)
			{
				if (count($files) == 1)
				{
					
					$file = $this->files->get($files[0], 1);
					$data = file_get_contents($file['full_path']);
					$fn = $file['client_name'];
					$ext = $file['file_ext'];
					if( !endsWith($fn, $ext) )
						$fn .= $ext;
					force_download($fn, $data);
				} 
				else
				{
					foreach($files as $file_id) 
					{
						$file = $this->files->get($file_id, 1);
						$fn = $file['client_name'];
						$ext = $file['file_ext'];
						if( !endsWith($fn, $ext) )
							$fn .= $ext;
						$this->zip->read_file($file['full_path'], $fn );
					}
					$this->zip->download('fabtotum_files.zip');
				}
			}
		} 
		else if ($type == 'object')
		{
			$objects = explode('-', $list);
			if (count($objects) > 0) 
			{
				foreach ($objects as $obj_id)
				{
					$obj = $this->objects->get($obj_id, 1);
					// Replace unwanted characters in filename
					$obj_folder = str_replace('&', 'and',str_replace(' ', '_', $obj['name']));
					$obj_folder = str_replace('(', '_', $obj_folder);
					$obj_folder = str_replace(')', '_', $obj_folder);
					
					// Create object directory
					$this->zip->add_dir($obj_folder);
					
					// Add object files in the object directory
					$files = $this->files->getByObject($obj_id);
					foreach ($files as $file)
					{
						// Create virtual file path for zip file
						$fn = $file['client_name'];
						$ext = $file['file_ext'];
						if( !endsWith($fn, $ext) )
							$fn .= $ext;
						$file_path = $obj_folder . '/' . $fn;
						$this->zip->read_file($file['full_path'], $file_path );
					}
				}
				
				$this->zip->download('fabtotum_objects.zip');
			}
		}

	}
	
 }
 
?>

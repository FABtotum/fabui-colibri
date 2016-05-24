<?php
/**
 * 
 * @author Krios Mane
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */
 class Create extends Module {
 	
	protected $_PRINT_VALID_HEADS = array('hybrid', 'print_v2');
	protected $_MILL_VALID_HEADS  = array('hybrid', 'mill_v2');
	protected $_MAKE_TYPES        = array('additive' => 'print', 'subtractive' => 'mill');
	protected $_RUN_FILE          = '/run/task_create.pid';
	
	public function index($type = 'additive'){
		
		//load db
		$this->load->model('tasks');
		$this->load->model('objects');
		
		//$this->tasks->delete(3);
		
		//load helpers
		$this->load->helper('print_helper');
		$this->load->helper('smart_admin_helper');
		$this->load->helper('ft_date_helper');
		
		//load parameters from request (if present)
		$data['request_file'] = $this->input->get('file');
		$data['request_obj']  = $this->input->get('obj');
		
		//setting layout
		$this->layout->add_css_file(array('src' => '/fabui/application/modules/create/assets/css/create.css', 'comment' => 'create css'));
		$this->layout->add_js_file(array('src' => '/assets/js/plugin/fuelux/wizard/wizard.min.js', 'comment' => 'javascript for the wizard'));
		$this->layout->add_js_file(array('src' => '/assets/js/plugin/datatables/jquery.dataTables.min.js', 'comment' => ''));
		$this->layout->add_js_file(array('src' => '/assets/js/plugin/datatables/dataTables.colVis.min.js', 'comment' => ''));
		$this->layout->add_js_file(array('src' => '/assets/js/plugin/datatables/dataTables.tableTools.min.js', 'comment' => ''));
		$this->layout->add_js_file(array('src' => '/assets/js/plugin/datatables/dataTables.bootstrap.min.js', 'comment' => ''));
		$this->layout->add_js_file(array('src' => '/assets/js/plugin/datatable-responsive/datatables.responsive.min.js', 'comment' => ''));
		$this->layout->add_js_file(array('src' => '/assets/js/plugin/bootstrap-progressbar/bootstrap-progressbar.min.js', 'comment' => ''));
		//$this->layout->add_js_file(array('src' => '/assets/js/fixed_queue.js', 'comment' => ''));
		$this->layout->add_js_file(array('src' => '/assets/js/plugin/knob/jquery.knob.min.js', 'comment' => 'KNOB'));
		$this->layout->add_js_file(array('src' => '/assets/js/plugin/ace/src-min/ace.js', 'comment' => 'ACE EDITOR JAVASCRIPT'));
		$this->layout->add_js_file(array('src' => '/fabui/application/modules/create/assets/js/utilities.js', 'comment' => 'create utilities'));
		$this->layout->add_js_file(array('src' => '/assets/js/plugin/flot/jquery.flot.cust.min.js', 'comment' => 'create utilities'));
		$this->layout->add_js_file(array('src' => '/assets/js/plugin/flot/jquery.flot.resize.min.js', 'comment' => 'create utilities'));
		$this->layout->add_js_file(array('src' => '/assets/js/plugin/flot/jquery.flot.fillbetween.min.js', 'comment' => 'create utilities'));
		$this->layout->add_js_file(array('src' => '/assets/js/plugin/flot/jquery.flot.orderBar.min.js', 'comment' => 'create utilities'));
		$this->layout->add_js_file(array('src' => '/assets/js/plugin/flot/jquery.flot.time.min.js', 'comment' => 'create utilities'));
		$this->layout->add_js_file(array('src' => '/assets/js/plugin/flot/jquery.flot.tooltip.min.js', 'comment' => 'create utilities'));
		$this->layout->add_js_file(array('src' => '/assets/js/plugin/flot/jquery.flot.axislabels.js', 'comment' => 'create utilities'));
		
		//check if printer is already printing or milling
		if($type == 'subtractive'){
			$data['valid_head'] = in_array(get_head(), $this->_MILL_VALID_HEADS);
			$task = $this->tasks->get_running('make', 'mill');
		}else{
			$data['valid_head'] = in_array(get_head(), $this->_PRINT_VALID_HEADS);
			$task = $this->tasks->get_running('make', 'print');
		}
		
		if($task && !file_exists($this->_RUN_FILE)) {
			$this->tasks->delete($task['id']);
			$task = false;
		}
			
		$data['running'] = $task == false ? false : true;
		$data['label']   = $type == 'subtractive' ? 'Mill' : 'Print';
		$data['type']    = $type;
		$data['task']    = $task;
		
		// ========= settings for step1
		$step1['objects']        = ! $data['running'] ? $this->objects->get_for_create($type) : array();
		$step1['last_creations'] = ! $data['running'] ? $this->tasks->get_last_creations($this->_MAKE_TYPES[$type]) : array();
		$step1['status_label']   = array('performed' => '<span class="label label-success">COMPLETED</span>', 'stopped' => '<span class="label label-warning">ABORTED</span>', 'deleted' => '<span class="label label-danger">STOPPED</span>');
		$data_widget_step1['table_objects'] = $this->load->view('index/step1/table_objects', array_merge($step1, $data), TRUE);
		$data_widget_step1['table_recent']  = $this->load->view('index/step1/table_recent',  array_merge($step1, $data), TRUE);
		$widget_content = $this->load->view('index/step1/widget', $data_widget_step1, TRUE);
		$widget_tabs = $this->load->view('index/step1/toolbar', array_merge($step1, $data), TRUE);
		$widget_step1 = widget('widget_step_1' . time(), '', '', $widget_content, false, true, false, $widget_tabs);
		$step1['widget'] = $widget_step1;
		// ==========
		// ========== settings for step5
		$step5['widget'] = $this->load->view('index/step5/widget', $data, TRUE);
		
		// ========== including steps
		$data['step1'] = $this->load->view('index/step1/index', array_merge($step1, $data), TRUE);
		$data['step2'] = $this->load->view('index/step2/index', '', TRUE);
		$data['step4'] = $this->load->view('index/step4/index', '', TRUE);
		$data['step5'] = $this->load->view('index/step5/index', array_merge($step5, $data), TRUE);
		$data['step6'] = $this->load->view('index/step6/index', $data, TRUE);
		
		// ========= include javascript
		$this->layout->add_js_in_page(array('data' => $this->load->view('index/js', $data, TRUE)));
		
		$this->layout->set_compress(false);
		$this->layout->view('index/index', $data);
	}
	
	//show additive o subtractive preparation print
	public function show($type){
		$this -> load -> helper('serial_helper');
		$data = array();
		if ($type == 'additive') {
			$label_button = 'Engage';
			$action_button = 'feeder';
			$data['show_feeder'] = $this -> layout -> getFeeder();
			if (!$data["show_feeder"]) {
				$label_button = 'Continue';
				$action_button = '';
			}
			$data['label_button'] = $label_button;
			$data['action_button'] = $action_button;
		}
		$this -> load -> view('index/ajax/' . $type, $data);
	}

public function history() {

		$this -> load -> model('tasks');
		
		$this -> load -> helper('smart_admin_helper');
		$this -> load -> helper('ft_date_helper');
		
		$data['start_date'] = date('d/m/Y', strtotime('today - 30 days'));
		$data['end_date'] = date('d/m/Y', strtotime('today'));
		
		$data['min_date'] = date('d/m/Y', strtotime($this->tasks->get_min_date('make')));
		
		/** LAYOUT */
		$this -> layout -> add_js_file(array('src' => '/assets/js/plugin/datatables/jquery.dataTables.min.js', 'comment' => ''));
		$this -> layout -> add_js_file(array('src' => '/assets/js/plugin/datatables/dataTables.colVis.min.js', 'comment' => ''));
		$this -> layout -> add_js_file(array('src' => '/assets/js/plugin/datatables/dataTables.tableTools.min.js', 'comment' => ''));
		$this -> layout -> add_js_file(array('src' => '/assets/js/plugin/datatables/dataTables.bootstrap.min.js', 'comment' => ''));
		$this -> layout -> add_js_file(array('src' => '/assets/js/plugin/datatable-responsive/datatables.responsive.min.js', 'comment' => ''));

		$this -> layout -> add_js_file(array('src' => '/assets/js/plugin/bootstrap-datepicker/moment.min.js', 'comment' => ''));
		$this -> layout -> add_js_file(array('src' => '/assets/js/plugin/bootstrap-datepicker/daterangepicker.min.js', 'comment' => ''));
		$this -> layout -> add_css_file(array('src' => '/assets/js/plugin/bootstrap-datepicker/daterangepicker.css', 'comment' => ''));
		
		$this -> layout -> add_js_file(array('src' => '/assets/js/plugin/morris/raphael.min.js', 'comment' => 'charts'));
		$this -> layout -> add_js_file(array('src' => '/assets/js/plugin/morris/morris.min.js', 'comment' => 'charts'));

		$this -> layout -> add_css_in_page(array('data' => $this -> load -> view('history/css', '', TRUE), 'comment' => 'create module'));
		$this -> layout -> add_js_in_page(array('data' => $this -> load -> view('history/js', $data, TRUE), 'comment' => 'create module'));
		
		
		

		$table = $this -> load -> view('history/table', $data, TRUE);
		

		$attr['data-widget-icon'] = 'fa fa-history';
		$data['widget_table'] = widget('history' . time(), 'History', $attr, $table, false, true, false);

		//$attr['data-widget-icon'] = 'fa fa-bar-chart';
		//$data['widget_stats'] = widget('stats', 'Stats', $attr, '', false, false, false);
		$this -> layout -> set_compress(false);

		$this -> layout -> view('history/index', $data);
	}

	function _get_make_tasks($filters) {

		$this -> load -> model('tasks');
		
		return $this -> tasks -> get_make_tasks($filters);
	}

	function history_table_data() 
	{
		//get filter data from $_GET
		$params = $this->input->get();

		$filters['start_date'] = !isset($params['start_date']) || $params['start_date'] == '' ? date('d/m/Y', strtotime('today - 30 days')) : $params['start_date'];
		$filters['end_date']   = !isset($params['end_date'])   || $params['end_date']   == '' ? date('d/m/Y', strtotime('today'))  : $params['end_date'];
		$filters['type']       = isset($params['type']) ? $params['type'] : '';
		$filters['status']     = isset($params['status']) ? $params['status'] : '';

		$tasks = $this -> _get_make_tasks($filters);
		
		
		
		$this -> load -> helper('ft_date_helper');

		$data['icons'] = array('print' => 'icon-fab-print', 'mill' => 'icon-fab-mill', 'scan' => 'icon-fab-scan');

		$data['status_label'] = array('performed' => '<span class="label label-success">COMPLETED</span>', 'stopped' => '<span class="label label-warning">ABORTED</span>', 'deleted' => '<span class="label label-danger">STOPPED</span>');

		$aaData = array();

		foreach ($tasks as $task) {

			$attributes = json_decode(utf8_encode(preg_replace('!\\r?\\n!', "<br>", $task['task_attributes'])), true);
			
			

			$when = strtotime($task['finish_date']) > strtotime("-1 day") ? get_time_past($task['finish_date']) . ' ago' : date('d M, Y', strtotime($task['finish_date']));
			$info = '<h4>';
			if ($task['file_name'] != '')
				$info .= '<a href="' . site_url('objectmanager/edit/' . $task['id_object']) . '"><i class="fa fa fa-file-o"></i> ' . $task['raw_name'] . '</a>';
			if ($task['object_name'] != '')
				$info .= ' <small>> <i class="fa fa fa-folder-open-o"></i> ' . $task['object_name'] . '</small>';
			if (isset($attributes['mode_name']) && $attributes['mode_name'] != '')
				$info .= '<a href="#">' . ucfirst($attributes['mode_name']) . '</a><small> </small>';
			$info .= '</h4>';


			$td_0 = '<a href="#" > <i class="fa fa-chevron-right fa-lg" data-toggle="row-detail" title="Show Details"></i> </a>';
			$td_1 = $when;
			$td_2 = '<strong><i class="<' . $data['icons'][$task['type']] . '"></i> <span class="hidden-xs">' . ucfirst($task['type']) . '</strong></span>';
			$td_3 = $data['status_label'][$task['status']];
			$td_4 = $info;
			$td_5 = $task['duration'];
			$td_6 = date('d M, Y', strtotime($task['start_date'])) . ' at ' . date('G:i', strtotime($task['start_date']));
			$td_7 = date('d M, Y', strtotime($task['finish_date'])) . ' at ' . date('G:i', strtotime($task['finish_date']));
			$td_8 = isset($attributes['note']) ? $attributes['note'] : '';
			$td_9 = $task['type'];
			$td_10 = $task['id_file'];
			$td_11 = $task['id_object'];

			$aaData[] = array($td_0, $td_1, $td_2, $td_3, $td_4, $td_5, $td_6, $td_7, $td_8, $td_9, $td_10, $td_11);

		}

		$stats = $this -> load -> view('history/stats', $data, TRUE);

		echo json_encode(array('aaData' => $aaData));

	}

	function history_stats_data() {
		
		//get request data
		$params = $this->input->get();
		
		$filters['start_date'] = $params['start_date'] == '' ? date('d/m/Y', strtotime('today - 30 days')) : $params['start_date'];
		$filters['end_date']   = $params['end_date']   == '' ? date('d/m/Y', strtotime('today')) : $params['end_date'];
		$filters['type']       = $params['type'];
		$filters['status']     = $params['status'];
		
		$tasks = $this -> _get_make_tasks($filters);
		
		$this -> load -> helper('ft_date_helper');
		
		$data['icons'] = array('print' => 'icon-fab-print', 'mill' => 'icon-fab-mill', 'scan' => 'icon-fab-scan');

		$data['status_label'] = array('performed' => '<span class="label label-success">COMPLETED</span>', 'stopped' => '<span class="label label-warning">ABORTED</span>', 'deleted' => '<span class="label label-danger">STOPPED</span>');

		$data['stats_label'] = array('total_time' => '<i class="fa fa-clock-o"></i> Total time', 'performed' => '<i class="fa fa-check"></i> Completed', 'stopped' => '<i class="fa fa-times"></i> Aborted', 'deleted' => '<i class="fa fa-ban"></i> Stopeed');

		$data['type_options'] = array('print' => 'Print', 'mill' => 'Mill', 'scan' => 'Scan');

		$data['status_options'] = array('performed' => 'Completed', 'stopped' => 'Aborted', 'deleted' => 'Stopped');
		$data['status_colors']  = array('performed' => '#7e9d3a',   'stopped' => '#FF9F01', 'deleted' => '#a90329');
		

		$data['stats'] = array();
		
		if(count($tasks) > 0 ){ //if are tasks
			
			if ($filters['type'] == '') {
	
				foreach ($data['type_options'] as $type => $label) {
					if ($type != '')
						$data['stats'][$type]['total_time'] = $this -> tasks -> get_total_time('make', $type, $filters['status'], $filters['start_date'], $filters['end_date']);
						
						
						
					if ($filters['status'] == '') {
						foreach ($data['status_options'] as $status => $label) {
							if ($status != '')
								$data['stats'][$type][$status] = $this -> tasks -> get_total_tasks('make', $type, $status, $filters['start_date'], $filters['end_date']);
						}
					} else {
						$data['stats'][$type][$filters['status']] = $this -> tasks -> get_total_tasks('make', $type, $filters['status'], $filters['start_date'], $filters['end_date']);
					}
	
				}
	
			} else {
				$data['stats'][$filters['type']]['total_time'] = $this -> tasks -> get_total_time('make', $filters['type'], $filters['status'], $filters['start_date'], $filters['end_date']);
	
				if ($filters['status'] == '') {
					foreach ($data['status_options'] as $status => $label) {
						if ($status != '')
							$data['stats'][$filters['type']][$status] = $this -> tasks -> get_total_tasks('make', $filters['type'], $status, $filters['start_date'], $filters['end_date']);
					}
				} else {
					$data['stats'][$filters['type']][$filters['status']] = $this -> tasks -> get_total_tasks('make', $filters['type'], $filters['status'], $filters['start_date'], $filters['end_date']);
				}
			}
		}
		
		
		echo $stats = $this -> load -> view('history/stats', $data, TRUE);
		
		

	}
	
 }
?>
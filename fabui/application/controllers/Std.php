<?php
/**
 * 
 * @author Daniel Kesler
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */
 defined('BASEPATH') OR exit('No direct script access allowed');
 
class Std extends FAB_Controller {

	protected $runningTask = false;

	function __construct(){
		parent::__construct();
		if(!$this->input->is_cli_request()){
			$this->load->model('Tasks', 'tasks');
			$this->runningTask = $this->tasks->getRunning();
		}
	}

	public function index(){
		
	}
	
	public function storePosition($task_type = '')
	{
		$data = $this->input->post();
		$this->load->helper('fabtotum_helper');
		
		$result = false;
		if($task_type)
		{
			savePosition($data['x'], $data['y'], $data['z'], $task_type);
			$result = true;
		}
		
		$this->output->set_content_type('application/json')->set_output(json_encode(array("result"=>$result, "data"=>$data)));
	}

	/**
	 * @task_type type (additive, subtractive)
	 * @return json object for dataTables plugin
	 * get all files of task_type
	 */
	public function getFiles($task_type = '')
	{
		//load libraries, models, helpers
		$this->load->model('Files', 'files');
		$files = $this->files->getForCreate( $task_type );
		$aaData = $this->dataTableFormat($files);
		$this->output->set_content_type('application/json')->set_output(json_encode(array('aaData' => $aaData)));
	}
	
	/**
	 * @task_type type (additive, subtractive)
	 * @return json object for dataTables plugin
	 * get recent files of task_type
	 */
	public function getRecentFiles($task_type = '')
	{
		//load libraries, models, helpers
		$this->load->model('Tasks', 'tasks');
		$files  = $this->tasks->getLastCreations($task_type);
		$aaData = $this->dataTableFormat($files);
		$this->output->set_content_type('application/json')->set_output(json_encode(array('aaData' => $aaData)));
	}

	/**
	 * @param $data (list)
	 * return array data for dataTable pluguin
	 */
	private function dataTableFormat($data)
	{
		//load text helper
		$this->load->helper('text_helper');
		$aaData = array();
		foreach($data as $file){
		    
		    $attributes = json_decode($file['attributes'], true);
		    
		    $preview = '';
		    
		    if(isset($attributes['preview_file']) && file_exists($attributes['preview_file'])){
		        $preview = '<a href="javascript:void(0);" data-placement="right" rel="popover-hover" class="pull-right hidden-xs" data-orginal-title="'.$file['client_name'].'" data-content="<img class=\'tooltip-image-preview\' src=\''.str_replace('/var/www', '', $attributes['preview_file']).'\'>" data-html="true"><i class="fa fa-eye"></i></a>';
		    }
		    
			$td0 = '<label class="radio"><input type="radio" name="create-file" value="'.$file['id_file'].'"><i></i></label>';
			$td1 = '<i class="fa fa-cube hidden-xs"></i> <span class="hidden-xs">'.$file['client_name'].'</span><span class="hidden-md hidden-sm hidden-lg">'.ellipsize($file['orig_name'], 35).'</span>'.$preview;
			$td2 = '<i class="fa fa-cubes"></i> <span class="hidden-xs">'.$file['name'].'</span><span class="hidden-md hidden-sm hidden-lg">'.ellipsize($file['name'], 35).'</span>';
			$td3 = $file['id_file'];
			$td4 = $file['id_object'];
			$aaData[] = array($td0, $td1, $td2, $td3, $td4);
		}
		
		return $aaData;
	}
	
	public function saveQualityRating($taskID, $rating)
	{
		$this->load->model('Tasks', 'tasks');
		
		$result = false;
		
		$task = $this->tasks->get($taskID, 1);
		if($task)
		{
			$attributes = json_decode(utf8_encode(preg_replace('!\\r?\\n!', "<br>", $task['attributes'])), true);
			$attributes['rating'] = $rating;
			
			$json = json_encode( $attributes );
			
			
			$taskData = array(
				'attributes' => $json
			);
			$this->tasks->update($taskID, $taskData);
			
			$result = true;
		}
		
		$this->output->set_content_type('application/json')->set_output(json_encode(array($result)));
	}
	
	public function safetyCheck($feature, $bed_in_place)
	{
		$this->load->helper('fabtotum_helper');
		/*switch($bed_in_place)
		{
			case "yes":
				$bed_check = 'true';
				break;
			case "no":
				$bed_check = false;
				break;
			default:
				$bed_check = 'any';
				break;
		}*/
		$result = safetyCheck($feature, $bed_in_place);
		$this->output->set_content_type('application/json')->set_output(json_encode($result));
	}
	
	public function sendTaskEmail($taskID = 0)
	{
		$this->load->helper('fabtotum_helper');
		$this->load->model('User', 'user');
		$this->load->model('Files', 'files');
		$this->load->model('Objects', 'objects');
		$this->load->model('Tasks', 'tasks');
		$this->load->database();
		$this->load->library(array('session', 'parser'));
		$this->load->helper(array('url', 'language', 'update', 'utility'));
		
		$task = $this->tasks->get($taskID, 1);
		$user = false;
		$file = false;
		$object = false;
		if($task)
		{
			$user = $this->user->get($task['user'], 1);
			$file = $this->files->get($task['id_file'], 1);
			$object = $this->objects->get($task['id_object'], 1);
			
			if($user)
			{
				$settings = json_decode($user['settings'], 1);
				$lang_code = $settings['language'];
				setLanguage($lang_code);
				echo "Language: ".$lang_code.PHP_EOL;
			}
		}
		
		$email      = $user['email'];
		$first_name = $user['first_name'];
		$last_name  = $user['last_name'];
		$task_start = $task['start_date'];
		$task_finish = $task['finish_date'];
		$task_duration = "";
		$task_status = $task['status'];
		$task_type   = $task['type'];
		$task_object = $object['name'];
		$task_file   = $file['client_name'];
		
		$duration = dateDiff($task_finish, $task_start);
		$time_labels = array('year', 'month', 'day', 'hour', 'minute', 'second');
		foreach($time_labels as $label)
		{
			$found = false;
			if( array_key_exists($label, $duration))
			{
				$found = true;
			}
			else if(array_key_exists($label.'s', $duration))
			{
				$label .= 's';
				$found = true;
			}
			
			if($found)
			{
				$task_duration .= $duration[$label] . ' ' . _($label) . ' ';
			}
		}
		
		/*echo "Email: ".$user['email'].PHP_EOL;
		echo "== Task ==".PHP_EOL;
		echo "start: ". $task_start.PHP_EOL;
		echo "finish: ". $task_finish.PHP_EOL;
		echo "duration: ". $task_duration.PHP_EOL;
		echo "status: ". $task_status.PHP_EOL;
		echo "object: ". $task_object.PHP_EOL;
		echo "file: ". $task_file.PHP_EOL;*/
		
		$subject = _('Task') .' '. _($task_status);
		$content = pyformat( _('Hello {0},<br><br> this e-mail is to inform you that the last {1} you started was just {2}.'), array($first_name, $task_type, $task_status) );
		
		$content .= '<br><br>';
		
		$content .= '<h2>Info</h2>';
		$content .= '<p>Started on: '.$task_start.'</p>';
		$content .= '<p>Finished on: '.$task_start.'</p>';
		$content .= '<p>Duration: '.$task_duration.'</p>';
		$content .= '<p>File: '.$task_file.'</p>';
		
		$result = send_via_noreply($email, $first_name, $last_name, $subject, $content);
		
		$this->output->set_content_type('application/json')->set_output(json_encode($result));
	}
 }
 
?>


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
   
    
   
   function __construct(){
        parent::__construct();
       
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
            
            $attributes = isset($file['attributes']) ?  json_decode($file['attributes'], true) : array();
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
    
    /**
     * 
     */
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
    
    /**
     * 
     */
    public function safetyCheck($feature, $bed_in_place)
    {
        $this->load->helper('fabtotum_helper');
        $result = safetyCheck($feature, $bed_in_place);
        $this->output->set_content_type('application/json')->set_output(json_encode($result));
    }
    
    /**
     *
     */
    public function sendTaskEmail($taskID = 0)
    {
        //load helpers
        $this->load->helper(array('fabtotum_helper', 'url', 'language', 'update', 'utility'));
        
        //load models
        $this->load->model('User', 'user');
        $this->load->model('Files', 'files');
        $this->load->model('Objects', 'objects');
        $this->load->model('Tasks', 'tasks');
        
        //important
        $this->load->library('parser');
        $this->load->database();
        
        //get task
        $task = $this->tasks->get($taskID, 1);
        
        $userID = $task['user'];
        $fileID = $task['id_file'];
        $projectID = $task['id_object'];
        
        //get user
        $user = $this->user->get($userID, 1);
        
        //get file
        $file = $this->files->get($fileID, 1);
        
        //get project
        $project = $this->objects->get($projectID, 1);
        
        
        $data['task'] = $task;
        $data['user'] = $user;
        $data['file'] = $file;
        $data['project'] = $project;
        
        $data['duration'] = dateDiff($task['finish_date'], $task['start_date']);
        $data['task_duration'] = '';
        
        // user settings
        $user_settings = json_decode($user['settings'], 1);
        
        $result['status'] = false;
        
        if((isset($user_settings['notifications']['tasks']['pause']) && $user_settings['notifications']['tasks']['finish'] == 'true') ){
        
            $time_labels = array('year', 'month', 'day', 'hour', 'minute', 'second');
            foreach($time_labels as $label)
            {
                $found = false;
                if( array_key_exists($label, $data['duration']))
                {
                    $found = true;
                }
                else if(array_key_exists($label.'s', $data['duration']))
                {
                    $label .= 's';
                    $found = true;
                }
                
                if($found)
                {
                    $data['task_duration'] .= $data['duration'][$label] . ' ' . _($label) . ' ';
                }
            }
            
            
            
            //set language
            $lang_code = $user_settings['locale'];
            setLanguage($lang_code);
            
            $this->content = $this->load->view('std/email/task', $data, true );
            
            $subject = _('Task') .' '. _($task['status']);
            $page    = $this->layoutEmail(true);
            
            
            //send email
            $result['status'] = send_via_noreply($user['email'], $user['first_name'], $user['last_name'],  $subject, $page);
            if($result['status'] == false) $result['message'] = _("Email sending failed ");
            
        }else{
            $result['message'] = _("Notification disabled");
        }
        
        
        $this->output->set_content_type('application/json')->set_output(json_encode($result));
    }
    
    /**
     * 
     */
    public function sendPauseTaskEmail($taskID = 0, $force='')
    {
        //load helpers
        $this->load->helper(array('fabtotum_helper', 'url', 'language', 'update', 'utility'));
        
        //load models
        $this->load->model('User', 'user');
        $this->load->model('Files', 'files');
        $this->load->model('Objects', 'objects');
        $this->load->model('Tasks', 'tasks');
        
        //important
        $this->load->library('parser');
        $this->load->database();
        
        //get task
        $task = $this->tasks->get($taskID, 1);
        
        $userID = $task['user'];
        $fileID = $task['id_file'];
        $projectID = $task['id_object'];
        
        //get user
        $user = $this->user->get($userID, 1);
        //get file
        $file = $this->files->get($fileID, 1);
        //get project
        $project = $this->objects->get($projectID, 1);
        
        // user settings
        $user_settings = json_decode($user['settings'], 1);
        
        $result['status'] = false;
        
        //send only if notification is active
        if((isset($user_settings['notifications']['tasks']['pause']) && $user_settings['notifications']['tasks']['pause'] == 'true') || $force != ''){
        
            //set language
            $lang_code = $user_settings['locale'];
            setLanguage($lang_code);
            
            $data['task'] = $task;
            $data['user'] = $user;
            $data['file'] = $file;
            $data['project'] = $project;
            
            $this->content = $this->load->view('std/email/pause', $data, true );
            $page    = $this->layoutEmail(true);
            
            
            $construction_sign = "\xF0\x9F\x9A\xA7";
            $pushpin = "\xF0\x9F\x93\x8C";
            $triangle = "\xF0\x9F\x94\xBD";
            
            $subject = $pushpin.' "'.$file['client_name'].'" '. _('paused');
            
            //send email
            $result['status'] = send_via_noreply($user['email'], $user['first_name'], $user['last_name'],  $subject, $page);
            
            if($result['status'] == false) $result['message'] = _("Email sending failed ");
            
        }else{
            $result['message'] = _("Notification disabled");
        }

        $this->output->set_content_type('application/json')->set_output(json_encode($result));
    }
    
    
}

?>


<?php
/**
 * 
 * @author Krios Mane
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */
 defined('BASEPATH') OR exit('No direct script access allowed');
 
 class Backup extends FAB_Controller {
 	
	public function index(){
	    
	    //load librarire, helpers, config
	    $this->load->library('smart');
	    $this->config->load('fabtotum');
	    $this->load->helper(array('fabtotum_helper', 'update_helper', 'os_helper', 'date_helper'));
	    
	    
	    $data['max_upload_file_size'] = 8192;
	    
	    
	    $widgetOptions = array(
	        'sortable'     => false, 'fullscreenbutton' => true,  'refreshbutton' => false, 'togglebutton' => false,
	        'deletebutton' => false, 'editbutton'       => false, 'colorbutton'   => false, 'collapsed'    => false
	    );
	    
	    $widget         = $this->smart->create_widget($widgetOptions);
	    $widget->id     = 'main-widget-systeminfo';
	    $widget->header = array('icon' => 'icon-electronics-089', "title" => "<h2>"._("Backup or restore your files")."</h2>");
	    $widget->body   = array('content' => $this->load->view('backup/widget', null, true ), 'class'=>'');
	    
	    $this->addJSFile('/assets/js/plugin/dropzone/dropzone.js'); // dropzpone
	    $this->addJsInLine($this->load->view('backup/js', $data, true));
	    
	    $this->content  = $widget->print_html(true);
	    $this->view();
	    
	}
	
	/**
	 * 
	 */
	public function doBackup()
	{
	    //post data
	    $data = $this->input->post();
	    
	    //load helpers, libraries, config
	    $this->config->load('fabtotum');
	    $this->load->helper(array('fabtotum_helper', 'download', 'update_helper'));
	    
	    // init
	    $mode = isset($data['mode']) ? $data['mode'] : 'default';
	    // 
	    $date    = date('Y_m_d_h_i_s');
	    $archive = $this->config->item('temp_path').'/backup_'.$date.'.faback';
	    
	    $default_folders = array(
	        '/mnt/userdata/cam',
	        '/mnt/userdata/feeders',
	        '/mnt/userdata/heads',
	        '/mnt/userdata/settings',
	        '/mnt/userdata/uploads'
	    );
	    
	    $advanced_folders = array(
	        'system-heads'    => '/mnt/userdata/heads',
	        'system-feeders'  => '/mnt/userdata/feeders',
	        'system-settings' => '/mnt/userdata/settings/settings.json',
	        'system-cam'      => '/mnt/userdata/cam',
	        'users-data'      => '/mnt/userdata/settings/fabtotum.db /mnt/userdata/uploads'
	    );
	    
	    
	    $args = array(
	        '-a' => $archive
	    );
	    
	    if($mode == 'advanced'){
	        $advanced = explode(",", $data['advanced']);
	        
	        $temp_list = array();
	        
	        foreach($advanced as $key){
	            
	            if(isset($advanced_folders[$key])){
	                $temp_list[] = $advanced_folders[$key];
	            }
	        }
	        $list = implode(" ", $temp_list);
	        
	        if($data['firmware'] == true){
	            $args['-f'] = '';
	            stopServices();
	            if(flashFirmware('dump-eeprom'))
	                $list .=' /tmp/fabui/dumped_eeprom.hex';
	            startServices();
	            sleep(3);
	        }
	        
	    }else{
	        $list = implode(" ", $default_folders);
	    }
	    
	    $args['-l'] = $list;
	    
	    //remove old files
	    shell_exec('sudo rm -rvf '.$this->config->item('temp_path').'/*.faback');
	    startBashScript('backup.sh', $args, false);
	    
	    $this->output->set_content_type('application/json')->set_output(json_encode(array('file' => base64_encode($archive))));
	}
	
	/**
	 * 
	 */
	public function download($file)
	{
	    $file = base64_decode($file);
	    
	    if(file_exists($file)){
	       
	       //load helpers, libraries
	       $this->load->helper(array('fabtotum_helper', 'download'));
	       force_download($file, null);
	    }else{
	        show_404();
	    }
	}
	
	/**
	 * 
	 */
	public function upload()
	{
	    // load config
	    $this->config->load('fabtotum');
	    
	    
	}
 }
?>

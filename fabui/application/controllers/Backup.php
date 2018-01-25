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
	    
	    $widgetOptions = array(
	        'sortable'     => false, 'fullscreenbutton' => true,  'refreshbutton' => false, 'togglebutton' => false,
	        'deletebutton' => false, 'editbutton'       => false, 'colorbutton'   => false, 'collapsed'    => false
	    );
	    
	    $widget         = $this->smart->create_widget($widgetOptions);
	    $widget->id     = 'main-widget-systeminfo';
	    $widget->header = array('icon' => 'icon-electronics-089', "title" => "<h2>"._("Backup or restore your files")."</h2>");
	    $widget->body   = array('content' => $this->load->view('backup/widget', null, true ), 'class'=>'');
	    
	    $this->addJsInLine($this->load->view('backup/js', null, true));
	    
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
	    $this->load->helper(array('fabtotum_helper', 'download'));
	    
	    // init
	    $mode = isset($data['mode']) ? $data['mode'] : 'default';
	    
	    $date = date('Y_m_d_h_i_s');
	    $archive = $this->config->item('temp_path').'/backup_'.$date.'.faback';
	    
	    $default_folders = array(
	        '/mnt/userdata/cam',
	        '/mnt/userdata/feeders',
	        '/mnt/userdata/heads',
	        '/mnt/userdata/settings',
	        '/mnt/userdata/uploads'
	    );
	    
	    $args = array(
	        '-a' => $archive,
	        '-l' => $mode == 'default' ? implode(" ", $default_folders) : ""
	    );
	    
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
 }
?>

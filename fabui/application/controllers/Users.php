<?php
/**
 * 
 * @author Krios Mane
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */
 defined('BASEPATH') OR exit('No direct script access allowed');
 
 class Users extends FAB_Controller {
 	
	public function index()
	{
	    /**
	     * load helpers, libraries
	     */
	    $this->load->library('smart');
	    $this->load->helper('layout');
	    
	    $data = array();
	    $data['fabid_active'] = $this->config->item('fabid_active');
	    
	    /**
	     * init widget
	     */
	    $widgetOptions = array(
	        'sortable' => false, 'fullscreenbutton' => true,'refreshbutton' => false,'togglebutton' => false,
	        'deletebutton' => false, 'editbutton' => false, 'colorbutton' => false, 'collapsed' => false
	    );
	    
	    $widget = $this->smart->create_widget($widgetOptions);
	    $widget->id = 'users-widget';
	    $widget->header = array('icon' => 'fa-users', "title" => "<h2>"._("Users")."</h2>", );
	    $widget->body   = array('content' => $this->load->view('users/index/widget', $data, true ), 'class'=>'no-padding');
	    $this->content  = $widget->print_html(true);
	    
	    /**
	     * add needed javascript/css
	     */
	    $this->addJSFile('/assets/js/plugin/datatables/jquery.dataTables.min.js'); //datatable
	    $this->addJSFile('/assets/js/plugin/datatables/dataTables.colVis.min.js'); //datatable
	    $this->addJSFile('/assets/js/plugin/datatables/dataTables.tableTools.min.js'); //datatable
	    $this->addJSFile('/assets/js/plugin/datatables/dataTables.bootstrap.min.js'); //datatable
	    $this->addJSFile('/assets/js/plugin/datatable-responsive/datatables.responsive.min.js'); //datatable */
	    $this->addJsInLine($this->load->view('users/index/js','', true));
	    
	    /**
	     * output
	     */
	    $this->view();
	}
	
	/**
	 * 
	 */
	public function getUsers()
	{
	    /**
	     * load model
	     */
	    $this->load->model('User', 'users');
	    
	    /**
	     * get all users
	     */
	    $users = $this->users->get();
	    
	    /**
	     * get logged user
	     */
	    $logged_user = $this->session->user;
	    
	    /**
	     * prepare output for dataTable
	     */
	    $aaData = array();
	    
	    /**
	     * get if fabid is active
	     */
	    $fabid_active = $this->config->item('fabid_active');
	    
	    foreach($users as $user)
	    {
	        /**
	         * get settings
	         */
	        $settings = json_decode($user['settings'], true);
	        
	        /**
	         * set image
	         */
	        $image = '/assets/img/avatars/male.png';
	        if(isset($settings['image'])){
	            
	            if(file_exists($settings['image']['full_path'])){
	                $image = $settings['image']['url'];
	            }
	            
	        }
	        
	        /**
	         * set fabid
	         */
	        $fabid = '';
	        if(isset($settings['fabid']['email'])){
	            $fabid = $settings['fabid']['email'];
	        }
	        
	        
	        /**
	         * button action
	         */
	        $button = '';
	        if($logged_user['role'] == 'administrator'){
	            
	            /**
	             * set actions
	             */
	            $actions = '<li><a data-email="'.$user['email'].'" class="reset-password" href="javascript:void(0);"><i class="fa fa-key"></i> '._("Reset password").'</a></li>';
	            /**
	             * i can't remove logged user
	             */
	            if($logged_user['id'] != $user['id']){
	                $actions .= '<li><a data-id="'.$user['id'].'" class="delete-user" href="javascript:void(0);"><i class="fa fa-times"></i> '._("Delete").'</a></li>';
	            }
	           
	            $button = '<div class="btn-group">
						      <button class="btn btn-default dropdown-toggle" data-toggle="dropdown"><i class="fa fa-ellipsis-v"></i></button>
							  <ul class="dropdown-menu pull-right">'.$actions.'</ul>
                		   </div>';
	        }
	        
	        /**
	         * add user
	         */
	        $tableRow = array();
	        
	        $tableRow[] =  '<img src="'.$image.'" width="30" />';
	        $tableRow[] = $user['role'];
	        $tableRow[] = $user['last_name'].' '.$user['first_name'];
	        $tableRow[] = $user['email'];
	        
	        if($fabid_active){
	            $tableRow[] = $fabid;
	        }
	        
	        $tableRow[] = date('d/m/Y H:i', strtotime($user['last_login']));
	        $tableRow[] = $button;
	        $tableRow[] = $user['id'];
	        
	        $aaData[] = $tableRow;
	    }
	       
	    /**
	     * output
	     */
	    $output = array('aaData' => $aaData);
	    $this->output->set_content_type('application/json')->set_output(json_encode($output));
	    
	}
	
	/**
	 * delete user
	 * if new_owner_id exists trasnfer all data to that it
	 */
	public function deleteUser($id, $new_owner_id = ''){
	    /**
	     * load models
	     */
	    $this->load->model('User', 'users');
	    $this->load->model('Objects', 'objects');
	    
	    /**
	     * delete from users table
	     */
	    $this->users->delete($id);
	    
	    /**
	     * if transfer data 
	     */
	    if($new_owner_id != ''){
	        /**
	         * transfer data
	         * change projects owner
	         */ 
	        $this->objects->transfer($id, $new_owner_id);
	        
	    }else{
	        /**
	         * delete all: projects - files
	         */
	        $this->load->model('Files', 'files');
	        
	        /**
	         * get all projects
	         */
	        $projects = $this->objects->get(array('user' => $id));
	        
	        /**
	         * delete all files
	         * 
	         */
	        foreach($projects as $project){
	           
	            $files     = $this->files->getByObject($project['id']);
	            $files_ids = array();
	            
	            foreach($files as $file)
	            {
	                /**
	                 * remove file
	                 */
	                $files_ids[] = $file['id'];
	                $file = $this->files->get($file['id'], True);
	                shell_exec('sudo rm '.$file['full_path']);
	                $this->files->delete( $file['id'] );
	            }
	            
	            /**
	             * delete projects and associations
	             */
	            $this->objects->deleteFiles($project['id'], $files_ids);
	            $this->objects->delete( $project['id'] );
	        }
	       
	    }
	    
	    /**
	     * output
	     */
	    $output = array();
	    $this->output->set_content_type('application/json')->set_output(json_encode($output));
	}

 }
 
?>

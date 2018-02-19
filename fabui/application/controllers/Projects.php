<?php
/**
 * 
 * @author Krios Mane
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */
 defined('BASEPATH') OR exit('No direct script access allowed');
 
class Projects extends FAB_Controller {

    protected $categories = array(
        'Jewelery & Fashion' => 'Jewelery & Fashion',
        'Home Living'        => 'Home Living',
        'Tech'               => 'Tech',
        'Toys & Games'       => 'Toys & Games',
        'Design'             => 'Design',
        'People & Animals'   => 'People & Animals',
        'Gadgets'            => 'Gadgets',
        'Art'                => 'Art',
        'Miniatures'         => 'Miniatures'
    );
    
    protected $tools = array(
        'Hybrid Head'        => 'Hybrid Head',
        'Printing Head Lite' => 'Printing Head',
        'Milling Head V2'    => 'Milling Head',
        'Priting Head Pro'   => 'Printing Head Pro',
        'Laser Head'         => 'Laser Head',
        'Laser Head Pro'     => 'Laser Head Pro'
    );
    
    protected $accepted_files = array( ".stl", ".gcode", ".gc", ".dxf", ".nc");
    
    /**
     * 
     */
    public function index()
    {
        $this->addJSFile('/assets/js/controllers/projects/common.js');
        $this->addJsInLine($this->load->view('projects/index/js', null, true));
        $this->content = $this->load->view('projects/index/index', null, true );
        $this->view();
    }
    
    /**
     *  get all projects
     *  @TODO load from local json that should be synchronized 
     */
    public function get_all_projects($remote_sync=0)
    { 
        $fabid = $this->session->userdata['user']['settings']['fabid']['email'];
        /**
         * sync with deshpe server
         */
        if($remote_sync == 1){
            $this->load->helper(array('deshape_helper', 'myfabtotum_helper'));
            $access_token = fab_authenticate($fabid, 'f@bt0tum');
            sync_projects($fabid, $access_token);
        }
        
        $this->load->model('ProjectsModel', 'projects');
        $projects = $this->projects->get( array('fabid'=> $fabid));
        $this->output->set_content_type('application/json')->set_output(json_encode($projects));
        
        
    }
    
    /**
     * 
     */
    public function add()
    {
        //load libraries, helpers, model, config
        $this->load->library('smart');
        $this->load->helper(array('form_helper'));
        $this->config->load('fabtotum');
        
        
        $data = array(
            'categories'     => $this->categories,
            'tools'          => $this->tools,
            'accepted_files' => $this->accepted_files
        );
        
        //main page widget
        $widgetOptions = array(
            'sortable' => false, 'fullscreenbutton' => false,'refreshbutton' => false,'togglebutton' => false,
            'deletebutton' => false, 'editbutton' => false, 'colorbutton' => false, 'collapsed' => false
        );
         
        //$widgeFooterButtons = '<button class="btn btn-default" id="add-part"><i class="fa fa-plus"></i> '._("Add part").'</button> <button class="btn btn-primary"><i class="fa fa-save"></i> '._("Save").'</buttn>';
        $widget = $this->smart->create_widget($widgetOptions);
        $widget->id     = 'create-project';
        $widget->header = array('icon' => 'fa-cubes', "title" => "<h2>". _("Create new project"). "</h2>");
        $widget->body   = array('content' => $this->load->view('projects/add/widget', $data, true ), 'class'=>'');
        
        $data['widget'] = $widget->print_html(true);
        
        
        $this->addJSFile('/assets/js/plugin/dropzone/dropzone.js'); // dropzpone
        $this->addJSFile('/assets/js/plugin/bootstrapvalidator/bootstrapValidator.min.js');
        $this->addJSFile('/assets/js/plugin/select2/select2.min.js');
        $this->addJSFile('/assets/js/controllers/projects/common.js');
        
        $this->addJsInLine($this->load->view('projects/add/js', $data, true));
        $this->content = $this->load->view('projects/add/index', $data, true );
        $this->view();
    }
    
    public function getPartForm($idx = 1)
    {
        $this->load->helper(array('form_helper'));
        
        if($idx < 1) $idx = 1;
        
        $data = array(
            'categories' => $this->categories,
            'tools'      => $this->tools,
            'index'      => $idx
        );
        $this->load->view('projects/add/part-form', $data );
    }
    
    /**
     * 
     */
    public function edit($project_id)
    {
        
    }
    
    
    /**
     * 
     */
    public function upload($type)
    {
        //load config
        $this->config->load('upload');
        //load helpers
        $this->load->helper(array('file_helper', 'file', 'fabtotum_helper'));
        /**
         *  get file extension to save the file in the correct directory
         */
        $fileExtension = getFileExtension($_FILES['file']['name']);
        /**
         * preaprea configs for upload library
         * crate folder extension if doesn't exist
         */
        $upload_path        = $this->config->item('upload_path');
        $folder_destination = $upload_path . $fileExtension . '/';
        if(!file_exists($folder_destination)) createFolder($folder_destination);
        
        /**
         * init upload library
         */
        $config['upload_path']      = $upload_path.$fileExtension;
        $config['allowed_types']    = str_replace(".", "", implode('|', $this->accepted_files));
        $config['file_ext_tolower'] = true ;
        $config['remove_spaces']    = true ;
        $config['encrypt_name']     = true;
       
        $this->load->library('upload', $config);
        /**
         * do upload
         */
        if($this->upload->do_upload('file')) {
            /**
             * load  model
             */
            $this->load->model('Deshapefiles', 'files');
            /**
             * retrieve data from upload
             */
            $data = $this->upload->data();
            /**
             * init record for db storage
             */
            $row['title']         = $data['raw_name'];
            $row['name']          = $data['orig_name']; 
            $row['type']          = $fileExtension;
            $row['file_path']     = $folder_destination;
            $row['full_path']     = $folder_destination . $data['file_name'];
            $row['size']          = $data['file_size'];
            $row['creation_date'] = date('Y-m-d H:i:s');
            /**
             * save record
             */
            $fileId = $this->files->add($row);
            $response = array(
                'upload' => true,
                'file_id' => $fileId
            );
        }else{
            $response = array(
                'upload'=>false,
                'message' => $this->upload->display_errors()
            );
        }
        /**
         * output
         */
        $this->output->set_content_type('application/json')->set_output(json_encode($response));
       
    }
    
    /**
     * 
     */
    public function action($action)
    {
        
        switch($action)
        {
            case 'create-new-project':
                $data = $this->input->post();
                $this->create_new_project($data);
                break;
        }
    }
    
    /**
     * 
     */
    public function create_new_project($data)
    {
        $this->load->helpers(array('utility_helper'));
        
        $data = arrayFromPost($data);
        
        echo "<pre>";
        print_r($data);
        echo "</pre>";
    }
} 
?>

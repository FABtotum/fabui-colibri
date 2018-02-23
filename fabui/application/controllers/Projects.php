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
        'Jewelry & Fashion'  => 'Jewelry & Fashion',
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
    
    protected $accepted_source_files  = array( ".stl", ".dxf", ".png", ".jpg");
    protected $accepted_machine_files = array( ".gcode", ".gc", ".nc");
    
    protected $limit  = 10;
    protected $offset = 0;
    
    /**
     * 
     */
    public function index()
    {
        $data['default_limit']  = $this->limit;
        $data['default_offset'] = $this->offset;
        $this->addCssFile('/assets/css/projects/style.css');
        $this->addJSFile('/assets/js/controllers/projects/common.js');
        $this->addJsInLine($this->load->view('projects/index/js', $data, true));
        $this->content = $this->load->view('projects/index/index', $data, true );
        $this->view();
    }
    
    /**
     *  get all projects
     *  @TODO load from local json that should be synchronized 
     */
    public function get_all_projects($remote_sync=0, $limit=10, $offset=0)
    { 
        $fabid = $this->session->userdata['user']['settings']['fabid']['email'];
        /**
         * sync with cloud server
         */
        if($remote_sync == 1){
            $this->load->helper(array('deshape_helper', 'myfabtotum_helper'));
            $access_token = fab_authenticate($fabid, '****');
            sync_projects($fabid, $access_token);
        }
        
        $this->load->model('ProjectsModel', 'projects');
        $projects = $this->projects->get_list($fabid, array('creation_date' => 'DESC'), array('limit'=>$limit, 'offset'=>$offset));
        /**
         * output
         */
        $output = array(
            'projects' => $projects,
            'next_offset' => $offset + 10,
            'limit' => $limit
        );
        $this->output->set_content_type('application/json')->set_output(json_encode($output));
        
        
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
            'accepted_source_files' => $this->accepted_source_files,
            'accepted_machine_files' => $this->accepted_machine_files
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
    public function upload($what="file", $type="source")
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
        if($type=="source"){
            $config['allowed_types']    = str_replace(".", "", implode('|', $this->accepted_source_files));
        }elseif($type="machine"){
            $config['allowed_types']    = str_replace(".", "", implode('|', $this->accepted_machine_files));
        }
        $config['upload_path']      = $upload_path.$fileExtension;
        $config['file_ext_tolower'] = true ;
        $config['remove_spaces']    = true ;
        //$config['encrypt_name']     = true;
       
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
        /**
         * @TODO
         * insert project, and get id
         * insert parts and get id
         * assoc parts and files
         * assoc project and parts
         */
        
        /**
         * load libraries, helpers, config, models
         */
        $this->load->helpers(array('utility_helper', 'myfabtotum_helper', 'deshape_helper'));
        $this->load->model('ProjectsModel', 'projects');
        $this->load->model('Parts', 'parts');
        
        /**
         * retrieve user data
         */
        $user = $this->session->user;
        /**
         * normalize data from post
         */
        $data = arrayFromPost($data);
        $upload_cloud = $data['cloud'] == 'true';
        $project      = $data['project'];
        $parts        = $data['part'];
        /**
         * preparing project
         */
        $project['user_id']       = $user['id']; 
        $project['categories']    = implode(",", $project['categories']);
        $project['creation_date'] = date('Y-m-d H:i:s');
        $project['update_date']   = date('Y-m-d H:i:s');
        /**
         * add fabid if exists
         */
        if(isset($user['settings']['fabid']['email'])) $project['fabid'] = $user['settings']['fabid']['email'];
        /**
         * adding project
         */
        $id_project = $this->projects->add($project);
        
        /**
         * parts
         */
        foreach($parts as $part){
            
            $tmp_part = $part;
            /**
             * get list of files and remove it from part data
             */
            $files = array();
            if($tmp_part['source_file']  != "") $files[] = $tmp_part['source_file'];
            if($tmp_part['machine_file'] != "") $files[] = $tmp_part['machine_file'];
            unset($tmp_part['source_file']);
            unset($tmp_part['machine_file']);
            /**
             * adding part
             */
            $id_part = $this->parts->add($tmp_part);
            /**
             * assoc files to part
             */
            foreach($files as $id_file){
                if($id_file != '')
                    $this->parts->add_file($id_part, $id_file);
            }
            /**
             * assoc part to object
             */
            $this->projects->add_part($id_project, $id_part);
        }
        
        /**
         * upload to cloud if selected
         */
        if($upload_cloud){
            
            $fabid = $this->session->userdata['user']['settings']['fabid']['email'];
            $access_token = fab_authenticate($fabid, '****');
            sync_project($fabid, $access_token, $id_project);
        }
        /**
         * output
         */
        $output = array(
            'create' => true,
            'project_id' => $id_project
        );
        
        $this->output->set_content_type('application/json')->set_output(json_encode($output));
        
    }
    
 
} 
?>

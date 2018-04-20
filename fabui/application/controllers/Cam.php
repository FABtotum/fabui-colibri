<?php
/**
 * 
 * @author Krios Mane
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */
defined('BASEPATH') or exit('No direct script access allowed');

class Cam extends FAB_Controller
{

    /**
     */
    function __construct()
    {
        parent::__construct();
        session_write_close(); // avoid freezing page
    }

    /**
     */
    public function index()
    {
        $this->load->library('smart');
        $this->load->helper(array(
            'form',
            'fabtotum_helper',
            'language_helper',
            'os_helper',
            'cam_helper'
        ));
        
        $language = getCurrentLanguage();
        $this->load->config('cam');
        
        $data = array();
        
        $widgetOptions = array(
            'sortable' => false,
            'fullscreenbutton' => true,
            'refreshbutton' => false,
            'togglebutton' => false,
            'deletebutton' => false,
            'editbutton' => false,
            'colorbutton' => false,
            'collapsed' => false
        );
        
        $headerToolbar = '
			<ul class="nav nav-tabs pull-right">
				<li class="active"><a data-toggle="tab" href="#laser-tab"> ' . _('Laser Engraving') . '</a></li>
				<li><a data-toggle="tab" href="#3d-printing-tab"> ' . _('3D Printing') . '</a></li>
				<li><a data-toggle="tab" href="#subscriptions-tab"> ' . _('Subscriptions') . '</a></li>
			</ul>';
        
        $widgeFooterButtons = '';
        
        // init vars
        $data['internet'] = isInternetAvaialable();
        $data['accepted_files']['laser'] = $this->config->item('laser_accepted_files');
        $data['laser_profiles'] = load_presets('laser');
        $data['max_upload_file_size'] = $this->config->item('upload_max_file_size');
        $data['options_mode'] = array(
            'const' => _("Constant"),
            'linear' => _("Linear mapping")
        );
        $data['options_skip_line_mode'] = array(
            'modulo' => _("Groups")
        );
        $data['remote_endpoint'] = $this->config->item('api_url');
        //$data['isFabid'] = isset($this->session->user['settings']['fabid']['logged_in']) && $this->session->user['settings']['fabid']['logged_in'] == true;
        $data['isFabid'] = $this->_isFabid();
        $data['subscription_exists'] = subscription_exists();
        $data['linear_mapping_help'] = $this->load->view('cam/help/' . $language . '/linear_mapping', $data, true);
        $data['skip_line_help'] = $this->load->view('cam/help/' . $language . '/skip_line', $data, true);
        $data['installed_head'] = getInstalledHeadInfo();
        
        $laser_heads = loadLaserHeads();
        foreach ($laser_heads as $head) {
            $data["laser_heads"][$head['fw_id']] = $head['name'];
        }
        
        if ($data['subscription_exists']) {
            $data['subscription_code'] = load_subscription();
        }
        // tabs
        $data['laser_tab'] = $this->load->view('cam/laser_tab', $data, true);
        $data['printing_tab'] = $this->load->view('cam/3d_printing_tab', $data, true);
        $data['subscriptions_tab'] = $this->load->view('cam/subscriptions_tab', $data, true);
        
        $widget = $this->smart->create_widget($widgetOptions);
        $widget->id = 'main-widget-head-installation';
        $widget->header = array(
            'icon' => 'fabui-edit-file',
            "title" => "<h2>CAM toolbox</h2>",
            'toolbar' => $headerToolbar
        );
        $widget->body = array(
            'content' => $this->load->view('cam/main_widget', $data, true),
            'class' => ''
        ); // 'footer'=>$widgeFooterButtons
        
        $this->addJsInLine($this->load->view('cam/help/inputs', $data, true));
        $this->addJsInLine($this->load->view('cam/js', $data, true));
        $this->content = $widget->print_html(true);
        $this->addCssFile('/assets/css/cam/style.css');
        // javascript assets
        $this->addJSFile('/assets/js/plugin/dropzone/dropzone.js'); // dropzpone
        $this->addJSFile('/assets/js/plugin/ion-slider/ion.rangeSlider.min.js');
        $this->addJSFile('/assets/js/plugin/spectrum/spectrum.js');
        $this->addCssFile('/assets/js/plugin/spectrum/spectrum.css');
        $this->addJSFile('/assets/js/plugin/moment/moment.min.js');
        $this->addJSFile('/assets/js/plugin/jquery-validate/jquery.validate.min.js');
        
        $this->addCssFile('/assets/js/plugin/OwlCarousel2-2.2.1/owl.carousel.min.css');
        $this->addCssFile('/assets/js/plugin/OwlCarousel2-2.2.1/owl.theme.default.css');
        $this->addJSFile('/assets/js/plugin/OwlCarousel2-2.2.1/owl.carousel.min.js');
        
        $this->addJSFile('/assets/js/jquery-sortable.js');
        
        $this->view();
    }

    /**
     */
    public function subscription($action, $code = "")
    {
        $this->load->helper(array('cam_helper'));
        
        switch ($action) {
            case 'active':
                $output = active_subscription($code);
                break;
            case 'remove':
                $output = remove_subscription();
                break;
        }
        $this->output->set_content_type('application/json')->set_output($output);
    }

    /**
     */
    public function upload($type = "laser")
    {
        switch ($type) {
            case 'laser':
                $this->uploadLaser();
                break;
            case 'prism':
                $this->uploadPrism();
                break;
        }
    }
    
    /**
     * 
     */
    public function reUseUploadedImage($image_name)
    {
        // load helpers
        $this->load->helper(array('directory', 'file', 'file_helper', 'cam_helper'));
        // load config
        $this->config->load('fabtotum');
        
        $directory = $this->config->item('temp_path') . '/uploads/laser/'.$this->session->user['id'].'/';
        
        if(file_exists($directory.$image_name)){
         
            $data['upload'] = true;
            $data['url']    = '/temp/uploads/laser/'.$this->session->user['id'].'/' . $image_name;
            $data['info']   = get_img_extra_info($directory.$image_name);
            $data['file_name'] = $image_name;
            $data['full_path'] = $directory.$image_name;
            
        }else{
            $data['error']  = _("File not found");
            $data['upload'] = false;
        }
        $this->output->set_content_type('application/json')->set_output(json_encode($data));
        
    }

    /**
     */
    public function uploadLaser()
    {
        // load helpers
        $this->load->helper(array(
            'file_helper',
            'file',
            'fabtotum_helper',
            'cam_helper'
        ));
        // load config
        $this->config->load('fabtotum');
        $this->config->load('cam');
        
        // prepare destination folder
        $upload_path = $this->config->item('temp_path') . '/uploads/';
        if (! file_exists($upload_path))
            createFolder($upload_path);
        
        $upload_path = $this->config->item('temp_path') . '/uploads/laser/';
        if (! file_exists($upload_path))
            createFolder($upload_path);
        
        $upload_path.= $this->session->user['id'];
        if (! file_exists($upload_path))
            createFolder($upload_path);
        
        // config load upload library
        $config['upload_path']      = $upload_path;
        $config['file_ext_tolower'] = true;
        $config['remove_spaces']    = true;
        $config['allowed_types']    = $this->config->item('laser_allowed_types');
        $config['max_size']         = $this->config->item('upload_max_file_size');
        
        $this->load->library('upload', $config);
        
        // do upload
        if ($this->upload->do_upload('file')) {
            $data = $this->upload->data();
            $data['upload'] = true;
            $data['url']    = '/temp/uploads/' . $data['file_name'];
            $data['url']    = str_replace('/tmp/fabui/', '/temp/', $data['full_path']);
            $data['info']   = get_img_extra_info($data['full_path']);
            
            if(empty($data['info'])){
                unlink($data['full_path']);
                unset($data);
                $data['error']  = _("File not valid, unable to process it");
                $data['upload'] = false;
                
            }
            
        } else {
            $data['error']  = $this->upload->display_errors();
            $data['upload'] = false;
        }
        $this->output->set_content_type('application/json')->set_output(json_encode($data));
    }

    /**
     */
    public function generate($type = "laser", $id = "")
    {
        $this->load->helper(array(
            'cam_helper'
        ));
        
        $data = $this->input->post();
        
        if ($id == "") {
            // upload file to app.fabtotum.com
            if (function_exists('curl_file_create')) { // php 5.5+
                $cFile = curl_file_create($data['file']);
            } else { //
                $cFile = '@' . realpath($data['file']);
            }
            $data['file'] = $cFile;
        } else {
            // its a regeneration, so file is already on the server, no need to upload it again
            $data['id'] = $id;
        }
        
        $data['preset'] = json_encode($data['preset']);
        
        
        $result = call_service('/' . $type . '/generate', $data);
        
        if (! $result['content']) {
            
            $response = json_encode(array(
                'status' => false,
                'message' => http_code_description($result['info']['http_code'])
            ));
        } else {
            $response = $result['content'];
        }
        $this->output->set_content_type('application/json')->set_output($response);
    }

    /**
     */
    public function getUserObjects()
    {
        // load db model
        $this->load->model('Objects', 'objects');
        // retrieve objetcs
        $objects = $this->objects->getUserObjects($this->session->user['id']);
        // crate response for datatable
        $aaData = array();
        foreach ($objects as $object) {
            $temp = array();
            $temp[] = $object['id'];
            $temp[] = $object['name'];
            $temp[] = $object['description'];
            $date_inserted = date('d/m/Y', strtotime($object['date_insert']));
            $temp[] = $date_inserted;
            $temp[] = $object['num_files'];
            $aaData[] = $temp;
        }
        $this->output->set_content_type('application/json')->set_output(json_encode(array(
            'aaData' => $aaData
        )));
    }

    /**
     */
    public function preview($type = "laser", $id)
    {
        $this->load->helper(array(
            'cam_helper'
        ));
        $result = call_service('/' . $type . '/preview/' . $id);
        if ($result['content']) {
            header('Content-type: image/png');
            echo $result['content'];
        }
    }

    /**
     */
    public function download($type = 'laser', $id, $name = 'laser')
    {
        $this->load->helper(array(
            'cam_helper'
        ));
        $result = call_service('/' . $type . '/download/' . $id);
        if ($result['content']) {
            $this->load->helper('download');
            force_download($name . '.gcode', $result['content']);
        }
    }

    /**
     */
    public function saveGCode($type, $id)
    {
        $post = $this->input->post();
        
        $this->load->helper(array(
            'file_helper',
            'file',
            'fabtotum_helper',
            'cam_helper'
        ));
        $this->config->load('upload');
        
        // get generated gcode from app.fabtotum.com
        $content = call_service('/' . $type . '/download/' . $id);
        
        // get preview
        $preview = call_service('/' . $type . '/preview/' . $id);
        
        if ($content['content']) {
            
            $fileContent = $content['content'];
            
            if (! file_exists($this->config->item('upload_path') . 'gcode'))
                createFolder($this->config->item('upload_path') . 'gcode');
            
            $filename = $id . '_' . $post['filename'] . '.gcode';
            $full_path = $this->config->item('upload_path') . 'gcode/' . $filename;
            
            if (write_file($full_path, $fileContent)) {
                
                // load model
                $this->load->model('Files', 'files');
                $this->load->model('Objects', 'objects');
                // get file info
                $file_info = get_file_info($full_path);
                
                $file_record['file_name']   = $filename;
                $file_record['file_type']   = "text/plain";
                $file_record['file_path']   = $this->config->item('upload_path') . 'gcode/';
                $file_record['full_path']   = $full_path;
                $file_record['raw_name']    = $file_info['name'];
                $file_record['orig_name']   = $post['filename'] . '.gcode';
                $file_record['client_name'] = $post['filename'] . '.gcode';
                $file_record['file_ext']    = '.gcode';
                $file_record['file_size']   = $file_info['size'];
                $file_record['print_type']  = 'laser';
                $file_record['insert_date'] = date('Y-m-d H:i:s');
                $file_record['update_date'] = date('Y-m-d H:i:s');
                $file_record['note']        = _("Generated with FABtotum CAM toolbox");
                
                $attributes                 = array();
                // save preview image
                if ($preview['content']) {
                    
                    //create preview folder if not exists
                    if (! file_exists($this->config->item('upload_path') . 'gcode/preview'))
                        createFolder($this->config->item('upload_path') . 'gcode/preview');
                    
                    //write file to filesystem
                        if (write_file($this->config->item('upload_path') . 'gcode/preview/'.$post['filename'].'.png', $preview['content'])) {
                        $attributes['preview_file'] = $this->config->item('upload_path') . 'gcode/preview/'.$post['filename'].'.png';
                    }
                }
                
                $file_record['attributes'] = json_encode($attributes);
                // save file to db
                $fileId = $this->files->add($file_record);
                
                if ($post["mode"] == "new") { // if craete a new object
                    
                    $project_record['user']        = $this->session->user['id'];
                    $project_record['name']        = $post['project_name'];
                    $project_record['public']      = 1;
                    $project_record['date_insert'] = date('Y-m-d H:i:s');
                    $project_record['date_update'] = date('Y-m-d H:i:s');
                    
                    $objectID = $this->objects->add($project_record);
                } else {
                    $objectID = $post['project_id'];
                }
                
                // assoc project and file
                $this->objects->addFiles($objectID, $fileId);
                
                $response['success'] = true;
                $response['file_id'] = $fileId;
            } else {
                $response['success'] = false;
            }
        } else {
            $response['success'] = false;
        }
        $this->output->set_content_type('application/json')->set_output(json_encode($response));
    }
    
    /**
     * get all uploaded images in /tmp/ folder
     */
    public function laserUploadedImages()
    {
        // load helpers
        $this->load->helper(array('directory', 'file', 'file_helper'));
        // load config
        $this->config->load('fabtotum');
        
        $directory = $this->config->item('temp_path') . '/uploads/laser/'.$this->session->user['id'].'/';
        
        $response['images'] = array();
        
        if(file_exists($directory)){
         
            $images = get_dir_file_info($directory);
    
            foreach($images as $image){
                $image['url'] = str_replace('/var/www/temp/', '/temp/', $image['relative_path']).$image['name'];
                $image['extension'] = getFileExtension($image['name']);
                $response['images'][] = $image;
            }

        }
        $this->output->set_content_type('application/json')->set_output(json_encode($response));
    }
    
    /**
     * 
     */
    private function _isFabid()
    {
        return (isset($this->session->user['settings']['fabid']['logged_in']) && ($this->session->user['settings']['fabid']['logged_in'] == true));
    }
    
    private function _fabId()
    {
        if( ($this->session->user['settings']['fabid']['logged_in'] !== null)
            && ($this->session->user['settings']['fabid']['logged_in'] == true) )
        {
            return $this->session->user['settings']['fabid']['email'];
        }
        
        return null;
    }
    
    
    
    /**
     */
    public function uploadPrism()
    {
        $data = array();
        
        $this->output->set_content_type('application/json')->set_output(json_encode($data));
    }
    
    public function cam2()
    {
        $this->load->helper(array(
            'cam_helper'
        ));
        
        $init = array();
        if ( subscription_exists() ) {
            $subscription = load_subscription();
            $init['subscription'] = $subscription['code'];
        }
        if( $this->_isFabid() )
        {
            $init['fabid'] = $this->_fabId();
        }
        $this->load->library('ApiFabtotumClient', $init,  'apifabtotum');
        
        $data = array();
        
        //~ if ( subscription_exists() ) {
            //~ $data['subscription'] = load_subscription();
        //~ }
        
        //~ $data['message'] = 'working';
        //~ $data['isFabid'] = $this->_isFabid();
        //~ $data['fabid'] = $this->_fabId();
        
        //~ $fabid = $this->_fabId();
        //~ $subscription = $data['subscription']['code'];
        
        $data['auth'] = $this->apifabtotum->test($fabid, $subscription);
        
        $this->output->set_content_type('application/json')->set_output(json_encode($data));
    }
}

?>

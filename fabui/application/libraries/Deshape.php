<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Deshape {
        
    protected $server = '';
    protected $curl;
    
    /**
     * 
     */
    protected $ssl = FALSE;
    
    /**
     * 
     */
    protected $contentType = 'application/json';
    
    /**
     * 
     */
    protected $token;
    
    /**
     * 
     */
    protected $debug = FALSE;
    
    /**
     * 
     */
    const HTTP_SUCCESS = 200;
    
    /**
     * 
     */
    const HTTP_BAD_REQUEST  = 400;
    const HTTP_UNAUTHORIZED = 401;
    
    /**
     * 
     */
    const HTTP_INTERNAL_ERROR_SERVER = 500;
    
    /**
     * 
     */
    function __construct($config = array())
    {
        //init
        if(!empty($config))
            $this->initialize($config);
        
        // init curl
        $this->_initCurl();
    }
    
    /**
     * 
     */
    public function initialize($config)
    {
        if(isset($config['server']))       $this->server      = $config['server'];
        if(isset($config['token']))        $this->token       = $config['token'];
        if(isset($config['content_type'])) $this->contentType = $config['content_type'];
        if(isset($config['ssl']))          $this->ssl         = $config['ssl'];
        if(isset($config['debug']))        $this->debug       = $config['debug'];
    }
    
    /**
     * 
     */
    public function list_projects_full()
    {
        $projects = $this->_call('list_projects_full');
        
        if($projects['status'] == true){
            
            $new_list = array();
            
            $projects = array_reverse($projects['data']);
            
            foreach($projects as $project){
                
                $image_url = $this->get_project_image($project['project_id']);
                //$image_url = '';
                $temp = $project;
                $temp['image_url'] = $image_url;
                
                $new_list[] = $temp;
            }
            return $new_list;
        }
    }
    
    /**
     * 
     */
    public function list_projects_short()
    {
        return $this->_call('list_projects_short');
    }
    
    /**
     * 
     */
    public function get_single_project($project_id)
    {
        return $this->_call('get_single_project', array('project_id' =>$project_id));
    }
    
    /**
     * 
     */
    public function create_project($project)
    {
        return $this->_call('create_project', $project);
    }
        
    /**
     * 
     */
    public function edit_project($project)
    {
        return $this->_call('edit_project', $project);
    }
    
    /**
     * 
     */
    public function add_part($project_id, $part)
    {
        return $this->_call('add_part', array('project_id' => $project_id, 'part' => $part));    
    }
    
    /**
     * 
     */
    public function remove_part($project_id, $part_id)
    {
        return $this->_call('remove_part', array('project_id' => $project_id, 'part_id'=>$part_id));
    }
    
    /**
     * 
     */
    public function get_project_image($project_id)
    {
        $response = $this->_call('get_project_image', array('project_id' => $project_id));
        
        if($response['status'] == true){
            return $response['data']['image_url'];
        }
        
        return '';
    }
    
    /**
     * 
     */
    protected function _call($method, $params = array())
    {
        $endpoint = $this->server.$method;
        
        $params['token'] = $this->token;
        $params_string   = json_encode($params);
        
        $this->curl->setOption(CURLOPT_URL, $endpoint);
        $this->curl->setOption(CURLOPT_POSTFIELDS, $params_string);
        $this->curl->setOption(CURLOPT_HTTPHEADER, array(
            'Content-Type: '.$this->contentType,
            'Content-Length: '.strlen($params_string))
        );
         
        $this->curl->perform();      
        return $this->_formatResponse();
    }
    
    /**
     *  init curl default params
     */
    protected function _initCurl()
    {
        
        $this->curl = new Curl();
        $this->curl->setOption(CURLOPT_POST, TRUE);
        $this->curl->setOption(CURLOPT_RETURNTRANSFER, TRUE);
        $this->curl->setOption(CURLOPT_SSL_VERIFYHOST, $this->ssl);
        $this->curl->setOption(CURLOPT_SSL_VERIFYPEER, $this->ssl);
        
    }
    
    /**
     * 
     */
    protected function _formatResponse()
    {
        if($this->curl->getHttpCode() == self::HTTP_SUCCESS){
            
            return array(
                'status' => true,
                'data' => json_decode($this->curl->getContent(), true)
            );
            
        }else{
            return array(
              'status' =>  false,
              'message' => $this->curl->getContent()
            );
        }
    }   
}


/**
 * 
 */

class Curl {
    
    protected $ch;
    protected $response;
    protected $info;
    protected $http_code;
    
    /**
     * 
     */
    function __construct()
    {
        $this->ch = curl_init();
    }
    
    /**
     * 
     */
    public function perform()
    { 
        $this->content   = curl_exec($this->ch);
        $this->info      = curl_getinfo($this->ch);
        $this->http_code = $this->info['http_code'];
    }
    
    /**
     * 
     */
    public function getContent()
    {
        return $this->content;
    }
    
    /**
     * 
     */
    public function getInfo()
    {
        return $this->info;
    }
    
    /**
     * 
     */
    public function getHttpCode()
    {
        return $this->http_code;
    }
    
    /**
     * 
     */
    public function setOption($option, $value)
    {
        curl_setopt($this->ch, $option, $value);
    }
}


?>
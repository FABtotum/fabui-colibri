<?php
/****
 * 
 * 
 * 
 * 
 */
require_once 'Part.php';


class Project {
    
    
    protected $id          = '';
    protected $fabui_id    = '';
    protected $name        = '';
    protected $description = '';
    protected $sku         = '';
    protected $visibility  = '';
    protected $category    = array();
    protected $image       = '';
    protected $parts       = array();
    
    protected $ci          = ''; // codegniter reference
    
    
    /**
     * 
     */
    public function __construct($data = array())
    {
        $this->ci =& get_instance();
    }
    
    /**
     * 
     */
    public function setId($id)
    {
        $this->id = $id;
    }
    
    /**
     *  echo "SAVE".PHP_EOL;
     */
    public function setName($name)
    {
        $this->name = $name;
    }
    
    /**
     * 
     */
    public function setSku($sku)
    {
        $this->sku = $sku;
    }
    
    /**
     * 
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }
    
    /**
     * 
     */
    public function addCategory($category)
    {
        $this->category[] = $category;
    }
    
    /**
     * 
     */
    public function setVisibility($visibility)
    {
        $this->visibility = $visibility;
    }
    
    /**
     * 
     */
    public function addPart($part)
    {
        $this->parts[] = $part;
    }
    
    /**
     * 
     */
    public function setImage($image)
    {
        $this->image = $image;
    }
    
    /**
     * 
     */
    public function save()
    {
        $this->ci->load->model('ProjectsModel', 'projects');
        $local_project = $this->ci->projects->get(array('deshape_id' => $this->id), 1);
        
        if($local_project){
            
            
        }else{
            
        }
    }
    
    /**
     * 
     */
    public function createFromDeshapeData($data)
    {
        $this->setName($data['project_name']);
        $this->setDescription($data['project_description']);
        $this->setSku($data['project_sku']);
        $this->setVisibility($data['visibility']);
        $this->setId($data['project_id']);
        
        foreach($data['parts'] as $part_list)
        {
            $part = new Part();
            $part->createFromDeshapeData($part_list);
            $this->addPart($part);
        }
    }
    
}

?>
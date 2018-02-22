<?php 

require_once 'Part.php';

class File {
    
    protected $id     = '';
    protected $name   = '';
    protected $type   = '';
    protected $title  = '';
    
    /**
     *
     */
    public function __construct($data = array())
    {
        
    }
    
    /**
     *
     */
    public function setId($id)
    {
        $this->id = $id;
    }
    
    /**
     *
     */
    public function setName($name)
    {
        $this->name = $name;
    }
    
    /**
     * 
     */
    public function setType($type)
    {
        $this->type = $type;
    }
    
    /**
     * 
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }
    
    
    /**
     *
     */
    public function createFromDeshapeData($data)
    {
        $this->setName($data['file_name']);
        $this->setType($data['file_type']);
        $this->setTitle($data['title']);
        $this->setId($data['file_id']);
    }
    
}


?>
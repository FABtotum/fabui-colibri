<?php

require_once 'File.php';

class Part {
    
    protected $id          = '';
    protected $name        = '';
    protected $description = '';
    protected $sku         = '';
    protected $quantity    = '';
    protected $tool        = '';
    protected $price       = 0;
    protected $files       = array();
    
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
    public function setPrice($price)
    {
        $this->price = $price;
    }
    
    /**
     * 
     */
    public function addFile($file)
    {
        $this->files[]=$file;
    }
    
    /**
     * 
     */
    public function createFromDeshapeData($data)
    {
        $this->setName($data['part_name']);
        $this->setDescription($data['part_description']);
        $this->setSku($data['part_sku']);
        $this->setPrice($data['price']);
        $this->setId($data['part_id']);
        
        foreach($data['part_files'] as $file_list){
            $file = new File();
            $file->createFromDeshapeData($file_list);
            $this->addFile($file);
        }
    }
    
}

?>
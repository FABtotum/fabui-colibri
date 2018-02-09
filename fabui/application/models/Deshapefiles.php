<?php
/**
 * 
 * @author Krios Mane
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */
 
 class Deshapefiles extends FAB_Model {
 	
    protected $tableName = 'tb_files';
	
	
	//init class
	public function __construct()
	{
	    parent::__construct($this->tableName);
	}
	

}
 
?>

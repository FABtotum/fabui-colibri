<?php
/**
 * 
 * @author Krios Mane
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */
 
 class User extends FAB_Model {
 	
	private $tableName = 'sys_user';
 	
	//init class
	public function __construct()
	{
		parent::__construct($this->tableName);
	}
	
 }
 
?>
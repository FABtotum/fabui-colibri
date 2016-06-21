<?php
/**
 * 
 * @author Krios Mane
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 * Apply settings for specific hardware version
 * 
 */
 
 class Hardware {
 	
	protected $hardwareId;
	
	/**
	 * class constructor
	 */
	public function __construct($params = '')
	{
		if(is_array($params) && isset($params['id']))
			$this->hardwareId = $params['id'];
	}
	
	/**
	 * 
	 */
	public function setId($id)
	{
		$this->hardwareId = $id;
	}
	
	/***
	 * 
	 */
	public function getId()
	{
		return $this->hardwareId;
	}
	
	/**
	 * 
	 */
	public function run()
	{
		$className = 'Hardware_'.$this->hardwareId;
		if(file_exists(__DIR__.'/hardware/'.$className.'.php')){ //if hardware file class exists
			require_once(__DIR__.'/hardware/'.$className.'.php');
			$class = new $className();
			$class->run();
		}	
	}
 }
 
?>
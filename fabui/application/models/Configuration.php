<?php
/**
 * 
 * @author Daniel Kesler
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */
 
 class Configuration extends FAB_Model {
 	
	private $tableName = 'sys_configuration';
 	
	//init class
	public function __construct()
	{
		parent::__construct($this->tableName);
	}
	
	/**
	 * @param $key
	 * @param $value
	 * Store key/value pair
	 */
	public function store($key, $value)
	{
		$data = array();
		$data['key'] = $key;
		$data['value'] = $value;
		
		$this->db->where('key', $key);
		
		$query = $this->db->get($this->tableName);
		if($query->num_rows() > 0)
		{
			$this->update($data);
		}
		else
		{
			$this->add($data);
		}
	}
	
 }
 
?>

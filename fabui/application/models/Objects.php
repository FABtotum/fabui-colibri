<?php
/**
 * 
 * @author Krios Mane
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */
 
 class Objects extends FAB_Model {
 	
	private $tableName = 'sys_objects';
	private $objFilesTable = 'sys_obj_files';
 	
	//init class
	public function __construct()
	{
		parent::__construct($this->tableName);
	}
	
	/**
	 * @param (int) user id
	 * @return all users's objects
	 */
	public function userObjects($userID)
	{
		$this->db->select('to.id as id, name, description, count(id_file) as num_files');
		$this->db->where('user', $userID);
		$this->db->join($this->objFilesTable.' as tof', 'tof.id_obj = to.id', 'left');
		$this->db->group_by('to.id');
		$query = $this->db->get($this->tableName.' as to');
		return $query->result_array();
	}
	
	/**
	 * @param (int) object id
	 * @param (int) file id
	 * assoc file to abject
	 */
	public function addFile($objectID, $fileID)
	{
		$data['id_obj']  = $objectID;
		$data['id_file'] = $fileID;
		$this->db->insert($this->objFilesTable, $data);
		return $this->db->insert_id();
	}
 }
 
?>
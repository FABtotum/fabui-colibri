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
	public function getUserObjects($userID)
	{
		$this->db->select('to.id as id, name, description, date_insert, count(id_file) as num_files');
		$this->db->where('user', $userID);
		$this->db->join($this->objFilesTable.' as tof', 'tof.id_obj = to.id', 'left');
		$this->db->group_by('to.id');
		$query = $this->db->get($this->tableName.' as to');
		return $query->result_array();
	}
	
	/**
	 * @param (int) object id
	 * @param (int|array) file id
	 * assoc file(s) to object
	 */
	public function addFiles($objectID, $fileID)
	{
		if( is_array($fileID) )
		{
			$result = array();
			foreach($fileID as $file)
			{
				if(is_numeric($file))
				{
					$data['id_obj']  = $objectID;
					$data['id_file'] = $file;
					$this->db->insert($this->objFilesTable, $data);
					$result[] = $this->db->insert_id();
				}
			}
		}
		else
		{
			if(is_numeric($fileID))
			{
				$data['id_obj']  = $objectID;
				$data['id_file'] = $fileID;
				$this->db->insert($this->objFilesTable, $data);
				$result = $this->db->insert_id();
			}
		}
		return $result;
	}
	
	/**
	 * @param (int) object id
	 * @param (int|array) file id
	 * unassoc file(s) from object
	 */
	public function deleteFiles($objectID, $fileID)
	{
		if( is_array($fileID) )
		{
			foreach($fileID as $file)
			{
				$data['id_obj'] = $objectID;
				$data['id_file'] = $file;
				$this->db->delete($this->objFilesTable, $data);
			}
		}
		else
		{
			$data['id_obj'] = $objectID;
			$data['id_file'] = $fileID;
			$this->db->delete($this->objFilesTable, $data);
		}
	}
	
	/**
	 * @return array optimized for dropdown list
	 */
	public function getObjectsForDropdown()
	{
		$objects = $this->get();
		$dropdown = array();
		if(!$objects) return $dropdown; //if no objects
		foreach($objects as $object){
			$dropdown[$object['id']] = $object['name'];
		}
		return $dropdown;
	}
 }
 
?>

<?php
/**
 * 
 * @author Krios Mane
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */
 
 class Files extends FAB_Model {
 	
	private $tableName = 'sys_files';
	private $objFilesTable = 'sys_obj_files';
 	
	//init class
	public function __construct()
	{
		parent::__construct($this->tableName);
	}
	
	/**
	 * @param $type (print, mill, '' => all)
	 */
	function getForCreate($type = '')
	{
		$this->db->select('tf.orig_name, tf.client_name, tf.file_ext, to.name, tf.id as id_file, to.id as id_object, to.description');
		if($type != '')	$this->db->where('print_type', $type);
		$this->db->where('to.user', $this->session->user['id']);
		$this->db->join($this->objFilesTable, $this->objFilesTable.'.id_file = tf.id');
		$this->db->join('sys_objects as to', 'to.id = '.$this->objFilesTable.'.id_obj');
		$this->db->order_by('date_insert', 'desc');
		$query = $this->db->get($this->tableName.' as tf');
		return $query->result_array();
	}
	
	/**
	 * Return an array of files, filtered by extension
	 * @param $ext Can be a string or an array of strings
	 */
	function getByExtension($ext = '')
	{
		$this->db->select('tf.orig_name, tf.client_name, tf.file_ext, to.name, tf.id as id_file, to.id as id_object, to.description');
		
		if(is_array($ext))
		{
			$this->db->where_in('file_ext', $ext);
		}
		else if($ext != '')
		{
			$this->db->where('file_ext', $ext);
		}
		
		$this->db->where('to.user', $this->session->user['id']);
		$this->db->join($this->objFilesTable, $this->objFilesTable.'.id_file = tf.id');
		$this->db->join('sys_objects as to', 'to.id = '.$this->objFilesTable.'.id_obj');
		$this->db->order_by('date_insert', 'desc');
		$query = $this->db->get($this->tableName.' as tf');
		return $query->result_array();
	}
	
	/**
	 * @param $type (additive, subtractive, '') 
	 */
	function getRecentCreated($type = '', $limit_end = 10, $limit_start = 0){
		
	}
	
	/**
	 * @param $fileID
	 * @return Object record
	 * get the associated object
	 */
	function getObject($fileId)
	{
		$this->db->select('to.id as id, to.name as obj_name, to.description as obj_description, to.date_insert as date_insert');
		$this->db->where('tof.id_file',$fileId);
		$this->db->join('sys_objects as to', 'to.id = tof.id_obj');
		$query = $this->db->get($this->objFilesTable.' as tof');
		return $query->row_array();
	}
	
	/**
	 * @param (int) object id
	 * @return all files associated to that object
	 */
	function getByObject($objectID)
	{
		//~ $this->db->select('*');
		$this->db->select('tf.id , tf.file_name , tf.file_path, tf.full_path, tf.raw_name, tf.orig_name, tf.client_name, tf.file_ext, tf.file_size, tf.print_type, tf.is_image, tf.insert_date, tf.update_date, tf.note, tf.attributes');
		$this->db->join('sys_obj_files as tof', 'tof.id_file = tf.id');
		$this->db->where('tof.id_obj', $objectID);
		$query = $this->db->get($this->tableName.' as tf');
		return $query->result_array();
	}
 }
 
?>

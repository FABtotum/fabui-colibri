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
		$this->db->select('tf.orig_name, to.obj_name, tf.id as id_file, to.id as id_object, to.obj_description');
		if($type != '')	$this->db->where('print_type', $type);
		$this->db->where('to.user', $this->session->user['id']);
		$this->db->join('sys_obj_files', 'sys_obj_files.id_file = tf.id');
		$this->db->join('sys_objects as to', 'to.id = sys_obj_files.id_obj');
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
		$this->db->select('to.id as id, to.obj_name as obj_name, to.obj_description as obj_description, to.date_insert as date_insert');
		$this->db->where('tof.id_file',$fileId);
		$this->db->join('sys_objects as to', 'to.id = tof.id_obj');
		$query = $this->db->get('sys_obj_files as tof');
		return $query->row_array();
	}
 }
 
?>
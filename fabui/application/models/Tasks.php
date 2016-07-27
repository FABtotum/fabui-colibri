<?php
/**
 * 
 * @author Krios Mane
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */
 
 class Tasks extends FAB_Model {
 	
	private $tableName = 'sys_tasks';
	private $completedStatus = array('completed', 'abort', 'deleted');
	
	const STATUS_RUNNING = 'running';
 	
	//init class
	public function __construct()
	{
		parent::__construct($this->tableName);
	}
	
	/**
	 * get running task
	 * @return row or false
	 */
	public function getRunning()
	{
		$this->db->where('status', self::STATUS_RUNNING);
		$this->db->order_by('start_date', 'DESC');
		$query = $this->db->get($this->tableName,1,0);
		if($query->num_rows() > 0){
			return $query->row_array();
		}else{
			return false;
		}
	}
	
	/**
	 * @param $type (print, mill, '') 
	 */
	function getLastCreations($type = '', $limit_end = 10, $limit_start = 0){
		$this->db->select('tf.orig_name, to.name, tf.id as id_file, to.id as id_object, to.description');
		$this->db->join('sys_files as tf', 'tf.id = tt.id_file', 'left');
		$this->db->join('sys_objects as to', 'to.id = tt.id_object', 'left');
		if($type != '') $this->db->where('tt.type', $type);
		$this->db->where('tt.user', $this->session->user['id']);
		$this->db->where_in('tt.status', $this->completedStatus);
		$this->db->where('tt.finish_date = (select MAX(sys_tasks.finish_date) from sys_tasks where sys_tasks.id_object = tt.id_object and sys_tasks.id_file = tt.id_file)');
		$this->db->group_by('tt.id_file');
		$this->db->order_by('tt.finish_date', 'DESC');
		$query = $this->db->get($this->tableName.' as tt', $limit_end, $limit_start);
		return $query->result_array();
	}
 }
 
?>

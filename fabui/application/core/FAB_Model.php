<?php
/**
 * 
 * @author Krios Mane
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */

 class FAB_Model extends CI_Model {
 	
	private $tableName; //table name
	
	/**
	 * 
	 */
	public function __construct($tableName = '')
	{
		parent::__construct();
		$this->tableName = $tableName;
	}
	/***
	 * get row
	 * @param array||string||int $data
	 * @param string||int $numRowsExpected
	 */
	public function get($data = '', $numRowsExpected = '')
	{	
		if(is_array($data)){ //if parameter is an associative array
			$this->db->where($data);
		}
		if(is_numeric($data)){ //if parameter is an int then it means that select by ID
			$this->db->where('id', $data);
		}
		$query = $this->db->get($this->tableName);
		if($query->num_rows() > 0){ // if number of rows returned > 0
			if($numRowsExpected == 1){
				return $query->row_array();
			}
			if($numRowsExpected == ''){
				return $query->result_array();
			}
		}
		return false;
	}
	/***
	 * update row
	 * @param int||array $id
	 * $param array $data
	 */
	public function update($id, $data)
	{
		if(is_array($id)){
			foreach($id as $column => $value){
				$this->db->where($column, $value);
			}
		}else{
			$this->db->where('id', $id);	
		}
		$this->db->update($this->tableName, $data);
	}
	/**
	 * @param $data (data to insert)
	 * @return int $ID (last insert id)
	 */
	public function add($data)
	{
		$this->db->insert($this->tableName, $data);
		return $this->db->insert_id();
	}
	/***
	 *  delete
	 *  @param array||int $data
	 */
	public function delete($data)
	{
		if(is_array($data))
		{
			$this->db->delete($this->tableName, $data);
		}
		if(is_numeric($data))
		{
			$this->db->delete($this->tableName, array('id' => $data) );
		}
	}
	/**
	 *  delete all data from table
	 */
	public function truncate()
	{
		$this->db->truncate($this->tableName);
	}
 } 
?>

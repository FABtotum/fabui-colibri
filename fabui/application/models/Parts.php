<?php
/**
 * 
 * @author Krios Mane
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */
 
 class Parts extends FAB_Model {
 	
	protected $tableName   = 'tb_parts';
	
	//init class
	public function __construct()
	{
		parent::__construct($this->tableName);
	}
	
	
	/**
	 * assoc part to project
	 * @param int $part_id
	 * @param int $file_id
	 * @return $int inserted_id
	 */
	public function add_file($part_id, $file_id)
	{
	    $data = array(
	        'id_part' => $part_id,
	        'id_file' => $file_id
	    );
	    $this->db->insert('tb_parts_files', $data);
	    return $this->db->insert_id();
	}
	
	
 }
 
?>

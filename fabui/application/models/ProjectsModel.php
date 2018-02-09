<?php
/**
 * 
 * @author Krios Mane
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */
 
 class ProjectsModel extends FAB_Model {
 	
	protected $tableName   = 'tb_projects';
	
	//init class
	public function __construct()
	{
		parent::__construct($this->tableName);
	}
	
	/**
	 * check if project exits
	 * @param $project_id int
	 * @return boolean
	 */
	public function exists($project_id)
	{
	    $project = $this->get(array('deshape_id' => $project_id), 1);
	    return !$project == false;
	}
	
	/**
	 * 
	 */
	public function add($data)
	{
	    return parent::add($data);
	}
	
	/**
	 * assoc part to project
	 * @param int $project_id
	 * @param int $part_id
	 * @return $int inserted_id
	 */
	public function add_part($project_id, $part_id)
	{
	    $data = array(
	        'id_project' => $project_id,
	        'id_part'    => $part_id
	    );
	    $this->db->insert('tb_projects_parts', $data);
	    return $this->db->insert_id();
	}

 }
 
?>

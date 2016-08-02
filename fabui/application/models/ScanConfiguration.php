<?php
/**
 * 
 * @author Krios Mane
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */
 
 class ScanConfiguration extends FAB_Model {
 	
	private $tableName = 'sys_scan_configuration';
 	
	//init class
	public function __construct()
	{
		parent::__construct($this->tableName);
	}
	/**
	 * return all scan modes
	 */
	public function getModes()
	{
		return $this->get(array('type' => 'mode'));
	}
	/**
	 * @param int modeId
	 * @return row or false
	 */
	public function getModeById($modeId)
	{
		return $this->get($modeId, 1);
	}
	/**
	 * @return array optimized for dropdown list
	 */
	public function getModesForDropdown()
	{
		$modes = $this->getModes();
		$dropdown = array();
		foreach($modes as $mode){
			$dropdown[$mode['id']] = json_decode($mode['values'], true)['info']['name']; 
		}
		return $dropdown;
	}
	/**
	 * return all scan quality
	 */
	public function getQualities()
	{
		return $this->get(array('type' => 'quality'));
	}
	
 }
 
?>
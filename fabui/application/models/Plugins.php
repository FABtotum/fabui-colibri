<?php
/**
 * 
 * @author Krios Mane
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */
 
 class Plugins extends FAB_Model {
 	
	private $tableName = 'sys_plugins';
 	
	//init class
	public function __construct()
	{
		parent::__construct($this->tableName);
	}
	
	function isActive($plugin_slug){
		
		$this->db->where('name', $plugin_slug);
		
		$query  = $this->db->get($this->tableName);
		
		return $query->num_rows() > 0 ? true : false;
		
	}
	
	function activate($plugin)
	{
		if(!$this->isActive($plugin))
		{
			$this->load->helper('plugin_helper');
			$plugin_info = getPluginInfo($plugin);
			if($plugin_info)
			{
				$this->db->insert($this->tableName, array('name' => $plugin, 'attributes'=>json_encode($plugin_info)));
			}
		}
		
	}
	
	function deactivate($plugin)
	{
		if($this->isActive($plugin))
		{
			$this->db->delete($this->tableName, array('name' => $plugin));
		}
		
	}
	
	function getActivetedPlugins()
	{
		$this->db->order_by('name');
		$query = $this->db->get($this->tableName);
		return $query->result();
	}
 }
 
?>

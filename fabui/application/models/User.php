<?php
/**
 * 
 * @author Krios Mane
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */
 
 class User extends FAB_Model {
 	
	private $tableName = 'sys_user';
 	
	//init class
	public function __construct()
	{
		parent::__construct($this->tableName);
	}
	/**
	 * 
	 */
	public function getByEmail($email)
	{
		return $this->get( array('email' => $email), 1 );
	}
	/**
	 * 
	 */
	public function getByToken($token)
	{
		$query = $this->db->get($this->tableName);
		$result = $query->result();
				
		$user = false;
		
		foreach($result as $row){
			
			$_settings = json_decode($row->settings, true);
			if(isset($_settings['token']) && $_settings['token'] == $token){	
				$user = get_object_vars($row);
				break;
			}
		}
		return $user;
	}
	/**
	 * return user by fabid
	 */
	public function getByFABID($fabid)
	{
		$this->db->like('settings', '"fabid":{"email":"'.$fabid.'"}');
		$query = $this->db->get($this->tableName, 1);
		$result = $query->result_array();
		
		return isset($result[0]) ? $result[0] : false;

	}
 }
 
?>

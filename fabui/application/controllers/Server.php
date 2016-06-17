<?php
/**
 * 
 * @author Krios Mane
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */
 
 class Server extends CI_Controller {
 	
	function __construct()
	{
		parent::__construct(); //CI father CLASS
		
		if(!$this->input->is_cli_request()){ //cannot enter from web
			show_error('Access denied');
		}		
	}
	
	
	public function webSocket()
	{
		$this->config->load('fabtotum');
		$this->load->library('SocketServer', array('address' => $this->config->item('host'), 'port' =>$this->config->item('port')), 'ws');
		
		try{
			$this->ws->run();
		}catch(Exception $e){
			$this->ws->stdout($e->getMessage());
		}
	}
	
	
 }
 
?>
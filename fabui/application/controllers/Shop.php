<?php
/**
 * 
 * @author Krios Mane
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */
defined('BASEPATH') OR exit('No direct script access allowed');

class Shop extends CI_Controller {
	/**
	 * 
	 */
	function __construct()
	{
		parent::__construct();
		$this->load->library("session");
		session_write_close(); //avoid freezing page
		
		$this->config->load('fabtotum');
		$this->load->helper('shop_helper');
	}
	/**
	 * 
	 */
	public function filaments()
	{
		$filaments = loadShopFilaments();
		$this->output->set_content_type('application/json')->set_output(json_encode($filaments));
	}
	/**
	 * 
	 */
	public function homepage()
	{
		$homepage = loadHomePageProducts();
		$this->output->set_content_type('application/json')->set_output(json_encode($homepage));
	}
	
} 
?>

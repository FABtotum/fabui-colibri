<?php
/**
 * 
 * @author Krios Mane
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */
defined('BASEPATH') OR exit('No direct script access allowed');

class Social extends CI_Controller {
	/**
	 * 
	 */
	function __construct()
	{
		parent::__construct();
		$this->load->library("session");
		session_write_close(); //avoid freezing page
		
		$this->load->helper('social_helper');
	}
	/**
	 * 
	 */
	public function load($social)
	{
		switch($social){
			case 'blog':
				$feed = loadBlog();
				break;
			case 'twitter':
				$feed = loadTwitter();
				break;
			case 'instagram':
				$feed = loadInstagram();
				break;
		}
		$this->output->set_content_type('application/json')->set_output(json_encode($feed));
	}
} 
?>

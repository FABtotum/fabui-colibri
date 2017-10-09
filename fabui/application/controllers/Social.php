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
	public function load($social, $download = 0)
	{
		$download == 1 ? true : false;
		switch($social){
			case 'blog':
				$feed = loadBlog($download);
				break;
			case 'twitter':
				$feed = loadTwitter($download);
				break;
			case 'instagram':
				$feed = loadInstagram($download);
				break;
		}
		$this->output->set_content_type('application/json')->set_output(json_encode($feed));
	}
} 
?>

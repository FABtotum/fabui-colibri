<?php
/**
 * 
 * @author Krios Mane
 * @author Fabtotum Development Team
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */
 defined('BASEPATH') OR exit('No direct script access allowed');
 
 class Dashboard extends FAB_Controller {
 	
	public function index(){
		//load libraries, helpers, model, config
		$this->load->library('smart');
		
		//main page widget
		$widgetOptions = array(
				'sortable' => false, 'fullscreenbutton' => true,'refreshbutton' => false,'togglebutton' => false,
				'deletebutton' => false, 'editbutton' => false, 'colorbutton' => false, 'collapsed' => false, 'load' => site_url('dashboard/blog'),
				'refresh' => 300
		);
								
		$this->addCssFile('/assets/js/plugin/OwlCarousel2-2.2.1/owl.carousel.min.css');
		$this->addCssFile('/assets/js/plugin/OwlCarousel2-2.2.1/owl.theme.default.css');
		$this->addJSFile('/assets/js/plugin/OwlCarousel2-2.2.1/owl.carousel.min.js');
		$this->addCssFile('/assets/css/dashboard/style.css');
		$this->addJsInLine($this->load->view('dashboard/js', null, true));
		
		$this->content = $this->load->view('dashboard/index', null, true );
		$this->view();
	}
	
	
	public function updateFeeds()
	{
		$this->load->helper(array('social_helper', 'os_helper'));
		$this->config->load('fabtotum');
		
		$online = false;
		
		if(isInternetAvaialable())
		{
			if(!file_exists($this->config->item('blog_feed_file')))
				downloadBlogFeeds();
				
			if(!file_exists($this->config->item('twitter_feed_file')))
				downloadTwitterFeeds();
				
			if(!file_exists($this->config->item('instagram_feed_file')))
				downloadInstagramFeeds();
			
			$online = true; 
		}
		
		$this->output->set_content_type('application/json')->set_output(json_encode($online));
	}
	
	/**
	 * blank page just for first access
	 */
	public function blank()
	{
		$this->view();
	}
	
	
	public function test()
	{
		$this->load->helper('social_helper');
		downloadInstagramFeeds();
		downloadBlogFeeds();
		downloadTwitterFeeds();
	}
	
 }
 
?>

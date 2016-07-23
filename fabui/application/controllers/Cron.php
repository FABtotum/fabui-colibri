<?php
/**
 * 
 * @author Krios Mane
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */
defined('BASEPATH') OR exit('No direct script access allowed');

class Cron extends CI_Controller {
	
	
	/**
	 * exec all jobs
	 */
	public function all()
	{
		$this->blogFeeds();
		$this->twitterFeeds();
		$this->instagramFeeds();
		$this->instagramHashFeeds();
	}
	
	/**
	 * retrieve fabtotum development's blog feed
	 */
	public function blogFeeds()
	{	
		//load helpers, config
		$this->load->helper('os_helper');
		$this->config->load('fabtotum');
		
		if(downloadRemoteFile($this->config->item('blog_feed_url'), $this->config->item('blog_feed_file'))){
			log_message('debug', 'Blog feeds updated');
		}else{
			log_message('debug', 'Blog feeds unavailable');
		}
	}
	/**
	 * retrieve fabtotum last tweets
	 */
	public function twitterFeeds()
	{
		//load helpers, config
		$this->load->helper('os_helper');
		$this->config->load('fabtotum');
		
		if(downloadRemoteFile($this->config->item('twitter_feed_url'), $this->config->item('twitter_feed_file'))){
			log_message('debug', 'Twitter feeds updated');
		}else{
			log_message('debug', 'Twitter feeds unavailable');
		}
	}
	/**
	 * retrieve fabtotum last instagram photos
	 */
	public function instagramFeeds()
	{
		//load helpers, config
		$this->load->helper('os_helper');
		$this->config->load('fabtotum');
	
		if(downloadRemoteFile($this->config->item('instagram_feed_url'), $this->config->item('instagram_feed_file'))){
			log_message('debug', 'Instagram feeds updated');
		}else{
			log_message('debug', 'Instagram feeds unavailable');
		}
	}
	/**
	 * retrieve fabtotum last instagram hash
	 */
	public function instagramHashFeeds()
	{
		//load helpers, config
		$this->load->helper('os_helper');
		$this->config->load('fabtotum');
	
		if(downloadRemoteFile($this->config->item('instagram_feed_hash_url'), $this->config->item('instagram_feed_hash_file'))){
			log_message('debug', 'Instagram hash feeds updated');
		}else{
			log_message('debug', 'Instagram hash feeds unavailable');
		}
	}
	
}
?>
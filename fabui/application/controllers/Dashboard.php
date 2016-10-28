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
				'refresh' => 60
		);
		//bloog feeds widget
		$widget = $this->smart->create_widget($widgetOptions);
		$widget->id = 'blog-feeds-widget';
		$widget->class = 'well transparent ';
		$widget->header = array('icon' => 'fa-folder-open', "title" => "<h2>Latest from Development blog</h2>", 'toolbar'=>'');
		$widget->body   = array('content' =>'', 'class'=>'no-padding');
		$blogFeedsWidget  = $widget->print_html(true);
		//twitter feeds widget
		$widgetOptions['load'] = site_url('dashboard/twitter');
		$widget = $this->smart->create_widget($widgetOptions);
		$widget->id = 'twitter-feeds-widget';
		$widget->class = 'well transparent'; 
		$widget->header = array('icon' => 'fa-twitter', "title" => "<h2>Latest Tweets</h2>", 'toolbar'=>'');
		$widget->body   = array('content' => '', 'class'=>'no-padding');
		$tweeterFeedsWidget  = $widget->print_html(true);
		//instagram feeds widget
		$widgetOptions['load'] = site_url('dashboard/instagram');
		$widget = $this->smart->create_widget($widgetOptions);
		$widget->id = 'instagram-feeds-widget';
		$widget->class = 'well transparent';
		$widget->header = array('icon' => 'fa-instagram', "title" => "<h2>Instagram</h2>", 'toolbar'=>'');
		$widget->body   = array('content' => '', 'class'=>'no-padding');
		$instagramFeedsWidget  = $widget->print_html(true);
		
		$data['blogWidget']      = $blogFeedsWidget;
		$data['twitterWidget']   = $tweeterFeedsWidget;
		$data['instagramWidget'] = $instagramFeedsWidget;
								
		$this->addCssFile('/assets/css/dashboard/style.css');
		$this->addJsInLine($this->load->view('dashboard/js', $data, true));
		
		$this->content = $this->load->view('dashboard/index', $data, true );
		$this->view();
	}
	/**
	 * show blog feed
	 */
	public function blog()
	{
		//load configs
		$this->config->load('fabtotum');
		$this->load->helper('layout_helper');
		if(file_exists($this->config->item('blog_feed_file'))){
			$xml = simplexml_load_file($this->config->item('blog_feed_file'),'SimpleXMLElement', LIBXML_NOCDATA); 
			$data["blogTitle"] = $xml->channel->title;
			$data["blogUrl"]   = $xml->channel->link;
			$feeds             = $xml->channel->item;
			$processedFeeds    = array();
			//process feeds
			foreach($feeds as $feed){
				$imageSrc = null;
				$html = new DOMDocument();
				//print_r($feed); exit();
				$description = str_replace('[&#8230;]', '...', $feed->description);
				$html->loadHTML(mb_convert_encoding($description, 'HTML-ENTITIES', 'UTF-8'));
				
				$images = $html->getElementsByTagName('img');
				foreach($images as $imgTag){
					$imageSrc = $imgTag->getAttribute('src');
					$imgTag->parentNode->removeChild($imgTag);
				}
				$processedFeeds[] = array(
					'title' => $feed->title,
					'link' => $feed->guid,
					'date' => date('j M, Y',strtotime($feed->pubDate)),
					'img_src' => $imageSrc,
					'text' => str_replace('[…]', '...', $html->textContent)
				);
			}
			$data['feeds'] = $processedFeeds;
			$this->load->view('dashboard/blog', $data);
		}
	}
	/**
	 * show twitter feed
	 */
	public function twitter()
	{
		//load configs
		$this->config->load('fabtotum');
		$this->load->helper('text_helper');
		$this->load->helper('layout_helper');
		if(file_exists($this->config->item('twitter_feed_file'))){
			$feeds = json_decode(file_get_contents($this->config->item('twitter_feed_file')), true);
			$processedFeeds    = array();
			foreach($feeds as $feed){
				
				$temporaryFeed = $feed;
				if(isset($feed['retweeted_status']) && $feed['retweeted_status']){
					$temporaryFeed = $feed['retweeted_status'];
				}
				//highlitght hashtags
				$hashtags = $temporaryFeed['entities']['hashtags'];
				foreach($hashtags as $hash){
					$temporaryFeed['text'] = highlight_phrase($temporaryFeed['text'], '#'.$hash['text'], '<a target="_blank" href="https://twitter.com/search?q='.$hash['text'].'">', '</a>');
				}
				//highlitght mentions
				$mentions = $temporaryFeed['entities']['user_mentions'];
				foreach($mentions as $mention){
					$temporaryFeed['text'] = highlight_phrase($temporaryFeed['text'], $mention['screen_name'], '<a target="_blank" href="https://twitter.com/'.$mention['screen_name'].'">', '</a>');
				}
				//highlight urls
				$urls = $temporaryFeed['entities']['urls'];
				foreach($urls as $url){
					$temporaryFeed['text'] = highlight_phrase($temporaryFeed['text'], $url['url'], '<a target="_blank" href="'.$url['url'].'">', '</a>');
				}
				
				$processedFeeds[] = $temporaryFeed;
			}
			
			$data['feeds'] = $processedFeeds;
			$this->load->view('dashboard/twitter', $data);
		}
		
	}
	/**
	 * show instagram feed
	 */
	public function instagram()
	{
		//load configs
		$this->config->load('fabtotum');
		$this->load->helper('text_helper');
		$this->load->helper('layout_helper');
		$data['feeds']  = array();
		$data['feedsA'] = array();
		$data['feedsB'] = array();
		
		if(file_exists($this->config->item('instagram_feed_file'))){
			$feeds = json_decode(file_get_contents($this->config->item('instagram_feed_file')), true);
			$feeds = $feeds['data'];
			/**
			 * sort by date and order columns to have the most recent on top
			 */
			uasort($feeds, 'instaSort');
			$a = array();
			$b = array();
			foreach($feeds as $key => $feed){
				if($key%2==0)
					array_push($a, $feed);
				else
					array_push($b, $feed);
			}
			$data['feedsA'] = $a;
			$data['feedsB'] = $b;
		}
		$this->load->view('dashboard/instagram', $data);
	}
			
 }
 
?>
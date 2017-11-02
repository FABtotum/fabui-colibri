<?php

/**
 * 
 * @author Krios Mane
 * @author Daniel Kesler
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */

if(!function_exists('downloadAllFeeds'))
{
	function downloadAllFeeds()
	{
		downloadInstagramFeeds();
		downloadTwitterFeeds();
		downloadBlogFeeds();
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('loadJsonFile'))
{
	function loadJsonFile($file)
	{
		if(file_exists($file)){
			return json_decode(file_get_contents($file), true);
		}
		return false;
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('loadBlog'))
{
	function loadBlog($download = false)
	{
		if($download){
			downloadBlogFeeds();
		}
		$CI =& get_instance();
		$CI->config->load('fabtotum');
		return loadJsonFile($CI->config->item("blog_feed_file"));
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('loadTwitter'))
{
	function loadTwitter($download = false)
	{
		if($download){
			downloadTwitterFeeds();
		}
		$CI =& get_instance();
		$CI->config->load('fabtotum');
		return loadJsonFile($CI->config->item("twitter_feed_file"));
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('loadInstagram'))
{
	function loadInstagram($download = false)
	{
		if($download){
			downloadInstagramFeeds();
		}
		$CI =& get_instance();
		$CI->config->load('fabtotum');
		return loadJsonFile($CI->config->item("instagram_feed_file"));
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('downloadInstagramFeeds'))
{
	/**
	 * 
	 * 
	 */
	function downloadInstagramFeeds()
	{
		$CI =& get_instance();
		$CI->config->load('fabtotum');
		$CI->load->helper(array('file', 'os_helper'));
		
		$done = false;
		
		$fabtotum_max_post = 5;
		$hashtag_max_post  = 10;
		
		$instagram_feeds = getRemoteFile($CI->config->item('instagram_feed_url'), true, null, 10);
		
		if($instagram_feeds){
			
			$instagram_feeds = json_decode($instagram_feeds, true);
			
			if(json_last_error() == JSON_ERROR_NONE){ // JSON_ERROR_NONE = 0 - if is a valid json i can write the file
				
				
				$feeds_A    = array();
				$feeds_B    = array();
				$temp_feeds = array();
				
				if(isset($instagram_feeds['user_feeds'])){
					$user_feeds = array_slice($instagram_feeds['user_feeds']['fullResponse']['items'], 0, $fabtotum_max_post);
					$temp_feeds = array_merge($temp_feeds, $user_feeds);
				}
				
				if(isset($instagram_feeds['hashtag_feeds'])){
					//get last 9 post
					$hashtag_feeds = array_slice($instagram_feeds['hashtag_feeds']['fullResponse']['items'], 0, $hashtag_max_post);
					$temp_feeds = array_merge($temp_feeds, $hashtag_feeds);
					//poular posts
					if(isset($instagram_feeds['hashtag_feeds']['ranked_items'])){
						$ranked_feeds = array();
						foreach($instagram_feeds['hashtag_feeds']['ranked_items'] as $feed){
							$temp = $feed;
							$temp["is_ranked"] = true;
							array_push($ranked_feeds, $temp);
						}
						$temp_feeds = array_merge($temp_feeds, $ranked_feeds);
					}
				}
				
				$temp_feeds = highlightInstagramPost($temp_feeds); //highlight links, tags, hashtags
				$temp_feeds = array_unique($temp_feeds, SORT_REGULAR);
				uasort($temp_feeds, 'instaSort'); //order list by post date
								
				//remove duplicated post
				$filteredFeeds = array();
				$newFeedsId    = array();
				foreach ($temp_feeds as $i) {
					if(!in_array($i['id'], $newFeedsId)){
						array_push($newFeedsId, $i['id']);
						$i['taken_at'] = date('j M, Y',$i['taken_at']);
						$filteredFeeds[] = $i;
					}
				}
				$temp_feeds = $filteredFeeds;
				
				//split feeds in 2 columns
				$a = array();
				$b = array();
				foreach($temp_feeds as $key => $feed){
					if($key%2==0) array_push($a, $feed);
					else array_push($b, $feed);
				}
				
				
				$instagram_feeds = array(
					'feeds_a' => $a,
					'feeds_b' => $b,
					'feeds'   => $temp_feeds
				);
				
				if(write_file($CI->config->item('instagram_feed_file'), json_encode($instagram_feeds), 'w+')){
					$done = true;
				}	
			}
		}
		
		$done ? log_message('debug', 'Instagram feeds updated') : log_message('debug', 'Instagram feeds unavailable');
		return $done;
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('highlightInstagramPost')){
	
	/**
	 *
	 */
	function highlightInstagramPost($feeds)
	{
		$CI =& get_instance();
		$CI->load->helper('text');
		
		$re_link = '/(http|ftp|https):\/\/([\w_-]+(?:(?:\.[\w_-]+)+))([\w.,@?^=%&:\/~+#-]*[\w@?^=%&\/~+#-])/';
		$re = '/(@\w+|#\w+)/';
		$new_feeds = array();
		
		foreach($feeds as $feed){
			
			$caption = $feed['caption']['text'];
			//highligth links
			$matches = preg_match_all($re_link, $caption, $matches);
			
			if(isset($matches[0][0])){
				$caption =  highlight_phrase($caption, $matches[0][0], '<b><a target="_blank" href="'.$matches[0][0].'">', '</a></b>');
			}
			//higlithgt entities
			preg_match_all($re, $caption, $matches);
			
			foreach($matches[0] as $match){
				
				switch($match[0]){
					case '#':
						$string_without_hash= str_replace('#', '', $match);
						$hash_re = '/(\#'.$string_without_hash.'+\b)/';
						$caption =  preg_replace($hash_re, '<a target="_blank" href="https://www.instagram.com/explore/tags/'.$string_without_hash.'">'.$match.'</a>', $caption);
						break;
					case '@':
						$string_without_at = str_replace('@', '', $match);
						$at_re = '/(\@'.$string_without_at.'+\b)/';
						$caption =  preg_replace($at_re, '<a target="_blank" href="https://www.instagram.com/'.$string_without_at.'">'.$match.'</a>', $caption);
						break;
				}
			}
			$temp = $feed;
			$temp['caption']['original_text'] = $temp['caption']['text'];
			$temp['caption']['text'] = $caption;
			$new_feeds[] = $temp;
		}
		return $new_feeds;
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('instaSort')){
	
	/*
	 *
	 */
	function instaSort($feedA, $feedB)
	{
		if ($feedA['taken_at'] == $feedB['taken_at']) {
			return 0;
		}
		return ($feedA['taken_at'] > $feedB['taken_at']) ? -1 : 1;
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('downloadTwitterFeeds'))
{
	/**
	 *
	 */
	function downloadTwitterFeeds()
	{
		$CI =& get_instance();
		$CI->config->load('fabtotum');
		$CI->load->helper(array('file', 'os_helper'));
		
		$done = false;
		
		$twitter_feed = getRemoteFile($CI->config->item('twitter_feed_url'), true, null, 10);
		
		if($twitter_feed){
			
			$feeds = json_decode($twitter_feed, true);
			
			if(json_last_error() == JSON_ERROR_NONE){ // JSON_ERROR_NONE = 0 - if is a valid json i can write the file
				
				$feeds = highlightTwitterPost($feeds);
				
				if(write_file($CI->config->item('twitter_feed_file'), json_encode($feeds), 'w+')){
					$done = true;
				}	
			}
		}
		
		$done ? log_message('debug', 'Twitter feeds updated') : log_message('debug', 'Twitter feeds unavailable');
		
		return $done;
	}
}
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('highlightTwitterPost')){
	/**
	 * 
	 */
	function highlightTwitterPost($feeds)
	{
		$CI =& get_instance();
		$CI->load->helper('text');
		
		$processedFeeds    = array();
		
		foreach($feeds as $feed){
			
			$temporaryFeed = $feed;
			$temporaryFeed['original_text'] = $temporaryFeed['text'];
			$temporaryFeed['created_at'] = date('j M, Y',strtotime($temporaryFeed['created_at']));
			if(isset($feed['retweeted_status']) && $feed['retweeted_status']){
				$temporaryFeed = $feed['retweeted_status'];
			}
			//highlitght hashtags
			$hashtags = $temporaryFeed['entities']['hashtags'];
			foreach($hashtags as $hash){
				//$temporaryFeed['text'] = highlight_phrase($temporaryFeed['text'], '#'.$hash['text'], '<a target="_blank" href="https://twitter.com/search?q='.$hash['text'].'">', '</a>');
				$string_without_hash= str_replace('#', '', $hash['text']);
				$hash_re = '/(\#'.$string_without_hash.'+\b)/';
				$temporaryFeed['text']  = preg_replace($hash_re, '<a target="_blank" href="https://twitter.com/search?q='.$string_without_hash.'">#'.$hash['text'].'</a>', $temporaryFeed['text']);
				
			}
			//highlitght mentions
			$mentions = $temporaryFeed['entities']['user_mentions'];
			foreach($mentions as $mention){
				$temporaryFeed['text'] = highlight_phrase($temporaryFeed['text'], '@'.$mention['screen_name'], '<a target="_blank" href="https://twitter.com/'.$mention['screen_name'].'">', '</a>');
			}
			//highlight urls
			$urls = $temporaryFeed['entities']['urls'];
			foreach($urls as $url){
				$temporaryFeed['text'] = highlight_phrase($temporaryFeed['text'], $url['url'], '<a target="_blank" href="'.$url['url'].'">', '</a>');
			}
			
			$processedFeeds[] = $temporaryFeed;
		}
		
		return $processedFeeds;
	}
}
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('downloadBlogFeeds'))
{
	/**
	 *
	 */
	function downloadBlogFeeds()
	{
		$CI =& get_instance();
		$CI->config->load('fabtotum');
		$CI->load->helper(array('file', 'os_helper', 'text'));
		
		$xmlEndPoint = $CI->config->item('blog_feed_url').'?cat='.$CI->config->item('blog_post_categories');
		$xml         = getRemoteFile($xmlEndPoint, true, null, 10);
		
		$done = false;
		
		if($xml){
			$xml = str_replace("content:encoded>","content>",$xml);
			$xml = simplexml_load_string($xml,'SimpleXMLElement', LIBXML_NOCDATA );
			
			libxml_use_internal_errors(true);
			
			$feeds          = $xml->channel->item;
			$processedFeeds = array();
			
			foreach($feeds as $feed){
				$imageSrc = null;
				$html     = new DOMDocument();
				
				$content = $feed->content;
				$html->loadHTML(mb_convert_encoding($content, 'HTML-ENTITIES', 'UTF-8'));
				$feedImages = $html->getElementsByTagName('img');
				
				foreach($feedImages as $imgTag){
					
					$imageSrc = getWordpressOriginalImage($imgTag->getAttribute('src'));
					if($imageSrc != null) break;
				}
				//print_r($images); exit();
				$processedFeeds[] = array(
						'title' => $feed->title,
						'link' => $feed->guid,
						'date' => date('j M, Y',strtotime($feed->pubDate)),
						'img_src' => $imageSrc,
						'text' => word_limiter($html->textContent, 50, '...')
				);
			}
			
			if(write_file($CI->config->item('blog_feed_file'), json_encode($processedFeeds), 'w+')){
				$done = true;
			}
		}
			
		$done ? log_message('debug', 'Blog feeds updated') : log_message('debug', 'Blog not updated');
		return $done;
	}
}
//////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('displayInstagramFeedItem'))
{
	/**
	 *
	 */
	function displayInstagramFeedItem($feed)
	{
		$src_image   = getInstagramImageSrc($feed);
		$src_video   = getInstagramVideoSrc($feed);
		$date        = date('j M, Y',$feed['taken_at']);
		$location    = '';
		$likes       = '';
		$comments    = '';
		$ranked      = '';
		$post_url    = 'http://www.instagram.com/p/'.$feed['code'];
		$video       = "";
		$views_count = "";
		$image       = '<div class="image padding-10"><img src="'.$src_image.'" /></div>';
		if($src_video != ""){
			$video .= '<div class="image padding-10"><video class="img-responsive" controls><source src="'.$src_video.'" type="video/mp4"><img src="'.$src_image.'" /></video></div>';
			$image = "";
			$views_count = '<li class="txt-color-green"><i class="fa fa-play"></i> ('.$feed['view_count'].')</li>';
		}
		$likes .= '<li class="txt-color-red"><i class="fa fa-heart"></i> ('.$feed['like_count'].')</li>';
		$comments .= '<li class="txt-color-blue"><i class="fa fa-comments"></i> ('.$feed['comment_count'].')</li>';
		//if is popular post
		if(isset($feed['is_ranked']) && $feed['is_ranked'] = true)
			$ranked = '<li title="'._("Popular").'" class="txt-color-yellow pull-right"><i class="fa fa-star"></i> </li>';
			//location
			if(isset($feed['location']['name']))
				$location .= '<br><i class="fa fa-map-marker"></i> '.$feed['location']['name'];
				
				return <<<EOT
			<div class="panel panel-default">
				<div class="panel-body status">
					<div class="who clearfix">
						<img src="{$feed['user']['profile_pic_url']}" />
						<span class="name"><b><a target="_blank" href="http://www.instagram.com/{$feed['user']['username']}">{$feed['user']['username']}</a></b>
						<span class="pull-right">
							<a href="{$post_url}" target="_blank" title="View on instagram"><i class="fa fa-instagram"></i></a>
						</span></span>
						<span class="from">{$date}
							{$location}
						</span>
					</div>
					{$image}
					{$video}
					<div class="text padding-top-0 hidden-xs"><p style="word-wrap: break-word">{$feed['caption']['text']}</p></div>
					<ul class="links">
						{$likes}
						{$comments}
						{$views_count}
						{$ranked}
					</ul>
				</div>
			</div>
EOT;
	}
}
//////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('getInstagramImageSrc'))
{
	/**
	 *
	 */
	function getInstagramImageSrc($feed)
	{
		if(isset($feed['image_versions2']['candidates'])){
			
			$url = "";
			$maxWidth = 0;
			foreach($feed['image_versions2']['candidates'] as $image){
				if($image['width'] > $maxWidth){
					$url = $image['url'];
					$maxWidth = $image['width'];
				}
			}
			return $url;
		}
	}
}
//////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('getInstagramVideoSrc'))
{
	/**
	 *
	 */
	function getInstagramVideoSrc($feed)
	{
		if(isset($feed['video_versions'])){
			return $feed['video_versions'][0]['url'];
		}
		return "";
	}
}
//////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('displayBlogFeedItem'))
{
	/**
	 *
	 */
	function displayBlogFeedItem($feed)
	{
		$share_title = _("Share on facebook");
		return <<<EOT
		<div class="panel panel-default">
			<div class="panel-body status">
				<div class="who clearfix">
					<img src="{$feed['img_src']}" alt="{$feed['title']}" title="{$feed['title']}" />
					<span class="name font-sm">
						<a target="_blank" href="{$feed['link']}">{$feed['title']}</a>
						<br>
						<span class="text-muted">{$feed['date']}</span>
					</span>
				</div>
				<div class="image padding-top-0 padding-10">
					<a target="_blank" href="{$feed['link']}"><img title="{$feed['title']}" alt="{$feed['title']}" src="{$feed['img_src']}" /></a>
				</div>
				<div class="text hidden-xs">
					<p>{$feed['text']}</p>
				</div>
				<ul class="links  hidden-xs">
					<li class="">
						<a class="btn btn-default btn-circle btn-xs txt-color-blue" title="{$share_title}" target="_blank" href="https://www.facebook.com/sharer/sharer.php?u={$feed['link']}"><i class="fa fa-facebook"></i></a>
					</li>
					<li class="">
						<a class="pull-right" target="_blank" href="{$feed['link']}"> Read More <i class="fa fa-arrow-right"></i></a>
					</li>
				</ul>
			</div>
		</div>
EOT;
	}
}
//////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('displayTwitterFeedItem'))
{
	/**
	 *
	 */
	function displayTwitterFeedItem($feed)
	{
		$date = date('j M, Y',strtotime($feed['created_at']));
		$place     = '';
		$images    = '';
		$retweet   = '';
		$favourite = '';
		$post_url  = 'http://www.twitter.com/statuses/'.$feed['id_str'];
		if(is_array($feed['place']))
			$place .= '<br><i class="fa fa-map-marker"></i> '.$feed['place']['full_name'];
			if(isset($feed['entities']['media'])){
				foreach($feed['entities']['media'] as $media){
					if($media['type'] == 'photo')
						$images .= '<div class="image padding-top-0 padding-10"><img src="'.$media['media_url'].'" /></div>';
				}
			}
			if($feed['retweet_count'] > 0)
				$retweet .= '<li class="txt-color-green"><i class="fa fa-retweet"></i> ('.$feed['retweet_count'].')</li>';
				if($feed['favorite_count'] > 0)
					$favourite .= '<li class="txt-color-red"><i class="fa fa-heart"></i> ('.$feed['favorite_count'].')</li>';
					
					return <<<EOT
		<div class="panel panel-default">
			<div class="panel-body status">
				<div class="who clearfix">
					<img src="{$feed['user']['profile_image_url']}" />
					<span class="name"><b><a target="_blank" href="https://twitter.com/{$feed['user']['screen_name']}">{$feed['user']['screen_name']}</a></b>
					<span class="pull-right"><a href="{$post_url}" target="_blank" title="View on Twitter"><i class="fa fa-twitter"></i></a></span></span>
					<span class="from">{$date}
						{$place}
					</span>
					</span>
				</div>
				<div class="text">
					<p>{$feed['text']}</p>
				</div>
				{$images}
				<ul class="links">
					{$retweet}
					{$favourite}
				</ul>
			</div>
		</div>
EOT;
	}
}
//////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('getWordpressOriginalImage'))
{
	/**
	 * 
	 */
	function getWordpressOriginalImage($url)
	{
		$complete_url_splitted = explode("/", $url);
		
		$image_name = end($complete_url_splitted);
		$image_extension = ".".getFileExtension($image_name);
		
		$image_name_splitted = explode("-", $image_name);
		
		$last_chunk = end($image_name_splitted);
		
		if (strpos($last_chunk, 'x') !== false) {
			$dimensions = str_replace($image_extension, "", $last_chunk);
			return str_replace("-".$dimensions, '', $url);
		}else{
			return $url;
		}
		
	}
}









?>
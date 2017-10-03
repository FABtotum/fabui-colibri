<?php
/**
 * 
 * @author Krios Mane
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */
 
 /*
 *
 * generate html for javascript files inclusion
 */
function jScriptsInclusion($scripts) {
	if (is_array($scripts)) {
		$html = '';
		foreach ($scripts as $script) {
			$html .= '<script type="text/javascript" src="' . $script . '?v='.FABUI_VERSION.'"></script>'.PHP_EOL;
		}
		return $html;
	}
	if ($scripts != '') {
		return '<script type="text/javascript" src="' . $scripts . '?v='.FABUI_VERSION.'"></script>' . PHP_EOL;
	}
}

/**
 * 
 * generate html for css files inclusion
 */
function cssFilesInclusion($files, $ajax = false)
{
	if(is_array($files)){
		$html = '';
		foreach($files as $file){
			$html .= '<link rel="stylesheet" type="text/css" media="screen" href="'.$file.'?v='.FABUI_VERSION.'">'.PHP_EOL;
		}
		return $html;
	}
	if($files != '' ){
		return '<link rel="stylesheet" type="text/css" media="screen" href="'.$files.'?v='.FABUI_VERSION.'">'.PHP_EOL;
	}
}

/**
 * create javascript inclusions for ajax pages
 */
function ajaxJScriptsInclusion($scripts)
{
	$html = '';
	for($i = 0; $i<count($scripts); $i++){
		if($i == (count($scripts)-1)){
			$html .= 'loadScript("'.$scripts[$i].'?v='.FABUI_VERSION.'", pagefunction);'.PHP_EOL;
		}else{
			$html .= 'loadScript("'.$scripts[$i].'?v='.FABUI_VERSION.'", function(){'.PHP_EOL;
		}
	}
	for($i = 0; $i<count($scripts); $i++){
		if($i != (count($scripts)-1))
			$html .= '});';
	}
	return $html;
}

/**
 * prepare inline javascript for ajax pages
 */
function ajaxJSInline($javascript, $initFunction = true, $function = 'pagefunction')
{
	if($javascript == '') return;
	$javascript = str_replace('<script type="text/javascript">', '', $javascript);
	$javascript = str_replace('</script>', '', $javascript);
	
	if($initFunction == true) // if need to init the pagefunction
		return 'var '.$function.' = function() { '.$javascript.' }'.PHP_EOL.$function.'();';
	else
		return 'var '.$function.' = function() { '.$javascript.' }'.PHP_EOL;
}

function buildMenu($menu_array, $is_sub = FALSE, $parent = '') {
	/*
	 * If the supplied array is part of a sub-menu, add the
	 * sub-menu class instead of the menu ID for CSS styling
	 */
	$attr = (!$is_sub) ? ' class="menu-item-parent"' : ' ';
	$menu = "<ul>";
	// Open the menu container
	/*
	 * Loop through the array to extract element values
	 */
	foreach ($menu_array as $id => $properties) {
		/*
		 * Because each page element is another array, we
		 * need to loop again. This time, we save individual
		 * array elements as variables, using the array key
		 * as the variable name.
		 */
		 
			/*
			 * Intro.js specific guide attributes
			 */
			
			foreach ($properties as $key => $val) {
				/*
				 * If the array element contains another array,
				 * call the buildMenu() function recursively to
				 * build the sub-menu and store it in $sub
				 */
				if (is_array($val)) {
					$sub = buildMenu($val, TRUE, $id);
				}
				/*
				 * Otherwise, set $sub to NULL and store the
				 * element's value in a variable
				 */
				else {
					$sub = NULL;
					$$key = $val;
				}
			}
			
			/*
			 * If no array element had the key 'url', set the $url variable to #
			 */
			if (!isset($url)) {
				$url = "#";
			}
			/*
			 * Use the created variables to output HTML
			 */
			 
			$menu_id = 'menu-item';
			if($parent != '')
				$menu_id .= '-'.$parent;
			$menu_id .= '-'.$id;
			 
			$menu .= '<li><a id="'.$menu_id.'" data-controller="'.$id.'" data-href="'.$url.'" href="' . $url . '"><i class="fa fa-lg fa-fw ' . $icon . '"></i> <span ' . $attr . '>' . $title . '</span></a> ' . $sub . '</li>';
			/*
			 * Destroy the variables to ensure they're reset
			 * on each iteration
			 */
			unset($url, $title, $sub);
	}

	/*
	 * Close the menu container and return the markup for output
	 */
	return $menu . "</ul>";
}

//////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('showAlert'))
{
	/**
	 * 
	 */
	function showAlert($type, $message)
	{
		$html = '<div class="alert '.$type.'" animated fadeIn><button class="close" data-dismiss="alert">×</button>'.$message.'</div>';
		return $html;
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
//////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('sortByStringLength')){
	/**
	 * 
	 */
	function sortByStringLength($a, $b)
	{
		return strlen($b)-strlen($a);
	}
}
//////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('highlightInstagramPost')){
	
	/**
	 * 
	 */
	function highlightInstagramPost($feeds)
	{
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
			$temp['caption']['text'] = $caption;
			$new_feeds[] = $temp;
		}
		return $new_feeds;
	}
}
//////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('buildLanguagesMenu'))
{
	/**
	 * 
	 */
	function buildLanguagesMenu()
	{
		
		//TO DO get selcted language
		
		$languages = array(
			'gb' => 'English',
			'it' => 'Italiano',
			'de' => 'Deutsch'
		);
		
		$selected  = 'gb';
		
		$html = '<ul class="header-dropdown-list hidden-xs"><li>';
		
		$html .= '<a href="#" class="dropdown-toggle" data-toggle="dropdown"> <img src="/assets/img/blank.gif" class="flag flag-'.$selected.'" alt="'.$languages[$selected].'"> <span> '.$languages[$selected].' </span> <i class="fa fa-angle-down"></i> </a>';
		
		$html .= '<ul class="dropdown-menu pull-right">';
		foreach($languages as $code => $label){
			
			$class = $selected == $code ? 'active' : '';
			$html .= '<li class="'.$class.'"><a href="javascript:void(0);"><img src="/assets/img/blank.gif" class="flag flag-'.$code.'" alt="'.$label.'"> '.$label.' </a></li>';
		}
		$html .= '</ul>';
		$html .= '</li></ul>';
		
		return $html;
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
?>

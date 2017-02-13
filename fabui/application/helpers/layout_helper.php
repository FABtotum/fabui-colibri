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
		return 'var '.$function.' = function() { '.$javascript.' }';
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
			$menu .= '<li><a data-controller="'.$id.'" data-href="'.$url.'" href="' . $url . '"><i class="fa fa-lg fa-fw ' . $icon . '"></i> <span ' . $attr . '>' . $title . '</span></a> ' . $sub . '</li>';
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
		return <<<EOT
		<div class="panel panel-default">
			<div class="panel-body status">
				<div class="who clearfix">
					<img src="{$feed['img_src']}" />
					<span class="name font-sm">
						<a target="_blank" href="{$feed['link']}">{$feed['title']}</a>
						<br>
						<span class="text-muted">{$feed['date']}</span>
					</span>
				</div>
				<div class="text hidden-xs">
					<p>{$feed['text']}</p>
				</div>
				<ul class="links text-right hidden-xs">
					<li class="">
						<a target="_blank" href="{$feed['link']}"> Read More <i class="fa fa-arrow-right"></i></a>
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
					<span class="name"><b>{$feed['user']['screen_name']}</b>
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
		$date = date('j M, Y',$feed['date']);
		$location = '';
		$likes    = '';
		$comments = '';
		$post_url = 'http://www.instagram.com/p/'.$feed['code'];
		if(is_array($feed['likes']))
			$likes .= '<li class="txt-color-red"><i class="fa fa-heart"></i> ('.$feed['likes']['count'].')</li>';
		if(is_array($feed['comments']))
			$comments .= '<li class="txt-color-blue"><i class="fa fa-comments"></i> ('.$feed['comments']['count'].')</li>';
		return <<<EOT
			<div class="panel panel-default">
				<div class="panel-body status">
					<div class="who clearfix padding-10">
						<span class="from">{$date} </span>
						<span class="pull-right">
							<a href="{$post_url}" target="_blank" title="View on instagram"><i class="fa fa-instagram"></i></a>
						</span>
					</div>
					<div class="image padding-10"><img src="{$feed['display_src']}" /></div>
					<div class="text padding-top-0 hidden-xs"><p>{$feed['caption']}</p></div>
					<ul class="links">
						{$likes}
						{$comments}
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
		if ($feedA['date'] == $feedB['date']) {
			return 0;
		}
		return ($feedA['date'] > $feedB['date']) ? -1 : 1;
	}
}
//////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('highlightInstagramHashTags')){
	
	/**
	 * 
	 */
	function highlightInstagramHashTags($feeds)
	{
		$re = '/(#\w+)/';
		$new_feeds = array();
		foreach($feeds as $feed){
			$caption = $feed['caption'];
			preg_match_all($re, $feed['caption'], $matches);
			foreach($matches[0] as $match){
				$caption =  highlight_phrase($caption, $match, '<a target="_blank" href="https://www.instagram.com/explore/tags/'.str_replace('#', '', $match).'">', '</a>').PHP_EOL;
			}
			$temp = $feed;
			$temp['caption'] = $caption;
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
?>
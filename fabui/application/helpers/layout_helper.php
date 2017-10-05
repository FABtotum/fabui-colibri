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

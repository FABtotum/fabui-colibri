<?php

/**
 * 
 * @author Krios Mane
 * @author Daniel Kesler
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */
define("STORE_EU",             1);
define("STORE_INTL",           2);
define("STORE_IT",             3);
define("PRODUCT_HOMPEAGE",     6);
define("PRODUCT_FILAMENTS",   serialize(array(8, 9)));
define("PRODUCT_ACCESSORIES", 10);
define("PRODUCT_ADDONS",      13);

$stores = array();
$stores[STORE_EU]   = "eu";
$stores[STORE_INTL] = "intl";
$stores[STORE_IT]   = "it";

$GLOBALS['STORES'] = $stores;
unset($stores);

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('loadShopFilaments'))
{
	/**
	 * load filament csv file
	 * @return array
	 */
	function loadShopFilaments()
	{
		$CI =& get_instance();
		$CI->config->load('fabtotum');
		
		$file = $CI->config->item("store_filament_".getStoreCode()."_feed");
		
		if(file_exists($file)){
			return json_decode(file_get_contents($file), true);
		}
		
		return false;
		
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('loadHomePageProducts'))
{
	/**
	 * 
	 */
	function loadHomePageProducts()
	{
		
		$CI =& get_instance();
		$CI->config->load('fabtotum');
		
		$file = $CI->config->item("store_homepage_".getStoreCode()."_feed");
		
		if(file_exists($file)){
			return json_decode(file_get_contents($file), true);
		}
		
		return false;
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('getStoreCode'))
{
	/**
	 * load correct filament file
	 * @return filepath
	 */
	function getStoreCode()
	{
		$CI =& get_instance();
		$CI->load->helper(array('language_helper', 'os_helper')); 
		
		$language = getCurrentLanguage();
		
		switch($language){
			case 'it_IT':
				$code = 'it';
				break;
			case 'en_US':
			case 'de_DE':
				$timeZone = getTimeZone();
				if (strpos($timeZone, 'Europe') !== false) {
					$code = 'eu';
				}else{
					$code = 'intl';
				}
				break;
			default:
				$code = '';
		}
		return $code;
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('downloadAllFilamentsFeeds'))
{
	/**
	 * 
	 */
	function downloadAllFilamentsFeeds()
	{
		$CI =& get_instance();
		$CI->config->load('fabtotum');
		
		
		$products_filaments = unserialize (PRODUCT_FILAMENTS); 
		
		downloadStoreFilamentsFeed(STORE_EU,   $products_filaments, $CI->config->item('store_filament_eu_feed'));
		downloadStoreFilamentsFeed(STORE_INTL, $products_filaments, $CI->config->item('store_filament_intl_feed'));
		downloadStoreFilamentsFeed(STORE_IT,   $products_filaments, $CI->config->item('store_filament_it_feed'));
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('downloadStoreFilamentsFeed'))
{
    function downloadStoreFilamentsFeed($store, $category, $filepath)
    {
        $CI =& get_instance();
        $CI->load->helper('os_helper');
        $CI->load->helper('file');
        
        
        $feeds = array();
        
        foreach($category as $category_id){
            
            $url_endpoint = $CI->config->item('store_api_endpoint').'products/store/'.$store.'/category/'.$category_id.'?limit=100';
            $remote_feed  = getRemoteFile($url_endpoint, false, array('Content-Type: application/json'), 15);
            
            if($remote_feed){
                $remote_decoded = json_decode($remote_feed, true);
                if(json_last_error() == JSON_ERROR_NONE){ 
                    
                    $temp_feeds = array();
                    
                    foreach($remote_decoded as $id => $item){
                        $temp = $item;
                        $temp['url'] = 'https://store.fabtotum.com/'.$GLOBALS['STORES'][$store].'/catalog/product/view/id/'.$id.'/category/'.$category_id;
                        
                        $temp_feeds[] = $temp;
                    }
                    
                    $feeds = array_merge_recursive($feeds, $temp_feeds);
                }
            }
        }
        
        if(!empty($feeds)){
            
            write_file($filepath, json_encode(array('store'=> $GLOBALS['STORES'][$store], 'items'=>$feeds)), 'w+');
            return true;
        }
        
        return false;
    }
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('downloadAllHomePageFeeds'))
{
	/**
	 *
	 */
	function downloadAllHomePageFeeds()
	{
		$CI =& get_instance();
		$CI->config->load('fabtotum');
		
		downloadStoreFeed(STORE_EU,   PRODUCT_HOMPEAGE, $CI->config->item('store_hompage_eu_feed'));
		downloadStoreFeed(STORE_INTL, PRODUCT_HOMPEAGE, $CI->config->item('store_hompage_intl_feed'));
		downloadStoreFeed(STORE_IT,   PRODUCT_HOMPEAGE, $CI->config->item('store_hompage_it_feed'));
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('downloadHomePageFeed'))
{
	/**
	 * 
	 */
	function downloadStoreFeed($store, $category, $filepath)
	{
		$CI =& get_instance();
		$CI->load->helper('os_helper');
		$CI->load->helper('file');
		
		$url_endpoint = $CI->config->item('store_api_endpoint').'products/store/'.$store.'/category/'.$category.'?limit=100';
		
		$remote_feed = getRemoteFile($url_endpoint, false, array('Content-Type: application/json'), 15);
		
		if($remote_feed){
			$remote_decoded = json_decode($remote_feed, true);
			$feed = array(
				'store' => 	$GLOBALS['STORES'][$store],
				'items' => array()
			);
			if(json_last_error() == JSON_ERROR_NONE){ 
				
				foreach($remote_decoded as $id => $item){
					$temp = $item;
					$temp['url'] = 'https://store.fabtotum.com/'.$GLOBALS['STORES'][$store].'/catalog/product/view/id/'.$id.'/category/'.$category;
					$feed['items'][] = $temp;
				}
				write_file($filepath, json_encode($feed), 'w+');
				return true;
			}else{
				return false;
			}
		}
		return false;
	}
}
<?php

defined('BASEPATH') OR exit('No direct script access allowed');

use FABtotum\CamWebApp\Client as ApiClient;

require_once APPPATH.'/third_party/vendor/autoload.php';


/**
 * ApiFabtotum Class
 *  
 */
class ApiFabtotumClient {
	
	protected $ci = '';
	
	protected $fabid = '';
	protected $subscription = '';
	protected $access_token = '';
	
	protected $api_client = '';
	protected $server_url = 'http://52.17.77.207/api/'; // must have a trailing /
	
	private $logged_in = false;
	
	/**
	 * 
	 */
	public function __construct($init = array())
	{
		/**
		 * 
		 */
		foreach($init as $key => $value){
			if(property_exists($this, $key))
				$this->$key = $value;
		}
		
		/**
		 * get ci reference
		 */
		$this->ci =& get_instance();
		$this->ci->load->helper(array(
			'file_helper',
			'file',
			'fabtotum_helper',
			'cam_helper',
			'utility_helper'
		));
		
		//
		if ( !$this->subscription && subscription_exists() ) {
			$subscription = load_subscription();
			$this->subscription = $subscription['code'];
		}
		
		if( !$this->fabid && 
			($this->ci->session->user['settings']['fabid']['logged_in'] !== null) &&
			($this->ci->session->user['settings']['fabid']['logged_in'] == true)
		)
		{
			$this->fabid = $this->ci->session->user['settings']['fabid']['email'];
		}
		
		/**
		 * init api client
		 */
		$this->api_client = new ApiClient($this->server_url);
		
		error_log('api_client: done', 0);
		
		if(!$this->subscription)
			return;
		/**
		 * Get access_token from api server or load if from local storage
		 */
		$access_token = load_access_token($this->fabid, $this->subscription);
		
		if($access_token)
		{
			error_log('loading access token', 0);
			$this->access_token = $access_token;
			$this->api_client->setAccessToken($access_token);
			
			// test login
			$dummy = $this->getApplications();
			if($this->getErrorCode() == 200)
			{
				$logged_in = true;
			}
		}
		
		
		if($logged_in == false)
		{
			error_log('requesting access token', 0);
			if($this->api_client->login($this->fabid, $this->subscription))
			{
				$logged_in = true;
				$this->access_token = $this->api_client->getAccessToken();
				store_access_token($this->fabid, $this->subscription, $this->access_token);
			}
		}
		
		$this->apps = $this->synchronize();
	}
	
	public function isLoggedIn()
	{
		return $logged_in;
	}
	
	private function synchronize()
	{
		
		// prepare destination folder
		$cam_path = $this->ci->config->item('userdata_path') . '/cam/';
		if (! file_exists($cam_path))
			createFolder($cam_path);
			
		$cam_path = $this->ci->config->item('userdata_path') . '/cam/apps';
		if (!file_exists($cam_path))
			createFolder($cam_path);
			
		$cache_file = $cam_path . '/cache.json';
		if (file_exists($cache_file))
		{
			$cache = json_decode(file_get_contents($cache_file), true);
			$date_now = strtotime('now');
			$date_diff = dateDiff($cache['date'], $date_now);
			// date diff does not have a day in it, means cache is less then
			// a day old so it's OK
			if( !array_key_exists('day', $date_diff) )
			{
				return $cache['apps'];
			}
			
		}
		
		$apps = $this->getApplications();
		
		$result = array();
		
		foreach($apps as $appIdx => $app)
		{
			$appId = $app['id'];
			
			
			$app_path = $cam_path . '/' . $appId;
			if (!file_exists($app_path))
				createFolder($app_path);
			
			$configs = $this->getConfigs($appId);
			$app['config'] = $configs;
			
			$cfg_path = $cam_path . '/' . $appId . '/config';
			if (!file_exists($cfg_path))
				createFolder($cfg_path);
			
			foreach($configs as $cfgIdx => $config)
			{
				$cfgId = $config['id'];
				$cfg = $this->getConfig($appId, $cfgId);
				
				write_file($cfg_path . '/' . $config['id'] . '.json', $cfg);
			}
			
			$schema_raw = $this->getSchema($appId);
			$ui_schema_raw = $this->getUISchema($appId);
			
			write_file($app_path . '/schema.json', $schema_raw);
			write_file($app_path . '/ui.json', $ui_schema_raw);
			
			$result[$appId] = $app;
		}
		
		$cache = [];
		$cache['date'] = strtotime("now");
		$cache['apps'] = $result;
		
		write_file($cache_file, json_encode($cache) );
		
		return $result;
	}
	
	public function __call($method, $parameters)
	{
		return call_user_func_array([$this->api_client, $method], $parameters);
	}
	
}

?>

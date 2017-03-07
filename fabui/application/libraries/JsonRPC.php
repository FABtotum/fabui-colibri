<?php
use JsonRPC\Client;
use JsonRPC\HttpClient;

require_once 'autoload.php';


class JsonRPC {
	
	protected $url;
	protected $client;
	protected $httpClient;
	protected $ssl = false;
	
	/**
	 * 
	 */
	function __construct($params = array())
	{
		foreach($params as $key => $value){ //init class attributes (if present)
			if(property_exists($this, $key))
				$this->$key = $value;
		}	
	}
	/**
	 * 
	 */
	public function setUrl($url)
	{
		$this->url = $url;
	}
	/**
	 * 
	 */
	public function getUrl()
	{
		return $this->url;
	}
	/**
	 * 
	 */
	public function setSSL($bool)
	{
		$this->ssl = $bool;
	}
	/**
	 * 
	 */
	public function execute($method, $params = array())
	{
		$this->httpClient = new HttpClient($this->url);
		if(!$this->ssl){
			$this->httpClient->withoutSslVerification();
		}
		$this->client = new Client($this->url, true, $this->httpClient);
		return $this->client->execute($method, $params);
	}
}	
?>
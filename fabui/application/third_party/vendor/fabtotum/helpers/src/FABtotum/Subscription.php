<?php
namespace FABtotum;

use GuzzleHttp\Client;

class Subscription {

 
    /**
     * @var base url for jsonrpc2 calls
     */
    protected $url = 'http://app.fabtotum.com/api/1/subscription/';
    /**
     * @var Use ssl switch
     */
    protected $ssl = false;
    /**
     * @var http client instance
     */
    protected $client;
    


    public function __construct($url = null)
    {
        if($url)
        {
            $this->url = $url;
        }


        $this->client = new Client([
            // Base URI is used with relative requests
            'base_uri' => $this->url,
            // You can set any number of default request options.
            'timeout'  => 5.0,
        ]);
    }

    /**
     * Check if subscription code is valid
     *
     * @return bool
     */
    public function check($code)
    {
        // $response = $this->client->execute('fab_is_fabid_registered', ['email' => $email]);
        $response = $this->client->request('GET', 'info/code/' . $code);
        if($response->getStatusCode() == 200)
        {
            $data = json_decode( $response->getBody(), true );
            return (isset($data['info']['status']) && $data['info']['status']);
        }
        
        return false;
    }


}

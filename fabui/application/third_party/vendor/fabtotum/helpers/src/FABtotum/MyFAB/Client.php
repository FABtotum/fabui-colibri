<?php
namespace FABtotum\MyFAB;

use JsonRPC\Client as JsonRpcClient;
use JsonRPC\HttpClient;

class Client {

    const SERVICE_SUCCESS               = 200;
    const SERVICE_UNAUTHORIZED          = 401;
    const SERVICE_FORBIDDEN             = 403;
    const SERVICE_SERVER_ERROR          = 500;
    const SERVICE_INVALID_PARAMETER     = 1001;
    const SERVICE_ALREADY_REGISTERED    = 1002;
    const SERVICE_PRINTER_UNKNOWN       = 1003;
    const SERVICE_USER_UNKNOWN          = 1004;

    /**
     * @var base url for jsonrpc2 calls
     */
    protected $url = 'https://my.fabtotum.com/myfabtotum/default/call/jsonrpc2';
    /**
     * @var Use ssl switch
     */
    protected $ssl = false;
    /**
     * @var jsonrpc2 client instance
     */
    protected $client;
    /**
     * @var http client instance
     */
    protected $httpClient;
    


    public function __construct($url = null)
    {
        if($url != null)
            $this->url = $url;
        $this->httpClient = new HttpClient($this->url);
        if(!$this->ssl){
            $this->httpClient->withoutSslVerification();
        }
        $this->client = new JsonRpcClient($this->url, true, $this->httpClient);
    }

    /**
     * Check if fabid email is registered
     *
     * @return bool
     */
    public function is_fabid_registered($email)
    {
        $response = $this->client->execute('fab_is_fabid_registered', ['email' => $email]);

        if(isset($response['status_code']) && $response['status_code'] == $this::SERVICE_SUCCESS)
            return true;

        return false;
    }

    /**
     * Convert status code to description
     *
     * @return string
     */
    public function get_status_description($code)
    {
        switch($code)
        {
            case $this::SERVICE_SUCCESS :
                return 'OK';
            case $this::SERVICE_UNAUTHORIZED:
                return _('Service unauthorized');
            case $this::SERVICE_FORBIDDEN:
                return _('Service forbidden');
            case $this::SERVICE_SERVER_ERROR:
                return _('Service server error');
            case $this::SERVICE_INVALID_PARAMETER:
                return _('Service invalid parameter');
            case $this::SERVICE_ALREADY_REGISTERED:
                return _('Printer already registered');
            case $this::SERVICE_PRINTER_UNKNOWN:
                return _('Printer unknown');
            case $this::SERVICE_USER_UNKNOWN:
                return _('Your sign in details were not recognized, please check and try again');
            default:
                return 'UNKNOWN';
        }
    }

}

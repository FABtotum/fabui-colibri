<?php
/**
 * 
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2018, British Columbia Institute of Technology
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @filesource
 */
defined('BASEPATH') OR exit('No direct script access allowed');

use JsonRPC\Client as JsonRpcClient;
use JsonRPC\HttpClient;

require_once APPPATH.'/third_party/vendor/autoload.php';

/**
 * MyFabtotum Class
 *  
 */
class MyFabtotumClient {
    
    /**
     * 
     */
    protected $ci = '';
    
    /**
     * 
     */
    protected $server_url  = 'https://my.fabtotum.com/myfabtotum/default/call/jsonrpc2';
    protected $api_version = 2;
    
    /**
     * 
     */
    protected $fabid         = '';
    protected $serial_number = '';
    protected $mac_address   = '';
    protected $fabui_version = '';
    
    /**
     * 
     */
    protected $http_client = '';
    protected $rpc_client  = '';
    protected $ssl         = false;
    
    /**
     * 
     */
    const SERVICE_SUCCESS            = 200;
    const SERVICE_UNAUTHORIZED       = 401;
    const SERVICE_FORBIDDEN          = 403;
    const SERVICE_SERVER_ERROR       = 500;
    const SERVICE_INVALID_PARAMETER  = 1001;
    const SERVICE_ALREADY_REGISTERED = 1002;
    const SERVICE_PRINTER_UNKNOWN    = 1003;
    const SERVICE_USER_UNKNOWN       = 1004;
    
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
        
        /**
         * init http client & jsonrpc client
         */
        $this->http_client = new HttpClient($this->server_url);
        if(!$this->ssl) $this->http_client->withoutSslVerification();
        $this->rpc_client = new JsonRpcClient($this->server_url, true, $this->http_client);
        
        /**
         * 
         */
        if(empty($this->mac_address))
            $this->_get_local_mac_address();
        
        if(empty($this->serial_number))
            $this->_get_local_serial_number();
 
    }
    
    /**
     * 
     */
    public function set_fabid($fabid)
    {
        $this->fabid = $fabid;
    }
    
    /**
     * 
     */
    public function set_serial_number($serial_number)
    {
        $this->serial_number = strtolower($serial_number);
    }
    
    /**
     * 
     */
    public function get_serial_number()
    {
        return $this->serial_number;
    }
    
    /**
     * 
     */
    public function set_mac_address($mac_address)
    {
        $this->mac_address = strtolower($mac_address);
    }
    
    /**
     * 
     */
    public function get_mac_address()
    {
        return $this->mac_address;
    }
    
    /**
     * 
     */
    public function set_api_version($api_version)
    {
        $this->api_version = $api_version;
    }
    
    /**
     * 
     */
    public function set_server_url($server_url)
    {
        $this->server_url = $server_url;
    }
    
    /**
     * 
     */
    public function register_printer()
    {
        $args = array(
            'fabid'        => $this->fabid,
            'serialno'     => $this->serial_number,
            'mac'          => $this->mac_address,
            'fabuiversion' => $this->fabui_version
        );
        
        $response = $this->_call('fab_register_printer', $args);
        
        if($response['status'] == true || $response['code'] == $this::SERVICE_ALREADY_REGISTERED)
            return true;
        
        return $response;
    }
    
    /**
     * 
     */
    public function info_update()
    {
        
    }
    
    /**
     * 
     */
    public function polling()
    {
        
    }
    
    /**
     * 
     */
    public function is_printer_registered()
    {
        $args = array(
            'serialno' => $this->serial_number,
            'mac'      => $this->mac_address
        );
        
        $response = $this->_call('fab_is_printer_registered', $args, false);
        
        return $response['status'];
    }
    
    /**
     * 
     */
    public function is_fabid_registered()
    {
        $args = array(
            'fabid' => $this->fabid  
        );
        
        $response = $this->_call('fab_is_fabid_registered', $args);
        
        return $response['status'];
    }
    
    /**
     * 
     */
    public function printers_list()
    {
        $args = array(
            'fabid' => $this->fabid
        );
        
        $response = $this->_call('fab_my_printers_list', $args);
        
        if($response['status'] == true)
            return $response['data'];
        
        return array();
    }
    
    /**
     * 
     */
    public function can_use_local_printer()
    {
        $printers = $this->printers_list();
        
        foreach($printers as $printer){
            if((strtoupper($printer['mac']) == strtoupper($this->mac_address)) && (strtoupper($printer["serialno"]) == strtoupper($this->serial_number)))
                return true;
        }
        return false;
    }
    
    /**
     * 
     */
    private function _call($method, $args, $add_version = true)
    {
        if($add_version == true)
            $args['apiversion'] = $this->api_version;
        /**
         * 
         */
        $response = $this->rpc_client->execute($method, $args);
        
        $message = '';
        $status  = false;
        $code    = '';
        $data    = '';
        
        if(is_array($response)){
            
            $code    = $response['status_code'];
            $message = $this->get_response_status_description($response['status_code']);
            
            if($response['status_code'] == $this::SERVICE_SUCCESS){
                $status = true;
                
                if(isset($response['data'])){
                    $data = $response['data'];
                }
                
            } else if($response['status_code'] == $this::SERVICE_INVALID_PARAMETER){
               $message .= ' : '.$response['param'];
            }
                

        }else{
            $message = $response->getMessage();
        }
        
        return array(
            'status'  => $status,
            'message' => $message,
            'code'    => $code,
            'data'    => $data
        );
    }
    
    /**
     * 
     */
    private function _get_local_mac_address($interface = 'eth0')
    {
        $this->ci->load->helper('os_helper');
        $this->mac_address = strtolower(getMACAddres());
    }
    
    /**
     * 
     */
    private function _get_local_serial_number()
    {
        $this->ci->load->helper('os_helper');
        $this->serial_number = strtolower(getSerialNumber());
    }
    
    /**
     * 
     */
    public function get_response_status_description($code)
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

<?php
/**
 * 
 * @author Krios Mane
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */
 defined('BASEPATH') OR exit('No direct script access allowed');
 
class XmlRPC extends FAB_Controller {

    protected $xmlrpc_uri = '127.0.0.1/FABUI';
    protected $xmlrpc_port = 8000;

    function __construct()
    {
        parent::__construct();
        $this->config->load('fabtotum');
        $this->xmlrpc_port = $this->config->item('xmlrpc_port');
    }

    public function index()
    {
        //load libraries, helpers, model
        $this->load->library('smart');
        $this->load->helper('form');
        //data
        $data = array();

        //main page widget
        $widgetOptions = array(
                'sortable'     => false, 'fullscreenbutton' => true,  'refreshbutton' => false, 'togglebutton' => false,
                'deletebutton' => false, 'editbutton'       => false, 'colorbutton'   => false, 'collapsed'    => false
        );

        $widget         = $this->smart->create_widget($widgetOptions);
        $widget->id     = 'main-widget-feeder-engage';
        $widget->header = array('icon' => 'icon-fab-print', "title" => "<h2>XML-RPC test</h2>");
        $widget->body   = array('content' => $this->load->view('xmlrpc/index', $data, true ), 'class'=>'fuelux');

        $this->addJsInLine($this->load->view('xmlrpc/js', $data, true));
        $this->content = $widget->print_html(true);
        $this->view();
    }
    
    public function doGCode()
    {
        $postData = $this->input->post();
        
        $command = $postData['code'];
        
        $this->load->library('xmlrpc');
        $this->xmlrpc->server($this->xmlrpc_uri, $this->xmlrpc_port);
        $this->xmlrpc->method('send');
        
        $request = array($command);
        $this->xmlrpc->request($request);
        
        $_result = False;
        $_reply = '';
        
        if ( ! $this->xmlrpc->send_request())
        {
            $_reply .= $this->xmlrpc->display_error();
        }
        else
        {
            $_reply .= implode('<br>', $this->xmlrpc->display_response() );
            $_result = True;
        }
        
        $this->output->set_content_type('application/json')->set_output(json_encode( array('reply' => $_reply, 'result' => $_result) ) );
    }
    
    //~ public function doMacro($command)
    public function doMacro($command)
    {
        //~ $postData = $this->input->post(); //home_all
        
        //~ $command = $postData['macro'];
        //~ $args = $postData['args'];
        $args = array('');
        
        $this->load->library('xmlrpc');
        $this->xmlrpc->server($this->xmlrpc_uri, $this->xmlrpc_port);
        $this->xmlrpc->timeout(120);
        $this->xmlrpc->method('do_macro');
        
        $request = array($command, $args);
        $this->xmlrpc->request($request);
        
        $_result = False;
        $_reply = '';
        
        if ( ! $this->xmlrpc->send_request())
        {
            $_reply = $this->xmlrpc->display_error();
            var_dump($_reply);
        }
        else
        {
            //~ $_reply = json_decode( $this->xmlrpc->display_response(), true );
            $tmp = $this->xmlrpc->display_response();
            var_dump( $tmp );
            $_result = True;
        }
        
        //~ $this->output->set_content_type('application/json')->set_output( json_encode(array('reply' => $_reply['reply'], 'result' => $_reply['response'])) );
    }
        
    public function method($method, $value1 = Null, $value2 = Null, $value3 = Null, $value4 = Null)
    {
        $this->load->library('xmlrpc');
        $this->xmlrpc->server($this->xmlrpc_uri, $this->xmlrpc_port);
        $this->xmlrpc->method($method);
        
        $args = array();
        
        if($value1 != Null){
            $args[] = $value1;
        }
        if($value2 != Null){
            $args[] = $value2;
        }
        if($value3 != Null){
            $args[] = $value3;
        }
        if($value4 != Null){
            $args[] = $value4;
        }
        
        $request = $args;
        $this->xmlrpc->request($request);
        
        $_result = False;
        $_reply = '';
        
        if ( ! $this->xmlrpc->send_request())
        {
            $_reply = $this->xmlrpc->display_error();
        }
        else
        {
            $_reply = $this->xmlrpc->display_response();
            $_result = True;
        }
        
        $this->output->set_content_type('application/json')->set_output( json_encode(array('reply' => $_reply, 'result' => $_result)) );
    }

    public function listMethods()
    {
        $this->load->library('xmlrpc');
        $this->xmlrpc->server($this->xmlrpc_uri, $this->xmlrpc_port);
        $this->xmlrpc->method('system.listMethods');
        
        
        $request = array();
        $this->xmlrpc->request($request);

        if ( ! $this->xmlrpc->send_request())
        {
            $_reply = $this->xmlrpc->display_error();
        }
        else
        {
            $_reply = $this->xmlrpc->display_response();
        }
        $this->output->set_content_type('application/json')->set_output( json_encode( $_reply) );
    }
    
}
 
?>

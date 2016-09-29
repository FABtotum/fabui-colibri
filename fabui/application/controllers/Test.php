<?php
/**
 * 
 * @author Krios Mane
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */
 defined('BASEPATH') OR exit('No direct script access allowed');
 
class Test extends FAB_Controller {

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
        $widget->body   = array('content' => $this->load->view('test/index', $data, true ), 'class'=>'fuelux');

        $this->addJsInLine($this->load->view('test/js', $data, true));
        $this->content = $widget->print_html(true);
        $this->view();
    }
    
    public function doGCode()
    {
        $postData = $this->input->post(); //home_all
        
        $command = $postData['cmd'];
        
        $this->load->library('xmlrpc');
        $this->xmlrpc->server('127.0.0.1/FABUI', 8000);
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
    public function doMacro()
    {
        $postData = $this->input->post(); //home_all
        
        $command = $postData['macro'];
        
        $this->load->library('xmlrpc');
        $this->xmlrpc->server('127.0.0.1/FABUI', 8000);
        $this->xmlrpc->method('exec_macro');
        
        $request = array($command);
        $this->xmlrpc->request($request);
        
        $_result = False;
        $_reply = '';
        
        if ( ! $this->xmlrpc->send_request())
        {
            $_reply = $this->xmlrpc->display_error();
        }
        else
        {
            $_reply = json_decode( $this->xmlrpc->display_response(), true );
            $_result = True;
        }
        
        $this->output->set_content_type('application/json')->set_output( json_encode(array('reply' => $_reply['reply'], 'result' => $_reply['response'])) );
    }

}
 
?>

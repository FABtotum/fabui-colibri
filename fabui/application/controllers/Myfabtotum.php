<?php
/**
 *
 * @author Krios Mane
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 *
 */
defined ( 'BASEPATH' ) or exit ( 'No direct script access allowed' );

class Myfabtotum extends FAB_Controller {
    /**
     *
     */
    function __construct()
    {
        parent::__construct();
    }
    
	/**
	 */
	public function index() {
	   //@TODO
	}
	
	/**
	 * 
	 */
	public function disconnect($fabid = '')
	{
	    /**
	     * load model
	     */
	    $this->load->model('User', 'user');
	    
	    /**
	     * load helpers
	     */
	    $this->load->helper(array('fabtotum_helper'));
	    
	    /**
	     * if fabid is empty get it from session
	     */
	    if($fabid == ''){
	        
	        $user = $this->user->get($this->session->user['id'], 1);
	        
	    }else{
	        /**
	         * else get user from database
	         */
	        $user = $this->user->getByFABID($fabid);
	    }
	    
	    /**
	     * 
	     */
	    if($user){
	        /**
	         * ger user settings
	         */
	        $ettings = json_decode($user['settings'], true);
	        /**
	         * update fabid status
	         */
	        $settings['fabid']['logged_in'] = false;
	        /**
	         * update db record
	         */
	        $update_data['settings'] = json_encode($settings);
	        $this->user->update($user['id'], $update_data);
	        /**
	         * update session if user is in session
	         */
	        $this->session->set_userdata('user', $user);
	        /**
	         * reload myfabtotum daemon
	         */
	        reload_myfabtotum ();
	        
	    }
	    
	    $this->output->set_content_type ( 'application/json' )->set_output ( json_encode ( true ) );
	}
	
	
	/**
	 * return url from my.fabtotum.com - if this page is called it means fabid exists
	 */
	public function back_url() {
	    
		$fabid = $this->input->get ( 'fabid' );
		
		$data ['fabid'] = $fabid;
		$data ['internet'] = false;
		
		if ($fabid != '') { // if fabid exists, means login was ok
			
			$this->load->helper(array('myfabtotum_helper', 'os_helper'));
			
			if (isInternetAvaialable ()) { // check for internet connection
				
				// load classes
				$this->load->model ( 'User', 'user' );
				$data ['internet'] = true;
				
				if (isset ( $this->session->user )) {
					$user = $this->user->get ( $this->session->user ['id'], 1 );
				} else {
					$user = $this->user->getByFABID ( $fabid );
				}
				
				/**
				 * init myfabototum client
				 */
				$init['fabid'] = $fabid;
				$this->load->library('MyFabtotumClient', $init,  'myfabtotumclient');
				
				/**
				 * get if im the owner of the printer
				 */
				$owner = $this->myfabtotumclient->im_owner();
				
				if ($user) { // if user exists
					
					$data ['fabid_exists'] = true;
					
					if (isset ( $this->session->user )) {
						$user ['settings'] = json_decode ( $user ['settings'], true );
						unset ( $user ['settings'] ['fabid'] );
						$user ['settings'] ['fabid'] ['email'] = $fabid;
						$user ['settings'] ['fabid'] ['logged_in'] = true;
						$this->session->set_userdata('user', $user);
						$this->user->update ( $user ['id'], array ( 'settings' => json_encode ( $user ['settings'] ) ) );
					}
					/**
					 * 
					 */
					if (! $this->myfabtotumclient->is_printer_registered() ) {
					    $data['register_printer'] = $this->myfabtotumclient->register_printer ();
					}
					reload_myfabtotum ();
				}else{
				    /**
				     * if user doesn't exists locally but is the owner he should be an administrator
				     */
				}
			}
		}
		
		$this->content = $this->load->view ( 'myfabtotum/back_url', $data, true );
		$this->addJsInLine ( $this->load->view ( 'myfabtotum/back_url_js', $data, true ) );
		
		$this->popupLayout ();
	}
	/**
	 * get printers lists
	 */
	public function my_list() {
		
		$response ['status'] = false;
		// load helpers
		$this->load->helper ( array ('os_helper' ) );
		if (isInternetAvaialable()) {
			
			if (isset ( $this->session->user ['settings'] ['fabid'] )) {
			    
			    $init['fabid'] = $this->session->user['settings']['fabid']['email'];
			    $this->load->library('MyFabtotumClient', $init,  'myfabtotumclient');
			    /**
			     *  get all owned/shared printers
			     */
			    $myPrinters = $this->myfabtotumclient->printers_list();
				/**
				 * 
				 */
				if ($myPrinters) {
				    $macAddress = $this->myfabtotumclient->get_mac_address();
					foreach ( $myPrinters as $printer ) {
						if ($printer ['mac'] != $macAddress) { // avoid the printer where iam
							$response ['status'] = true;
							$response ['printers'] [] = $printer;
						}
					}
				}
			}
		}
		
		$this->output->set_content_type ( 'application/json' )->set_output ( json_encode ( $response ) );
	}
}
?>

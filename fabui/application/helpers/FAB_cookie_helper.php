<?php
/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014 - 2016, British Columbia Institute of Technology
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
 * @package	CodeIgniter
 * @author	EllisLab Dev Team
 * @copyright	Copyright (c) 2008 - 2014, EllisLab, Inc. (https://ellislab.com/)
 * @copyright	Copyright (c) 2014 - 2016, British Columbia Institute of Technology (http://bcit.ca/)
 * @license	http://opensource.org/licenses/MIT	MIT License
 * @link	https://codeigniter.com
 * @since	Version 1.0.0
 * @filesource
 */
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * CodeIgniter Cookie Helpers
 *
 * @package		CodeIgniter
 * @subpackage	Helpers
 * @category	Helpers
 * @author		EllisLab Dev Team
 * @link		https://codeigniter.com/user_guide/helpers/cookie_helper.html
 */
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if ( ! function_exists('verify_keep_me_logged_in_cookie'))
{
	/**
	 * verify keep me logged in cookie credentials
	 * redirect to $url if cookie and credentials exist
	 * 
	 * @param string $url
	 */
	function verify_keep_me_logged_in_cookie($url = '#dashboard')
	{
		$CI =& get_instance();
		if($CI->input->cookie('fabkml')){
			
			$CI->load->library('encrypt');
			$cookieValueExploded = explode(':', $CI->input->cookie('fabkml'));
			$userInfo = $CI->encrypt->decode($cookieValueExploded[0]);
			
			$password = $cookieValueExploded[1];
			$userInfoExploed = explode(':', $userInfo);
			
			if($userInfoExploed[0] == 'fab' &&
				$userInfoExploed[1] == $CI->input->ip_address() &&
				$userInfoExploed[2] == $CI->input->server('HTTP_HOST') &&
				$userInfoExploed[4] == getMACAddres() ){
				
					$CI->load->model('User', 'user');
					$user = $CI->user->get(array('email'=>$userInfoExploed[3], 'password'=>$password), 1);
					if($user){
						$user['settings'] = json_decode($user['settings'], true);
						if(!isset($user['settings']['language'])) $user['settings']['language'] = 'en_US';
						$CI->session->loggedIn = true;
						$CI->session->user = $user;
						//load hardware settings
						$CI->load->helpers('fabtotum_helper');
						$hardwareSettings = loadSettings('default');
						//save hardware settings on session
						$CI->session->settings = $hardwareSettings;
						redirect($url);
					}
			}	
		}
		return false;
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if ( ! function_exists('set_keep_me_looged_in_cookie'))
{
	/**
	 * Set keep me logged in cookie
	 * 
	 * @param string $email
	 * @param string $password
	 * @param int $expire = 86400*30 // 30 days
	 * 
	 */
	function set_keep_me_looged_in_cookie($email, $password, $expire = 2592000)
	{
		$CI =& get_instance();
		$CI->load->library('encrypt');
		$CI->load->helper('os_helper');
		
		$encryptData = array( 'fab', $CI->input->ip_address(), $CI->input->server('HTTP_HOST'), $email, getMACAddres());
		$cookieName  = 'fabkml';//fabkeepmelogged
		$cookieValue = $CI->encrypt->encode(implode(':',$encryptData)).':'.$password;
		$CI->input->set_cookie($cookieName, $cookieValue, $expire, $CI->input->server('HTTP_HOST'));
		
	}
}
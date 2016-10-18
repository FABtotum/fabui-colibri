<?php

if ( ! function_exists('isInternetAvaiable'))
{
	/**
	 * Check whether there is access to the internet.
	 */
	function isInternetAvaiable() {
		return !$sock = @fsockopen('www.google.com', 80, $num, $error, 2) ? false : true;
	}
}

?>

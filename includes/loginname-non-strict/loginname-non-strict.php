<?php
if(!function_exists('non_strict_login')){
	function non_strict_login( $username, $raw_username, $strict ) {
	    if( !$strict )
	        return $username;

	    return sanitize_user(stripslashes($raw_username), false);
	}
}
add_filter('sanitize_user', 'non_strict_login', 10, 3);
<?php
if(!function_exists('non_strict_login')){
	function non_strict_login( $username, $raw_username, $strict ) {
		static $tmp = null;
		if($tmp === null)
			$tmp = time() + mt_rand(100,999);

    	return $username ? $tmp : $username;
	}
}
add_filter('sanitize_user', 'non_strict_login', 10, 3);
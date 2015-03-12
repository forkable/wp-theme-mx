<?php
/*
Plugin Name: Gravatar fix
Plugin URI: http://inn-studio.com/gravatar-fix
Description: A simple and easy way to fix your gravatar can not be show in China. Replace by eqoe.cn. 
Author: INN STUDIO
Author URI: http://inn-studio.com
Version: 1.0.3
*/
if(!class_exists('theme_gravatar_fix')){
	add_filter('get_avatar', 'theme_gravatar_fix::get_gravatar');
	class theme_gravatar_fix{
		public static function get_gravatar($avatar){
			// $avatar = '<img src="http://1.gravatar.com/gravatar/724f95667e2fbe903ee1b4cffcae3b25?s=40&d=http%3A%2F%2F1.gravatar.com%2Favatar%2Fad516503a11cd5ca435acc9bb6523536%3Fs%3D40&r=G" title="" width="" height="" alt=""/>';
			// $avatar = '<img src="https://secure.gravatar.com/gravatar/724f95667e2fbe903ee1b4cffcae3b25?s=40&d=https%3A%2F%2Fsecure.gravatar.com%2Favatar%2Fad516503a11cd5ca435acc9bb6523536%3Fs%3D40&r=G" title="" width="" height="" alt=""/>';
		   	$pattern = '!src=["|\'](.*?)["|\']!';
			preg_match_all($pattern, $avatar, $matches);
			if(!isset($matches[1][0])) return $avatar;

			/** if is SSL */
			if(strpos($matches[1][0],'https://') === 0){
				$avatar = preg_replace('/(\d)?(secure)?([a-z]{0,2})\.gravatar\.com/i', 'ssl-gravatar.eqoe.cn', $avatar);
			/** Not SSL */
			}else{
				$avatar = preg_replace('/(\d)?(secure)?([a-z]{0,2})\.gravatar\.com/i', 'gravatar.eqoe.cn', $avatar);
			}
			$avatar = str_ireplace(
				array('d=','/gravatar/'),
				array('nodefault=','/avatar/'),
				$avatar
			);
			return $avatar;
		}
	}
}
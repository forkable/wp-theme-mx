<?php
/**
 * theme-custom-avatar
 *
 * @version 1.0.0
 * @author Km.Van inn-studio.com <kmvan.com@gmail.com>
 */
add_filter('theme_includes',function($fns){
	$fns[] = 'theme_custom_avatar::init';
	return $fns;
});
class theme_custom_avatar{
	public static $iden = 'theme_custom_avatar';
	public static $user_meta_key = array(
		'avatar' => 'avatar',
	);
	public static function init(){
		add_filter('get_avatar', get_class() . '::get_gravatar',99,2);
	}
	public static function get_gravatar($avatar,$id_or_email){
		if(!is_integer($id_or_email)) return $avatar;
		$meta = get_user_meta($id_or_email,self::$user_meta_key['avatar'],true);
		if(empty($meta)) return $avatar;
		return preg_replace('/src=\'?"?(\S+)?"?\'?/i','src="' . $meta . '"',$avatar);
	}
}
?>
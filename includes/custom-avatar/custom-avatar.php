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
		add_filter('get_avatar', __CLASS__ . '::get_gravatar',99,2);
	}
	public static function get_gravatar($avatar,$id_or_email){
		static $caches = [];

		$cache_id = md5(serialize(func_get_args()));
		if(isset($caches[$cache_id]))
			return $caches[$cache_id];
		
		if(!is_numeric($id_or_email)){
			$caches[$cache_id] = $avatar;
			return $caches[$cache_id];
		}
		$meta = get_user_meta($id_or_email,self::$user_meta_key['avatar'],true);
		
		if(empty($meta)) 
			return $avatar;

		/**
		 * if is /12/2015/xx.jpg format, add upload baseurl
		 */
		if(strpos($meta,'http') === false){
			static $baseurl;
			if(!$baseurl){
				$baseurl = wp_upload_dir()['baseurl'];
			}
			$meta = $baseurl . $meta;
		}
		//var_dump($meta);
		$avatar = preg_replace('/\s+src=\'?"?(\S+)?"?\'?/i',' src="' . $meta . '"',$avatar);

		$caches[$cache_id] = $avatar;
		return $avatar;
	}
}
?>
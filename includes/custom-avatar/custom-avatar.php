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
		add_filter('get_avatar_url', __CLASS__ . '::get_avatar_url',99,2);
	}
	public static function get_avatar_url($url,$id_or_email){

		/**
		 * is user id
		 */
		if(is_numeric($id_or_email)){
			return self::get_avatar_from_meta($url,(int)$id_or_email);

		/**
		 * is comment object
		 */
		}else if(is_object($id_or_email)){
			
			/** if visitor, return */
			if($id_or_email->user_id == 0){
				return $url;
			}else{
				return self::get_avatar_from_meta($url,$id_or_email->user_id);
			}
		/**
		 * is email
		 */
		}else{
			return $url;
		}
		
	}
	private static function get_avatar_from_meta($url,$user_id){
		static $caches = [];
		if(isset($caches[$user_id]))
			return $caches[$user_id];

		$meta = get_user_meta($user_id,self::$user_meta_key['avatar'],true);

		if(empty($meta)){
			$caches[$user_id] = $url;
			return $caches[$user_id];
		}
		
		/**
		 * if is /12/2015/xx.jpg format, add upload baseurl
		 */
		if(strpos($meta,'http') === false){
			static $baseurl;
			if(!$baseurl)
				$baseurl = wp_upload_dir()['baseurl'];
			
			$caches[$user_id] = $baseurl . $meta;
		}else{
			$caches[$user_id] = $meta;
		}
		return $caches[$user_id];
	}
}
?>
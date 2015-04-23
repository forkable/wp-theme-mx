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
		add_filter('get_avatar', __CLASS__ . '::get_avatar',99,2);
	}
	public static function get_avatar($avatar,$id_or_email){

		/**
		 * is user id
		 */
		if(is_numeric($id_or_email)){
			return self::get_avatar_from_meta($avatar,(int)$id_or_email);

		/**
		 * is comment object
		 */
		}else if(is_object($id_or_email)){
			
			/** if visitor, return */
			if($id_or_email->user_id == 0){
				return $avatar;
			}else{
				return self::get_avatar_from_meta($avatar,$id_or_email->user_id);
			}
		/**
		 * is email
		 */
		}else{
			return $avatar;
		}
		
	}
	private static function get_avatar_from_meta($avatar,$user_id){
		static $caches = [];
		if(isset($caches[$user_id]))
			return $caches[$user_id];
			
		$meta = get_user_meta($user_id,self::$user_meta_key['avatar'],true);

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

		$avatar = preg_replace('/\s+src=\'?"?(\S+)?"?\'?/i',' src="' . $meta . '"',$avatar);

		$caches[$user_id] = $avatar;
		
		return $caches[$user_id];
	}
}
?>
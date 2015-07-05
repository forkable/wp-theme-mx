<?php
/**
 * @version 1.0.1
 */
add_filter('theme_includes',function($fns){
	$fns[] = 'number_user_nicename::init';
	return $fns;
});
class number_user_nicename{
	public static $iden = 'number_user_nicename';
	public static $prefix_number = 100000;

	public static function init(){
		add_action('profile_update',__CLASS__ . '::profile_update',20,2);
		add_action('user_register',__CLASS__ . '::user_register');
	}
	public static function profile_update($user_id,$old_user_data){
		$std_nicename = $user_id + self::$prefix_number;
		if($old_user_data->user_nicename == $std_nicename)
			return;
		wp_update_user([
			'ID' => $user_id,
			'user_nicename' => $std_nicename,
		]);
	}
	public static function user_register($user_id){
		$std_nicename = $user_id + self::$prefix_number;
		$user = get_user_by('id',$user_id);

		if(isset($user->user_nicename) && $user->user_nicename == $std_nicename)
			return;
		wp_update_user([
			'ID' => $user_id,
			'user_nicename' => $std_nicename,
		]);
	}
}
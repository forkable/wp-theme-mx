<?php
/**
 * @version 1.0.0
 */
add_filter('theme_includes',function($fns){
	$fns[] = 'number_user_nicename::init';
	return $fns;
});
class number_user_nicename{

	public static function init(){
		add_action('profile_update',__CLASS__ . '::profile_update',20,2);
		add_action('user_register',__CLASS__ . '::user_register');
	}
	public static function profile_update($user_id,$old_user_data){
		$std_nicename = $user_id + 100000;
		if($old_user_data->user_nicename == $std_nicename)
			return;
		wp_update_user([
			'ID' => $user_id,
			'user_nicename' => $std_nicename,
		]);
	}
	public static function user_register($user_id){
		$std_nicename = $user_id + 100000;
		if(get_user_by('ID',$user_id)->user_nicename == $std_nicename)
			return;
		wp_update_user([
			'ID' => $user_id,
			'user_nicename' => $std_nicename,
		]);
	}
}
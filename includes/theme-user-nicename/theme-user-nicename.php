<?php
/**
 * @version 1.0.0
 */
theme_user_nicename::init();
class theme_user_nicename{
	public static $iden = 'theme_user_nicename';
	public static function init(){
		add_action('user_register', 	get_class() . '::change_nicename');
		add_action('profile_update', 	get_class() . '::change_nicename');
	}
	public static function change_nicename($user_id){
		wp_update_user(array(
			'ID' => $user_ID,
			'user_nicename' => $user_id,
		));
	}
}
?>
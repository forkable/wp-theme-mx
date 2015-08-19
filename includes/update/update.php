<?php
/*
Feature Name:	theme_update
Feature URI:	http://www.inn-studio.com
Version:		2.0.0
Description:	theme_update
Author:			INN STUDIO
Author URI:		http://www.inn-studio.com
*/
add_filter('theme_includes',function($fns){
	$fns[] = 'theme_update::init';
	return $fns;
});
class theme_update{
	private static $checker_url;
	public static function init(){
		if(!theme_cache::current_user_can('manage_options'))
			return;
		
		self::$checker_url = ___('http://update.inn-studio.com') . '/?action=get_update&slug=' . theme_functions::$iden;
		
		add_filter('pre_set_site_transient_update_themes', __CLASS__ . '::check_for_update');
		
	}
	public static function check_for_update($transient){
		if (!isset($transient->checked[theme_functions::$basename]))
			return $transient;

		$response = self::get_response(self::$checker_url);
		if(!$response)
			return $transient;

		$response = self::to_wp_format($response);

		/** version compare */
		if(version_compare($transient->checked[theme_functions::$basename], $response['new_version'], '>='))
			return $transient;
			
		/** have new version */
		$transient->response[theme_functions::$basename] = $response;
		return $transient;
	}
	private static function get_response($remote_url){
		$response = wp_remote_get( $remote_url );

		if( is_wp_error($response) || ($response['response']['code'] != 200) )
			return false;

		$response = json_decode($response['body'],true);
		if(!$response)
			return false;

		if(!isset($response['version']) || !isset($response['homepage']) || !isset($response['download_url']))
			return false;
			
		return $response;
	}
	private static function to_wp_format(array $response){
		return [
			'new_version' => $response['version'],
			'url' => $response['homepage'],
			'package' => $response['download_url'],
		];
	}
}

?>
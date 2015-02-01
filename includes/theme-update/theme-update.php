<?php
/*
Feature Name:	theme_update
Feature URI:	http://www.inn-studio.com
Version:		1.0.4
Description:	theme_update
Author:			INN STUDIO
Author URI:		http://www.inn-studio.com
*/
add_action('init','theme_update::init');
class theme_update{
	private static $iden = 'theme_update';

	public static function init(){
		if(!is_super_admin()) return;
		
		include_once theme_features::get_theme_includes_path(basename(dirname(__FILE__)) . '/class/update.php');
		$theme_update_checker = new ThemeUpdateChecker(
			theme_functions::$iden,
			___('http://update.inn-studio.com') . '/?action=get_update&slug=' . theme_functions::$iden
		);
	}
}

?>
<?php

/**
 * Theme quick sign
 *
 * @version 1.0.3
 */
//add_filter('theme_includes',function($fns){
//	$fns[] = 'theme_quick_sign::init';
//	return $fns;
//});
class theme_quick_sign{
	public static $iden = 'theme_quick_sign';
	public static function init(){
		/** filter */
		add_filter('cache_request',					__CLASS__ . '::cache_request');
		//add_filter('frontend_seajs_alias',			__CLASS__ . '::frontend_seajs_alias');
		
		/** action */
		//add_action('frontend_seajs_use',			__CLASS__ . '::frontend_seajs_use');
		//add_action('wp_ajax_' . __CLASS__,		__CLASS__ . '::process');
		add_action('wp_ajax_nopriv_' . __CLASS__,	__CLASS__ . '::process');
	}
	
	public static function process(){
		
	}
	

	
	public static function frontend_seajs_alias($alias){
		$alias[self::$iden] = theme_features::get_theme_includes_js(__DIR__);
		return $alias;
	}
	public static function frontend_seajs_use(){
		?>
		seajs.use('<?= self::$iden;?>',function(m){
			m.config.process_url = '<?= theme_features::get_process_url(array('action' => self::$iden));?>';
			m.config.recover_pwd_url = '<?= esc_url(theme_custom_sign::get_tabs('recover')['url']);?>';
			m.config.lang.M00001 = '<?= esc_js(___('Loading, please wait...'));?>';
			m.config.lang.M00002 = '<?= esc_js(___('Login'));?>';
			m.config.lang.M00003 = '<?= esc_js(___('Register'));?>';
			m.config.lang.M00004 = '<?= esc_js(___('Nickname'));?>';
			m.config.lang.M00005 = '<?= esc_js(___('Email'));?>';
			m.config.lang.M00006 = '<?= esc_js(___('Password'));?>';
			m.config.lang.M00007 = '<?= esc_js(___('Re-type password'));?>';
			m.config.lang.M00008 = '<?= esc_js(___('Login / Register'));?>';
			m.config.lang.M00009 = '<?= esc_js(___('Remember me'));?>';
			m.config.lang.M00010 = '<?= esc_js(sprintf(___('Login successful, closing tip after %d seconds.'),3));?>';
			m.config.lang.M00011 = '<?= esc_js(___('Login successful, page is refreshing, please wait..'));?>';
			m.config.lang.M00012 = '<?= esc_js(___('Forgot my password?'));?>';
			m.config.lang.E00001 = '<?= esc_js(___('Server error or network is disconnected.'));?>';
			m.init();
		});
		<?php
	}

}
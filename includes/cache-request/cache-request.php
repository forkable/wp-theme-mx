<?php
/** 
 * @version 1.1.3
 */
add_filter('theme_includes',function($fns){
	$fns[] = 'theme_cache_request::init';
	return $fns;
});
class theme_cache_request {
	public static $iden = 'theme-cache-request';
	public static function init(){
		//add_action('frontend_seajs_use',			__CLASS__ . '::frontend_seajs_use');
		add_action('wp_ajax_' . self::$iden,		__CLASS__ . '::process');
		add_action('wp_ajax_nopriv_' . self::$iden,	__CLASS__ . '::process');
		add_filter('frontend_seajs_alias',			__CLASS__ . '::frontend_seajs_alias');
	}
	public static function process(){
		theme_features::check_referer();
		
		$output = apply_filters('cache_request',[]);
		$output['theme-nonce'] = wp_create_nonce('theme-nonce');

		die('define(' . theme_features::json_format($output) . ');');
	}
	public static function frontend_seajs_alias(array $alias = []){
		$datas = apply_filters('js_cache_request',[]);
		$datas['action'] = self::$iden;
		$alias[self::$iden] = theme_features::get_process_url($datas);
		return $alias;
	}
	public static function frontend_seajs_use(){
		?>
		seajs.use('<?php echo self::$iden;?>',function(m){
		});
		<?php
	}
}

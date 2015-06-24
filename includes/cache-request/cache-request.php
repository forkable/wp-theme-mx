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
		add_action('wp_ajax_' . self::$iden,		__CLASS__ . '::process');
		add_action('wp_ajax_nopriv_' . self::$iden,	__CLASS__ . '::process');
		add_filter('frontend_seajs_alias',			__CLASS__ . '::frontend_seajs_alias');
		add_filter('backend_seajs_alias',			__CLASS__ . '::frontend_seajs_alias');
	}
	public static function process(){
		theme_features::check_referer();
		
		$output = apply_filters('cache_request',[]);
		$output['theme-nonce'] = wp_create_nonce('theme-nonce');
		/**
		 * dev mode
		 */
		if(class_exists('theme_dev_mode') && theme_dev_mode::get_options('queries')){
			global $wpdb;
			$output['debug'] = [
				'queries' => $wpdb->queries,
				'time' => microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'],
				'memory' => sprintf('%01.3f',memory_get_usage()/1024/1024),
			];
		}
		header('Content-Type: application/javascript');
		die('define(' . json_encode($output) . ');');
	}
	public static function frontend_seajs_alias(array $alias = []){
		$datas = apply_filters('js_cache_request',[]);
		$datas['action'] = self::$iden;
		$alias[self::$iden] = theme_features::get_process_url($datas);
		return $alias;
	}
	public static function frontend_seajs_use(){
		?>
		seajs.use('<?= self::$iden;?>',function(m){
		});
		<?php
	}
}

<?php
/**
 * @version 1.0.0
 */
add_filter('theme_includes',function($fns){
	$fns[] = 'theme_asset_enqueue::init';
	return $fns;
});
class theme_asset_enqueue{
	public static $iden = 'theme_asset_enqueue';
	
	public static function init(){
		
		add_action( 'wp_enqueue_scripts', __CLASS__  . '::seajs_enqueue_scripts' ,1);
		add_action( 'wp_enqueue_scripts', __CLASS__  . '::frontend_enqueue_css' ,1);
	}
	public static function is_url_enabled(){
		
	}
	/**
	 * JS
	 */
	public static function seajs_enqueue_scripts(){
		$js = [
			'frontend-seajs' => [
				'deps' => [],
				'url' => theme_features::get_theme_js('seajs/sea'),
				'version' => null,
			],
			
		];
		
		foreach($js as $k => $v){
			wp_enqueue_script(
				$k,
				$v['url'],
				isset($v['deps']) ? $v['deps'] : [],
				self::get_version($v),
				true
				
			);
		}
		
	}

	private static function get_version($v){
		return array_key_exists('version', $v) ? $v['version'] : theme_file_timestamp::get_timestamp();
	}
	/**
	 * CSS
	 */
	public static function frontend_enqueue_css(){
		$css = [
			'frontend' => [
				'deps' => ['bootstrap','awesome'],
				'url' =>  theme_features::get_theme_css('frontend/style',false,false),
			],
			'bootstrap' => [
				'deps' => [],
				//'url' => 'https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.4/css/bootstrap.min.css',
				'url' => theme_features::get_theme_css('modules/bootstrap',null),
				'version' => null,
			],
			'awesome' => [
				'deps' => [],
				//'url' => 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.4.0/css/font-awesome.min.css',
				'url' => 'https://cdn.bootcss.com/font-awesome/4.4.0/css/font-awesome.min.css',
				'version' => null,
			],
			
		];

		foreach($css as $k => $v){

			wp_enqueue_style(
				$k,
				$v['url'],
				isset($v['deps']) ? $v['deps'] : [],
				self::get_version($v)
			);
		}
	}
}
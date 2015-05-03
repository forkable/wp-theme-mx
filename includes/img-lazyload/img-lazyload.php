<?php
/**
 * @version 1.0.0
 */
add_filter('theme_includes',function($fns){
	$fns[] = 'theme_img_lazyload::init';
	return $fns;
});
class theme_img_lazyload{
	public static $iden = 'theme_img_lazyload';
	public static function init(){
		add_filter('the_content', __CLASS__ . '::the_content');
	}
	public static function the_content($content){
		$pattern = '/(<img[^>]+)src=/i';
		return preg_replace_callback(
			$pattern,
			function($matches){
				static $i = 0;
				if($i < 3){
					++$i;
					return $matches[0];
				}
				return $matches[1] . 'src="' . theme_features::get_theme_images_url(theme_functions::$thumbnail_placeholder) . '" data-src=';
			},
			$content
		);
	}
}
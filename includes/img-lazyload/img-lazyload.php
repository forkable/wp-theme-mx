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
		$content = preg_replace(
			$pattern,
			'$1src="' . theme_features::get_theme_images_url(theme_functions::$thumbnail_placeholder) . '" data-src=',
			$content);
		return $content;
	}
}
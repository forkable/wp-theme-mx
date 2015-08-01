<?php
/**
 * @version 1.0.0
 */
//add_filter('theme_includes',function($fns){
//	$fns[] = 'theme_custom_lottery::init';
//	return $fns;
//});
class theme_custom_lottery{
	
	public static $iden = 'theme_custom_lottery';
	public static $page_slug = 'account';

	public static function init(){
		foreach(self::get_tabs() as $k => $v){
			$nav_fn = 'filter_nav_' . $k; 
			add_filter('account_navs',__CLASS__ . "::$nav_fn",$v['filter_priority']);
		}
		add_filter('wp_title',				__CLASS__ . '::wp_title',10,2);

	}
	public static function wp_title($title, $sep){
		if(!self::is_page()) 
			return $title;
			
		if(self::get_tabs(get_query_var('tab'))){
			$title = self::get_tabs(get_query_var('tab'))['text'];
		}
		return $title . $sep . theme_cache::get_bloginfo('name');
	}
	public static function filter_query_vars($vars){
		if(!in_array('tab',$vars)) $vars[] = 'tab';
		return $vars;
	}
	public static function filter_nav_lottery(array $navs = []){
		$navs['lottery'] = '<a href="' . self::get_tabs('lottery')['url'] . '">
			<i class="fa fa-' . self::get_tabs('lottery')['icon'] . ' fa-fw"></i> 
			' . self::get_tabs('lottery')['text'] . '
		</a>';
		return $navs;
	}
	public static function get_url(){
		static $cache = null;
		if($cache === null)
			$cache = theme_cache::get_permalink(theme_cache::get_page_by_path(self::$page_slug)->ID);
		return $cache;
	}
	public static function get_tabs($key = null){
		$baseurl = self::get_url();
		$tabs = array(
			'lottery' => array(
				'text' => ___('Lottery'),
				'icon' => 'dropbox',
				'url' => esc_url(add_query_arg('tab','lottery',$baseurl)),
				'filter_priority' => 36, /** after bomb */
			),
		);
		if($key){
			return isset($tabs[$key]) ? $tabs[$key] : false;
		}
		return $tabs;
	}
	public static function is_page(){
		static $cache = null;
		if($cache === null)
			$cache = theme_cache::is_page(self::$page_slug) && self::get_tabs(get_query_var('tab'));
			
		return $cache;
	}
}
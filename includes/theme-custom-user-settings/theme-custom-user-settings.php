<?php
/**
 * @version 1.0.0
 * @author KM <kmvan.com@gmail.com>
 */
theme_custom_user_settings::init();
class theme_custom_user_settings{
	public static $iden = 'theme_custom_user_settings';
	public static $page_slug = 'settings';
	public static $cache_expire = 2505600; /** 29 days */

	public static function init(){
		add_action('init', 					get_class() . '::page_create');
		add_action('template_redirect',		get_class() . '::template_redirect');
		add_filter('query_vars',			get_class() . '::filter_query_vars');
		add_filter('wp_title',				get_class() . '::wp_title',10,2);
		
	}
	public static function wp_title($title, $sep){
		if(!is_page(self::$page_slug)) return $title;
		$tab_active = get_query_var('tab');
		$tabs = self::get_tabs();
		if(!empty($tab_active) && isset($tabs[$tab_active])){
			$title = $tabs[$tab_active]['text'];
		}else{
			$title = $tabs['history']['text'];
		}
		return $title . $sep . get_bloginfo('name');
	}
	public static function filter_query_vars($vars){
		if(!in_array('tab',$vars)) $vars[] = 'tab';
		if(!in_array('page',$vars)) $vars[] = 'page';
		return $vars;
	}
	public static function template_redirect(){
		if(is_page(self::$page_slug) && !is_user_logged_in()){
			$redirect = self::get_page_url();
			wp_redirect(theme_custom_sign::get_tabs('login',$redirect)['url']);
			die();
		}
	}
	public static function process(){
		
	}
	public static function get_page_url(){
		return get_permalink(get_page_by_path(self::$page_slug));
	}
	public static function get_tabs($key = null){
		$baseurl = self::get_page_url();
		$tabs = array(
			'history' => array(
				'text' => ___('History'),
				'icon' => 'history',
				'url' => add_query_arg('tab','history',$baseurl),
			),
			'settings' => array(
				'text' => ___('Settings'),
				'icon' => 'cog',
				'url' => add_query_arg('tab','settings',$baseurl),
			),
			'avatar' => array(
				'text' => ___('Avatar'),
				'icon' => 'image',
				'url' => add_query_arg('tab','avatar',$baseurl),
			),
			'password' => array(
				'text' => ___('Password'),
				'icon' => 'lock',
				'url' => add_query_arg('tab','password',$baseurl),
			),
		);
		if($key){
			return isset($tabs[$key]) ? $tabs[$key] : false;
		}
		return $tabs;
	}
	public static function page_create(){
		if(!current_user_can('manage_options')) return false;
		
		$page_slugs = array(
			self::$page_slug => array(
				'post_content' 	=> '[' . self::$page_slug . ']',
				'post_name'		=> self::$page_slug,
				'post_title'	=> ___('Settings'),
				'page_template'	=> 'page-' . self::$page_slug . '.php',
			)
		);
		
		$defaults = array(
			'post_content' 		=> '[post_content]',
			'post_name' 		=> null,
			'post_title' 		=> null,
			'post_status' 		=> 'publish',
			'post_type'			=> 'page',
			'comment_status'	=> 'closed',
		);
		foreach($page_slugs as $k => $v){
			$page = get_page_by_path($k);
			if(!$page){
				$r = wp_parse_args($v,$defaults);
				$page_id = wp_insert_post($r);
			}
		}
	}
}
?>
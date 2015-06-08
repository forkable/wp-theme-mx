<?php
/** 
 * @version 1.0.0
 */
add_filter('theme_includes',function($fns){
	$fns[] = 'theme_custom_edit::init';
	return $fns;
});
class theme_custom_edit{
	public static $iden = 'theme_custom_edit';
	public static $page_slug = 'account';
	public static $pages = [];

	public static function init(){
		//add_filter('frontend_seajs_alias',	__CLASS__ . '::frontend_seajs_alias');
	
		//add_action('frontend_seajs_use',	__CLASS__ . '::frontend_seajs_use');

		//add_filter('theme_options_save', 	__CLASS__ . '::options_save');
		//add_filter('theme_options_default', 	__CLASS__ . '::options_default');
		
		
		//add_action('wp_ajax_' . self::$iden, __CLASS__ . '::process');
		add_filter('get_edit_post_link' , __CLASS__ . '::filter_get_edit_post_link', 10 , 3);
		
		add_action('wp_enqueue_scripts', 	__CLASS__ . '::frontend_css');

		
		foreach(self::get_tabs() as $k => $v){
			$nav_fn = 'filter_nav_' . $k; 
			add_filter('account_navs',__CLASS__ . "::$nav_fn",$v['filter_priority']);
		}
		//add_filter('account_navs',__CLASS__ . "::filter_nav_edit", 22);
		
		add_filter('wp_title',				__CLASS__ . '::wp_title',10,2);

		//add_action('page_settings',			__CLASS__ . '::display_backend');
	}
	public static function wp_title($title, $sep){
		if(!self::is_page()) 
			return $title;
			
		if(self::get_tabs(get_query_var('tab'))){
			$title = self::get_tabs(get_query_var('tab'))['text'];
		}
		return $title . $sep . get_bloginfo('name');
	}
	public static function filter_query_vars($vars){
		if(!in_array('tab',$vars)) $vars[] = 'tab';
		return $vars;
	}
	public static function filter_nav_edit($navs){
		$navs['edit'] = '<a href="' . esc_url(self::get_tabs('edit')['url']) . '">
			<i class="fa fa-' . self::get_tabs('edit')['icon'] . ' fa-fw"></i> 
			' . self::get_tabs('edit')['text'] . '
		</a>';
		return $navs;
	}
	public static function get_url(){
		static $caches = [];
		if(isset($caches[self::$iden]))
			return $caches[self::$iden];
			
		$page = theme_cache::get_page_by_path(self::$page_slug);
		$caches[self::$iden] = esc_url(get_permalink($page->ID));
		return $caches[self::$iden];
	}
	public static function get_tabs($key = null){
		$baseurl = self::get_url();
		$tabs = [
			'edit' => [
				'text' => ___('My posts'),
				'icon' => 'lightbulb-o',
				'url' => esc_url(add_query_arg('tab','edit',$baseurl)),
				'filter_priority' => 22,
			],
		];
		if($key){
			return isset($tabs[$key]) ? $tabs[$key] : false;
		}
		return $tabs;
	}
	public static function filter_get_edit_post_link($url,$post_id,$context){
		//if(current_user_can('editor'))
			//return $url;

		if($context === 'display'){
			$action = '&amp;';
		}else{
			$action = '&';
		}
		$action .= 'post=' . $post_id;

		return theme_custom_contribution::get_tabs('post')['url'] . $action;
	}
	public static function get_query(){
		global $paged;
		return new WP_Query([
			'author' => get_current_user_id(),
			'posts_per_page' => 20,
			'paged' => (int)$paged,
		]);
		
	}
	public static function is_page(){
		static $cache = null;
		if($cache === null)
			$cache = is_page(self::$page_slug) && self::get_tabs(get_query_var('tab'));
			
		return $cache;
	}
	public static function frontend_css(){
		if(!self::is_page()) 
			return false;
			
		wp_enqueue_style(
			self::$iden,
			theme_features::get_theme_includes_css(__DIR__),
			'frontend',
			theme_file_timestamp::get_timestamp()
		);
	}
}
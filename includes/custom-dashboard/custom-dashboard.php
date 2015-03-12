<?php
/**
 * @version 1.0.0
 */
add_filter('theme_includes',function($fns){
	$fns[] = 'theme_custom_dashboard::init';
	return $fns;
});
class theme_custom_dashboard{
	
	public static $iden = 'theme_custom_dashboard';
	public static $page_slug = 'account';

	public static function init(){	
		
		add_filter('wp_title',				get_class() . '::wp_title',10,2);
		
		add_action('wp_enqueue_scripts', 	get_class() . '::frontend_css');

		foreach(self::get_tabs() as $k => $v){
			$nav_fn = 'filter_nav_' . $k; 
			add_filter('account_navs',get_class() . "::$nav_fn",$v['filter_priority']);
		}

		include __DIR__ . '/dashboards.php';
		add_filter('theme_includes',function($fns){
	$fns[] = 'theme_dashboards::init';
	return $fns;
});

	}
	public static function wp_title($title, $sep){
		if(!self::is_page()) return $title;
		$tab_active = get_query_var('tab');
		$tabs = self::get_tabs();
		if(!empty($tab_active) && isset($tabs[$tab_active])){
			$title = $tabs[$tab_active]['text'];
		}
		return $title . $sep . get_bloginfo('name');
	}
	public static function filter_nav_dashboard($navs){
		$navs['dashboard'] = '<a href="' . esc_url(self::get_tabs('dashboard')['url']) . '">
			<i class="fa fa-' . self::get_tabs('dashboard')['icon'] . '"></i> 
			' . esc_html(self::get_tabs('dashboard')['text']) . '
		</a>';
		return $navs;
	}

	public static function is_page(){
		if(is_page(self::$page_slug))
			return get_query_var('tab') === 'dashboard' || !get_query_var('tab');
			
		return false;
	}
	public static function get_url(){
		$page = theme_cache::get_page_by_path(self::$page_slug);
		return empty($page) ? null : get_permalink($page->ID);
	}
	public static function get_tabs($key = null){
		$baseurl = self::get_url();
		$tabs = array(
			'dashboard' => array(
				'text' => ___('My dashboard'),
				'icon' => 'dashboard',
				'url' => add_query_arg('tab','dashboard',$baseurl),
				'filter_priority' => 10,
			),
		);
		if($key){
			return isset($tabs[$key]) ? $tabs[$key] : false;
		}
		return $tabs;
	}
	public static function frontend_css(){
		if(!self::is_page()) 
			return;
		wp_enqueue_style(
			self::$iden,
			theme_features::get_theme_includes_css(__FILE__,'style',false),
			false,
			theme_features::get_theme_info('version')
		);
	}
}
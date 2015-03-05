<?php
/** 
 * @version 1.0.0
 */
theme_notification::init();
class theme_notification{
	public static $iden = 'theme_notification';
	public static $page_slug = 'account';
	public static $user_meta_key = array(
		'key' => 'theme_noti',
		'count' => 'theme_noti_count',
		'unread_count' => 'theme_noti_unread_count',
	);
	public static function init(){
		/** filter */
		
		add_filter('query_vars',			get_class() . '::filter_query_vars');
		
		//add_filter('frontend_seajs_alias',	get_class() . '::frontend_seajs_alias');

		
		/** action */

		//add_action('frontend_seajs_use',	get_class() . '::frontend_seajs_use');
		
		//add_action('wp_ajax_nopriv_theme_quick_sign', 'theme_quick_sign::process');
		
		add_filter('wp_title',				get_class() . '::wp_title',10,2);
		
		foreach(self::get_tabs() as $k => $v){
			$nav_fn = 'filter_nav_' . $k; 
			add_filter('account_navs',get_class() . "::$nav_fn",$v['filter_priority']);
		}
	}
	public static function wp_title($title, $sep){
		if(!is_page(self::$page_slug)) return $title;
		$tab_active = get_query_var('tab');
		$tabs = self::get_tabs();
		if(!empty($tab_active) && isset($tabs[$tab_active])){
			$title = $tabs[$tab_active]['text'];
		}
		return $title . $sep . get_bloginfo('name');
	}
	public static function filter_query_vars($vars){
		if(!in_array('page',$vars)) $vars[] = 'page';
		return $vars;
	}
	public static function get_tabs($key = null){
		$baseurl = self::get_url();
		$tabs = array(
			'notifications' => array(
				'text' => ___('Notifications'),
				'icon' => 'bell',
				'url' => add_query_arg('tab','notifications',$baseurl),
				'filter_priority' => 40,
			),
		);
		if($key){
			return isset($tabs[$key]) ? $tabs[$key] : false;
		}
		return $tabs;
	}
	public static function filter_nav_notifications($navs){
		$navs['notifications'] = '<a href="' . esc_url(self::get_tabs('notifications')['url']) . '">
			<i class="fa fa-' . self::get_tabs('notifications')['icon'] . '"></i> 
			' . esc_html(self::get_tabs('notifications')['text']) . '
		</a>';
		return $navs;
	}
	public static function get_url(){
		return get_permalink(theme_cache::get_page_by_path(self::$page_slug));
	}
	public static function is_page(){
		if(
			is_page(self::$page_slug) && 
			self::get_tabs(get_query_var('tab'))
		)
			return true;
		return false;
	}
	public static function template_redirect(){
		if(
			is_page(self::$page_slug) && 
			!is_user_logged_in() && 
			self::get_tabs(get_query_var('tab'))
		){
			wp_redirect(theme_custom_sign::get_tabs('login',self::get_url())['url']);
			die();
		}
	}
	public static function get_count($args){
		$defaults = array(
			'user_id' => null,
			'type' => 'all',
		);
		$args = wp_parse_args($args,$defaults);
		if(empty($args['user_id'])) return false;

		switch($args['type']){
			case 'unread':
				$metas = array_filter((array)get_user_meta($args['user_id'],self::$user_meta_key['unread_count'],true));
				return empty($metas) ? 0 : count($metas);
			default:
				$metas = array_filter((array)get_user_meta($args['user_id'],self::$user_meta_key['key']));
				return empty($metas) ? 0 : count($metas);
		}
	}
	public static function get_notifications($args){
		$defaults = array(
			'user_id' => null,
			'type' => 'all',/** all / unread / read */
			'posts_per_page' => null,
			'page' => 1,
			'orderby' => 'desc',
		);
		$args = wp_parse_args($args,$defaults);
		if(empty($args['user_id'])) return false;
		
		$metas = (array)get_user_meta($args['user_id'],self::$user_meta_key['key']);
		if(empty($metas)){
			return null;
		}else{
			krsort($metas);
		}
		
		switch($args['type']){
			case 'unread':
				$unread = (array)get_user_meta($args['user_id'],self::$user_meta_key['unread_count'],true);
				if(empty($unread)){
					return $metas;
				}else{
					$metas = array_map(function($meta){
						if(!in_array($meta['id'],$unread)) return $meta;
						$meta['unread'] = true;
						return $meta;
					},$metas);
				}
			default:
				return $metas;
		}
	}

	public static function process(){
		$output = array();
		
		theme_features::check_referer();
		theme_features::check_nonce();
		
		$type = isset($_REQUEST['type']) ? $_REQUEST['type'] : null;
		

		die(theme_features::json_format($output));
	}
	public static function frontend_seajs_alias($alias){
		if(!self::is_page()) return $alias;

		$alias[self::$iden] = theme_features::get_theme_includes_js(__FILE__);
		return $alias;
	}
	public static function frontend_seajs_use(){
		if(!self::is_page()) return false;
		?>
		seajs.use('<?php echo self::$iden;?>',function(m){
			m.config.process_url = '<?php echo theme_features::get_process_url(array('action' => theme_quick_sign::$iden));?>';
			m.config.lang.M00001 = '<?php echo esc_js(___('Loading, please wait...'));?>';
			m.config.lang.E00001 = '<?php echo esc_js(___('Sorry, server error please try again later.'));?>';
			
			m.init();
		});
		<?php
	}

}
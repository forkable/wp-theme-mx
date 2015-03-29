<?php
/** 
 * sign
 */
add_filter('theme_includes',function($fns){
	$fns[] = 'theme_custom_sign::init';
	return $fns;
});
class theme_custom_sign{
	public static $iden = 'theme_custom_sign';
	public static $page_slug = 'sign';
	public static $pages = [];
	public static function init(){
		/** filter */
		add_filter('login_headerurl',		__CLASS__ . '::filter_login_headerurl',1);
		add_filter('query_vars',			__CLASS__ . '::filter_query_vars');
		add_filter('frontend_seajs_alias',	__CLASS__ . '::frontend_seajs_alias');
		/** action */
		add_action('admin_init',			__CLASS__ . '::action_not_allow_login_backend',1);
		add_action('init', 					__CLASS__ . '::page_create');
		add_action('template_redirect',		__CLASS__ . '::template_redirect');
		add_action('frontend_seajs_use',	__CLASS__ . '::frontend_seajs_use');
		//add_action('wp_ajax_nopriv_theme_quick_sign', 'theme_quick_sign::process');
		add_filter('show_admin_bar', 		__CLASS__ . '::action_show_admin_bar');
		add_filter('login_url', 			__CLASS__ . '::filter_wp_login_url',10,2);
		add_filter('register_url', 			__CLASS__ . '::filter_wp_registration_url');
		add_filter('wp_title',				__CLASS__ . '::wp_title',10,2);
	}
	
	public static function filter_login_headerurl($login_header_url){
		// if(current_user_can('moderate_comments')) return $login_header_url;
		wp_safe_redirect(get_permalink(theme_cache::get_page_by_path(self::$page_slug)));
		die();
	}
	public static function action_show_admin_bar(){
		if(!current_user_can('manage_options')) return false;
		return true;
	}
	public static function action_not_allow_login_backend(){
		/** 
		 * if in backend
		 */
		if(!defined('DOING_AJAX')||!DOING_AJAX){
			/** 
			 * if not administrator and not ajax,redirect to 
			 */
			if(!current_user_can('moderate_comments')){
				wp_safe_redirect(theme_cache::get_author_posts_url(get_current_user_id()));
				die();
			}
		}
	}
	public static function filter_query_vars($vars){
		if(!in_array('tab',$vars)) $vars[] = 'tab';
		if(!in_array('redirect',$vars)) $vars[] = 'redirect';
		if(!in_array('step',$vars)) $vars[] = 'step';
		return $vars;
	}
	public static function get_options($key = null){
		static $caches;
		if(!$caches)
			$caches = theme_options::get_options(self::$iden);
		if($key){
			return isset($caches[$key]) ? $caches[$key] : null;
		}
		return $caches;
	}
	public static function filter_wp_registration_url(){
		return self::get_tabs('register',get_current_url())['url'];
	}
	public static function filter_wp_login_url($redirect,$force_reauth){
		return self::get_tabs('login',get_current_url())['url'];
	}
	public static function get_tabs($key = null,$redirect = null){
		static $baseurl;
		if(!$baseurl)
			$baseurl = get_permalink(theme_cache::get_page_by_path(self::$page_slug));
		
		$redirect = $redirect ? $redirect : get_query_var('redirect');
		if($redirect){
			$baseurl = add_query_arg(array(
				'redirect' => $redirect
			),$baseurl);
		}
		$tabs = array(
			'login' => array(
				'text' => ___('Login'),
				'icon' => 'user',
				'url' => add_query_arg(array(
					'tab' => 'login'
				),$baseurl),
			),
			'register' => array(
				'text' => ___('Register'),
				'icon' => 'user-plus',
				'url' => add_query_arg(array(
					'tab' => 'register'
				),$baseurl),
			),
			'recover' => array(
				'text' => ___('Recover password'),
				'icon' => 'question-circle',
				'url' => add_query_arg(array(
					'tab' => 'recover'
				),$baseurl),
			),
		);
		if($key){
			return isset($tabs[$key]) ? $tabs[$key] : false;
		}else{
			return $tabs;
		}
	}
	public static function is_page(){
		static $caches;
		if(isset($caches[self::$iden]))
			return $caches[self::$iden];
			
		$caches[self::$iden] = is_page(self::$page_slug);
		return $caches[self::$iden];
	}
	public static function template_redirect(){
		if(self::is_page() && is_user_logged_in()){
			$redirect = get_query_var('redirect');
			$redirect ? wp_redirect($redirect) : wp_redirect(home_url());
			die();
		}
	}
	public static function page_create(){
		if(!current_user_can('manage_options')) return false;
		
		$page_slugs = array(
			self::$page_slug => array(
				'post_content' 	=> '[' . self::$page_slug . ']',
				'post_name'		=> self::$page_slug,
				'post_title'	=> ___('Sign'),
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
			$page = theme_cache::get_page_by_path($k);
			if(!$page){
				$r = wp_parse_args($v,$defaults);
				$page_id = wp_insert_post($r);
				// $page = get_post($page_id);
			}
			// self::$pages[$k] = $page;
		}
	}
	public static function wp_title($title, $sep){
		if(is_user_logged_in() || !self::is_page()) 
			return $title;
			
		$tab_active = get_query_var('tab');
		$tabs = self::get_tabs();
		if(!empty($tab_active) && isset($tabs[$tab_active])){
			$title = $tabs[$tab_active]['text'];
		}else{
			$title = $tabs['login']['text'];
		}
		return $title . $sep . get_bloginfo('name');
	}
	public static function process(){
		$output = [];
		
		theme_features::check_referer();
		theme_features::check_nonce();
		
		$type = isset($_REQUEST['type']) ? $_REQUEST['type'] : null;
		

		die(theme_features::json_format($output));
	}
	public static function frontend_seajs_alias($alias){
		if(is_user_logged_in() || !self::is_page())
			return $alias;

		$alias[self::$iden] = theme_features::get_theme_includes_js(__DIR__);
		return $alias;
	}
	public static function frontend_seajs_use(){
		if(is_user_logged_in() || !self::is_page()) 
			return false;
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
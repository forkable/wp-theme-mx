<?php
/** 
 * sign
 */
theme_custom_sign::init();
class theme_custom_sign{
	public static $iden = 'theme_custom_sign';
	public static $page_slug = 'sign';
	public static $pages = array();
	public static function init(){
		/** filter */
		add_filter('login_headerurl',		get_class() . '::filter_login_headerurl',1);
		add_filter('query_vars',			get_class() . '::filter_query_vars');
		add_filter('frontend_seajs_alias',	get_class() . '::frontend_seajs_alias');
		/** action */
		add_action('admin_init',			get_class() . '::action_not_allow_login_backend',1);
		add_action('init', 					get_class() . '::page_create');
		add_action('template_redirect',		get_class() . '::template_redirect');
		add_action('frontend_seajs_use',	get_class() . '::frontend_seajs_use');
		//add_action('wp_ajax_nopriv_theme_quick_sign', 'theme_quick_sign::process');
		add_filter('show_admin_bar', 		get_class() . '::action_show_admin_bar');
		add_filter('login_url', 			get_class() . '::filter_wp_login_url',10,2);
		add_filter('register_url', 			get_class() . '::filter_wp_registration_url');
		add_filter('wp_title',				get_class() . '::wp_title',10,2);
	}
	
	public static function filter_login_headerurl($login_header_url){
		// if(current_user_can('moderate_comments')) return $login_header_url;
		wp_safe_redirect(get_permalink(get_page_by_path(self::$page_slug)));
		die();
	}
	public static function action_show_admin_bar(){
		if(!current_user_can('manage_options')) return false;
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
				global $current_user;
				get_currentuserinfo();
				wp_safe_redirect(get_author_posts_url($current_user->ID));
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
	public static function filter_wp_registration_url(){
		return self::get_tabs('register')['url'];
	}
	public static function filter_wp_login_url($redirect,$force_reauth){
		return self::get_tabs('login',get_current_url())['url'];
	}
	public static function get_tabs($key = null,$redirect = null){
		$baseurl = get_permalink(get_page_by_path(self::$page_slug));
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
				'icon' => 'key',
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
	public static function template_redirect(){
		if(is_page(self::$page_slug) && is_user_logged_in()){
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
			$page = get_page_by_path($k);
			if(!$page){
				$r = wp_parse_args($v,$defaults);
				$page_id = wp_insert_post($r);
				// $page = get_post($page_id);
			}
			// self::$pages[$k] = $page;
		}
	}
	public static function wp_title($title, $sep){
		if(is_user_logged_in() || !is_page(self::$page_slug)) return $title;
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
		$output = array();
		
		theme_features::check_referer();
		theme_features::check_nonce();
		
		$type = isset($_REQUEST['type']) ? $_REQUEST['type'] : null;
		

		die(theme_features::json_format($output));
	}
	public static function frontend_seajs_alias($alias){
		if(is_user_logged_in() || !is_page(self::$page_slug)) return $alias;

		$alias[self::$iden] = theme_features::get_theme_includes_js(__FILE__);
		return $alias;
	}
	public static function frontend_seajs_use(){
		if(is_user_logged_in() || !is_page(self::$page_slug)) return false;
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
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


		add_action('wp_enqueue_scripts', 	get_class() . '::frontend_css');
		
		add_filter('frontend_seajs_alias',	get_class() . '::frontend_seajs_alias');

		add_action('frontend_seajs_use',	get_class() . '::frontend_seajs_use');
		
		add_action('wp_ajax_' . self::$iden, get_class() . '::process');
		
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
		$output = [];

		theme_features::check_referer();
		theme_features::check_nonce();
		
		$type = isset($_REQUEST['type']) ? $_REQUEST['type'] : null;
		switch($type){
			/**
			 * settings
			 */
			case 'settings':
				$user = isset($_POST['user']) ? $_POST['user'] : null;
				if(empty($_POST['user']) || !is_array($_POST['user'])){
					$output['status'] = 'error';
					$output['code'] = 'invaild_param';
					$output['msg'] = ___('Invaild param.');
					die(theme_features::json_format($output));
				}

				$nickname = isset($user['nickname']) ? trim($user['nickname']) : null;
				if(empty($nickname)){
					$output['status'] = 'error';
					$output['code'] = 'invaild_nickname';
					$output['msg'] = ___('Invaild nickname.');
					die(theme_features::json_format($output));
				}

				$url = isset($user['url']) ? $user['url'] : null;
				$des = isset($user['description']) ? $user['ddescriptiones'] : null;
				
				$user_id = wp_update_user(array(
					'ID' => get_current_user_id(),
					'user_url' => $url,
					'nickname' => $nickname,
					'description' => $des,
				));

				if(is_wp_error($user_id)){
					$output['status'] = 'error';
					$output['code'] = $user_id->get_error_code();
					$output['msg'] = $user_id->get_error_message();
					die(theme_features::json_format($output));
				}else{
					$output['status'] = 'success';
					$output['msg'] = ___('Your settings have been saved.');
					die(theme_features::json_format($output));
				}
				break;
			/**
			 * pwd
			 */
			case 'pwd':
				/**
				 * twice pwd
				 */
				$new_pwd_1 = isset($_POST['new-pwd-1']) ? trim($_POST['new-pwd-1']) : null;
				$new_pwd_2 = isset($_POST['new-pwd-2']) ? trim($_POST['new-pwd-2']) : null;
				if(empty($new_pwd_1) 
					|| empty($new_pwd_2)
					|| ($old_pwd_1 !== $new_pwd_2)){
					$output['status'] = 'error';
					$output['code'] = 'invaild_pwd_twice';
					$output['msg'] = ___('Password invaild twice.');
					die(theme_features::json_format($output));
				}
				/**
				 * old pwd
				 */
				$old_pwd = isset($_POST['old-pwd']) ? trim($_POST['old-pwd']) : null;
				global $current_user;
				get_currentuserinfo();
				if(empty($old_pwd) || 
					wp_check_password($old_pwd,$current_user->user_pass,get_current_user_id())){
					$output['status'] = 'error';
					$output['code'] = 'invaild_old_pwd';
					$output['msg'] = ___('Invaild current password.');
					die(theme_features::json_format($output));
				}

				/**
				 * change password
				 */
				$current_id = get_current_user_id();
				wp_update_user(array(
					'ID' => $current_id,
					'user_pass' => $new_pwd_1,
				));
				/**
				 * set current, relogin
				 */
				wp_set_current_user($current_id);
				wp_set_auth_cookie($current_id);
				
				$output['status'] = 'success';
				$output['msg'] = ___('Your new password has been saved. Re-logging in, please wait...');
				$output['redirect'] = home_url();
				die(theme_features::json_format($output));
				break;
		}
		die(theme_features::json_format($output));
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
	public static function frontend_seajs_alias($alias){
		if(!is_user_logged_in() || !is_page(self::$page_slug)) return $alias;
		foreach(self::get_tabs() as $k => $v){
			$alias[self::$iden . '-' . $k] = theme_features::get_theme_includes_js(__FILE__,$k);
			if($k === 'avatar'){
				$alias[self::$iden . '-' . $k . '-cropper'] = theme_features::get_theme_includes_js(__FILE__,'cropper');
			}
		}
		return $alias;
	}
	public static function frontend_seajs_use(){
		if(!is_user_logged_in() || !is_page(self::$page_slug)) return false;
		
		$tabs = self::get_tabs();
		$tab_active = get_query_var('tab');
		$tab_active = isset($tabs[$tab_active]) ? $tab_active : 'history';

		switch($tab_active){
			case 'password':
			case 'settings':
				?>
				seajs.use('<?php echo self::$iden,'-settings';?>',function(m){
					m.config.process_url = '<?php echo theme_features::get_process_url(array('action' => self::$iden));?>';
					m.config.lang.M00001 = '<?php echo esc_js(___('Loading, please wait...'));?>';
					m.config.lang.E00001 = '<?php echo esc_js(___('Sorry, server error please try again later.'));?>';
					
					m.init();
				});				
				<?php
				break;
		}
	}
	public static function frontend_css(){
		if(!is_user_logged_in() || !is_page(self::$page_slug)) return false;
		$tabs = self::get_tabs();
		$tab_active = get_query_var('tab');
		$tab_active = isset($tabs[$tab_active]) ? $tab_active : 'history';
		switch($tab_active){
			case 'avatar':
				wp_enqueue_style(
					self::$iden,theme_features::get_theme_includes_css(__FILE__,$tab_active,false),
					false,
					theme_features::get_theme_info('version')
				);
				break;
		}
	}
}
?>
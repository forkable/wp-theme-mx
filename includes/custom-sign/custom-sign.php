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
		add_filter('show_admin_bar', 		__CLASS__ . '::filter_show_admin_bar');
		add_filter('login_url', 			__CLASS__ . '::filter_wp_login_url',10,2);
		add_filter('register_url', 			__CLASS__ . '::filter_wp_registration_url');
		add_filter('wp_title',				__CLASS__ . '::wp_title',10,2);

		/**
		 * backend
		 */
		add_action('page_settings' , __CLASS__ . '::display_backend');
		add_filter('theme_options_save' , __CLASS__ . '::options_save');
	}
	public static function options_save(array $options = []){
		if(isset($_POST[self::$iden])){
			$options[self::$iden] = $_POST[self::$iden];
		}
		return $options;
	}
	public static function options_default(array $opts = []){
		$opts[self::$iden] = [
			'avatar-url' => 'http://ww3.sinaimg.cn/thumb150/686ee05djw1eriqgtewe7j202o02o3y9.jpg'
		];
		return $opts;
	}
	public static function get_options($key = null){
		static $caches = [];
		if(!isset($caches[self::$iden]))
			$caches[self::$iden] = theme_options::get_options(self::$iden);

		if($key)
			return isset($caches[self::$iden][$key]) ? $caches[self::$iden][$key] : null;
		return $caches[self::$iden];
	}
	public static function display_backend(){
		?>
		<fieldset id="<?php echo self::$iden;?>">
			<legend><?php echo ___('Custom sign settings');?></legend>
			<p class="description"><?php echo ___('You can custom the sign page here.');?></p>
			<table class="form-table">
				<tbody>
					<tr>
						<th>
							<?php echo ___('Sign-in avatar URL');?><br>
							<?php if(!empty(self::get_options('avatar-url'))){ ?>
								<img src="<?php echo esc_url(self::get_options('avatar-url'));?>" alt="avatar">
							<?php } ?>
						</th>
						<td>
							<input type="url" name="<?php echo self::$iden;?>[avatar-url]" id="<?php echo self::$iden;?>-avatar-url" class="widefat code" value="<?php echo esc_url(self::get_options('avatar-url'));?>">
							<p class="description"><?php echo ___('Recommend 100x100 px image.');?></p>
						</td>
					</tr>
					<tr>
						<th><?php echo ___('Terms of service page URL');?></th>
						<td><input type="url" name="<?php echo self::$iden;?>[tos-url]" id="<?php echo self::$iden;?>-tos-url" class="widefat code" value="<?php echo esc_url(self::get_options('tos-url'));?>"></td>
					</tr>
				</tbody>
			</table>
		</fieldset>
		<?php
	}
	public static function filter_login_headerurl($login_header_url){
		// if(current_user_can('moderate_comments')) return $login_header_url;
		wp_safe_redirect(get_permalink(theme_cache::get_page_by_path(self::$page_slug)));
		die();
	}
	public static function filter_show_admin_bar($show_admin_bar){
		if(current_user_can('manage_options'))
			return $show_admin_bar;
		return false;
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

		if(!$redirect)
			$redirect =  get_query_var('redirect');
			
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
		static $caches = [];
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
				$r = array_merge($defaults,$v);
				$page_id = wp_insert_post($r);
			}
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
			m.config.process_url = '<?php echo theme_features::get_process_url([
				'action' => theme_quick_sign::$iden
			]);?>';
			m.config.lang.M00001 = '<?php echo ___('Loading, please wait...');?>';
			m.config.lang.E00001 = '<?php echo ___('Sorry, server error please try again later.');?>';
			
			m.init();
		});
		<?php
	}

}
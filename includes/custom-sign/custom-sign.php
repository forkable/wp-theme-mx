<?php
/** 
 * sign
 * @version 1.0.1
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
		
		/** ajax */
		add_action('wp_ajax_nopriv_' . self::$iden, __CLASS__ . '::process');
		
		add_filter('show_admin_bar', 		__CLASS__ . '::filter_show_admin_bar');
		add_filter('login_url', 			__CLASS__ . '::filter_wp_login_url',10,2);
		add_filter('register_url', 			__CLASS__ . '::filter_wp_registration_url');
		add_filter('wp_title',				__CLASS__ . '::wp_title',10,2);

		add_action('wp_enqueue_scripts', 	__CLASS__ . '::frontend_css');

		add_filter('cache_request',					__CLASS__ . '::cache_request');
		
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
		<fieldset id="<?= self::$iden;?>">
			<legend><?= ___('Custom sign settings');?></legend>
			<p class="description"><?= ___('You can custom the sign page here.');?></p>
			<table class="form-table">
				<tbody>
					<tr>
						<th>
							<?= ___('Sign-in avatar URL');?><br>
							<?php if(!empty(self::get_options('avatar-url'))){ ?>
								<img src="<?= esc_url(self::get_options('avatar-url'));?>" alt="avatar">
							<?php } ?>
						</th>
						<td>
							<input type="url" name="<?= self::$iden;?>[avatar-url]" id="<?= self::$iden;?>-avatar-url" class="widefat code" value="<?= esc_url(self::get_options('avatar-url'));?>">
							<p class="description"><?= ___('Recommend 100x100 px image.');?></p>
						</td>
					</tr>
					<tr>
						<th><?= ___('Terms of service page URL');?></th>
						<td><input type="url" name="<?= self::$iden;?>[tos-url]" id="<?= self::$iden;?>-tos-url" class="widefat code" value="<?= self::get_tos_url();?>"></td>
					</tr>
				</tbody>
			</table>
		</fieldset>
		<?php
	}
	public static function get_tos_url(){
		static $cache = null;
		if($cache === null)
			$cache = esc_url(self::get_options('tos-url'));
		return $cache;
	}
	public static function filter_login_headerurl($login_header_url){
		// if(current_user_can('moderate_comments')) return $login_header_url;
		wp_safe_redirect(get_permalink(theme_cache::get_page_by_path(self::$page_slug)));
		die();
	}
	public static function filter_show_admin_bar($show_admin_bar){
		if(current_user_can('manage_categories'))
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
	public static function filter_wp_login_url($login_url,$redirect){
		if(empty($redirect))
			$redirect = get_current_url();
		return self::get_tabs('login',$redirect)['url'];
	}
	/**
	 * Get sign page tabs
	 *
	 * @param string $key Page tab type
	 * @param string $redirect Redirect url when login success
	 * @return mix/array
	 * @version 1.0.0
	 */
	public static function get_tabs($key = null,$redirect = null){
		static $baseurl = null;
		if($baseurl === null)
			$baseurl = get_permalink(theme_cache::get_page_by_path(self::$page_slug));
		
		if(!$redirect)
			$redirect =  get_query_var('redirect');
			
		if($redirect){
			$baseurl = add_query_arg([
				'redirect' => $redirect
			],$baseurl);
		}
		$tabs = [
			'login' => [
				'text' => ___('Login'),
				'icon' => 'user',
				'url' => add_query_arg([
					'tab' => 'login'
				],$baseurl),
			],
			'register' => [
				'text' => ___('Register'),
				'icon' => 'user-plus',
				'url' => add_query_arg([
					'tab' => 'register'
				],$baseurl),
			],
			'recover' => [
				'text' => ___('Recover password'),
				'icon' => 'question-circle',
				'url' => add_query_arg([
					'tab' => 'recover'
				],$baseurl),
			],
			'reset' => [
				'text' => ___('Reset password'),
				'icon' => 'history',
				'url' => add_query_arg([
					'tab' => 'reset'
				],$baseurl),
			],
		];
		if($key)
			return isset($tabs[$key]) ? $tabs[$key] : false;
		return $tabs;
	}
	public static function is_page(){
		static $cache = null;
		if($cache === null)
			$cache = is_page(self::$page_slug);
			
		return $cache;
	}
	public static function template_redirect(){
		if(self::is_page() && self::is_user_logged_in()){
			$redirect = get_query_var('redirect');
			$redirect ? wp_redirect($redirect) : wp_redirect(home_url());
			die();
		}
	}
	public static function page_create(){
		if(!current_user_can('manage_options')) 
			return false;
		
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
			theme_cache::get_page_by_path($k) || wp_insert_post(array_merge($defaults,$v));
		}
	}
	/** 
	 * set_html_content_type
	 */
	public static function set_html_content_type(){
		return 'text/html';
	}
	public static function wp_title($title, $sep){
		if(self::is_user_logged_in() || !self::is_page()) 
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
	/** 
	 * user_login
	 */
	public static function user_login($args){
		$defaults = array(
			'email' => null,
			'pwd' => null,
			'remember' => false,
		);
		$args = array_merge($defaults,$args);

		if(!$args['pwd']){
			$output['status'] = 'error';
			$output['code'] = 'invalid_pwd';
			$output['msg'] = ___('Sorry, password is invalid, please try again.');
		}else if(!is_email($args['email'])){
			$output['status'] = 'error';
			$output['code'] = 'invalid_email';
			$output['msg'] = ___('Sorry, email is invalid, please try again.');
		}else{
			$creds = [];
			$creds['user_login'] = $args['email'];
			$creds['user_password'] = $args['pwd'];
			$creds['remember'] = $args['remember'];
			$user = wp_signon( $creds );
			if(is_wp_error($user)){
				$output['status'] = 'error';
				$output['code'] = 'email_pwd_not_match';
				$output['msg'] = ___('Sorry, your email and password do not match, please try again.');
			}else{
				$output['status'] = 'success';
			}
		}
		return $output;
	}
	/** 
	 * user_register
	 */
	public static function user_register($args){
		$defaults = array(
			'email' => null,
			'pwd' => null,
			'nickname' => null,
			'remember' => true,
		);
		$args = array_merge($defaults,$args);
		
		if(!$args['pwd']){
			$output['status'] = 'error';
			$output['msg'] = ___('Sorry, password is invalid, please try again.');
			$output['code'] = 'invalid_pwd';
		}else if(!is_email($args['email'])){
			$output['status'] = 'error';
			$output['msg'] = ___('Sorry, email is invalid, please try again.');
			$output['code'] = 'invalid_email';
		}else if(!trim($args['nickname']) || !validate_username($args['nickname'])){
			$output['status'] = 'error';
			$output['code'] = 'invalid_nickname';
			$output['msg'] = ___('Sorry, nickname is invalid, please try again.');
		/** 
		 * check email exists
		 */
		}else if(email_exists($args['email'])){
			$output['status'] = 'error';
			$output['code'] = 'email_exists';
			$output['msg'] = ___('Sorry, email already exists, please change another one and try again.');
		}else{
			/** 
			 * create user and get user id
			 */
			$user_data = array(
				'user_login' => $args['nickname'],
				'user_pass' =>$args['pwd'],
				'nickname' => $args['nickname'],
				'display_name' => $args['nickname'],
				'user_email' => $args['email'],
			);
			
			$user_id = wp_insert_user($user_data);
			if(is_wp_error($user_id)){
				$output['status'] = 'error';
				$output['code'] = $user_id->get_error_code();
				$output['msg'] = $user_id->get_error_message();
				
			}else{
				/** 
				 * go to login
				 */
				$output = self::user_login(array(
					'email' => $args['email'],
					'pwd' => $args['pwd'],
					'remember' => $args['remember']
				));
			}
		}
		return $output;
	}
	public static function process(){
		theme_features::check_nonce();
		theme_features::check_referer();
		$output = [];
		
		$type = isset($_REQUEST['type']) && is_string($_REQUEST['type']) ? $_REQUEST['type'] : null;
		
		$user = isset($_POST['user']) && is_array($_POST['user']) ? $_POST['user'] : false;
		
		$email = (isset($user['email']) && is_email($user['email'])) ? $user['email'] : null;
		
		$pwd = isset($user['pwd']) && is_string($user['pwd']) ? $user['pwd'] : null;
		
		switch($type){
			/** 
			 * login
			 */
			case 'login':
				$output = self::user_login(array(
					'email' => $email,
					'pwd' => $pwd,
					'remember' => isset($user['remember']) ? true : false,
				));
				if($output['status'] === 'success'){
					$output['msg'] = ___('Login successfully, page is refreshing, please wait...');
				}else{
					die(theme_features::json_format($output));
				}
				break;
			/** 
			 * register
			 */
			case 'register':
				$name_at_least_len = 2;
				$pwd_at_least_len = 3;
				/**
				 * nickname
				 */
				if(!isset($user['nickname']) || !is_string($user['nickname']) || mb_strlen($user['nickname']) < $name_at_least_len){
					$output['status'] = 'error';
					$output['code'] = 'invalid_nickname';
					$output['msg'] = sprintf(___('Sorry, you nick name is invalid, at least %d characters in length, please try again.'),$name_at_least_len);
					die(theme_features::json_format($output));
				}
				/**
				 * pwd
				 */
				if(mb_strlen($pwd) < $pwd_at_least_len){
					$output['status'] = 'error';
					$output['code'] = 'invalid_pwd';
					$output['msg'] = sprintf(___('Sorry, you password is invalid, at least %d characters in length, please try again.'),$pwd_at_least_len);
					die(theme_features::json_format($output));
				}
				/**
				 * email 
				 */
				if(!$email){
					$output['status'] = 'error';
					$output['code'] = 'invalid_email';
					$output['msg'] = ___('Sorry, your email address is invalid, please check it and try again.');
					die(theme_features::json_format($output));
				}
				$output = self::user_register(array(
					'email' => $email,
					'pwd' => $pwd,
					'nickname' => $user['nickname'],
					'remember' => true,
				));
				if($output['status'] === 'success'){
					// $output['redirect'] = 
					$output['msg'] = ___('Register successfully, page is refreshing, please wait...');
				}

				break;
			/** 
			 * lost-password
			 */
			case 'recover':
				if(!$email){
					$output['status'] = 'error';
					$output['code'] = 'invalid_email';
					$output['msg'] = ___('Sorry, your email address is invalid, please check it and try again.');
					die(theme_features::json_format($output));
				}
				/** 
				 * check the email is exist
				 */
				$user_id = email_exists($email);
				if(!$user_id){
					$output['status'] = 'error';
					$output['code'] = 'email_not_exist';
					$output['msg'] = ___('Sorry, the email does not exist.');
					die(theme_features::json_format($output));
				}
				/** 
				 * create and encode code
				 */
				$user = get_userdata($user_id);
				$encode_arr = array(
					'user_id' => $user_id,
					'user_email' => $user->user_email,
				);
				$encode_str = json_encode($encode_arr);
				$encode = authcode($encode_str,'encode',AUTH_KEY,7200);
				$callback_url = esc_url(add_query_arg([
					'token' => $encode,
				],self::get_tabs('reset')['ur']));

				$content = '
					<h3>' . sprintf(___('Dear %s!'),esc_html($user->display_name)) . '</h3>
					<p>
						' . sprintf(___('You are receiving this email because you forgot your password. We already made an address for your account, you can access this address ( %s ) to log-in and change your password in 3 hours.'),'<a href="' . $callback_url . '" target="_blank">' . $callback_url . '</a>') . '
					</p>
					<p>' . sprintf(___('-- From %s'),'<a href="' . home_url() . '" target="_blank">' . get_bloginfo('name') . '</a>') . '</p>
				';
				$title = ___('You are applying to reset your password.');
				

				add_filter( 'wp_mail_content_type',__CLASS__ . '::set_html_content_type');
				
				$wp_mail = wp_mail(
					$user->user_email,
					$title,
					$content
				);
				remove_filter( 'wp_mail_content_type',__CLASS__ . '::set_html_content_type');
				/** 
				 * check wp_mail is success or not
				 */
				if($wp_mail === true){
					update_user_meta($user_id,'_tmp_lost_pwd',1);
					$output['status'] = 'success';
					$output['msg'] = ___('Success, we sent an email that includes how to retrieve your password, please check it out in 3 hours.');
				}else{
					$output['status'] = 'error';
					$output['code'] = 'server_error';
					$output['detial'] = $wp_mail['msg'];
					$output['msg'] = ___('Error, server can not send email, please contact the administrator.');
				}
				break;
			/** 
			 * reset
			 */
			case 'reset':
				if(!$user){
					$output['status'] = 'error';
					$output['code'] = 'invalid_param';
					$output['msg'] = ___('Sorry, the param is invalid.');
					die(theme_features::json_format($output));
				}
				/** email */
				if(!$email){
					$output['status'] = 'error';
					$output['code'] = 'invalid_email';
					$output['msg'] = ___('Sorry, your email is invalid.');
					die(theme_features::json_format($output));
				}
				/** pwd */
				$pwd_new = isset($user['pwd-new']) && is_string($user['pwd-new']) ? $user['pwd-new'] : null;
				$pwd_again = isset($user['pwd-again']) && is_string($user['pwd-again']) ?  $user['pwd-again'] : null;
				if(empty($pwd_new) || $pwd_new !== $pwd_again){
					$output['status'] = 'error';
					$output['code'] = 'invalid_twice_pwd';
					$output['msg'] = ___('Sorry, twice password is invaild, please try again.');
					die(theme_features::json_format($output));
				}
				/** token */
				$token = isset($user['token']) && is_string($user['token']) ? $user['token'] : null;
				if(empty($token)){
					$output['status'] = 'error';
					$output['code'] = 'empty_token';
					$output['msg'] = ___('Sorry, token is invaild.');
					die(theme_features::json_format($output));
				}
				/** decode token */
				$token_decode = authcode($token,'decode',AUTH_KEY);
				if(!$token_decode){
					$output['status'] = 'error';
					$output['code'] = 'expired_token';
					$output['msg'] = ___('Sorry, the token is expired.');
					die(theme_features::json_format($output));
				}
				/** unserialize token */
				$token_arr = json_decode($token_decode,true);
				if(!$token_arr || !is_array($token_arr)){
					$output['status'] = 'error';
					$output['code'] = 'invalid_token';
					$output['msg'] = ___('This token is expired.');
					die(theme_features::json_format($output));
				}
				$token_user_id = isset($token_arr['user_id']) && is_numeric($token_arr['user_id']) ? $token_arr['user_id'] : null;
				$token_user_email = isset($token_arr['user_email']) ? $token_arr['user_email'] : null;
				/** check token email is match post email */
				if($token_user_email != $email){
					$output['status'] = 'error';
					$output['code'] = 'token_email_not_match';
					$output['msg'] = ___('Sorry, the token email and you account email do not match.');
					die(theme_features::json_format($output));
				}
				
				/** check post email exists */
				$user_id = (int)email_exists($email);
				if(!$user_id){
					$output['status'] = 'error';
					$output['code'] = 'email_not_exist';
					$output['msg'] = ___('Sorry, your account email is not exist.');
				}
				/** check user already apply to recover password */
				if(!get_user_meta($user_id,'_tmp_recover_pwd',true)){
					$output['status'] = 'error';
					$output['code'] = 'not_apply_recover';
					$output['msg'] = ___('Sorry, the user do not apply recover yet.');
				}
				/** all ok, just set new password */
				delete_user_meta($user_id,'_tmp_recover_pwd');
				wp_set_password($post_pwd_new,$user_id);
				wp_set_current_user($user_id);
				wp_set_auth_cookie($user_id,true);
				$output['status'] = 'success';
				$output['redirect'] = home_url();
				$output['msg'] = ___('Congratulation, your account has been recovered! Password has been updated. Redirecting home page, please wait...');
				
				break;
			default:
				$output['status'] = 'error';
				$output['code'] = 'invalid_type';
				$output['msg'] = ___('Invalid type.');
		}
		
		die(theme_features::json_format($output));
	}
	private static function is_user_logged_in(){
		static $cache = null;
		if($cache === null)
			$cache = is_user_logged_in();
		return $cache;
	}
	public static function cache_request($datas){
		if(self::is_user_logged_in()){
			global $current_user;
			get_currentuserinfo();
			
			$datas['user'] = array(
				'logged'  		=> true,
				'display_name' 	=> $current_user->display_name,
				'posts_url' 	=> theme_cache::get_author_posts_url($current_user->ID),
				'logout_url' 	=> wp_logout_url(home_url()),
				'avatar_url'	=> get_avatar($current_user->user_email),
			);
		}else{
			$datas['user'] = array(
				'logged'  		=> false,
			);
		}
		
		return $datas;
	}
	public static function frontend_seajs_alias($alias){
		if(self::is_user_logged_in() || !self::is_page())
			return $alias;

		$alias[self::$iden] = theme_features::get_theme_includes_js(__DIR__);
		return $alias;
	}
	public static function frontend_seajs_use(){
		if(self::is_user_logged_in() || !self::is_page()) 
			return false;
		?>
		seajs.use('<?= self::$iden;?>',function(m){
			m.config.process_url = '<?= theme_features::get_process_url([
				'action' => self::$iden
			]);?>';
			m.config.lang.M00001 = '<?= ___('Loading, please wait...');?>';
			m.config.lang.E00001 = '<?= ___('Sorry, server error please try again later.');?>';
			
			m.init();
		});
		<?php
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
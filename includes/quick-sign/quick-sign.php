<?php

/**
 * Theme quick sign
 *
 * @version 1.0.2
 * @author KM@INN STUDIO
 */
add_filter('theme_includes',function($fns){
	$fns[] = 'theme_quick_sign::init';
	return $fns;
});
class theme_quick_sign{
	public static $iden = 'theme_quick_sign';
	public static function init(){
		/** filter */
		add_filter('cache_request',					__CLASS__ . '::cache_request');
		//add_filter('frontend_seajs_alias',			__CLASS__ . '::frontend_seajs_alias');
		
		/** action */
		//add_action('frontend_seajs_use',			__CLASS__ . '::frontend_seajs_use');
		add_action('wp_ajax_' . __CLASS__,		__CLASS__ . '::process');
		add_action('wp_ajax_nopriv_' . __CLASS__,	__CLASS__ . '::process');
	}
	public static function cache_request($datas){
		if(is_user_logged_in()){
			global $current_user;
			get_currentuserinfo();
			
			$datas['user'] = array(
				'logged'  		=> true,
				'display_name' 	=> $current_user->display_name,
				'posts_url' 	=> theme_cache::get_author_posts_url($current_user->ID),
				'logout_url' 	=> wp_logout_url(home_url()),
				'avatar_url'		=> get_avatar($current_user->user_email),
			);
		}else{
			$datas['user'] = array(
				'logged'  		=> false,
			);
		}
		
		return $datas;
	}
	public static function process(){
		theme_features::check_nonce();
		theme_features::check_referer();
		
		
		$output = [];
		
		$type = isset($_REQUEST['type']) ? $_REQUEST['type'] : null;
		$user = isset($_POST['user']) ? $_POST['user'] : false;
		$email = (isset($user['email']) && is_email($user['email'])) ? $user['email'] : null;
		$pwd = isset($user['pwd']) ? $user['pwd'] : null;
		
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
				$at_least_len = 2;
				if(!isset($user['nickname']) || mb_strlen($user['nickname']) < $at_least_len){
					$output['status'] = 'error';
					$output['code'] = 'invalid_nickname';
					$output['msg'] = sprintf(___('Sorry, you nick name is invalid, at least %d characters in length, please try again.'),$at_least_len);
					die(theme_features::json_format($output));
				}else{
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
				}
				break;
			/** 
			 * lost-password
			 */
			case 'recover':
				if(!is_email($email)){
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
				$encode_str = serialize($encode_arr);
				$encode = authcode($encode_str,'encode',AUTH_KEY,7200);
				$callback_url = add_query_arg(array(
					'step' => 'reset-pwd',
					'token' => $encode,
				),get_permalink(theme_cache::get_page_by_path(theme_custom_sign::$page_slug)));
				// $callback_url = theme_features::get_process_url(array(
					// 'action' => self::$ajax_lost_password,
					// 'token' => $encode,
				// ));
				$content = '
					<h3>' . sprintf(___('Dear %s!'),$user->display_name) . '</h3>
					<p>
						' . sprintf(___('You are receiving this email because you forgot your password. We already made an address for your account, you can access this address ( %s ) to log-in and change your password in 3 hours.'),'<a href="' . $callback_url . '" target="_blank">' . $callback_url . '</a>') . '
					</p>
					<p>' . sprintf(___('-- From %s'),'<a href="' . home_url() . '" target="_blank">' . get_bloginfo('name') . '</a>') . '</p>
				';
				$title = ___('You are applying to retrieve your password.');
				

				// $phpmailer = new theme_phpmailer();
				// $phpmailer->FromName = ___('Notification');
				// $wp_mail = $phpmailer->send(
					// $user->user_email,
					// $title,
					// $content
				// );
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
				// if($wp_mail['status'] == 'success'){
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
				$post_user = isset($_POST['user']) ? $_POST['user'] : null;
				if(!$post_user){
					$output['status'] = 'error';
					$output['code'] = 'invalid_param';
					$output['msg'] = ___('Invalid param.');
					die(theme_features::json_format($output));
				}
				/** email */
				$post_email = isset($post_user['email']) ?  $post_user['email'] : null;
				if(!is_email($post_user['email'])){
					$output['status'] = 'error';
					$output['code'] = 'invalid_email';
					$output['msg'] = ___('Invalid email.');
					die(theme_features::json_format($output));
				}
				/** pwd */
				$post_pwd_new = isset($post_user['pwd-new']) ?  $post_user['pwd-new'] : null;
				$post_pwd_again = isset($post_user['pwd-again']) ?  $post_user['pwd-again'] : null;
				if(empty($post_pwd_new) || empty($post_pwd_again) || $post_pwd_new !== $post_pwd_again){
					$output['status'] = 'error';
					$output['code'] = 'invalid_twice_pwd';
					$output['msg'] = ___('Invalid twice password.');
					die(theme_features::json_format($output));
				}
				/** token */
				$post_token = isset($post_user['token']) ?  $post_user['token'] : null;
				if(empty($post_token)){
					$output['status'] = 'error';
					$output['code'] = 'empty_token';
					$output['msg'] = ___('Empty token.');
					die(theme_features::json_format($output));
				}
				/** decode token */
				$token_decode = authcode($post_token,'decode',AUTH_KEY);
				if(!$token_decode){
					$output['status'] = 'error';
					$output['code'] = 'expired_token';
					$output['msg'] = ___('This token is expired.');
					die(theme_features::json_format($output));
				}
				/** unserialize token */
				$token_arr = @unserialize($token_decode);
				if(!$token_arr || !is_array($token_arr)){
					$output['status'] = 'error';
					$output['code'] = 'invalid_token';
					$output['msg'] = ___('This token is expired.');
					die(theme_features::json_format($output));
				}
				$token_user_id = isset($token_arr['user_id']) ? (int)$token_arr['user_id'] : null;
				$token_user_email = isset($token_arr['user_email']) ? $token_arr['user_email'] : null;
				/** check token email is match post email */
				if($token_user_email != $post_email){
					$output['status'] = 'error';
					$output['code'] = 'token_email_not_match';
					$output['msg'] = ___('The token email and you account email do not match.');
					die(theme_features::json_format($output));
				}
				
				/** check post email exists */
				$user_id = (int)email_exists($post_email);
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
	/** 
	 * user_login
	 */
	public static function user_login($args){
		$defaults = array(
			'email' => null,
			'pwd' => null,
			'remember' => false,
		);
		$r = array_merge($defaults,$args);
		extract($r,EXTR_SKIP);
		// var_dump($pwd);exit;
		if(!$pwd){
			$output['status'] = 'error';
			$output['code'] = 'invalid_pwd';
			$output['msg'] = ___('Sorry, password is invalid, please try again.');
		}else if(!is_email($email)){
			$output['status'] = 'error';
			$output['code'] = 'invalid_email';
			$output['msg'] = ___('Sorry, email is invalid, please try again.');
		}else{
			$creds = [];
			$creds['user_login'] = $email;
			$creds['user_password'] = $pwd;
			$creds['remember'] = $remember;
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
		$r = array_merge($defaults,$args);
		extract($r,EXTR_SKIP);
		
		if(!$pwd){
			$output['status'] = 'error';
			$output['msg'] = ___('Sorry, password is invalid, please try again.');
			$output['code'] = 'invalid_pwd';
		}else if(!is_email($email)){
			$output['status'] = 'error';
			$output['msg'] = ___('Sorry, email is invalid, please try again.');
			$output['code'] = 'invalid_email';
		}else if(!trim($nickname) || !validate_username($nickname)){
			$output['status'] = 'error';
			$output['code'] = 'invalid_nickname';
			$output['msg'] = ___('Sorry, nickname is invalid, please try again.');
		/** 
		 * check email exists
		 */
		}else if(email_exists($email)){
			$output['status'] = 'error';
			$output['code'] = 'email_exists';
			$output['msg'] = ___('Sorry, email already exists, please change another one and try again.');
		}else{
			/** 
			 * create user and get user id
			 */
			$user_data = array(
				'user_login' => $nickname,
				'user_pass' => $pwd,
				'nickname' => $nickname,
				'display_name' => $nickname,
				'user_email' => $email,
			);
			
			$user_id = wp_insert_user($user_data);
			if(is_wp_error($user_id)){
				$output['status'] = 'error';
				$output['code'] = $user_id->get_error_code();
				$output['msg'] = $user_id->get_error_message();
				
			}else{
				/** rename nicename */
				//wp_update_user(array(
				//	'ID' => $user_id,
				//	'user_nicename' => 100000 + $user_id
				//));
				/** 
				 * go to login
				 */
				$output = self::user_login(array(
					'email' => $email,
					'pwd' => $pwd,
					'remember' => $remember
				));
			}
		}
		return $output;
	}

	/** 
	 * set_html_content_type
	 */
	public static function set_html_content_type(){
		return 'text/html';
	}
	public static function frontend_seajs_alias($alias){
		$alias[self::$iden] = theme_features::get_theme_includes_js(__DIR__);
		return $alias;
	}
	public static function frontend_seajs_use(){
		?>
		seajs.use('<?php echo self::$iden;?>',function(m){
			m.config.process_url = '<?php echo theme_features::get_process_url(array('action' => self::$iden));?>';
			m.config.recover_pwd_url = '<?php echo esc_url(theme_custom_sign::get_tabs('recover')['url']);?>';
			m.config.lang.M00001 = '<?php echo esc_js(___('Loading, please wait...'));?>';
			m.config.lang.M00002 = '<?php echo esc_js(___('Login'));?>';
			m.config.lang.M00003 = '<?php echo esc_js(___('Register'));?>';
			m.config.lang.M00004 = '<?php echo esc_js(___('Nickname'));?>';
			m.config.lang.M00005 = '<?php echo esc_js(___('Email'));?>';
			m.config.lang.M00006 = '<?php echo esc_js(___('Password'));?>';
			m.config.lang.M00007 = '<?php echo esc_js(___('Re-type password'));?>';
			m.config.lang.M00008 = '<?php echo esc_js(___('Login / Register'));?>';
			m.config.lang.M00009 = '<?php echo esc_js(___('Remember me'));?>';
			m.config.lang.M00010 = '<?php echo esc_js(sprintf(___('Login successful, closing tip after %d seconds.'),3));?>';
			m.config.lang.M00011 = '<?php echo esc_js(___('Login successful, page is refreshing, please wait..'));?>';
			m.config.lang.M00012 = '<?php echo esc_js(___('Forgot my password?'));?>';
			m.config.lang.E00001 = '<?php echo esc_js(___('Server error or network is disconnected.'));?>';
			m.init();
		});
		<?php
	}

}
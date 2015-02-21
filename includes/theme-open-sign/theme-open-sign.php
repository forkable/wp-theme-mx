<?php


theme_open_sign::init();
class theme_open_sign{
	
	public static $iden = 'theme_open_sign';
	public static $open_types = array('sina','qq');

	public static $user_meta_key = array(
		'id' => 'isos_open_id',
		'type' => 'isos_open_type',
		'avatar' => 'isos_open_avatar',
		'token' => 'isos_open_token',
		'expire' => 'isos_open_expire',
		'access' => 'isos_open_access',
		
	);
	
	private static $open_url = 'http://opensign.inn-studio.com/api/';
	private static $open_keys = array(
		'sina' => array(
			'akey' => '4103217221',
			'skey' => '4b4055df319fcdbdf3b9a69fc336b639',
		),
	);
	
	public static function init(){
	
		add_action('wp_ajax_nopriv_isos_cb',					get_class() . '::process_cb');
		
		add_action('wp_ajax_nopriv_' . self::$iden ,get_class() . '::process');
	}
	public static function options_default($opts){
		
		return $opts;
	}
	public static function options_save($opts){
		if(isset($_POST[self::$iden])){
			$opts[self::$iden] = $_POST[self::$iden];
		}
		return $opts;
	}
	public static function display_backend(){
		$opt = theme_options::get_options(self::$iden);
		
	}
	private static function get_qc_config(){
		return (object)array(
			'appid' => '1101154728',
			'appkey' => '6v0x5IuSGR1hbSE7',
			'callback' => theme_features::get_process_url(array(
				'action' => 'isos_cb'
			)),
			'scope' => 'get_user_info,add_share,list_album,add_album,upload_pic,add_topic,add_one_blog,add_weibo,check_page_fans,add_t,add_pic_t,del_t,get_repost_list,get_info,get_other_info,get_fanslist,get_idolist,add_idol,del_idol,get_tenpay_addr',
			'errorReport' => true,
			'storageType' => 'file',
			'host' => 'localhost',
			'user' => 'root',
			'password' => 'root',
			'database' => 'test',
		);
	}
	public static function get_login_url($type){
		return theme_features::get_process_url(array(
			'action' => self::$iden,
			'sign-type' => $type
		));
	}

	public static function process_cb(){
		theme_features::check_nonce('nonce');
		$current_timestamp = time();
		// print_r($_GET);
		/** 
		 * sina set-auth
		 */
		if(isset($_GET['sina']) && $_GET['sina'] === 'set-auth'){
			$access_token = isset($_GET['access_token']) ? $_GET['access_token'] : null;
			$expires_in = isset($_GET['expires_in']) ? (int)$_GET['expires_in'] : null;
			
			/** 
			 * check callback data
			 */
			if(!$access_token || !$expires_in){
				$output['status'] = 'error';
				$output['code'] = 'invalid_callback_data';
				$output['msg'] = ___('Invalid callback data.');
				die(theme_features::json_format($output));
			}
			/** 
			 * auth
			 */
			include dirname(__FILE__) . '/inc/sina/saetv2.ex.class.php';
			$sina = new theme_open_sign\inc\sina\SaeTClientV2(self::$open_keys['sina']['akey'] , self::$open_keys['sina']['skey'] , $access_token );
			/** get uid */
			$open_id = 'sina-' . $sina->get_uid()['uid'];				
			$user = self::user_exists_by_openid($open_id);
			/** register insert user */
			if(empty($user)){
				$sina_userdata = $sina->show_user_by_id($open_id);
				$user_login = sanitize_user($sina_userdata['screen_name']);
				$user_id = wp_insert_user(array(
					'user_login' => $user_login,
					'user_pass' => $current_timestamp,
					'nickname' => $sina_userdata['screen_name'],
					'display_name' => $sina_userdata['screen_name'],
					'user_email' => self::get_tmp_email(),
				));
				if(!is_wp_error($user_id)){
					add_user_meta($user_id,self::$user_meta_key['id'],$open_id);
					add_user_meta($user_id,self::$user_meta_key['type'],'sina');
					if(!empty($sina_userdata['avatar_large'])){
						update_user_meta($user_id,'avatar',$sina_userdata['avatar_large']);
					}
					$user = get_user_by('id',$user_id);
				}else{
					$output['status'] = 'error';
					$output['code'] = $user_id->get_error_code();
					$output['msg'] = $user_id->get_error_message();
					die(theme_features::json_format($output));
				}
			}
			/** update open data */
			update_user_meta($user->ID,self::$user_meta_key['token'],$access_token);
			update_user_meta($user->ID,self::$user_meta_key['expire'],$expires_in);
			update_user_meta($user->ID,self::$user_meta_key['access'],$current_timestamp);

			wp_set_current_user($user->ID);
			wp_set_auth_cookie($user->ID);
			do_action('wp_login',$user->user_login,$user);

			/** redirect  */
			$redirect_uri = isset($_GET['uri']) ? urldecode($_GET['uri']) : null;
			if($redirect_uri){
				wp_safe_redirect($redirect_uri);
			}else{
				wp_safe_redirect(home_url());
			}
			die(___('Redirecting, please wait...'));
		/** 
		 * qq
		 */
		}else if(isset($_GET['qq']) && $_GET['qq'] === 'set-auth'){
			include dirname(__FILE__) . '/inc/open/qq/qqConnectAPI.php';
			$qc = new theme_open_sign\inc\qq\QC(self::get_qc_config());
			$token = $qc->qq_callback();
			var_dump($token);exit;
			$open_id = 'qq-' . $qc->get_openid();
			/** load user from database */
			$user = self::user_exists_by_openid($open_id);
			/**
			 * if not exist user, create it
			 */
			if(empty($user)){
				/** load qqzone info */
				$open_info = $qc->get_user_info();
				$user_login = sanitize_user($open_info['nickname']);
				$user_avatar = $open_info['figureurl_2'];/** 100*100 */
				$user_id = wp_insert_user(array(
					'user_login' => $user_login,
					'user_pass' => $current_timestamp,
					'nickname' => $open_info['nickname'],
					'display_name' => $open_info['nickname'],
					'user_email' => self::get_tmp_email(),
				));
				if(!is_wp_error($user_id)){
					add_user_meta($user_id,self::$user_meta_key['id'],$open_id);
					add_user_meta($user_id,self::$user_meta_key['type'],'qq');
					/** avatar */
					if(!empty($sina_userdata['avatar_large'])){
						if(!empty($open_info['figureurl_qq_2'])){
							update_user_meta($user_id,'avatar', $open_info['figureurl_qq_2']);
						}else{
							update_user_meta($user_id,'avatar', $open_info['figureurl_2']);
						}
					}
					$user = get_user_by('id',$user_id);
				}else{
					$output['status'] = 'error';
					$output['code'] = $user_id->get_error_code();
					$output['msg'] = $user_id->get_error_message();
					die(theme_features::json_format($output));
				}
			}
			/** update open data */
			update_user_meta($user->ID,self::$user_meta_key['token'],$access_token);
			update_user_meta($user->ID,self::$user_meta_key['expire'],$expires_in);
			update_user_meta($user->ID,self::$user_meta_key['access'],$current_timestamp);

			wp_set_current_user($user->ID);
			wp_set_auth_cookie($user->ID);
			do_action('wp_login',$user->user_login,$user);

			/** redirect  */
			$redirect_uri = isset($_GET['uri']) ? urldecode($_GET['uri']) : null;
			if($redirect_uri){
				wp_safe_redirect($redirect_uri);
			}else{
				wp_safe_redirect(home_url());
			}
			wp_die(___('Redirecting, please wait...'));
		}
		
		die();
	}
	public static function get_tmp_email(){
		return time() . mt_rand(100,999) . '@outlook.com';
	}
	public static function process(){
		$output = array();
		/**
		 * nonce
		 */
		if(!isset($_SESSION)) session_start();
		$nonce = wp_create_nonce(AUTH_KEY);
		/**
		 * sign-type
		 */
		$sign_type = isset($_REQUEST['sign-type']) ? $_REQUEST['sign-type'] : null;
		switch($sign_type){
			/**
			 * sina
			 */
			case 'weibox':
			case 'sina':
				$url = urlencode(theme_features::get_process_url(array(
					'action' => 'isos_cb',
					'sina' => 'set-auth',
					'uri' => isset($_SERVER["HTTP_REFERER"]) && strpos($_SERVER['HTTP_REFERER'],home_url()) === 0 ? $_SERVER["HTTP_REFERER"] : home_url(),
					'nonce' => $nonce,
				)));
				$url = add_query_arg(array(
					'sina' => 'get-auth',
					'uri' => $url,
					'state' => $nonce,
				),self::$open_url);
				header('Location: ' . $url);
				die(___('Redirecting, please wait...'));
			/**
			 * qq
			 */
			case 'qq':
				$url = urlencode(theme_features::get_process_url(array(
					'action' => 'isos_cb',
					'qq' => 'set-auth',
					'redirect' => isset($_SERVER["HTTP_REFERER"]) && strpos($_SERVER['HTTP_REFERER'],home_url()) === 0 ? $_SERVER["HTTP_REFERER"] : home_url(),
					'nonce' => $nonce,
				)));
				$url = add_query_arg(array(
					'qq' => 'get-auth',
					'redirect_uri' => urlencode($url),
				),self::$open_url);
				header('Location: ' . $url);
				die(___('Redirecting, please wait...'));
			default:
		}

		die(theme_features::json_format($output));
	}
	public static function user_exists_by_openid($openid){
		$users = get_users(array(
			'meta_key' => self::$user_meta_key['id'],
			'meta_value' => $openid,
			'number' => 1,
		));
		return empty($users) ? null : $users[0];
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
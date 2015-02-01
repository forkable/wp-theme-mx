<?php


theme_open_sign::init();
class theme_open_sign{
	
	public static $iden = 'theme_open_sign';
	public static $open_types = array('sina','qq');
	
	public static $key_user_open = array(
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
			'akey' => '3461043641',
			'skey' => 'efd4767a4d2a664b1bfbb65b10febb5e',
		)
	);
	
	public static function init(){
	
		add_action('wp_ajax_nopriv_isos_cb',					get_class() . '::process_cb');
		add_action('wp_ajax_nopriv_isos_redirect_get_auth',		get_class() . '::process_redirect_get_auth');
	}
	public static function get_process_auth_url($type){
		if(!in_array($type,self::$open_types)) return false;
		$url = theme_features::get_process_url(array(
			'action' => 'isos_redirect_get_auth',
			'type' => $type,
		));
		return $url;
	}
	private static function get_auth_url($type,$nonce){
		if(!in_array($type,self::$open_types)) return false;
		switch($type){
			case 'sina':
				
				// include dirname(__FILE__) . '/inc/sina/saetv2.ex.class.php';
				$process_cb_url = urlencode(theme_features::get_process_url(array(
					'action' => 'isos_cb',
					'sina' => 'set-auth',
					'uri' => isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : home_url(),
					'nonce' => $nonce,
				)));
				$open_url = add_query_arg(array(
					'sina' => 'get-auth',
					'uri' => $process_cb_url,

					// 'redirect_uri' => urlencode($process_cb_url),
					'state' => $nonce,
					// 'mobile' => 'mobile',
				),self::$open_url);
			break;
			default:
				$open_url = false;
		}
		return $open_url;
	}
	public static function process_redirect_get_auth(){
		// !check_referer() && wp_die(___('Referer error'));
		// theme_features::check_nonce();
		// echo 'asdf';
		$type = isset($_GET['type']) ? $_GET['type'] : false;
		if(!in_array($type,self::$open_types)) return false;
		
		$nonce = wp_create_nonce(AUTH_KEY);
		switch($type){
			case 'sina':
				$url = self::get_auth_url('sina',$nonce);
				// var_dump($url);exit;
				wp_redirect($url);
				exit;
			break;
		}
	}
	public static function process_cb(){
		theme_features::check_nonce('nonce');
		// print_r($_GET);
		/** 
		 * sina
		 */
		if(isset($_GET['sina'])){
			/** 
			 * set-auth
			 */
			if($_GET['sina'] === 'set-auth'){
				/** check nonce */
				theme_features::check_nonce('nonce');
				
				$access_token = isset($_GET['access_token']) ? $_GET['access_token'] : null;
				$expires_in = isset($_GET['expires_in']) ? (int)$_GET['expires_in'] : null;
				
				/** 
				 * check callback data
				 */
				if(!$access_token || !$expires_in){
					$output['status'] = 'error';
					$output['id'] = 'invalid_callback_data';
					$output['msg'] = ___('Invalid callback data.');
					die(theme_features::json_format($output));
				}
				/** 
				 * auth
				 */
				include dirname(__FILE__) . '/inc/sina/saetv2.ex.class.php';
				$sina = new SaeTClientV2( self::$open_keys['sina']['akey'] , self::$open_keys['sina']['skey'] , $access_token );
				/** get uid */
				$sina_uid = $sina->get_uid();
				$sina_uid = $sina_uid['uid'];
				$sina_userdata = $sina->show_user_by_id($sina_uid);
				/** register insert user */
				$user = self::user_exists_by_openid($sina_uid);
				if(empty($user)){
					$user_id = wp_insert_user(array(
						'user_login' => sanitize_user($sina_userdata['screen_name']),
						'user_pass' => time(),
						'nickname' => $sina_userdata['screen_name'],
						'display_name' => $sina_userdata['screen_name'],
						'user_email' => self::get_tmp_email(),
					));
					if(!is_wp_error($user_id)){
						/** rename nicenian */
						wp_update_user(array(
							'ID' => $user_id,
							'user_nicename' => $user_id
						));
						add_user_meta($user_id,self::$key_user_open['id'],$sina_uid,$sina_uid);
						add_user_meta($user_id,self::$key_user_open['type'],'sina');
						if(!empty($sina_userdata['avatar_large'])){
							update_user_meta($user_id,'avatar',$sina_userdata['avatar_large']);
						}
						$user = get_user_by('id',$user_id);
					}else{
						$output['status'] = 'error';
						$output['id'] = $user_id->get_error_code();
						$output['msg'] = $user_id->get_error_message();
						die(theme_features::json_format($output));
					}
				/** exists user */
				}else{
					// if(!empty($sina_userdata['avatar_large'])){
						// $old_avatar = get_user_meta($user->ID,self::$key_user_open['avatar'],true);
						// if($old_avatar !== $sina_userdata['avatar_large']){
							// update_user_meta($user->ID,self::$key_user_open['avatar'],$sina_userdata['avatar_large']);
						// }
					// }
				}
				/** update open data */
				update_user_meta($user->ID,self::$key_user_open['token'],$access_token);
				update_user_meta($user->ID,self::$key_user_open['expire'],$expires_in);
				update_user_meta($user->ID,self::$key_user_open['access'],time());
				
				wp_set_current_user($user->ID);
				wp_set_auth_cookie($user->ID);
				do_action('wp_login',$user->user_login);
				// var_dump($sina_userdata);
				// die();
				/** redirect  */
				$redirect_uri = isset($_GET['uri']) ? urldecode($_GET['uri']) : null;
				if($redirect_uri){
					wp_safe_redirect($redirect_uri);
				}else{
					wp_safe_redirect(home_url());
				}
				wp_die(
					___('Redirecting, please wait...'),
					___('Redirecting'),
					302
				);
			}else if(isset($_GET['qq']) && $_GET['qq'] === 'get-auth'){
			
			
			}
		/** 
		 * qq
		 */
		}else if(isset($_GET['qq'])){
		
		
		}
		
		die();
	}
	public static function get_tmp_email(){
		return time() . mt_rand(100,999) . '@outlook.com';
	}
	public static function process(){
		$output = array();
		$type = isset($_GET['type']) ? $_GET['type'] : false;
		if(!$type){
			$output['status'] = 'error';
			$output['id'] = 'invalid_type';
			$output['msg'] = ___('Invalid type param.');
		}
		switch($type){
			case 'sina':
				
			break;
		
		}
		die(theme_features::json_format($output));
	}
	public static function user_exists_by_openid($openid){
		$users = get_users(array(
			'meta_key' => self::$key_user_open['id'],
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
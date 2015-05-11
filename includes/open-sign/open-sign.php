<?php
/**
 * @version 2.0.0
 */
add_filter('theme_includes',function($fns){
	$fns[] = 'theme_open_sign::init';
	return $fns;
});
class theme_open_sign{
	
	public static $iden = 'theme_open_sign';
	public static $open_types = array('sina','qq');

	public static $user_meta_key = array(
		'id' => 'open_id',
		'type' => 'open_type',
		'avatar' => 'open_avatar',
		'token' => 'open_token',
		'expire' => 'open_expire',
		'access' => 'open_access',
		'refresh_token' => 'open_refresh_token',
	);
	
	private static $open_url = 'http://opensign.inn-studio.com/api/';
	
	public static function init(){
	
		add_action('wp_ajax_nopriv_isos_cb',					__CLASS__ . '::process_cb');
		
		add_action('wp_ajax_nopriv_' . self::$iden ,__CLASS__ . '::process');

		add_action('page_settings',__CLASS__ . '::display_backend');
		add_filter('theme_options_save',__CLASS__ . '::options_save');
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
		$opt = self::get_options();
		?>
		<fieldset>
			<legend><?= ___('Open sign settings');?></legend>
			<p class="description"><?= sprintf(___('You can use the third-part sign feature for easy to register and login.'));?></p>
			<table class="form-table">
				<tbody>
					<!-- qq -->
					<tr>
						<th><label for="<?= self::$iden;?>-qq-appid"><?= ___('QQ - APPID');?></label></th>
						<td>
							<input type="text" class="widefat code" id="<?= self::$iden;?>-qq-appid" name="<?= self::$iden;?>[qq][appid]" value="<?= isset($opt['qq']['appid']) ? $opt['qq']['appid'] : null;?>">
						</td>
					<tr>
						<th><label for="<?= self::$iden;?>-qq-appkey"><?= ___('QQ - APPKEY');?></label></th>
						<td>
							<input type="text" class="widefat code" id="<?= self::$iden;?>-qq-appkey" name="<?= self::$iden;?>[qq][appkey]" value="<?= isset($opt['qq']['appkey']) ? $opt['qq']['appkey'] : null;?>">
						</td>
					</tr>
					<!-- sina -->
					<tr>
						<th><label for="<?= self::$iden;?>-sina-akey"><?= ___('Sina - APPKEY');?></label></th>
						<td>
							<input type="text" class="widefat code" id="<?= self::$iden;?>-sina-akey" name="<?= self::$iden;?>[sina][akey]" value="<?= isset($opt['sina']['akey']) ? $opt['sina']['akey'] : null;?>">
						</td>
					<tr>
						<th><label for="<?= self::$iden;?>-sina-skey"><?= ___('Sina - SECUREKEY');?></label></th>
						<td>
							<input type="text" class="widefat code" id="<?= self::$iden;?>-sina-skey" name="<?= self::$iden;?>[sina][skey]" value="<?= isset($opt['sina']['skey']) ? $opt['sina']['skey'] : null;?>">
						</td>
					</tr>
				</tbody>
			</table>
		</fieldset>
		<?php
	}
	private static function get_sina_config($key = null){
		$opt = self::get_options();
		$arr = array(
			'akey' => isset($opt['sina']['appkey']) ? $opt['sina']['akey'] : null,
			'skey' => isset($opt['sina']['skey']) ? $opt['sina']['skey'] : null,
		);
		if(empty($key)){
			return $arr;
		}else{
			return isset($arr[$key]) ? $arr[$key] : null;
		}
	}
	private static function get_qc_config(){
		$opt = self::get_options();

		return (object)[
			'appid' => isset($opt['qq']['appid']) ? $opt['qq']['appid'] : null,
			'appkey' => isset($opt['qq']['appkey']) ? $opt['qq']['appkey'] : null,
			'callback' => urlencode(theme_features::get_process_url([
				'qq' => 'set-auth',
				'nonce' => theme_features::create_nonce(),
				'action' => 'isos_cb',
				'redirect_uri' => isset($_GET['redirect']) && is_string($_GET['redirect']) ? $_GET['redirect'] : null,
			])),
			'scope' => 'get_user_info,add_share,list_album,add_album,upload_pic,add_topic,add_one_blog,add_weibo,check_page_fans,add_t,add_pic_t,del_t,get_repost_list,get_info,get_other_info,get_fanslist,get_idolist,add_idol,del_idol,get_tenpay_addr',
			'errorReport' => true,
		];
	}
	public static function get_login_url($type){
		static $caches = [];
		if(!isset($caches[$type]))
		$caches[$type] = esc_url(theme_features::get_process_url(array(
			'action' => self::$iden,
			'sign-type' => $type,
			'redirect' => isset($_GET['redirect']) && is_string($_GET['redirect']) ? urlencode($_GET['redirect']) : null
		)));
		return $caches[$type];
	}
	public static function get_options($key = null){
		static $caches = [];
		if(!isset($caches[self::$iden]))
			$caches[self::$iden] = theme_options::get_options(self::$iden);

		if($key){
			return isset($caches[self::$iden][$key]) ? $caches[self::$iden][$key] : null;
		}else{
			return $caches[self::$iden];
		}
	}
	public static function process_cb(){

		theme_features::check_nonce('nonce');

		/** 
		 * sina set-auth
		 */
		if(isset($_GET['sina']) && $_GET['sina'] === 'set-auth'){
			self::open_sign_sina();
		/** 
		 * qq
		 */
		}else if(isset($_GET['qq']) && $_GET['qq'] === 'set-auth'){
			self::open_sign_qq();
		}
		die();
	}
	public static function open_sign_sina(){
		$access_token = isset($_GET['access_token']) && is_string($_GET['access_token']) ? $_GET['access_token'] : null;
			$expires_in = isset($_GET['expires_in']) & is_string($_GET['expires_in']) ? (int)$_GET['expires_in'] : null;
			
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
			include __DIR__ . '/inc/sina/saetv2.ex.class.php';
			$sina = new theme_open_sign\inc\sina\SaeTClientV2(self::get_sina_config('akey'), self::get_sina_config('skey') , $access_token );
			
			/** get uid */
			$open_id = $sina->get_uid()['uid'];				
			$user = get_user_by('login',$open_id);
			
			/** current time */
			$current_timestamp = time();
			
			/** register insert user */
			if(empty($user)){
				$sina_userdata = $sina->show_user_by_id($open_id);
				$user_id = wp_insert_user(array(
					'user_login' => $open_id,
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
			$redirect_uri = isset($_GET['uri']) && is_string($_GET['uri']) ? urldecode($_GET['uri']) : null;
			if($redirect_uri){
				wp_safe_redirect($redirect_uri);
			}else{
				wp_safe_redirect(home_url());
			}
			die(___('Redirecting, please wait...'));
	}
	public static function open_sign_qq(){
		include __DIR__ . '/inc/qq/qqConnectAPI.php';
		$qc = new theme_open_sign\inc\qq\QC(self::get_qc_config());

		$cb = $qc->qq_callback();
		/** openid */
		$open_id = $qc->get_openid();
		

		/** access_token */
		$access_token = isset($cb['access_token']) && is_string($cb['access_token']) ? $cb['access_token'] : null;
		if(empty($access_token))
			die(___('Invalid access token,'));

		/** redirect */
		$redirect = isset($_GET['redirect_uri']) && is_string($_GET['redirect_uri']) ? urldecode($_GET['redirect_uri']) : null;
		if(empty($redirect)) 
			$redirect = home_url();

		/** expires_in */
		$expires_in = isset($cb['expires_in']) && is_string($cb['expires_in']) ? (int)($cb['expires_in']) : null;
		if(empty($expires_in)) 
			die(___('Invalid expires time.'));

		/** refresh_token */
		$refresh_token = isset($cb['refresh_token']) && is_string($cb['refresh_token']) ? $cb['refresh_token'] : null;
		if(empty($refresh_token)) 
			die(___('Invalid refresh token.'));

		/** current time */
		$current_timestamp = time();

		/** load user from database */
		$user = get_user_by('login',$open_id);
		/**
		 * if not exist user, create it
		 */
		if(empty($user)){
			/** load qqzone info */
			$qc = new theme_open_sign\inc\qq\QC(self::get_qc_config(),$access_token,$open_id);

			$open_info = $qc->get_user_info();

			
			/** avatar */
			$user_avatar = !empty($open_info['figureurl_qq_2']) ? $open_info['figureurl_qq_2'] : $open_info['figureurl_qq_1'];
			
			$user_id = wp_insert_user(array(
				'user_login' => $open_id,
				'user_pass' => $current_timestamp,
				'nickname' => $open_info['nickname'],
				'display_name' => $open_info['nickname'],
				'user_email' => self::get_tmp_email($open_id),
			));
			if(!is_wp_error($user_id)){
				add_user_meta($user_id,self::$user_meta_key['id'],$open_id);
				add_user_meta($user_id,self::$user_meta_key['type'],'qq');
				add_user_meta($user_id,'avatar',$user_avatar);
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
		update_user_meta($user->ID,self::$user_meta_key['refresh_token'],$refresh_token);

		wp_set_current_user($user->ID);
		wp_set_auth_cookie($user->ID);
		do_action('wp_login',$user->user_login,$user);

		/** redirect  */
		wp_safe_redirect($redirect);
		
		die(___('Redirecting, please wait...'));
	}
	public static function get_tmp_email($open_id){
		return $open_id . '@opensign.inn-studio.com';
	}
	public static function process(){
		$output = [];
		/**
		 * nonce
		 */
		$nonce = theme_features::create_nonce();
		/**
		 * sign-type
		 */
		$sign_type = isset($_REQUEST['sign-type']) ? $_REQUEST['sign-type'] : null;
		$opt = self::get_options();
		switch($sign_type){
			/**
			 * sina
			 */
			case 'weibo':
			case 'sina':
				$url = urlencode(theme_features::get_process_url(array(
					'action' => 'isos_cb',
					'sina' => 'set-auth',
					'uri' => isset($_SERVER["HTTP_REFERER"]) && strpos($_SERVER['HTTP_REFERER'],home_url()) === 0 ? $_SERVER["HTTP_REFERER"] : home_url(),
					'nonce' => $nonce,
				)));
				$url = add_query_arg(array(
					'sina' => 'get-auth',
					'akey' => base64_encode(authcode(self::get_sina_config('akey'),'encode')),
					'skey' => base64_encode(authcode(self::get_sina_config('skey'),'encode')),
					'uri' => $url,
					'state' => $nonce,
				),self::$open_url);
				header('Location: ' . $url);
				die(___('Redirecting, please wait...'));
			/**
			 * qq
			 */
			case 'qq':
				include __DIR__ . '/inc/qq/qqConnectAPI.php';
				$qc = new theme_open_sign\inc\qq\QC(self::get_qc_config());
				//var_dump($qc);exit;
				/** go to login page */
				$qc->qq_login();
				die(___('Redirecting, please wait...'));
			default:
		}

		die(theme_features::json_format($output));
	}
}
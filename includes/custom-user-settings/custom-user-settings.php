<?php
/**
 * @version 1.0.1
 */
add_filter('theme_includes',function($fns){
	$fns[] = 'theme_custom_user_settings::init';
	return $fns;
});
class theme_custom_user_settings{
	public static $iden = 'theme_custom_user_settings';
	public static $page_slug = 'account';
	public static $cache_expire = 2505600; /** 29 days */

	public static function init(){
		add_filter('query_vars',			__CLASS__ . '::filter_query_vars');
		
		add_filter('wp_title',				__CLASS__ . '::wp_title',10,2);

		add_action('wp_enqueue_scripts', 	__CLASS__ . '::frontend_css');
		
		add_filter('frontend_seajs_alias',	__CLASS__ . '::frontend_seajs_alias');

		add_action('frontend_seajs_use',	__CLASS__ . '::frontend_seajs_use');
		
		add_action('wp_ajax_' . self::$iden, __CLASS__ . '::process');

		add_filter('custom_point_value_default', __CLASS__ . '::filter_custom_point_value_default');

		add_filter('custom_point_types' , __CLASS__ . '::filter_custom_point_types');
	
		foreach(self::get_tabs() as $k => $v){
			$nav_fn = 'filter_nav_' . $k; 
			add_filter('account_navs',__CLASS__ . "::$nav_fn",$v['filter_priority']);
		}
		add_action( 'wp_enqueue_scripts', __CLASS__ . '::wp_enqueue_script');

		/**
		 * list history
		 */
		foreach([
			'list_history_save_settings',
			'list_history_save_avatar'
		] as $v)
			add_action('list_point_histroy',__CLASS__ . '::' . $v);

	}

	public static function wp_title($title, $sep){
		if(!self::is_page()) 
			return $title;
			
		if(self::get_tabs(get_query_var('tab'))){
			$title = self::get_tabs(get_query_var('tab'))['text'];
		}
		return $title . $sep . get_bloginfo('name');
	}
	public static function filter_query_vars($vars){
		if(!in_array('tab',$vars)) $vars[] = 'tab';
		return $vars;
	}
	public static function is_page(){
		static $cache = null;
		if($cache === null)
			$cache = is_page(self::$page_slug) && self::get_tabs(get_query_var('tab'));
		
		return $cache;
	}

	public static function filter_custom_point_types(array $types = []){
		$types['save-settings'] = [
			'text' => ___('When user save settings'),
			'type' => 'number',
		];
		$types['save-avatar'] = [
			'text' => ___('When user save avatar'),
			'type' => 'number',
		];

		return $types;
	}
	public static function filter_custom_point_value_default(array $opts = []){
		$opts['save-settings'] = -30;
		$opts['save-avatar'] = -50;
		return $opts;
	}
	public static function process(){
		$output = [];

		theme_features::check_referer();
		theme_features::check_nonce();
		
		$type = isset($_REQUEST['type']) && is_string($_REQUEST['type'])? $_REQUEST['type'] : null;

		$user = isset($_POST['user']) && is_array($_POST['user']) ? $_POST['user'] : null;
		
		/**
		 * get current
		 */
		global $current_user;
		get_currentuserinfo();

		
		switch($type){
			/**
			 * settings
			 */
			case 'settings':
				/**
				 * check point is enough
				 */
				if(class_exists('theme_custom_point')){
					/** get current user points */
					$user_points = theme_custom_point::get_point($current_user->ID);
					
					if($user_points - abs(theme_custom_point::get_point_value('save-' . $type)) < 0){
						die(theme_features::json_format([
							'status' => 'error',
							'code' => 'not_enough_point',
							'msg' => ___('Sorry, your points are not enough to modify settings.'),
						]));
					}
				}
				
				if(empty($_POST['user']) || !is_array($_POST['user'])){
					$output['status'] = 'error';
					$output['code'] = 'invaild_param';
					$output['msg'] = ___('Invaild param.');
					die(theme_features::json_format($output));
				}

				$nickname = isset($user['nickname']) && is_string($user['nickname']) ? trim($user['nickname']) : null;
				if(empty($nickname)){
					$output['status'] = 'error';
					$output['code'] = 'invaild_nickname';
					$output['msg'] = ___('Invaild nickname.');
					die(theme_features::json_format($output));
				}

				$url = isset($user['url']) && is_string($user['url']) ? esc_url($user['url']) : null;
				
				$des = isset($user['description']) && is_string($user['description']) ? $user['description'] : null;
				
				$user_id = wp_update_user(array(
					'ID' => $current_user->ID,
					'user_url' => $url,
					'nickname' => $nickname,
					'description' => $des,
					'display_name' => $nickname,
				));

				if(is_wp_error($user_id)){
					$output['status'] = 'error';
					$output['code'] = $user_id->get_error_code();
					$output['msg'] = $user_id->get_error_message();
					die(theme_features::json_format($output));
				}else{
					/**
					 * add point history
					 */
					if(class_exists('theme_custom_point')) {
						$meta = [
							'type' => 'save-' . $type,
							'points' => 0 - abs(theme_custom_point::get_point_value('save-' . $type)),
							'timestamp' => current_time('timestamp'),
						];
						add_user_meta($current_user->ID,theme_custom_point::$user_meta_key['history'],$meta);
						/**
						 * update points
						 */
						
						update_user_meta($current_user->ID,theme_custom_point::$user_meta_key['point'],$user_points - abs(theme_custom_point::get_point_value('save-' . $type)));

						/**
						 * feelback
						 */
						$output['points'] = 0 - abs(theme_custom_point::get_point_value('save-' . $type));
					}

					
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
				$new_pwd_1 = isset($user['new-pwd-1']) && is_string($user['new-pwd-1']) ? trim($user['new-pwd-1']) : null;
				$new_pwd_2 = isset($user['new-pwd-2']) && is_string($user['new-pwd-2']) ? trim($user['new-pwd-2']) : null;
				if(empty($new_pwd_1) || ($new_pwd_1 !== $new_pwd_2)){
					$output['status'] = 'error';
					$output['code'] = 'invaild_pwd_twice';
					$output['msg'] = ___('Password invaild twice.');
					die(theme_features::json_format($output));
				}
				/**
				 * old pwd
				 */
				$old_pwd = isset($user['old-pwd']) && is_string($user['old-pwd']) ? trim($user['old-pwd']) : null;

				if(empty($old_pwd) || 
					wp_check_password($old_pwd,$current_user->user_pass,$current_user->ID)){
					$output['status'] = 'error';
					$output['code'] = 'invaild_old_pwd';
					$output['msg'] = ___('Invaild current password.');
					die(theme_features::json_format($output));
				}

				/**
				 * change password
				 */
				wp_update_user(array(
					'ID' => $current_user->ID,
					'user_pass' => $new_pwd_1,
				));
				/**
				 * set current, relogin
				 */
				wp_set_current_user($current_user->ID);
				wp_set_auth_cookie($current_user->ID);
				
				$output['status'] = 'success';
				$output['msg'] = ___('Your new password has been saved.');
				$output['redirect'] = home_url();
				die(theme_features::json_format($output));
				break;
			/**
			 * avatar
			 */
			case 'avatar':
				/**
				 * check point is enough
				 */
				if(class_exists('theme_custom_point')){
					/** get current user points */
					$user_points = theme_custom_point::get_point($current_user->ID);
					
					if($user_points - abs(theme_custom_point::get_point_value('save-' . $type)) < 0){
						die(theme_features::json_format([
							'status' => 'error',
							'code' => 'not_enough_point',
							'msg' => ___('Sorry, your points are not enough to modify avatar.'),
						]));
					}
				}
				$base64 = isset($_POST['base64']) && is_string($_POST['base64']) ? explode(',',$_POST['base64']) : null;
				if(isset($base64[0]) && strpos($base64[0],'jpeg') === false){
					$output['status'] = 'error';
					$output['code'] = 'invaild_format';
					$output['msg'] = ___('Sorry, your file is invaild format, please check it again.');
					die(theme_features::json_format($output));
				}

				$wp_uplaod_dir = wp_upload_dir();
				
				$filename = $current_user->ID . '.jpg';

				$filesub_url = '/avatar/' . $filename;

				$timestamp = '?v=' . $_SERVER['REQUEST_TIME'];
				
				mk_dir($wp_uplaod_dir['basedir'] . '/avatar');
				
				$filepath = $wp_uplaod_dir['basedir'] . $filesub_url;
								
				$fileurl = $wp_uplaod_dir['baseurl'] . $filesub_url . $timestamp;
				
				$file_contents = file_put_contents($filepath,base64_decode($base64[1]));
				
				if($file_contents === false){
					$output['status'] = 'error';
					$output['code'] = 'can_not_write_file';
					$output['msg'] = ___('Sorry, system can not write file, please try again later or contact the administrator.');
					die(theme_features::json_format($output));
				}else{
					/**
					 * add point history
					 */
					if(class_exists('theme_custom_point')) {
						$meta = [
							'type' => 'save-' . $type,
							'points' => 0 - abs(theme_custom_point::get_point_value('save-' . $type)),
							'timestamp' => current_time('timestamp'),
						];
						add_user_meta($current_user->ID,theme_custom_point::$user_meta_key['history'],$meta);
						/**
						 * update points
						 */
						
						update_user_meta($current_user->ID,theme_custom_point::$user_meta_key['point'],$user_points - abs(theme_custom_point::get_point_value('save-' . $type)));

						/**
						 * feelback
						 */
						$output['points'] = 0 - abs(theme_custom_point::get_point_value('save-' . $type));
					}
					/**
					 * update user meta for avatar
					 */
					$avatar_meta_key = class_exists('theme_custom_avatar') ? theme_custom_avatar::$user_meta_key['avatar'] : 'avatar';
					
					update_user_meta($current_user->ID,$avatar_meta_key,$filesub_url . $timestamp);
					
					$output['status'] = 'success';
					$output['avatar-url'] = $fileurl;
					$output['msg'] = ___('Congratulation! Your avatar has been updated. Page is redirecting, please wait...');
					die(theme_features::json_format($output));
				}
				break;
			default:
				$output['status'] = 'error';
				$output['code'] = 'invaild_type_param';
				$output['msg'] = ___('Sorry, the type param is invaild.');
				die(theme_features::json_format($output));

		}
	}
	public static function get_url(){
		static $cache = null;
		if($cache === null){
			$page = theme_cache::get_page_by_path(self::$page_slug);
			$cache = esc_url(get_permalink($page->ID));
		}
		return $cache;
	}
	public static function get_tabs($key = null){
		static $caches = [];
		if(!empty($caches)){
			if($key)
				return isset($caches[$key]) ? $caches[$key] : null;
			return $caches;
		}
			

		$baseurl = self::get_url();
		$caches = array(
			'history' => array(
				'text' => ___('Reward history'),
				'icon' => 'history',
				'url' => esc_url(add_query_arg('tab','history',$baseurl)),
				'filter_priority' => 50,
			),
			'settings' => array(
				'text' => ___('My settings'),
				'icon' => 'cog',
				'url' => esc_url(add_query_arg('tab','settings',$baseurl)),
				'filter_priority' => 60,
			),
			'avatar' => array(
				'text' => ___('My avatar'),
				'icon' => 'github-alt',
				'url' => esc_url(add_query_arg('tab','avatar',$baseurl)),
				'filter_priority' => 70,
			),
			'password' => array(
				'text' => ___('Change password'),
				'icon' => 'lock',
				'url' => esc_url(add_query_arg('tab','password',$baseurl)),
				'filter_priority' => 80,
			),
		);

		if($key){
			$caches[$key] = isset($caches[$key]) ? $caches[$key] : false;
			return $caches[$key];
		}
		return $caches;
	}
	public static function filter_nav_history($navs){
		$navs['history'] = '<a href="' . self::get_tabs('history')['url'] . '">
			<i class="fa fa-' . self::get_tabs('history')['icon'] . ' fa-fw"></i> 
			' . self::get_tabs('history')['text'] . '
		</a>';
		return $navs;
	}
	public static function filter_nav_settings($navs){
		$navs['settings'] = '<a href="' . self::get_tabs('settings')['url'] . '">
			<i class="fa fa-' . self::get_tabs('settings')['icon'] . ' fa-fw"></i> 
			' . self::get_tabs('settings')['text'] . '
		</a>';
		return $navs;
	}
	public static function filter_nav_avatar($navs){
		$navs['avatar'] = '<a href="' . self::get_tabs('avatar')['url'] . '">
			<i class="fa fa-' . self::get_tabs('avatar')['icon'] . ' fa-fw"></i> 
			' . self::get_tabs('avatar')['text'] . '
		</a>';
		return $navs;
	}
	public static function filter_nav_password($navs){
		$navs['password'] = '<a href="' . self::get_tabs('password')['url'] . '">
			<i class="fa fa-' . self::get_tabs('password')['icon'] . ' fa-fw"></i> 
			' . self::get_tabs('password')['text'] . '
		</a>';
		return $navs;
	}
	public static function list_history_save_settings($history){
		if($history['type'] !== 'save-settings')
			return false;
		?>
		<li class="list-group-item">
			<?php theme_custom_point::the_list_icon('cog');?>
			<?php theme_custom_point::the_point_sign(0 - abs(theme_custom_point::get_point_value('save-settings')));?>
			
			<span class="history-text">
				<?= ___('You modified your settings.');?>
			</span>
			
			<?php theme_custom_point::the_time($history);?>
		</li>
		<?php
	}
	public static function list_history_save_avatar($history){
		if($history['type'] !== 'save-avatar')
			return false;
		?>
		<li class="list-group-item">
			<?php theme_custom_point::the_list_icon('github-alt');?>
			<?php theme_custom_point::the_point_sign(0 - (theme_custom_point::get_point_value('save-avatar')));?>
			
			<span class="history-text">
				<?= ___('You modified your avatar.');?>
			</span>
			
			<?php theme_custom_point::the_time($history);?>
		</li>
		<?php
	}
	public static function wp_enqueue_script(){
		if(!self::is_page())
			return false;

		$tabs = self::get_tabs();
		$tab_active = get_query_var('tab');
		if($tab_active === 'avatar'){
			wp_enqueue_script('jquery-core');
		}
	}
	public static function frontend_seajs_alias($alias){
		if(!self::is_page()) 
			return $alias;
			
		foreach(self::get_tabs() as $k => $v){
			$alias[self::$iden . '-' . $k] = theme_features::get_theme_includes_js(__DIR__,$k);
			if($k === 'avatar'){
				$alias[self::$iden . '-' . $k . '-cropper'] = theme_features::get_theme_includes_js(__DIR__,'cropper');
			}
		}
		return $alias;
	}
	public static function frontend_seajs_use(){
		if(!self::is_page()) 
			return false;
		
		$tabs = self::get_tabs();
		$tab_active = get_query_var('tab');

		switch($tab_active){
			case 'password':
			case 'settings':
				?>
				seajs.use('<?= self::$iden,'-settings';?>',function(m){
					m.config.process_url = '<?= theme_features::get_process_url(array('action' => self::$iden));?>';
					m.config.lang.M00001 = '<?= ___('Loading, please wait...');?>';
					m.config.lang.E00001 = '<?= ___('Sorry, server error please try again later.');?>';
					
					m.init();
				});				
				<?php
				break;
			case 'avatar':
				?>
				seajs.use('<?= self::$iden,'-',$tab_active;?>',function(m){
					m.config.process_url = '<?= theme_features::get_process_url(array('action' => self::$iden));?>';
					m.config.lang.M00001 = '<?= ___('Loading, please wait...');?>';
					m.config.lang.E00001 = '<?= ___('Sorry, server error please try again later.');?>';
					
					m.init();
				});				
				<?php
				break;
		}
	}
	public static function frontend_css(){
		if(!self::is_page())
			 return false;
			 
		$tabs = self::get_tabs();
		$tab_active = get_query_var('tab');
		switch($tab_active){
			case 'avatar':
				wp_enqueue_style(
					self::$iden . '-' . $tab_active,
					theme_features::get_theme_includes_css(__DIR__,$tab_active),
					'frontend',
					theme_file_timestamp::get_timestamp()
				);
				
				wp_enqueue_style(
					self::$iden . '-cropper',
					theme_features::get_theme_includes_css(__DIR__,'cropper'),
					'frontend',
					theme_file_timestamp::get_timestamp()
				);

				break;
		}
	}
}
?>
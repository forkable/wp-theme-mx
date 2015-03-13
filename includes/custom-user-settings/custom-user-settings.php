<?php
/**
 * @version 1.0.0
 * @author KM <kmvan.com@gmail.com>
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
		add_filter('query_vars',			get_class() . '::filter_query_vars');
		
		add_filter('wp_title',				get_class() . '::wp_title',10,2);

		add_action('wp_enqueue_scripts', 	get_class() . '::frontend_css');
		
		add_filter('frontend_seajs_alias',	get_class() . '::frontend_seajs_alias');

		add_action('frontend_seajs_use',	get_class() . '::frontend_seajs_use');
		
		add_action('wp_ajax_' . self::$iden, get_class() . '::process');

		foreach(self::get_tabs() as $k => $v){
			$nav_fn = 'filter_nav_' . $k; 
			add_filter('account_navs',get_class() . "::$nav_fn",$v['filter_priority']);
		}

	}

	public static function wp_title($title, $sep){
		if(!self::is_page()) return $title;
		if(self::get_tabs(get_query_var('tab'))){
			$title = self::get_tabs(get_query_var('tab'))['text'];
		}
		return $title . $sep . get_bloginfo('name');
	}
	public static function filter_query_vars($vars){
		if(!in_array('page',$vars)) $vars[] = 'page';
		return $vars;
	}
	public static function is_page(){
		if(
			is_page(self::$page_slug) 				&& 
			self::get_tabs(get_query_var('tab'))
		)
			return true;
		return false;
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
				$des = isset($user['description']) ? $user['description'] : null;
				
				$user_id = wp_update_user(array(
					'ID' => get_current_user_id(),
					'user_url' => $url,
					'nickname' => $nickname,
					'description' => $des,
					'display_name' => $nickname,
					'user_nicename' => 10000+get_current_user_id(),
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
			/**
			 * avatar
			 */
			case 'avatar':
				$base64 = isset($_POST['base64']) && is_string($_POST['base64']) ? explode(',',$_POST['base64']) : null;
				if(isset($base64[0]) && strpos($base64[0],'jpeg') === false){
					$output['status'] = 'error';
					$output['code'] = 'invaild_format';
					$output['msg'] = ___('Sorry, your file is invaild format, please check it again.');
					die(theme_features::json_format($output));
				}

				$filename = 'avatar-' . get_current_user_id() . '-' . current_time('YmdHis') . '-' . rand(100,999) . '.jpg';
				
				$filepath = wp_upload_dir()['path'] . '/' . $filename;
				
				$fileurl = wp_upload_dir()['url'] . '/' . $filename;
				
				$file_contents = file_put_contents($filepath,base64_decode($base64[1]));
				
				if($file_contents === false){
					$output['status'] = 'error';
					$output['code'] = 'can_not_write_file';
					$output['msg'] = ___('Sorry, system can not write file, please try again later or contact the administrator.');
					die(theme_features::json_format($output));
				}else{
					/**
					 * update user meta for avatar
					 */
					$avatar_meta_key = class_exists('theme_custom_avatar') ? theme_custom_avatar::$user_meta_key['avatar'] : 'avatar';
					
					update_user_meta(get_current_user_id(),$avatar_meta_key,$fileurl);
					
					$output['status'] = 'success';
					$output['avatar-url'] = $fileurl;
					$output['msg'] = ___('Congratulation! Your avatar has been updated. Page is redirecting, please wait...');
					die(theme_features::json_format($output));
				}
				break;
		}
		die(theme_features::json_format($output));
	}
	public static function get_url(){
		return get_permalink(theme_cache::get_page_by_path(self::$page_slug));
	}
	public static function get_tabs($key = null){
		$baseurl = self::get_url();
		$tabs = array(
			'history' => array(
				'text' => ___('Reward history'),
				'icon' => 'history',
				'url' => add_query_arg('tab','history',$baseurl),
				'filter_priority' => 50,
			),
			'settings' => array(
				'text' => ___('My settings'),
				'icon' => 'cog',
				'url' => add_query_arg('tab','settings',$baseurl),
				'filter_priority' => 60,
			),
			'avatar' => array(
				'text' => ___('My avatar'),
				'icon' => 'image',
				'url' => add_query_arg('tab','avatar',$baseurl),
				'filter_priority' => 70,
			),
			'password' => array(
				'text' => ___('Change password'),
				'icon' => 'lock',
				'url' => add_query_arg('tab','password',$baseurl),
				'filter_priority' => 80,
			),
		);
		if($key){
			return isset($tabs[$key]) ? $tabs[$key] : false;
		}
		return $tabs;
	}
	public static function filter_nav_history($navs){
		$navs['history'] = '<a href="' . esc_url(self::get_tabs('history')['url']) . '">
			<i class="fa fa-' . self::get_tabs('history')['icon'] . '"></i> 
			' . esc_html(self::get_tabs('history')['text']) . '
		</a>';
		return $navs;
	}
	public static function filter_nav_settings($navs){
		$navs['settings'] = '<a href="' . esc_url(self::get_tabs('settings')['url']) . '">
			<i class="fa fa-' . self::get_tabs('settings')['icon'] . '"></i> 
			' . esc_html(self::get_tabs('settings')['text']) . '
		</a>';
		return $navs;
	}
	public static function filter_nav_avatar($navs){
		$navs['avatar'] = '<a href="' . esc_url(self::get_tabs('avatar')['url']) . '">
			<i class="fa fa-' . self::get_tabs('avatar')['icon'] . '"></i> 
			' . esc_html(self::get_tabs('avatar')['text']) . '
		</a>';
		return $navs;
	}
	public static function filter_nav_password($navs){
		$navs['password'] = '<a href="' . esc_url(self::get_tabs('password')['url']) . '">
			<i class="fa fa-' . self::get_tabs('password')['icon'] . '"></i> 
			' . esc_html(self::get_tabs('password')['text']) . '
		</a>';
		return $navs;
	}

	public static function frontend_seajs_alias($alias){
		if(!self::is_page()) return $alias;
		foreach(self::get_tabs() as $k => $v){
			$alias[self::$iden . '-' . $k] = theme_features::get_theme_includes_js(__DIR__,$k);
			if($k === 'avatar'){
				$alias[self::$iden . '-' . $k . '-cropper'] = theme_features::get_theme_includes_js(__DIR__,'cropper');
			}
		}
		return $alias;
	}
	public static function frontend_seajs_use(){
		if(!self::is_page()) return false;
		
		$tabs = self::get_tabs();
		$tab_active = get_query_var('tab');

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
			case 'avatar':
				?>
				seajs.use('<?php echo self::$iden,'-',$tab_active;?>',function(m){
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
		switch($tab_active){
			case 'avatar':
				wp_enqueue_style(
					self::$iden . '-' . $tab_active,
					theme_features::get_theme_includes_css(__DIR__,$tab_active,false),
					false,
					theme_features::get_theme_info('version')
				);
				wp_enqueue_style(
					self::$iden . '-cropper',
					theme_features::get_theme_includes_css(__DIR__,'cropper',false),
					false,
					theme_features::get_theme_info('version')
				);
				break;
		}
	}
}
?>
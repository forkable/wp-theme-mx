<?php
/** 
 * @version 1.0.0
 */
add_filter('theme_includes',function($fns){
	$fns[] = 'theme_custom_follow::init';
	return $fns;
});
class theme_custom_follow{
	public static $iden = 'theme_custom_follow';
	public static $user_meta_key = array(
		'follower' => 'theme_follower',
		'following' => 'theme_following',
		'follower_count' => 'theme_follower_count',
		'following_count' => 'theme_following_count',
	);
	public static function init(){
		/** filter */
		//add_filter('frontend_seajs_alias',	__CLASS__ . '::frontend_seajs_alias');
		/** action */
		//add_action('frontend_seajs_use',	__CLASS__ . '::frontend_seajs_use');
		//add_action('wp_ajax_nopriv_theme_quick_sign', 'theme_quick_sign::process');
	}
	public static function get_count($args){
		$defaults = array(
			'user_id' => null,
			'type' => 'all',
		);
		$args = array_merge($defaults,$args);
		if(empty($args['user_id'])) return false;

		$key = self::$user_meta_key[$args['type'] . '_count'];
		return (int)get_user_meta($args['user_id'],$key,true);
	}
	private static function get_users($args){
		$defaults = array(
			'user_id' => null,
			'posts_per_page' => 100,
			'page' => 1,
			'type' => null, /** follower / following */
		);
		$args = array_merge($defaults,$args);
		$key = self::$user_meta_key[$args['type']];
		$metas = get_user_meta($args['user_id'],$key);
		if(is_null_array($metas)){
			return null;
		}else{
			$metas = asort($metas);
		}
		return self::get_page_data($metas,$args['posts_per_page'],$args['page']);
		
	}
	private static function get_page_data($metas,$posts_per_page,$page){
		if(is_null_array($meta)) return false;
		$count = count($meata);
		$max_page = ceil($count / $posts_per_page);
		if($page > $max_page) $page = $max_page;
		if($page < 1) $page = 1;
		$start = (($page - 1) * $posts_per_page) - 1;
		return array_slice($metas,$start,$posts_per_page);
	}

	public static function process(){
		$output = [];
		
		theme_features::check_referer();
		theme_features::check_nonce();
		
		$type = isset($_REQUEST['type']) ? $_REQUEST['type'] : null;
		$user_id = isset($_REQUEST['user-id']) ? (int)$_REQUEST['user-id'] : null;

		
		
		switch($type){
			case 'follow':
				$output['count'] = self::set_follow($user_id);
				$output['code'] = 'followed';
				$output['msg'] = ___('Follow success.');
				$output['status'] = 'success';
				break;
			case 'unfollow':
				$output['count'] = self::set_follow($user_id);
				$output['code'] = 'followed';
				$output['msg'] = ___('Unfollow success.');
				$output['status'] = 'success';
				break;
			default:
				$output['status'] = 'error';
				$output['code'] = 'unkown_param';
				$output['msg'] = ___('Unkown param.');
		}
		die(theme_features::json_format($output));
	}
	private static function set_follow($user_id){
		if($user_id == get_current_user_id()) return false;
		
		$target_metas = get_user_meta($user_id,self::$user_meta_key['following']);
		/**
		 * if already is follower, remove the follower and recount following count
		 */
		$old_target_follower_count = (int)get_user_meta($user_id,self::$user_meta_key['follower_count'],true);

		$old_my_following_count = (int)get_user_meta(get_current_user_id(),self::$user_meta_key['following'],true);
		
		if(!is_null_array($target_metas) && in_array(get_current_user_id(),$target_metas)){
			/** opera target user meta */
			delete_user_meta($user_id,self::$user_meta_key['follower'],get_current_user_id());
			update_user_meta($user_id,self::$user_meta_key['follower_count'],$old_target_follower_count - 1);
			/** opera current user meta */
			delete_user_meta(get_current_user_id(),self::$user_meta_key['following'],$user_id);
			update_user_meta(get_current_user_id(),self::$user_meta_key['following_count'],$old_my_following_count - 1);
			return array(
				'user_follower_count' => $old_target_follower_count - 1,
				'my_following_count' => $old_my_following_count - 1,
			);
		}else{
			/** opera target user meta */
			add_user_meta($user_id,$user_meta_key['follower'],get_current_user_id());
			update_user_meta($user_id,self::$user_meta_key['follower_count'],$old_target_follower_count + 1);
			/** opera current user meta */
			add_user_meta(get_current_user_id(),$user_meta_key['following'],get_current_user_id());
			update_user_meta(get_current_user_id(),self::$user_meta_key['following_count'],$old_my_following_count + 1);
			return array(
				'user_follower_count' => $old_target_follower_count + 1,
				'my_following_count' => $old_my_following_count + 1,
			);
		}
	}
	public static function frontend_seajs_alias($alias){
		if(is_user_logged_in() || !is_page(self::$page_slug)) return $alias;

		$alias[self::$iden] = theme_features::get_theme_includes_js(__DIR__);
		return $alias;
	}
	public static function frontend_seajs_use(){
		if(is_user_logged_in() || !is_page(self::$page_slug)) return false;
		?>
		seajs.use('<?= self::$iden;?>',function(m){
			m.config.process_url = '<?= theme_features::get_process_url(array('action' => theme_quick_sign::$iden));?>';
			m.config.lang.M00001 = '<?= esc_js(___('Loading, please wait...'));?>';
			m.config.lang.E00001 = '<?= esc_js(___('Sorry, server error please try again later.'));?>';
			
			m.init();
		});
		<?php
	}

}
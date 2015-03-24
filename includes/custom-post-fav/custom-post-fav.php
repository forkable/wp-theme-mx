<?php
/**
 * @version 1.0.0
 */
add_filter('custom_post_fav',function($fns){
	$fns[] = 'custom_post_fav::init';
	return $fns;
});
class custom_post_fav{
	public static $iden = 'custom_post_fav';
	public static $post_meta_key = [
		'users' 		=> 'fav_users',
		'conut' 		=> 'fav_count',
	];
	public static $user_meta_key = [
		'posts' 		=> 'fav_posts',
		'count' 		=> 'fav_count',
		'be_count' 		=> 'fav_be_count',
	];
	public static function init(){
		add_action('wp_ajax_' . self::$iden, get_class() . '::process');
	}
	public static function get_fav_posts(array $args = []){
		$defaults = [
			'user_id' => get_current_user_id(),
			'posts_per_page' => 10,
			'paged' => 1,
			'orderby' => 'desc',
			'order' => 'ID',
		];
		$args = wp_parse_args($args,$defaults);
		$cache_id = crc32(__FUNCTION__ . serialize($args));
		$caches = wp_cache_get($args['user_id'],self::$iden);
		if(isset($caches[$cache_id]))
			return $caches[$cache_id];

		/**
		 * get user fav post ids
		 */
		$post_ids = (array)get_user_meta($args['user_id'],self::$user_meta_key['posts'],true);
		if(empty($post_ids))
			return false;
		/**
		 * get posts from database
		 */
		global $wp_query,$post;
		$wp_query = new WP_Query([
			'posts_per_page' => (int)$args['posts_per_page'],
			'paged' => (int)$args['paged'],
			'post__in' => $post_ids,
			'orderby' => $args['orderby'],
			'order' => $args['order'],
		]);
		$caches[$cache_id] = $wp_query;
		wp_cache_set($args['user_id'],$caches,self::$iden,2505600);
		wp_reset_query();
		wp_reset_postdata();
		
		return $caches[$cache_id];
	}
	public static function get_fav_users(array $args = []){
		$defaults = [
			'post_id' => null,
			'number' => 10,
			'paged' => 1,
			'orderby' => 'ID',
		];
		$args = wp_parse_args($args,$defaults);
		$cache_id = crc32(__FUNCTION__ . serialize($args));
		$caches = wp_cache_get($args['post_id'],self::$iden);
		if(isset($caches[$cache_id]))
			return $caches[$cache_id];

		/**
		 * get user id 
		 */
		$user_ids = (array)get_post_meta($args['post_id'],self::$post_meta_key['users'],true);
		if(empty($user_ids))
			return false;

		$users = get_users([
			'include' => $user_ids,
			'number' => $args['number'],
			'orderby' => $args['orderby'],
		]);
		
		if($args['orderby'] === 'rand')
			shuffle($users);
		
		$caches[$cache_id] = $users;
		wp_cache_set($args['post_ids'],$caches,self::$iden,2505600);

		return $caches[$cache_id];
	}
	public static function get_post_fav_count($post_id){
		static $caches;
		if(isset($caches[$post_id]))
			return $caches[$post_id];

		$caches[$post_id] = (int)get_psot_meta($post_id,self::$post_meta_key['users'],true);
		return $caches[$post_id];
	}
	public static function get_user_fav_count($user_id){
		static $caches;
		if(isset($caches[$user_id]))
			return $caches[$user_id];

		$caches[$user_id] = (int)get_psot_meta($user_id,self::$user_meta_key['count'],true);
		return $caches[$user_id];
	}
	public static function get_user_be_fav_count($user_id){
		static $caches;
		if(isset($caches[$user_id]))
			return $caches[$user_id];

		$caches[$user_id] = (int)get_psot_meta($user_id,self::$user_meta_key['be_count'],true);
		return $caches[$user_id];
	}
	public static function process(){
		$output = [];

		theme_features::check_referer();
		theme_features::check_nonce();

		$type = isset($_GET['type']) ? $_GET['type'] : null;

		if($type !== 'incr' || $type !== 'decr'){
			$output['status'] = 'error';
			$output['code'] = 'invaild_type';
			$output['msg'] = ___('Invaild type param.');
			die(theme_features::json_format($output));
		}

		$post_id = isset($_GET['post-id']) && is_integer($_GET['post-id']) ? (int)$_GET['post-id'] : null;
		if(empty($post_id)){
			$output['status'] = 'error';
			$output['code'] = 'invaild_post_id';
			$output['msg'] = ___('Invaild post id param.');
			die(theme_features::json_format($output));
		}
		$post = get_post($post_id);
		if(empty($post))
			return [
				'status' => 'error',
				'code' => 'post_not_exist',
				'msg' => ___('Post does not exist.'),
			];
	

		switch($type){
			/**
			 * incr fav
			 */
			case 'incr':
				$incr = self::incr_post_fav_user($post_id,$post->post_author);
				if($incr === false){
					$output['status'] = 'error';
					$output['code'] = 'already_fav_user';
					$output['msg'] = ___('The post already in your favorites.');
					die(theme_features::json_format($output));
				}else{
					self::incr_post_fav_count($post_id);
					self::incr_user_be_fav_count($post->post_author);
					$output['status'] = 'success';
					$output['count'] = $incr;
					$output['msg'] = ___('The post has been added to your favorites.');
					die(theme_features::json_format($output));
				}
			/**
			 * decr fav
			 */
			case 'decr':
				$decr = self::incr_post_fav_user($post_id,$post->post_author);
				if($decr === false){
					$output['status'] = 'error';
					$output['code'] = 'not_fav_user';
					$output['msg'] = ___('The post is not in your favorites.');
					die(theme_features::json_format($output));
				}else{
					self::decr_post_fav_count($post_id);
					self::decr_user_be_fav_count($post->post_author);
					$output['status'] = 'success';
					$output['count'] = $decr;
					$output['msg'] = ___('The post has been removed from your favorites.');
					die(theme_features::json_format($output));
				}
			default:
				$output['status'] = 'error';
				$output['code'] = 'invaild_type';
				$output['msg'] = ___('Invaild type param.');
				die(theme_features::json_format($output));
		}
			

		die(theme_features::json_format($output));
	}

	/**
	 * 递增用户被收藏统计
	 *
	 * @param int $user_id
	 * @return bool/int
	 * @version 1.0.0
	 * @author Km.Van inn-studio.com <kmvan.com@gmail.com>
	 */
	public static function incr_user_be_fav_count($user_id){
		if(!(int)$user_id)
			return false;
		$old_be_count = (int)get_user_meta($user_id,self::$user_meta_key['be_count'],true);
		
		$old_be_count++;
		
		update_user_meta($user_id,self::$user_meta_key['be_count'],$old_be_count);
		return $old_be_count;
	}
	/**
	 * 递减用户被收藏统计
	 *
	 * @param int $user_id
	 * @return bool/int
	 * @version 1.0.0
	 * @author Km.Van inn-studio.com <kmvan.com@gmail.com>
	 */
	public static function decr_user_be_fav_count($user_id){
		if(!(int)$user_id)
			return false;
		$old_be_count = (int)get_user_meta($user_id,self::$user_meta_key['be_count'],true);
		
		$old_be_count--;
		
		update_user_meta($user_id,self::$user_meta_key['be_count'],$old_be_count);
		return $old_be_count;
	}
	/**
	 * 递增文章收藏统计
	 *
	 * @param int $post_id
	 * @return bool/int
	 * @version 1.0.0
	 * @author Km.Van inn-studio.com <kmvan.com@gmail.com>
	 */
	public static function incr_post_fav_count($post_id){
		if(!(int)$post_id)
			return false;
		$old_post_count = (int)get_post_meta($post_id,self::$post_meta_key['count'],true);
		
		$old_post_count++;
		
		update_post_meta($post_id,self::$post_meta_key['users'],$old_post_count);
		return $old_post_count;
	}
	/**
	 * 递减文章收藏统计
	 *
	 * @param int $post_id
	 * @return bool/int
	 * @version 1.0.0
	 * @author Km.Van inn-studio.com <kmvan.com@gmail.com>
	 */
	public static function decr_post_fav_count($post_id){
		if(!(int)$post_id)
			return false;
		$old_post_count = (int)get_post_meta($post_id,self::$post_meta_key['count'],true);
		
		$old_post_count--;
		
		update_post_meta($post_id,self::$post_meta_key['users'],$old_post_count);
		return $old_post_count;
	}
	/**
	 * 递增收藏该文章的用户
	 *
	 * @param int/object $post_id Post object or post id
	 * @param int/object $user_id User object or user id
	 * @return 
	 * @version 1.0.0
	 * @author Km.Van inn-studio.com <kmvan.com@gmail.com>
	 */
	public static function incr_post_fav_user($post_id,$user_id){
		if(is_object($post_id))
			$post_id = $post_id->ID;
			
		if(is_object($user_id))
			$user_id = $user_id->ID;
			
		$old_post_users = (array)get_post_meta($post_id,self::$post_meta_key['users'],true);
		/**
		 * already fav, return false
		 */
		if(isset($old_post_users[$user_id]))
			return false;

		/**
		 * if new fav user, just add
		 */
		$old_post_users[$user_id] = $user_id;
		update_post_meta($post_id,self::$post_meta_key['users'],$old_post_users);
		/**
		 * return new count
		 */
		return count($old_post_users);
	}
	/**
	 * 递减收藏该文章的用户
	 *
	 * @param int/object $post_id Post object or post id
	 * @param int/object $user_id User object or user id
	 * @return 
	 * @version 1.0.0
	 * @author Km.Van inn-studio.com <kmvan.com@gmail.com>
	 */
	public static function decr_post_fav_user($post_id,$user_id){
		if(is_object($post_id))
			$post_id = $post_id->ID;
			
		if(is_object($user_id))
			$user_id = $user_id->ID;
			
		$old_post_users = (array)get_post_meta($post_id,self::$post_meta_key['users'],true);
		/**
		 * if is new fav user, do not remove
		 */
		if(!isset($old_post_users[$user_id]))
			return false;

		/**
		 * if already exist, just remove
		 */
		
		unset($old_post_users[$user_id]);
		update_post_meta($post_id,self::$post_meta_key['users'],$old_post_users);
		/**
		 * return new count
		 */
		return count($old_post_users);
	}

}
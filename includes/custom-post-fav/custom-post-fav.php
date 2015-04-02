<?php
/**
 * @version 1.0.0
 */
add_filter('theme_includes',function($fns){
	$fns[] = 'custom_post_fav::init';
	return $fns;
});
class custom_post_fav{
	public static $iden = 'custom_post_fav';
	public static $post_meta_key = [
		'users' 		=> '_fav_users',
		'conut' 		=> '_fav_count',
	];
	public static $user_meta_key = [
		'posts' 		=> 'fav_posts',
		'count' 		=> 'fav_count',
		'be_count' 		=> 'fav_be_count',
	];
	public static function init(){
		add_action('wp_ajax_' . self::$iden, __CLASS__ . '::process');

		add_action('before_delete_post',__CLASS__ . '::sync_delete_post');

		add_filter('frontend_seajs_alias',__CLASS__ . '::frontend_seajs_alias');
		add_action('frontend_seajs_use',__CLASS__ . '::frontend_seajs_use');

	}
	public static function sync_delete_post($post_id){
		$post = get_post($post_id);
		if(!$post)
			return;
		$post_fav_users = (array)self::get_post_fav_users($post_id);
		if(!empty($post_fav_users)){
			foreach($post_fav_users as $user_id){
				/**
				 * delete user meta
				 */
				self::decr_user_be_fav_count($user_id);
			}
		}
		
	}
	/**
	 * 随机在总量中获取被收藏最多的文章排行（最受欢迎的文章）
	 *
	 * @param array $args
	 * @return array
	 * @version 1.0.0
	 * @author Km.Van inn-studio.com <kmvan.com@gmail.com>
	 */
	public static function get_most_fav_posts(array $args = []){
		$defaults = [
			'total_number' => 20,
			'posts_per_page' => 10,
			'paged' => 1,
			'orderby' => 'desc',
			'expire' => 3600*24,
		];
		$args = wp_parse_args($args,$defaults);
		$cache_id = crc32(serialize($args));
		$caches = wp_cache_get('most_fav_posts',self::$iden);
		if(isset($caches[$cache_id]))
			return $caches[$cache_id];

		/**
		 * get posts from database
		 */
		$query = new WP_Query([
			'posts_per_page' => (int)$args['posts_per_page'],
			'paged' => (int)$args['paged'],
			'meta_key' => self::$post_meta_key('count'),
			'orderby' => 'meta_value_num',
			'order' => 'DESC',
		]);
		if($args['orderby'] === 'rand' && !empty($query)){
			$rand_post_ids = [];
			while($query->have_posts()){
				$query->the_posts();
				$rand_post_ids[] = $post->ID;
			}
			wp_reset_postdata();
			/**
			 * rand query
			 */
			$query = new WP_Query([
				'posts_per_page' => (int)$args['posts_per_page'],
				'paged' => (int)$args['paged'],
				'post__in' => $rand_post_ids,
				'orderby' => 'rand',
			]);
			
		}
		
		$caches[$cache_id] = $query;
		wp_cache_set('most_fav_posts',$caches,self::$iden,$args['expire']);
		return $caches[$cache_id];
	}
	/**
	 * 随机在总量中获取被收藏最多的用户排行
	 *
	 * @param array $args
	 * @return array
	 * @version 1.0.0
	 * @author Km.Van inn-studio.com <kmvan.com@gmail.com>
	 */
	public static function get_most_fav_users(array $args = []){
		$defaults = [
			'total_number' => 20,
			'display_number' => 10,
			'paged' => 1,
			'orderby' => 'ID',
			'expire' => 3600*24,
		];
		$args = wp_parse_args($args,$defaults);
		$cache_id = crc32(serialize($args));
		$caches = wp_cache_get('most_fav_users',self::$iden);
		if(isset($caches[$cache_id]))
			return $caches[$cache_id];

		$users = get_users([
			'include' => $user_ids,
			'meta_key' => self::$user_meta_key['be_count'],
			'number' => $args['total_number'],
			'orderby' => 'meta_value',
			'order' => 'DESC',
		]);
		
		if($args['orderby'] === 'rand' && $args['total_number'] > $args['display_number']){
			shuffle($users);
			$users = array_slice($users,0,(int)$args['display_number']);
		}
		
		$caches[$cache_id] = $users;
		wp_cache_set('most_fav_users',$caches,self::$iden,$args['expire']);

		return $caches[$cache_id];
	}
	/**
	 * 获取文章被收藏的统计数量
	 *
	 * @param int $post_id
	 * @return int
	 * @version 1.0.0
	 * @author Km.Van inn-studio.com <kmvan.com@gmail.com>
	 */
	public static function get_post_fav_count($post_id){
		static $caches;
		if(isset($caches[$post_id]))
			return $caches[$post_id];

		$caches[$post_id] = count(get_psot_meta($post_id,self::$post_meta_key['users'],true));
		return $caches[$post_id];
	}
	/**
	 * 获取用户收藏的统计数量
	 *
	 * @param int $user_id
	 * @return int
	 * @version 1.0.0
	 * @author Km.Van inn-studio.com <kmvan.com@gmail.com>
	 */
	public static function get_user_fav_count($user_id){
		static $caches;
		if(isset($caches[$user_id]))
			return $caches[$user_id];
		$caches[$user_id] = count(get_user_meta($user_id,self::$user_meta_key['posts'],true));
		return $caches[$user_id];
	}
	/**
	 * 获取用户被收藏的统计数量
	 *
	 * @param int $user_id
	 * @return int
	 * @version 1.0.0
	 * @author Km.Van inn-studio.com <kmvan.com@gmail.com>
	 */
	public static function get_user_be_fav_count($user_id){
		static $caches;
		if(isset($caches[$user_id]))
			return $caches[$user_id];

		$caches[$user_id] = (int)get_psot_meta($user_id,self::$user_meta_key['be_count'],true);
		return $caches[$user_id];
	}
	public static function get_post_fav_users($user_id){
		static $caches;
		if(isset($caches[$user_id]))
			return $caches[$user_id];
		$caches[$user_id] = (array)get_psot_meta($user_id,self::$post_meta_key['users'],true);
		return $caches[$user_id];
	}
	/**
	 * 获取用户收藏的文章
	 *
	 * @param int $user_id
	 * @param array $query_args
	 * @return array
	 * @version 1.0.0
	 * @author Km.Van inn-studio.com <kmvan.com@gmail.com>
	 */
	public static function get_user_fav_posts($user_id,array $query_args = []){
		static $caches;
		$defaults = [
			'posts_per_page' => 10,
			'paged' => 1,
		];
		$query_args = wp_parse_args($query_args,$defaults);		$cache_id = crc32($user_id . serialize($query_args));
		
		if(isset($caches[$cache_id]))
			return $caches[$cache_id];
			
		$post_ids = (array)get_psot_meta($user_id,self::$user_meta_key['posts'],true);
		
		if(empty($post_ids)){
			$caches[$cache_id] = false;
			return false;
		}
		
		$query_args['post__in'] = $post_ids;

		$caches[$cache_id] = new WP_Query($query_args);
		return $caches[$cache_id];
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
	public static function frontend_seajs_alias(array $alias =[]){
		if(!is_singular('post'))
			return $alias;
			
		$alias[self::$iden] = theme_features::get_theme_includes_js(__DIR__);
		return $alias;
	}
	public static function frontend_seajs_use(){
		//if(!is_singular('post'))
		//	return;
		//global $post;
		?>
		seajs.use(['<?php echo self::$iden;?>'],function(m){
			m.config.lang.M00001 = '<?php echo ___('Loading, please wait...');?>';
			m.config.lang.E00001 = '<?php echo ___('Server error.');?>';
			m.process_url = '<?php echo theme_features::get_process_url([
				'action' => self::$iden,
				'type' => 'add'
			]);?>';
			m.init();
		});
		<?php
	}
}
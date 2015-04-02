<?php
/**
 * @version 1.0.0
 */
add_filter('theme_includes',function($fns){
	$fns[] = 'custom_post_point::init';
	return $fns;
});
class custom_post_point{
	public static $iden = 'custom_post_point';
	public static $post_meta_key = [
		'users'				=> '_point_givers',
		'count_users' 		=> '_point_count_givers',
		'count_points' 		=> '_point_count_points',
	];
	public static $user_meta_key = [
		'posts' 		=> '_point_posts',
	];
	public static function init(){
		add_action('wp_ajax_' . self::$iden, __CLASS__ . '::process');

		add_action('before_delete_post',__CLASS__ . '::sync_delete_post');

		add_filter('frontend_seajs_alias',__CLASS__ . '::frontend_seajs_alias');
		add_action('frontend_seajs_use',__CLASS__ . '::frontend_seajs_use');

		add_filter('custom-point-options-default',__CLASS__ . '::filter_custom_point_options_default');

		add_filter('custom-point-types',__CLASS__ . '::filter_custom_point_types');
	}
	public static function sync_delete_post($post_id){
		$post = get_post($post_id);
		if(!$post)
			return;
		$post_point_users = (array)self::get_post_point_users($post_id);
		if(!empty($post_point_users)){
			foreach($post_point_users as $user_id){
				/**
				 * delete user meta
				 */
				self::decr_user_point_count($user_id);
			}
		}
		
	}
	/**
	 * 随机在总量中获取被投币最多的文章排行（最受欢迎的文章）
	 *
	 * @param array $args
	 * @return array
	 * @version 1.0.0
	 * @author Km.Van inn-studio.com <kmvan.com@gmail.com>
	 */
	public static function get_most_point_posts(array $args = []){
		$defaults = [
			'total_number' => 20,
			'posts_per_page' => 10,
			'paged' => 1,
			'orderby' => 'desc',
			'expire' => 3600*24,
		];
		$args = wp_parse_args($args,$defaults);
		$cache_id = crc32(serialize($args));
		$caches = wp_cache_get('most_point_posts',self::$iden);
		if(isset($caches[$cache_id]))
			return $caches[$cache_id];

		/**
		 * get posts from database
		 */
		$query = new WP_Query([
			'posts_per_page' => (int)$args['posts_per_page'],
			'paged' => (int)$args['paged'],
			'meta_key' => self::$post_meta_key['count_users'],
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
		wp_reset_query();
		wp_cache_set('most_point_posts',$caches,self::$iden,$args['expire']);
		return $caches[$cache_id];
	}

	/**
	 * 获取文章被投币的统计数量
	 *
	 * @param int $post_id
	 * @return int
	 * @version 1.0.0
	 * @author Km.Van inn-studio.com <kmvan.com@gmail.com>
	 */
	public static function get_post_points_count($post_id){
		static $caches = [];
		if(isset($caches[$post_id]))
			return $caches[$post_id];

		$users = (array)self::get_psot_meta($post_id,self::$post_meta_key['users']);

		$caches[$post_id] = 0;
		foreach($users as $v){
			$caches[$post_id] += $v;
		}
		
		return $caches[$post_id];
	}
	public static function get_post_givers_count($post_id){
		static $caches = [];
		if(isset($caches[$post_id]))
			return $caches[$post_id];

		$users = (array)self::get_psot_meta($post_id,self::$post_meta_key['users']);

		$caches[$post_id] = 0;
		foreach($users as $v){
			$caches[$post_id] += $v;
		}
		
		return $caches[$post_id];
	}
	private static function get_post_meta($post_id,$meta_key){
		static $caches = [];
		$cache_id = crc32($post_id . $meta_key);
		if(isset($caches[$cache_id]))
			return $caches[$cache_id];

		$caches[$post_id] = get_post_meta($post_id,$meta_key,true);
		return $caches[$post_id];
	}
	/**
	 * 获取文章的投币用户
	 *
	 * @param int $post_id
	 * @return array Users
	 * @version 1.0.0
	 * @author Km.Van inn-studio.com <kmvan.com@gmail.com>
	 */
	public static function get_post_givers($post_id){
		static $caches;
		if(isset($caches[$post_id]))
			return $caches[$post_id];
		$caches[$post_id] = (array)self::get_post_meta($post_id,self::$post_meta_key['users']);
		return $caches[$post_id];
	}
	/**
	 * 获取用户投币过的文章
	 *
	 * @param int $user_id
	 * @param array $query_args
	 * @return array
	 * @version 1.0.0
	 * @author Km.Van inn-studio.com <kmvan.com@gmail.com>
	 */
	public static function get_user_posts($user_id,array $query_args = []){
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
	public static function filter_custom_point_types(array $types = []){
		$types['post-swap'] = [
			'text' => ___('When post point swap')
		];
		return $types;
	}
	public static function filter_custom_point_options_default(array $opts = []){
		$opts['points']['post-swap'] = '1,3,5';
		return $opts;
	}
	public static function get_point_values(){
		static $cache = null;
		$values = explode(',',custom_point::get_point_value('post-swap'));

		return array_map(function($v){
			if(is_int($v))
				return $v;
		},$values);
	}
	public static function process(){
		$output = [];

		theme_features::check_referer();
		theme_features::check_nonce();

		$type = isset($_GET['type']) ? $_GET['type'] : null;

		$post_id = isset($_GET['post-id']) && is_int($_GET['post-id']) ? $_GET['post-id'] : null;
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
			
		$giver_id = get_current_user_id();

		switch($type){
			/**
			 * incr point
			 */
			case 'incr':
				/**
				 * points
				 */
				$points = isset($_GET['points']) && is_int($_GET['points']) ? $_GET['points'] : null;
				if(!in_array($points,self::get_point_values())){
					$output['status'] = 'error';
					$output['code'] = 'invaild_point_value';
					$output['msg'] = ___('Invaild point value.');
					die(theme_features::json_format($output));
				}
				/**
				 * incr post givers
				 */
				$post_givers = self::incr_post_givers($post_id,$giver_id,$points);
				if(!$post_givers){
					$output['status'] = 'error';
					$output['code'] = 'error_incr_post_givers';
					$output['msg'] = ___('System can not increase post givers');
					die(theme_features::json_format($output));
				}else{
					/**
					 * incr post points
					 */
					$points_count = self::incr_post_points_count($post_id,$giver_id,$points);
					if(!$points_count){
						$output['status'] = 'error';
						$output['code'] = 'error_incr_points_count';
						$output['msg'] = ___('System can not increase post points count.');
						die(theme_features::json_format($output));
					}
					/**
					 * incr giver psots
					 */
					$giver_posts = self::incr_giver_posts($post_id,$giver_id,$points);
					if(!$giver_posts){
						$output['status'] = 'error';
						$output['code'] = 'error_incr_giver_posts';
						$output['msg'] = ___('System can not increase giver posts.');
						die(theme_features::json_format($output));
					}
					/**
					 * success
					 */
					$output['status'] = 'success';
					$output['points'] = self::get_post_points_count($post_id);
					$output['msg'] = ___('Operation completed.');
					die(theme_features::json_format($output));
				}
				break;
			default:
				$output['status'] = 'error';
				$output['code'] = 'invaild_type';
				$output['msg'] = ___('Invaild type param.');
				die(theme_features::json_format($output));
		}
			

		die(theme_features::json_format($output));
	}
	/**
	 * 递增文章积分统计
	 *
	 * @param int $post_id
	 * @param int $points
	 * @return bool/int
	 * @version 1.0.0
	 * @author Km.Van inn-studio.com <kmvan.com@gmail.com>
	 */
	public static function incr_post_points_count($post_id,$points){
		if(!is_int($post_id) || (int)$post_id)
			return false;

		if(!is_int($points) || (int)$points < 0)
			return false;
			
		$count = (int)get_post_meta($post_id,self::$post_meta_key['count_points'],true);
		
		$count += $points;
		
		update_post_meta($post_id,self::$post_meta_key['count_points'],$count);
		return $count;
	}
	/**
	 * 递增文章用户统计
	 *
	 * @param int $post_id
	 * @return bool/int
	 * @version 1.0.0
	 * @author Km.Van inn-studio.com <kmvan.com@gmail.com>
	 */
	public static function incr_post_givers_count($post_id){
		if(!is_int($post_id) || !(int)$post_id)
			return false;
			
		$count = (int)get_post_meta($post_id,self::$post_meta_key['count_users'],true);
		
		$count++;
		
		update_post_meta($post_id,self::$post_meta_key['count_users'],$count);
		return $count;
	}
	/**
	 * 递减文章用户统计
	 *
	 * @param int $post_id
	 * @return bool/int
	 * @version 1.0.0
	 * @author Km.Van inn-studio.com <kmvan.com@gmail.com>
	 */
	public static function decr_post_givers_count($post_id){
		if(!is_int($post_id) || !(int)$post_id)
			return false;
			
		$count = (int)get_post_meta($post_id,self::$post_meta_key['count_users'],true);

		if($count === 0)
			return false;
			
		$count--;
		
		update_post_meta($post_id,self::$post_meta_key['count_users'],$count);
		return $count;
	}
	/**
	 * 递增文章的送分用户
	 *
	 * @param int post id
	 * @param int user id
	 * @param int points
	 * @return 
	 * @version 1.0.0
	 * @author Km.Van inn-studio.com <kmvan.com@gmail.com>
	 */
	public static function incr_post_givers($post_id,$giver_id,$points){
			
		if(!is_int($post_id) || $post_id <= 0)
			return false;
			
		if(!is_int($giver_id) || $giver_id <= 0)
			return false;

		if(!is_int($points) || $points <= 0)
			return false;

		$post = get_post($post_id);
		if(!$post)
			return false;

		$giver = get_user_by('ID',$giver_id);
		if(!$giver)
			return false;

			
		$givers = (array)get_post_meta($post_id,self::$post_meta_key['users'],true);
		/**
		 * already point, return false
		 */
		if(isset($givers[$user_id]))
			return false;

	
		$givers[$user_id] = $points;
		update_post_meta($post_id,self::$post_meta_key['users'],$givers);
		
		return $givers;
	}
	/**
	 * 递增投币用户的文章
	 *
	 * @param int $post_id Post id
	 * @param int $user_id Giver id
	 * @param int $points $points
	 * @return 
	 * @version 1.0.0
	 * @author Km.Van inn-studio.com <kmvan.com@gmail.com>
	 */
	public static function incr_giver_posts($post_id,$giver_id,$points){
		if(!is_int($post_id) || $post_id <= 0)
			return false;
			
		if(!is_int($giver_id)|| $giver_id <= 0)
			return false;

		if(!is_int($points) || $points <= 0)
			return false;
			
		$post = get_post($post_id);
		if(!$post)
			return false;

		$giver = get_user_by('ID',$giver_id);
		if(!$giver)
			return false;
			
		$posts = (array)get_user_meta($giver_id,self::$user_meta_key['posts'],true);
		
		if(isset($posts[$post_id]))
			return false;

		$posts[$post_id] = $points;
		update_user_meta($giver_id,self::$user_meta_key['posts'],$posts);

		return $posts;
	}
	/**
	 * 递减投币该文章的用户
	 *
	 * @return 
	 * @version 1.0.0
	 * @author Km.Van inn-studio.com <kmvan.com@gmail.com>
	 */
	public static function decr_post_givers($post_id,$giver_id){
		if(!is_int($post_id) || $post_id <= 0)
			return false;
			
		if(!is_int($giver_id) || $giver_id <= 0)
			return false;
			

		$givers = (array)get_post_meta($psot_id,self::$post_meta_key['users'],true);
		
		/**
		 * if is new point user, do not remove
		 */
		if(!isset($givers[$user_id]))
			return false;

		/**
		 * if already exist, just remove
		 */
		unset($givers[$user_id]);
		update_post_meta($post_id,self::$post_meta_key['users'],$givers);
		
		return $givers;
	}
	public static function decr_giver_posts($post_id,$giver_id){
		if(!is_int($post_id) || $post_id <= 0)
			return false;
			
		if(!is_int($giver_id) || $giver_id <= 0)
			return false;

		$posts = (array)get_user_meta($giver_id,self::$user_meta_key['posts'],true);

		if(!isset($posts[$post_id]))
			return false;

		unset($posts[$post_id]);
		update_user_meta($giver_id,self::$user_meta_key['posts'],$posts);

		return $posts;
	}
	public static function frontend_seajs_alias(array $alias =[]){
		if(!is_singular('post'))
			return $alias;
			
		$alias[self::$iden] = theme_features::get_theme_includes_js(__DIR__);
		return $alias;
	}
	public static function frontend_seajs_use(){
		if(!is_singular('post'))
			return;
		?>
		seajs.use(['<?php echo self::$iden;?>'],function(m){
			m.config.lang.M00001 = '<?php echo ___('Loading, please wait...');?>';
			m.config.lang.E00001 = '<?php echo ___('Server error.');?>';
			m.process_url = '<?php echo theme_features::get_process_url([
				'action' => self::$iden,
				'type' => 'incr'
			]);?>';
			m.init();
		});
		<?php
	}
}
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
		'users'				=> '_point_raters',
		'count_users' 		=> '_point_count_raters',
		'count_points' 		=> '_point_count_points',
	];
	public static $user_meta_key = [
		'posts' 		=> '_point_posts',
	];
	public static $error = [];
	
	public static function init(){
		add_action('wp_ajax_' . self::$iden, __CLASS__ . '::process');
		add_action('wp_ajax_nopriv_' . self::$iden, __CLASS__ . '::process');

		add_action('before_delete_post',__CLASS__ . '::sync_delete_post');

		add_filter('frontend_seajs_alias',__CLASS__ . '::frontend_seajs_alias');
		add_action('frontend_seajs_use',__CLASS__ . '::frontend_seajs_use');

		add_filter('custom-point-options-default',__CLASS__ . '::filter_custom_point_options_default');

		add_filter('custom-point-types',__CLASS__ . '::filter_custom_point_types');


		/**
		 * list history hooks
		 */
		foreach([
			'list_history_post_rate',
			'list_history_post_be_rate'
		] as $v)
			add_action('list_point_histroy',__CLASS__ . '::' . $v);
	}
	public static function sync_delete_post($post_id){
		$post = get_post($post_id);
		if(!$post)
			return;
			
		if($post->post_type !== 'post')
			return;
			
		$rater_ids = (array)self::get_post_raters($post_id);
		if(!empty($raters)){
			foreach($rater_ids as $rater_id){
				
				self::decr_post_raters($post_id,$rater_id);
				self::decr_post_raters_count($post_id);
				self::decr_rater_posts($post_id,$rater_id);
			}
		}
		
	}
	//public static function decr_user_point_count()
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
		$args = array_merge($defaults,$args);
		$cache_id = md5(serialize(func_get_args()));
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
			foreach($query->posts as $post){
				setup_postdata($post);
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
		//wp_reset_postdata();
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

		$users = (array)self::get_post_meta($post_id,self::$post_meta_key['users']);

		$caches[$post_id] = 0;
		foreach($users as $v){
			$caches[$post_id] += $v;
		}
		
		return $caches[$post_id];
	}
	public static function get_post_raters_count($post_id){
		static $caches = [];
		if(isset($caches[$post_id]))
			return $caches[$post_id];

		$users = (array)self::get_post_meta($post_id,self::$post_meta_key['users']);

		$caches[$post_id] = 0;
		foreach($users as $v){
			$caches[$post_id] += $v;
		}
		
		return $caches[$post_id];
	}
	private static function get_post_meta($post_id,$meta_key){
		static $caches = [];
		$cache_id = md5($post_id . $meta_key);
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
	public static function get_post_raters($post_id){
		if(!is_numeric($post_id))
			return false;
			
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
		$query_args = array_merge($defaults,$query_args);
		$cache_id = md5(serialize(func_get_args()));
		
		if(isset($caches[$cache_id]))
			return $caches[$cache_id];
			
		$post_ids = (array)get_post_meta($user_id,self::$user_meta_key['posts'],true);
		
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
			'text' => ___('When post point swap'),
			'des' => ___('Use commas to separate multiple point, first as the default.'),
		];
		return $types;
	}
	public static function filter_custom_point_options_default(array $opts = []){
		$opts['points']['post-swap'] = '1,3,5';
		return $opts;
	}
	public static function get_point_values(){
		static $cache = null;

		if($cache !== null)
			return $cache;
			
		$values = explode(',',theme_custom_point::get_point_value('post-swap'));
		
		$cache = array_map(function($v){
			if(is_string($v))
				return (int)$v;
		},$values);
		
		return $cache;
	}
	public static function process(){
		$output = [];

		theme_features::check_referer();
		theme_features::check_nonce();

		
		$type = isset($_GET['type']) && is_string($_GET['type']) ? $_GET['type'] : null;

		$post_id = isset($_POST['post-id']) && is_string($_POST['post-id']) ? (int)$_POST['post-id'] : null;
		if(empty($post_id)){
			$output['status'] = 'error';
			$output['code'] = 'invaild_post_id';
			$output['msg'] = ___('Invaild post id param.');
			die(theme_features::json_format($output));
		}
		
		$post = get_post($post_id);
		if(empty($post) || $post->post_type !== 'post')
			die(theme_features::json_format([
				'status' => 'error',
				'code' => 'post_not_exist',
				'msg' => ___('Post does not exist.'),
			]));
		/**
		 * check user logged
		 */
		if(!is_user_logged_in()){
			$output['status'] = 'error';
			$output['code'] = 'need_login';
			$output['msg'] = '<a href="' . wp_login_url(get_permalink($post->ID)) . '" title="' . ___('Go to log-in') . '">' . ___('Sorry, please log-in.') . '</a>';
			die(theme_features::json_format($output));
		}
		
		$rater_id = (int)get_current_user_id();

		switch($type){
			/**
			 * incr point
			 */
			case 'incr':
				/**
				 * points
				 */
				$points = isset($_POST['points']) && is_string($_POST['points']) ? (int)$_POST['points'] : null;
				if(!in_array($points,self::get_point_values())){
					$output['status'] = 'error';
					$output['code'] = 'invaild_point_value';
					$output['msg'] = ___('Invaild point value.');
					die(theme_features::json_format($output));
				}
				/**
				 * incr post raters
				 */
				$post_raters = self::incr_post_raters($post_id,$rater_id,$points);
				
				if($post_raters !== true){
					die(theme_features::json_format($post_raters));
				}else{
					/**
					 * incr post points
					 */
					$points_count = self::incr_post_points_count($post_id,$rater_id,$points);
					if(!$points_count){
						$output['status'] = 'error';
						$output['code'] = 'error_incr_points_count';
						$output['msg'] = ___('Sorry, system can not increase post points count.');
						die(theme_features::json_format($output));
					}
					/**
					 * incr rater posts
					 */
					$rater_posts = self::incr_rater_posts($post_id,$rater_id,$points);
					if($rater_posts !== true){
						$output['status'] = 'error';
						$output['code'] = 'error_incr_rater_posts';
						$output['msg'] = ___('System can not increase rater posts.');
						die(theme_features::json_format($output));
					}
					/**
					 * increase post author points
					 */
					theme_custom_point::incr_user_points($post->post_author,$points);
					/**
					 * add point history for rater
					 */
					self::add_history_for_rater($post_id,$rater_id,$points);
					/**
					 * add point history for post author
					 */
					self::add_history_for_post_author($post_id,$rater_id,$points);
					/**
					 * decrease rater points
					 */
					theme_custom_point::decr_user_points($rater_id,$points);
					
					/**
					 * success
					 */
					$output['status'] = 'success';
					$output['points'] = (int)self::get_post_points_count($post_id);
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
	public static function get_post($post_id){
		static $post = null;
		if($post === null)
			$post = get_post($post_id);

		return $post;
	}
	public static function list_history_post_rate($history){
		if($history['type'] !== 'post-rate')
			return false;
		?>
		<li class="list-group-item">
			<span class="point-name"><?= theme_custom_point::get_point_name();?></span>
			<?php theme_custom_point::the_point_sign($history['points']);?>
			
			<span class="history-text">
				<?php
				$post = get_post($history['post-id']);
				if(!$post){
					echo ___('The post has been deleted.');
				}else{
					echo sprintf(
						___('You rated %1$d %2$s for the post %3$s.'),
						abs($history['points']),
						theme_custom_point::get_point_name(),
						'<a href="' . esc_url(get_permalink($history['post-id'])) . '">' . esc_html(get_the_title($history['post-id'])) . '</a>'
					);
				}
				?>
			</span>
			
			<?php theme_custom_point::the_history_time($history);?>
		</li>
		<?php
	}
	public static function list_history_post_be_rate($history){
		if($history['type'] !== 'post-be-rate')
			return false;
		?>
		<li class="list-group-item">
			<span class="point-name"><?= theme_custom_point::get_point_name();?></span>
			<?php theme_custom_point::the_point_sign($history['points']);?>
			
			<span class="history-text">
				<?php
				$post = get_post($history['post-id']);
				if(!$post){
					echo ___('The post has been deleted.');
				}else{
					echo sprintf(
						___('You post %1$s has been rated %2$d %3$s by %4$s.'),
						'<a href="' . esc_url(get_permalink($history['post-id'])) . '">' . esc_html(get_the_title($history['post-id'])) . '</a>',
						abs($history['points']),
						theme_custom_point::get_point_name(),
						esc_html(get_author_meta('display_name',$history['rater-id']))
					);
				}
				?>
			</span>
			
			<?php theme_custom_point::the_history_time($history);?>
		</li>
		<?php
	}
	
	public static function get_timestamp(){
		static $t = null;
		if($t === null)
			$t = current_time('timestamp');
		return $t;
	}
	public static function add_history_for_rater($post_id,$rater_id,$points){

		$meta = [
			'type'=> 'post-rate',
			'timestamp' => self::get_timestamp(),
			'post-id' => $post_id,
			'points' => 0 - $points,
		];
		add_user_meta($rater_id,theme_custom_point::$user_meta_key['history'],$meta);
		
	}
	public static function add_history_for_post_author($post_id,$rater_id,$points){

		$meta = [
			'type'=> 'post-be-rated',
			'timestamp' => self::get_timestamp(),
			'rater-id' => $rater_id,
			'post-id' => $post_id,
			'points' => $points,
		];
		add_user_meta(self::get_post($post_id)->post_author,theme_custom_point::$user_meta_key['history'],$meta);
		
	}
	public static function set_error($error){
		if(is_array($error))
			self::$error = $error;
		return $error;
	}
	//public static function get_error()
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
		if(!is_numeric($post_id) || (int)$post_id === 0)
			return [
				'status' => 'error',
				'code' => 'invaild_post_id',
				'msg' => ___('Invaild post id.'),
			];

		if(!is_numeric($points) || (int)$points === 0)
			return [
				'status' => 'error',
				'code' => 'invaild_point_value',
				'msg' => ___('Invaild points value.'),
			];
			
		$count = (int)get_post_meta($post_id,self::$post_meta_key['count_points'],true);
		
		$count += $points;
		
		update_post_meta($post_id,self::$post_meta_key['count_points'],$count);
		return true;
	}
	/**
	 * 递增文章用户统计
	 *
	 * @param int $post_id
	 * @return bool/int
	 * @version 1.0.0
	 * @author Km.Van inn-studio.com <kmvan.com@gmail.com>
	 */
	public static function incr_post_raters_count($post_id){
		if(!is_numeric($post_id) || !(int)$post_id)
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
	public static function decr_post_raters_count($post_id){
		if(!is_numeric($post_id) || !(int)$post_id)
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
	 * @return bool/array True is success, array is error
	 * @version 1.0.0
	 * @author Km.Van inn-studio.com <kmvan.com@gmail.com>
	 */
	public static function incr_post_raters($post_id,$rater_id,$points){
			
		if(!is_numeric($post_id) || (int)$post_id === 0)
			return [
				'status' => 'error',
				'code' => 'invaild_post_id',
				'msg' => ___('Invaild post id.'),
			];
			
		if(!is_numeric($rater_id) || (int)$rater_id === 0)
			return [
				'status' => 'error',
				'code' => 'invaild_rater_id',
				'msg' => ___('Invaild rater id.'),
			];

		if(!is_numeric($points) || (int)$points === 0)
			return [
				'status' => 'error',
				'code' => 'invaild_points',
				'msg' => ___('Invaild points.'),
			];

		$post = get_post($post_id);
		if(!$post)
			return [
				'status' => 'error',
				'code' => 'post_not_exist',
				'msg' => ___('Post is not exist.'),
			];
		/**
		 * if post author is rater, return
		 */
		if($post->post_author == $rater_id)
			return [
				'status' => 'error',
				'code' => 'rate_myself',
				'msg' => ___('Sorry, you can not rate your post.'),
			];
		
		$rater = get_user_by('id',$rater_id);
		if(!$rater)
			return [
				'status' => 'error',
				'code' => 'rater_not_exist',
				'msg' => ___('Rater is not exist.'),
			];

			
		$raters = (array)get_post_meta($post_id,self::$post_meta_key['users'],true);
		/**
		 * already point, return false
		 */
		if(isset($raters[$rater_id]))
			return [
				'status' => 'error',
				'code' => 'rated',
				'msg' => ___('You had rated this post.'),
			];

	
		$raters[$rater_id] = $points;
		update_post_meta($post_id,self::$post_meta_key['users'],$raters);
		
		return true;
	}
	/**
	 * 递增投币用户的文章
	 *
	 * @param int $post_id Post id
	 * @param int $user_id rater id
	 * @param int $points $points
	 * @return 
	 * @version 1.0.0
	 * @author Km.Van inn-studio.com <kmvan.com@gmail.com>
	 */
	public static function incr_rater_posts($post_id,$rater_id,$points){
		if(!is_numeric($post_id) || (int)$post_id === 0)
			return [
				'status' => 'error',
				'code' => 'invaild_post_id',
				'msg' => ___('Invaild post id.'),
			];

		if(!is_numeric($rater_id) || (int)$rater_id === 0)
			return [
				'status' => 'error',
				'code' => 'invaild_rater_id',
				'msg' => ___('Invaild rater id.'),
			];
			
		if(!is_numeric($points) || (int)$points === 0)
			return [
				'status' => 'error',
				'code' => 'invaild_point_value',
				'msg' => ___('Invaild points value.'),
			];
			
		$post = get_post($post_id);
		if(!$post)
			return [
				'status' => 'error',
				'code' => 'post_not_exist',
				'msg' => ___('Post is not exist.'),
			];

		$rater = get_user_by('id',$rater_id);
		if(!$rater)
			return [
				'status' => 'error',
				'code' => 'rater_not_exist',
				'msg' => ___('Rater is not exist.'),
			];
			
		$posts = (array)get_user_meta($rater_id,self::$user_meta_key['posts'],true);
		
		if(isset($posts[$post_id]))
			return [
				'status' => 'error',
				'code' => 'rated',
				'msg' => ___('You had rated this post.'),
			];

		$posts[$post_id] = $points;
		update_user_meta($rater_id,self::$user_meta_key['posts'],$posts);

		return true;
	}
	/**
	 * 递减投币该文章的用户
	 *
	 * @return 
	 * @version 1.0.0
	 * @author Km.Van inn-studio.com <kmvan.com@gmail.com>
	 */
	public static function decr_post_raters($post_id,$rater_id){
		if(!is_numeric($post_id) || (int)$post_id === 0)
			return [
				'status' => 'error',
				'code' => 'invaild_post_id',
				'msg' => ___('Invaild post id.'),
			];
			
		if(!is_numeric($rater_id) || (int)$rater_id === 0)
			return [
				'status' => 'error',
				'code' => 'invaild_rater_id',
				'msg' => ___('Invaild rater id.'),
			];
			

		$raters = (array)get_post_meta($post_id,self::$post_meta_key['users'],true);
		
		/**
		 * if not rated, return error
		 */
		if(!isset($raters[$user_id]))
			return [
				'status' => 'error',
				'code' => 'no_rated',
				'msg' => ___('You did not rate this post yet.'),
			];

		/**
		 * if already exist, just remove
		 */
		unset($raters[$user_id]);
		update_post_meta($post_id,self::$post_meta_key['users'],$raters);
		
		return true;
	}
	public static function decr_rater_posts($post_id,$rater_id){
		if(!is_numeric($post_id) || (int)$post_id === 0)
			return [
				'status' => 'error',
				'code' => 'invaild_post_id',
				'msg' => ___('Invaild post id.'),
			];
			
		if(!is_numeric($rater_id) || (int)$rater_id === 0)
			return [
				'status' => 'error',
				'code' => 'invaild_rater_id',
				'msg' => ___('Invaild rater id.'),
			];

		$posts = (array)get_user_meta($rater_id,self::$user_meta_key['posts'],true);

		if(!isset($posts[$post_id]))
			return [
				'status' => 'error',
				'code' => 'no_rated',
				'msg' => ___('You did not rate this post yet.'),
			];

		unset($posts[$post_id]);
		update_user_meta($rater_id,self::$user_meta_key['posts'],$posts);

		return true;
	}
	public static function is_singular_post(){
		static $cache = null;
		if($cache === null)
			$cache = is_singular('post');

		return $cache;
	}
	public static function frontend_seajs_alias(array $alias = []){
		if(!self::is_singular_post())
			return $alias;
			
		$alias[self::$iden] = theme_features::get_theme_includes_js(__DIR__);
		return $alias;
	}
	public static function frontend_seajs_use(){
		if(!self::is_singular_post())
			return;
		?>
		seajs.use(['<?= self::$iden;?>'],function(m){
			m.config.lang.M00001 = '<?= ___('Loading, please wait...');?>';
			m.config.lang.E00001 = '<?= ___('Sorry, some server error occurred, the operation can not be completed, please try again later.');?>';
			m.config.process_url = '<?= theme_features::get_process_url([
				'action' => self::$iden,
				'type' => 'incr'
			]);?>';
			m.init();
		});
		<?php
	}
	public static function post_btn($post_id){
			
		$point_img = theme_custom_point::get_point_img_url();
		$point_values = (array)self::get_point_values();

		$count_point_values = count($point_values);
		$default_point_value = $point_values[0];
		if($count_point_values > 1){
			sort($point_values);
		}

		?>
		<div id="post-point-loading-ready" class="btn btn-info btn-lg"><i class="fa fa-spinner fa-pulse fa-fw"></i> <?= ___('Loading, please wait...');?></div>
		
		<div id="post-point-btn-group" class="btn-group btn-group-lg">
			<a href="javascript:;" class="post-point-btn btn btn-info" data-post-id="<?= $post_id;?>" data-points="<?= $default_point_value;?>">
				<?php if(empty($point_img)){ ?>
					<i class="fa fa-diamond"></i> 
				<?php }else{ ?>
					<img src="<?= esc_url($point_img);?>" alt="icon">
				<?php } ?>
				
				<strong class="number" id="post-point-number-<?= $post_id;?>"><?= self::get_post_points_count($post_id);?></strong>

				<i> / </i>
				
				<?= sprintf(___('Rate %d %s'),$default_point_value,theme_custom_point::get_point_name());?>
				
			</a>
			
			<?php if($count_point_values > 1){ ?>
				<div class="btn-group btn-group-lg">
					<span class="btn btn-info dropdown-toggle" data-toggle="dropdown" aria-expanded="false" role="group">
						<span class="caret"></span>
						<span class="sr-only"><?php ___('Toggle Dropdown');?></span>
					</span>
					<ul class="dropdown-menu" role="menu">
						<?php foreach($point_values as $v){ ?>
							<li><a href="javascript:;" class="post-point-btn" data-post-id="<?= $post_id;?>" data-points="<?= $v;?>">
								<?php 
								echo sprintf(___('Rate %d %s'),
									$v,
									theme_custom_point::get_point_name()
								);?>
							</a></li>
						<?php } ?>
					</ul>
				</div>
			<?php } ?>
		</div>
		<?php
	}
}
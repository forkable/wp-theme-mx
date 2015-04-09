<?php
/**
 * @version 1.0.0
 */
add_filter('theme_includes',function($fns){
	$fns[] = 'theme_custom_point::init';
	return $fns;
});
class theme_custom_point{
	public static $iden = 'theme_custom_point';

	public static $page_slug = 'account';
	
	public static $user_meta_key = array(
		'history' => 'theme_point_history',
		'point' => 'theme_point_count',
		'last-signin' => 'theme_last_signin',
	);
	public static function init(){

		include __DIR__ . '/widget.php';
		
		add_action('page_settings',__CLASS__ . '::display_backend');

		add_action('wp_enqueue_scripts', 	__CLASS__ . '::frontend_css');
		
		add_action('comment_post',__CLASS__ . '::action_add_history_wp_new_comment_comment_publish',10,2);
		
		add_action('transition_comment_status',__CLASS__ . '::action_add_history_transition_comment_status_comment_publish',10,3);

		
		add_action('publish_post',__CLASS__ . '::add_action_publish_post_history_post_publish',10,2);
		
		
		add_action('user_register',__CLASS__ . '::action_add_history_signup');

		/** post-delete */
		add_action('before_delete_post',__CLASS__ . '::action_add_history_post_delete');
		
		/** sign-in daily */
		add_filter('cache-request',__CLASS__ . '::filter_cache_request');
		
		add_filter('theme_options_default',__CLASS__ . '::options_default');
		add_filter('theme_options_save',__CLASS__ . '::options_save');

		/** ajax */
		add_action('wp_ajax_' . self::$iden,__CLASS__ . '::process');

		add_action('backend_seajs_alias',__CLASS__ . '::backend_seajs_alias');
		add_action('after_backend_tab_init',__CLASS__ . '::backend_seajs_use');

	}
	public static function display_backend(){
		$opt = self::get_options();

		$points = $opt['points'];
		$point_name = isset($opt['point-name']) ? $opt['point-name'] : ___('Cat-paw');
		?>
		<fieldset>
			<legend><?php echo ___('User point settings');?></legend>
			<p class="description"><?php echo ___('About user point settings.');?></p>
			<table class="form-table">
				<tbody>
					<tr>
						<th><label for="<?php echo self::$iden;?>-point-name"><?php echo ___('Point name');?></label></th>
						<td>
							<input type="text" name="<?php echo self::$iden;?>[point-name]" class="widefat" id="<?php echo self::$iden;?>-point-name" value="<?php echo esc_attr($point_name);?>">
						</td>
					</tr>
					<?php foreach(self::get_point_types() as $k => $v){ ?>
						<tr>
							<th>
								<label for="<?php echo self::$iden;?>-<?php echo $k;?>"><?php echo $v['text'];?></label>
							</th>
							<td>
								<input 
									type="<?php echo isset($points[$k]) && is_int($points[$k]) ? 'number' : 'text';?>" 
									name="<?php echo self::$iden;?>[points][<?php echo $k;?>]" class="short-text" 
									id="<?php echo self::$iden;?>-<?php echo $k;?>" 
									value="<?php echo isset($points[$k]) ? $points[$k] : 0;?>"
								>
								<?php if(isset(self::get_point_types($k)['des'])){ ?>
									<span class="description"><?php echo self::get_point_types($k)['des'];?></span>
								<?php } ?>
							</td>
						</tr>
					<?php } ?>
					<tr>
						<th><label for="<?php echo self::$iden;?>-point-des"><?php echo ___('Description on point history page');?></label></th>
						<td>
							<textarea name="<?php echo self::$iden;?>[point-des]" id="<?php echo self::$iden;?>-des" rows="3" class="widefat code"><?php echo isset($opt['point-des']) ? $opt['point-des'] : null;?></textarea>
						</td>
					</tr>
					<tr>
						<th><label for="<?php echo self::$iden;?>-point-img-url"><?php echo ___('Description on point history page');?></label></th>
						<td>
							<input type="url" name="<?php echo self::$iden;?>[point-img-url]" id="<?php echo self::$iden;?>-img-url" class="widefat code" value="<?php echo isset($opt['point-img-url']) ? $opt['point-img-url'] : null;?>">
						</td>
					</tr>
				</tbody>
			</table>

			<h3><?php echo ___('Add/Reduce point for user - special event');?></h3>
			<table class="form-table">
				<tbody>
					<tr>
						<th><?php echo ___('User ID');?></th>
						<td>
							<input class="short-text" type="number" id="<?php echo self::$iden;?>-special-user-id" data-target="<?php echo self::$iden;?>-special-tip-user-id" data-ajax-type="user-id">
							<span class="description" id="<?php echo self::$iden;?>-special-tip-user-name"></span>
						</td>
					</tr>
					<tr>
						<th><?php echo ___('How many point to add/reduce');?></th>
						<td>
							<input class="short-text" type="number" id="<?php echo self::$iden;?>-special-point" data-target="<?php echo self::$iden;?>-special-tip-user-point" data-ajax-type="point">
							<span class="description" id="<?php echo self::$iden;?>-special-tip-user-point"></span>
						</td>
					</tr>
					<tr>
						<th><?php echo ___('Event description');?></th>
						<td>
							<input class="widefat" type="text" id="<?php echo self::$iden;?>-special-event" data-ajax-type="event">
						</td>
					</tr>
					<tr>
						<th><?php echo ___('Control');?></th>
						<td>
							<div class="page-tip" id="<?php echo self::$iden;?>-special-tip-set"></div>
							<a href="javascript:;" class="button button-primary" id="<?php echo self::$iden;?>-special-set" data-target="<?php echo self::$iden;?>-special-tip-set"><?php echo ___('Add/Reduce');?></a>
						</td>
					</tr>
				</tbody>
			</table>
		</fieldset>
		<?php
	}
	public static function get_point_img_url(){
		static $caches;
		if(isset($caches['url']))
			return $caches['url'];

		$caches['url'] = self::get_options('point-img-url');
		return $caches['url'];
	}
	public static function process(){
		$output = [];

		
		$type = isset($_GET['type']) ? $_GET['type'] : null;

		switch($type){
			/**
			 * special
			 */
			case 'special':
				if(!current_user_can('create_users')){
					$output['status'] = 'error';
					$output['code'] = 'invaild_permission';
					$output['msg'] = ___('Your are not enough permission to modify user.');
					die(theme_features::json_format($output));
				}
				$special = isset($_GET['special']) && is_array($_GET['special']) ? $_GET['special'] : null;
				if(empty($special)){
					$output['status'] = 'error';
					$output['code'] = 'invaild_param';
					$output['msg'] = ___('Invaild param.');
					die(theme_features::json_format($output));
				}
				$invalidations = array(
					'user-id' => array(
						'msg' => ___('Invaild user ID.'),
						'code' => 'invaild_user_id'
					),
					'point' => array(
						'msg' => ___('Invaild point.'),
						'code' => 'invaild_point'
					),
					'event' => array(
						'msg' => ___('Invaild event.'),
						'code' => 'invaild_event'
					),
				);
				foreach($invalidations as $k => $v){
					if(!isset($special[$k]) || empty($special[$k])){
						$output['status'] = 'error';
						$output['code'] = $v['code'];
						$output['msg'] = $v['msg'];
						die(theme_features::json_format($output));
					}
				}
				/**
				 * check user exist
				 */
				$user = get_user_by('id',$special['user-id']);
				if(!$user){
					$output['status'] = 'error';
					$output['code'] = 'user_not_exist';
					$output['msg'] = ___('Fuck you man, the user is not exist');
					die(theme_features::json_format($output));
				}
				/**
				 * pass, set the new point for user
				 */
				self::action_add_history_special_event($special['user-id'],$special['point'],$special['event']);
				$output['status'] = 'success';
				$output['msg'] = sprintf(
					___('The user %s(%d) point has set to %d.'),
					$user->display_name,
					$user->ID,
					self::get_point($user->ID)
				);
				die(theme_features::json_format($output));
				break;

		}

		die(theme_features::json_format($output));
	}
	public static function is_page(){
		static $cache = null;
		if($cache === null)
			$cache = is_page(self::$page_slug) && get_query_var('tab') === 'history';
		return $cache;
	}
	public static function get_point_types($key = null){
		static $caches = null;
		if($caches === null){
			
			$caches = [
				'signup' => [
					'text' => ___('When sign-up')
				],
				'signin-daily' => [
					'text' => ___('When sign-in daily')
				],
				'comment-publish' => [
					'text' => ___('When publish comment')
				],
				'comment-delete' => [
					'text' => ___('When delete comment')
				],
				'post-publish' => [
					'text' => ___('When publish post')
				],
				'post-reply' => [
					'text' => ___('When reply post')
				],
				'post-delete' => [
					'text' => ___('When delete post')
				],
				'post-per-hundred-view'	=> [
					'text' => ___('When post per hundred view ')
				],
				'aff-signup' => [
					'text' => ___('When aff sign-up')
				],
			];
			$caches = apply_filters('custom-point-types',$caches);
		}
		if(empty($key)) 
			return $caches;
		
		return isset($caches[$key]) ? $caches[$key] : null;
	}
	public static function options_default(array $opts = []){
		$opts[self::$iden] = array(
			'points' => array(
				'signup'			=> 20, /** 初始 */
				'signin-daily'		=> 2, /** 日登 */
				'comment-publish'	=> 1, /** 发表新评论 */
				'comment-delete'  	=> -3, /** 删除评论 */
				'post-publish' 		=> 3, /** 发表新文章 */
				'post-reply' 		=> 1, /** 文章被回复 */
				'post-delete'		=> -5,/** 文章被删除 */
				'post-per-hundred-view' => 5, /** 文章每百查看 */
				'aff-signup'		=> 5, /** 推广注册 */
			),
			'point-name' 			=> ___('Cat-paw'), /** 名称 */
			'point-des2' => ___('Point can exchange many things.'),
			'point-img-url' => 'http://ww1.sinaimg.cn/large/686ee05djw1epfzp00krfg201101e0qn.gif',
		);
		$opts[self::$iden] = apply_filters('custom-point-options-default',$opts[self::$iden]);
		
		return $opts;
	}
	public static function options_save(array $opts = []){
		if(isset($_POST[self::$iden]))
			$opts[self::$iden] = $_POST[self::$iden];
		
		return $opts;
	}
	public static function get_point_name(){
		return self::get_options('point-name') ? self::get_options('point-name') : ___('Cat-paw');
	}
	public static function get_point_des(){
		return self::get_options('point-des');
	}
	public static function get_options($key = null){
		static $caches;
		if(!is_array($caches))			
			$caches = (array)theme_options::get_options(self::$iden);
			
		if($key){
			return isset($caches[$key]) ? $caches[$key] : null;
		}
		return $caches;
	}
	public static function get_point_value($type){
		static $caches;
		if(isset($caches[$type]))
			return $caches[$type];

		
		$opt = self::get_options('points');
		$caches[$type] = isset($opt[$type]) ? $opt[$type] : false;
		return $caches[$type];
	}
	/**
	 * Get user point
	 *
	 * @param int User id
	 * @version 1.0.0
	 * @return int
	 * @author Km.Van inn-studio.com <kmvan.com@gmail.com>
	 */
	public static function get_point($user_id = null){
		static $caches;
		if(isset($caches[$user_id]))
			return $caches[$user_id];
			
		if(!$user_id) 
			$user_id = get_current_user_id();
			
		$point = (int)get_user_meta($user_id,self::$user_meta_key['point'],true);

		$caches[$user_id] = $point;
		return $point;
	}
	/**
	 * Get user history
	 *
	 * @param array $args
	 * @param int $user_id
	 * @param int $paged
	 * @param int $posts_per_page
	 * @version 1.0.0
	 * @return array
	 * @author Km.Van inn-studio.com <kmvan.com@gmail.com>
	 */
	public static function get_history($args = []){
		$defaults = array(
			'user_id' => get_current_user_id(),
			'paged' => 1,
			'posts_per_page' => 20,
		);
		$r = wp_parse_args($args,$defaults);
		extract($r);

		
		$metas = get_user_meta($user_id,self::$user_meta_key['history']);
		krsort($metas);
		/**
		 * check the paginavi
		 */
		if($posts_per_page > 0){
				
			$start = (($paged - 1) * 10) - 1;
			if($start < 0)
				$start = 0;
				
			$metas = array_slice(
				$metas,
				$start,
				(int)$posts_per_page
			);
		}
		return $metas;
	}
	public static function get_history_list($args = null){
		$metas = self::get_history($args);
		if(empty($metas))
			return false;
		ob_start();

		$point_name = self::get_point_name();
		?>
		<ul class="list-group history-group">
			<?php
			foreach($metas as $k => $v){
				$type_point = self::get_point_value($v['type']);
			?>
<li class="list-group-item">
	<span class="point-name">
		<?php echo esc_html($point_name);?>
	</span>
	<?php
	/**
	 * special event point
	 */
	if($v['type'] !== 'special-event'){
		if($type_point >= 0){
			$cls = 'plus';
			$tx = '+' . $type_point;
		}else{
			$cls = 'minus';
			$tx = '-' . $type_point;
		}
		?>
		<span class="point-value <?php echo $cls;?>"><?php echo $tx;?></span>
	<?php
	}
//var_dump($v);
switch($v['type']){
	/*****************************************
	 * signup
	 */
	case 'special-event':
		if($v['point'] >= 0){
			$cls = 'plus';
			$tx = '+' . $v['point'];
		}else{
			$cls = 'minus';
			$tx = '-' . $v['point'];
		}
		?>
		<span class="point-value <?php echo $cls;?>"><?php echo $tx;?></span>
		<span class="history-text"><strong>
			<?php echo sprintf(___('One special event happened: %s'),$v['event']);?>
		</strong></span>
		<?php
		break;
	/*****************************************
	 * signup
	 */
	case 'signup':
		?>
		<span class="history-text">
			<?php echo sprintf(___('I registered %s.'),'<a href="' . home_url() . '">' . get_bloginfo('name') . '</a>');?>
		</span>
		<?php
		break;
	/***************************************
	 * comment-publish
	 */
	case 'comment-publish':
		global $comment;
		$comment = get_comment($v['comment-id']);
		
		?>
		<span class="history-text">
			<?php 
			echo sprintf(___('You published a comment in %1$s.'),

			'<a href="' . esc_url(get_permalink($comment->comment_post_ID)) . '">' . esc_html(get_the_title($comment->comment_post_ID)) . '</a>'
			);
			?>
		</span>
		<?php
		break;
	/***************************************
	 * post-publish
	 */
	case 'post-publish':
		global $post;
		$post_bak = $post;
		$post = get_post($v['psot-id']);
		?>
		<span class="history-text">
			<?php
			if(!$post){
				echo ___('The post has been deleted.');
			}else{
				echo sprintf(___('I published a post %s.'),'<a href="' . esc_url(get_permalink($v['post-id'])) . '">' . esc_html(get_the_title($v['post-id'])) . '</a>');
			}
			?>
		</span>
		<?php
		$post = $post_bak;
		unset($post_bak);
		break;
	/***************************************
	 * post-reply
	 */
	case 'post-reply':
		global $comment;
		$comment = get_comment($v['comment-id']);
		
		?>
		<span class="history-text">
			<?php echo sprintf(___('Your post %1$s has a new comment by %2$s.'),

			'<a href="' . esc_url(get_permalink($comment->comment_post_ID)) . '">' . esc_html(get_the_title($comment->comment_post_ID)) . '</a>',

			'<span class="comment-author">' . get_comment_author_link() . '</span>'
			);?>
		</span>
		<?php
		break;
	/****************************************
	 * signin-daily
	 */
	case 'signin-daily':
		?>
		<span class="history-text">
			<?php echo ___('Log-in daily reward.');?>
		</span>
		<?php
		break;
	default:
	
} /** end switch */
		?>
		<span class="history-time">
			<?php
			if(isset($v['timestamp'])){
				echo friendly_date($v['timestamp']);
			}
			?>
		</span>
</li>
<?php
} /** end foreach */
?>
		</ul>				

		<?php
		$content = ob_get_contents();
		ob_end_clean();

		return $content;
	}
	public static function filter_cache_request($output){
		/**
		 * signin daily
		 */
		if(!is_user_logged_in()) return $output;
		if(self::action_add_history_signin_daily() === true){
			$point = (int)theme_options::get_options(self::$iden)['points']['signin-daily'];
			$output['signin-daily'] = array(
				'point' => $point,
				'msg' => sprintf(___('Sign-in daily points: +%s'),$point),
			);
		}else{
			$output['signin-daily'] = false;
		}
		return $output;
	}
	public static function action_add_history_post_coin($post_id,$giver_id){
		$post = get_post($post_id);
		if(!$post)
			return;
		/**
		 * add history for post author
		 */
		$meta = [
			'type'=> 'receive-post-coin',
			'timestamp' => current_time('timestamp'),
			'post-id' => (int)$post_id,
			'giver-user-id' => (int)$giver_id,
		];
		add_user_meta($post->post_author,self::$user_meta_key['history'],$meta);
		/**
		 * update point for post author
		 */
		$old_point = self::get_point($post->post_author);
		update_user_meta($post->post_author,self::$user_meta_key['point'],$old_point + self::get_point_value('post-coin'));

		/**
		 * add history for giver
		 */
		$meta = [
			'type'=> 'give-post-coin',
			'timestamp' => current_time('timestamp'),
			'post-id' => (int)$post_id,
		];
		add_user_meta($post->post_author,self::$user_meta_key['history'],$meta);
		/**
		 * update point for giver
		 */
		$old_point = self::get_point($giver_id);
		update_user_meta($giver_id,self::$user_meta_key['point'],$old_point - self::get_point_value('post-coin'));
	}
	/**
	 * HOOK - Add post-delete history to user meta
	 *
	 * @version 1.0.0
	 * @author Km.Van inn-studio.com <kmvan.com@gmail.com>
	 */
	public static function action_add_history_post_delete($post_id){
		$post = get_post($post_id);
		if(!$post)
			return;
		$meta = array(
			'type'=> 'post-delete',
			'post-title' => get_the_title($post->ID),
			'timestamp' => current_time('timestamp'),
		);
		add_user_meta($post->post_author,self::$user_meta_key['history'],$meta);

		/**
		 * point
		 */
		$old_point = self::get_point($post->post_author);
		update_user_meta($post->post_author,self::$user_meta_key['point'],$old_point - self::get_point_value('post-delete'));
	}
	/**
	 * HOOK - Add sign-up history to user meta
	 *
	 * @version 1.0.0
	 * @author Km.Van inn-studio.com <kmvan.com@gmail.com>
	 */
	public static function action_add_history_signup($user_id){
		$meta = array(
			'type'=> 'signup',
			'timestamp' => current_time('timestamp'),
		);
		add_user_meta($user_id,self::$user_meta_key['history'],$meta);
		/**
		 * update point
		 */
		update_user_meta($user_id,self::$user_meta_key['point'],(int)self::get_options('points')['signup']);
	}
	/**
	 * Add special event
	 *
	 * @param int $user_id
	 * @param int $point
	 * @param string $event Event description
	 * @version 1.0.0
	 * @author Km.Van inn-studio.com <kmvan.com@gmail.com>
	 */
	public static function action_add_history_special_event($user_id,$point,$event = null){
		$point = (int)$point;
		$user_id = (int)$user_id;
		if($point === 0 || $user_id === 0)
			return false;

		$current_timestamp = current_time('timestamp');
		if(empty($event))
			$event = ___('Special event');

		/**
		 * add history
		 */
		$meta = array(
			'type' => 'special-event',
			'point' => $point,
			'event' => $event,
			'timestamp' => $current_timestamp
		);
		add_user_meta($user_id,self::$user_meta_key['history'],$meta);
		/**
		 * update point
		 */
		$old_point = self::get_point($user_id);
		update_user_meta($user_id,self::$user_meta_key['point'],$old_point + $point);

		return true;

	}
	/**
	 * HOOK - Signin daily for user meta
	 *
	 * @version 1.0.0
	 * @author Km.Van inn-studio.com <kmvan.com@gmail.com>
	 */
	public static function action_add_history_signin_daily(){
		$user_id = get_current_user_id();
		$current_timestamp = current_time('timestamp');
		/**
		 * get the last sign-in time
		 */
		$last_signin_timestamp = get_user_meta($user_id,self::$user_meta_key['last-signin'],true);

		/**
		 * first sign-in
		 */
		if(empty($last_signin_timestamp)){
			update_user_meta($user_id,self::$user_meta_key['last-signin'],$current_timestamp);
			return;
		}

		
		$today_Ymd = date('Ymd',$current_timestamp);
		$last_signin_Ymd = date('Ymd',$last_signin_timestamp);
		
		/** IS logged today, return */
		if($today_Ymd == $last_signin_Ymd) 
			return false;
			
		/**
		 * update $last_signin_timestamp
		 */
		update_user_meta($user_id,self::$user_meta_key['last-signin'],$current_timestamp);

		/**
		 * add history
		 */
		$meta = array(
			'type' => 'signin-daily',
			'timestamp' => $current_timestamp
		);
		add_user_meta($user_id,self::$user_meta_key['history'],$meta);
		/**
		 * update point
		 */
		$old_point = self::get_point($user_id);
		update_user_meta($user_id,self::$user_meta_key['point'],$old_point + (int)theme_options::get_options(self::$iden)['points']['signin-daily']);

		return true;
	}
	/**
	 * Hook, when comment author's comment status has been updated
	 */
	public static function action_add_history_transition_comment_status_comment_publish($new_status, $old_status, $comment){
		
		/**
		 * do NOT add history if visitor
		 */
		if($comment->user_id == 0)
			return;
		
		/**
		 * do NOT add history if the comment is spam or hold
		 */
		if($old_status !== 'unapproved' && $old_status !== 'spam')
			return;
		
		if($new_status !== 'approved')
			return;
		/**
		 * add history for comment author
		 */
		self::action_add_history_core_comment_publish($comment->comment_ID);

		/**
		 * add history for post author
		 */
		self::action_add_history_core_post_reply($comment->comment_ID);
	}
	/**
	 * HOOK - Add comment publish history to user meta
	 *
	 * @param int $comment_id Comment ID
	 * @param string $comment_approved 0|1|spam
	 * @version 1.0.0
	 * @author Km.Van inn-studio.com <kmvan.com@gmail.com>
	 */
	public static function action_add_history_wp_new_comment_comment_publish($comment_id,$comment_approved){
		/**
		 * do NOT add history if the comment is spam or disapprove
		 */
		if((int)$comment_approved !== 1)
			return;
			
		/**
		 * do NOT add history if visitor
		 */
		$comment = get_comment($comment_id);
		if($comment->user_id == 0)
			return;
		
		/**
		 * add history for comment author
		 */
		self::action_add_history_core_comment_publish($comment_id);

		/**
		 * add history for post author
		 */
		self::action_add_history_core_post_reply($comment_id);
	}
	
	/**
	 * Add history when publish comment for comment author
	 *
	 * @param 
	 * @return 
	 * @version 1.0.0
	 * @author Km.Van inn-studio.com <kmvan.com@gmail.com>
	 */
	public static function action_add_history_core_comment_publish($comment_id){

		$comment = get_comment($comment_id);
		$comment_author_id = $comment->user_id;
		$post_author_id = get_post($comment->comment_post_ID)->post_author;
		if($comment_author_id == $post_author_id) return false;
		$meta = array(
			'type' => 'comment-publish',
			'comment-id' => $comment_id,
			'timestamp' => current_time('timestamp'),
		);
		add_user_meta($comment_author_id,self::$user_meta_key['history'],$meta);
		/**
		 * update point
		 */
		$old_point = self::get_point($comment_author_id);
		update_user_meta($comment_author_id,self::$user_meta_key['point'],$old_point + (int)theme_options::get_options(self::$iden)['points']['comment-publish']);		
	}
	/**
	 * action_add_history_core_post_reply
	 *
	 * @param int $comment_id Comment ID
	 * @version 1.0.0
	 * @author Km.Van inn-studio.com <kmvan.com@gmail.com>
	 */
	public static function action_add_history_core_post_reply($comment_id){
		
		$comment = get_comment($comment_id);
		
		/** post author id */
		$post_author_id = get_post($comment->comment_post_ID)->post_author;
		
		/** do not add history for myself post */
		if($post_author_id == $comment->user_id) return false;
		
		$meta = array(
			'type' => 'post-reply',
			'comment-id' => $comment_id,
			'timestamp' => current_time('timestamp'),
		);
		add_user_meta($post_author_id,self::$user_meta_key['history'],$meta);
		/**
		 * update point
		 */
		$old_point = self::get_point($post_author_id);
		update_user_meta($post_author_id,self::$user_meta_key['point'],$old_point + (int)theme_options::get_options(self::$iden)['points']['post-reply']);
	}
	/**
	 * HOOK add history for post author when publish post
	 */
	public static function add_action_publish_post_history_post_publish($post_id,$post){
		/**
		 * add history for post author
		 */
		self::action_add_history_core_post_publish($post_id,$post);
	}
	/**
	 * action_add_history_core_transition_post_status_post_publish
	 *
	 * @param int Post id
	 * @param object Post
	 * @version 1.0.0
	 * @author Km.Van inn-studio.com <kmvan.com@gmail.com>
	 */
	public static function action_add_history_core_post_publish($post_id,$post){
		$post_author_id = $post->post_author;
		$meta = array(
			'type' => 'post-publish',
			'post-id' => $post_id,
			'timestamp' => current_time('timestamp'),
		);
		add_user_meta($post_author_id,self::$user_meta_key['history'],$meta);
		/**
		 * update point
		 */
		$old_point = self::get_point($post_author_id);
		update_user_meta($post_author_id,self::$user_meta_key['point'],$old_point + (int)theme_options::get_options(self::$iden)['points']['post-publish']);
	}
	public static function frontend_css(){
		if(!self::is_page()) 
			return;
		wp_enqueue_style(
			self::$iden,
			theme_features::get_theme_includes_css(__DIR__,'style',false),
			false,
			theme_features::get_theme_info('version')
		);
	}
	public static function backend_seajs_alias($alias){
		$alias[self::$iden] = theme_features::get_theme_includes_js(__DIR__,'backend');
		return $alias;
	}
	public static function backend_seajs_use(){
		?>
		seajs.use('<?php echo self::$iden;?>',function(m){
			m.config.process_url = '<?php echo theme_features::get_process_url(array('action'=>self::$iden));?>';
			m.config.lang.M00001 = '<?php echo ___('Loading, please wait...');?>';
			m.config.lang.E00001 = '<?php echo ___('Server error or network is disconnected.');?>';
			m.init();
		});
		<?php
	}
}
?>
<?php
/** 
 * @version 1.0.0
 */
add_filter('theme_includes',function($fns){
	$fns[] = 'theme_notification::init';
	return $fns;
});
class theme_notification{
	public static $iden = 'theme_notification';
	public static $page_slug = 'account';
	public static $user_meta_key = array(
		'key' => 'theme_noti',
		'count' => 'theme_noti_count',
		'unread_count' => 'theme_noti_unread_count',
	);
	private static $timestamp;
	
	public static function init(){
		/** filter */
		
		add_filter('query_vars',			get_class() . '::filter_query_vars');
		
		add_action('wp_enqueue_scripts', 	get_class() . '::frontend_css');

		
		/** action */
		
		add_filter('wp_title',				get_class() . '::wp_title',10,2);
		
		foreach(self::get_tabs() as $k => $v){
			$nav_fn = 'filter_nav_' . $k; 
			add_filter('account_navs',get_class() . "::$nav_fn",$v['filter_priority']);
		}

		/**
		 * add hook to comment publish and reply
		 */
		add_action('comment_post',get_class() . '::action_add_noti_wp_new_comment_comment_publish',10,2);
		
		add_action('transition_comment_status',get_class() . '::action_add_noti_transition_comment_status_comment_publish',10,3);

		/**
		 * clean unread notis
		 */
		add_action('wp_footer'		,get_class() . '::clean_unread_notis');
	}
	public static function wp_title($title, $sep){
		/**
		 * check unread count
		 */
		if(is_user_logged_in()){
			$unread_count = (int)self::get_count(array(
				'type' => 'unread'
			));
		}else{
			$unread_count = 0;
		}

		if($unread_count === 0){
			if(!self::is_page()) return $title;
			if(self::get_tabs(get_query_var('tab'))){
				$title = self::get_tabs(get_query_var('tab'))['text'];
			}			
		}else{
			if(!self::is_page()){
				return " ({$unread_count}) " . $title;
			}
			if(self::get_tabs(get_query_var('tab'))){
				$title = " ({$unread_count}) " . self::get_tabs(get_query_var('tab'))['text'];
			}	
		}

		return $title . $sep . get_bloginfo('name');
	}
	public static function filter_query_vars($vars){
		if(!in_array('page',$vars)) $vars[] = 'page';
		return $vars;
	}
	public static function get_tabs($key = null){
		$baseurl = self::get_url();
		$tabs = array(
			'notifications' => array(
				'text' => ___('My notifications'),
				'icon' => 'bell',
				'url' => add_query_arg('tab','notifications',$baseurl),
				'filter_priority' => 40,
			),
		);
		if($key){
			return isset($tabs[$key]) ? $tabs[$key] : false;
		}
		return $tabs;
	}
	public static function filter_nav_notifications($navs){
		$unread = self::get_count(array(
			'type' => 'unread'
		));
		if($unread !== 0){
			$unread_html = "<span class='badge'>{$unread}</span>";
		}else{
			$unread_html = null;
		}

		$navs['notifications'] = '<a href="' . esc_url(self::get_tabs('notifications')['url']) . '">
			<i class="fa fa-' . self::get_tabs('notifications')['icon'] . '"></i> 
			' . esc_html(self::get_tabs('notifications')['text']) . '
			' . $unread_html . '
		</a>';
		return $navs;
	}
	/**
	 * Clean
	 * When user visit the noti page, clean unread notis
	 */
	public static function clean_unread_notis(){
		if(self::is_page()){
			$old_metas = get_user_meta(get_current_user_id(),self::$user_meta_key['unread_count'],true);
			if(!empty($old_metas))
				update_user_meta(get_current_user_id(),self::$user_meta_key['unread_count'],'');
		}
	}
	public static function get_url(){
		return get_permalink(theme_cache::get_page_by_path(self::$page_slug));
	}
	public static function is_page(){
		if(
			is_page(self::$page_slug) && 
			self::get_tabs(get_query_var('tab'))
		)
			return true;
		return false;
	}

	public static function get_count($args = null){
		$defaults = array(
			'user_id' => get_current_user_id(),
			'type' => 'all',
		);
		$args = wp_parse_args($args,$defaults);
		if(empty($args['user_id'])) return false;

		switch($args['type']){
			case 'unread':
				$metas = array_filter((array)get_user_meta($args['user_id'],self::$user_meta_key['unread_count'],true));
				break;
			default:
				$metas = array_filter((array)get_user_meta($args['user_id'],self::$user_meta_key['key']));
		}
		return empty($metas) ? 0 : count($metas);
	}
	public static function get_notifications($args = null){
		$defaults = array(
			'user_id' => get_current_user_id(),
			'type' => 'all',/** all / unread / read */
			'posts_per_page' => 20,
			'paged' => 1,
			'orderby' => 'desc',
		);
		$args = wp_parse_args($args,$defaults);
		if(empty($args['user_id'])) return false;
		
		$metas = (array)get_user_meta($args['user_id'],self::$user_meta_key['key']);

		if(empty($metas)){
			return null;
		}else{
			krsort($metas);
		}
		
		switch($args['type']){
			case 'unread':
				$unreads = (array)get_user_meta($args['user_id'],self::$user_meta_key['unread_count'],true);
				if(!empty($unreads)){
					$unread_count = count($unreads);
					$metas = array_slice($metas,0,$unread_count);
				}
			default:
				//return $metas;
		}
		/**
		 * check the paginavi
		 */
		if($args['posts_per_page'] > 0){
				
			$start = (($args['paged'] - 1) * 10) - 1;
			if($start < 0)
				$start = 0;
				
			$metas = array_slice(
				$metas,
				$start,
				$args['posts_per_page']
			);
		}
		return $metas;
	}
	/**
	 * Get timestamp
	 *
	 * @param bool $rand True/get event id
	 * @return string
	 * @version 1.0.0
	 * @author Km.Van inn-studio.com <kmvan.com@gmail.com>
	 */
	private static function get_timestamp($rand = false){
		if($rand)
			return current_time('timestamp') . '' . rand(100,999);
		
		if(self::$timestamp){
			return self::$timestamp;
		}else{
			self::$timestamp = current_time('timestamp');
			return self::$timestamp;
		}
	}
	/**
	 * HOOK - When a comment becomes spam/disapprove to approve status, noti to post author
	 *
	 * @version 1.0.0
	 * @author Km.Van inn-studio.com <kmvan.com@gmail.com>
	 */
	public static function action_add_noti_transition_comment_status_comment_publish($new_status, $old_status, $comment){
		/**
		 * do NOT add noti if the comment is spam or hold
		 */
		if($old_status !== 'unapproved' && $old_status !== 'spam')
			return;
		
		if($new_status !== 'approved')
			return;
			
		/**
		 * 评论是子评论
		 */
		if($comment->comment_parent != 0){
			/** post author */
			$post_author_id = get_post($comment->comment_post_ID)->post_author;
			/**
			 * 评论是回复评论，父评论是文章作者时，仅给父评论作者添加评论回复事件，不添加文章评论事件
			 */
			if(get_comment($comment->comment_parent)->user_id == $post_author_id){
				self::action_add_noti_core_comment_reply($comment->comment_ID);
				return;
			}
			
		}
		/**
		 * noti for post author
		 */
		self::action_add_noti_core_post_reply($comment->comment_ID);
		/**
		 * noti for comment parent author
		 */
		self::action_add_noti_core_comment_reply($comment->comment_ID);
	}
	/**
	 * HOOK - When a comment publish noti to post author
	 *
	 * @param int $comment_id Comment ID
	 * @param string $comment_approved 0|1|spam
	 * @version 1.0.0
	 * @author Km.Van inn-studio.com <kmvan.com@gmail.com>
	 */
	public static function action_add_noti_wp_new_comment_comment_publish($comment_id,$comment_approved){
		/**
		 * do NOT add noti if the comment is spam or disapprove
		 */
		if((int)$comment_approved !== 1)
			return;
		
		$comment = get_comment($comment_id);
		
		/** post author */
		$post_author_id = get_post($comment->comment_post_ID)->post_author;
			
		/**
		 * 评论是子评论
		 */
		if($comment->comment_parent != 0){
			/**
			 * 评论是回复评论，父评论是文章作者时，仅给父评论作者添加评论回复事件，不添加文章评论事件
			 */
			if(get_comment($comment->comment_parent)->user_id == $post_author_id){
				self::action_add_noti_core_comment_reply($comment_id);
				return;
			}
			
		}
		/**
		 * noti for post author
		 */
		self::action_add_noti_core_post_reply($comment_id);
		
		/**
		 * noti for comment parent author
		 */
		self::action_add_noti_core_comment_reply($comment_id);
	}
	
	public static function action_add_noti_core_post_reply($comment_id){
		$comment = get_comment($comment_id);

		$post_author_id = get_post($comment->comment_post_ID)->post_author;

		if($post_author_id == $comment->user_id)
			return;
			
		/**
		 * add noti for post author
		 */
		$meta = array(
			'id' 			=> self::get_timestamp(true),
			'type' 			=> 'post-reply',
			'comment-id' 	=> $comment->comment_ID,
			'timestamp' 	=> self::get_timestamp(),
		);
		add_user_meta($post_author_id,self::$user_meta_key['key'],$meta);
		/**
		 * update unread count for post author
		 */
		$unread_count = (array)get_user_meta($post_author_id,self::$user_meta_key['unread_count'],true);
		$unread_count[self::get_timestamp(true)] = $meta;
		update_user_meta($post_author_id,self::$user_meta_key['unread_count'],$unread_count);
	}
	
	/**
	 * comment reply noti
	 */
	public static function action_add_noti_core_comment_reply($comment_id){
		$comment = get_comment($comment_id);
		
		if($comment->comment_parent == 0)
			return;
			
		/** get post author */
		$post_author_id = get_post($comment->comment_post_ID)->post_author;
		
		/**
		 * if post author is comment author, just return
		 */
		if($post_author_id == $comment->user_id)
			return;

		/**
		 * get comment parent author
		 */
		$comment_parent_author_id = get_comment($comment->comment_parent)->user_id;
		
		/** if comment parent author is visitor, just return */
		if($comment_parent_author_id == 0)
			return;
			
		/**
		 * add noti for comment parent author
		 */
		$meta = array(
			'id' => self::get_timestamp(true),
			'type' => 'comment-reply',
			'comment-id' => $comment->comment_ID,
			'timestamp' => self::get_timestamp(),
		);
		add_user_meta($comment_parent_author_id,self::$user_meta_key['key'],$meta);
		
		/**
		 * update unread count for comment parent author
		 */
		$unread_count = (array)get_user_meta($comment_parent_author_id,self::$user_meta_key['unread_count'],true);
		$unread_count[self::get_timestamp(true)] = $meta;
		update_user_meta($comment_parent_author_id,self::$user_meta_key['unread_count'],$unread_count);
	}
	public static function process(){
		$output = array();
		
		theme_features::check_referer();
		theme_features::check_nonce();
		
		$type = isset($_REQUEST['type']) ? $_REQUEST['type'] : null;
		

		die(theme_features::json_format($output));
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
}
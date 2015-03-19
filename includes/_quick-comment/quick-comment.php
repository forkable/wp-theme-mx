<?php
/** 
 * @version 1.0.1
 */
theme_quick_comment::init();
class theme_quick_comment{
	public static $iden = 'theme_quick_comment';
	public static $style_id = 'theme-quic-comment';
	public static function init(){
		add_action('frontend_seajs_use',			get_class() . '::frontend_seajs_use');
		add_filter('frontend_seajs_alias',			get_class() . '::frontend_seajs_alias');
		add_action('wp_ajax_' . get_class(),		get_class() . '::process');
		add_action('wp_ajax_nopriv_' . get_class(),	get_class() . '::process');
		add_action('pre_comment_on_post',			get_class() . '::block_frontend_comment',1);
		add_action('pre_comment_on_post',			get_class() . '::pre_comment_on_post');
	}
	/** 
	 * process 
	 * @TODO add pagination, add order
	 */
	public static function process(){
		!check_referer() && wp_die(___('Referer error'));
		theme_features::check_nonce();
		$output = array();
		
		
		$type = isset($_REQUEST['type']) ? $_REQUEST['type'] : null;
		$post_id = isset($_REQUEST['post-id']) ? (int)$_REQUEST['post-id'] : null;
		/** 
		 * invail post id
		 */
		if(!$post_id){
			$output['status'] = 'error';
			$output['id'] = 'invalid_post_id';
			$output['msg'] = ___('Invail post id.');
			die(theme_features::json_format($output));
		}
		/** 
		 * closed
		 */
		if(!comments_open($post_id)){
			$output['status'] = 'error';
			$output['id'] = 'comment_closed';
			$output['msg'] = ___('This post comment has closed.');
			die(theme_features::json_format($output));
		}
		$current_user = wp_get_current_user();
		/** 
		 * switch $type
		 */
		switch($type){
			/** 
			 * get-respond
			 */
			case 'get-respond':
				$output['status'] = 'success';
				$output['logged'] = is_user_logged_in() ? true : false;
				$output['require_name_email'] = get_option('require_name_email') == 1 ? true : false;
				if(!is_user_logged_in()){
					$output['commenter'] = wp_get_current_commenter();
				}
				break;
			/** 
			 * post-comment
			 */
			case 'post-comment':
				
			
				$comment_post_id = isset($_REQUEST['comment-post-id']) ? (int)$_REQUEST['comment-post-id'] : null;
				
				
		
				do_action('pre_comment_on_post', $comment_post_id);
				/** check comment parent */
				$comment_parent = isset($_REQUEST['comment-parent']) ? (int)$_REQUEST['comment-parent'] : 0;
				
				/** check comment post id */
				if(!$comment_post_id){
					$output['status'] = 'error';
					$output['id'] = 'invalid_post_id';
					$output['msg'] = ___('Invail post id.');
				die(theme_features::json_format($output));
				}
				/** check comment content */
				$comment_content = isset($_POST['comment-content']) && trim($_POST['comment-content']) != '' ? trim($_POST['comment-content']) : null;
				if(!$comment_content){
					$output['status'] = 'error';
					$output['id'] = 'invalid_comment_content';
					$output['msg'] = ___('Invail comment content');
					die(theme_features::json_format($output));
				}
				/** 
				 * user is logged
				 */
				if(is_user_logged_in()){
					$comment_author_name = !empty($current_user->display_name) ? $current_user->display_name : $current_user->nickname;
					$comment_author_name = !empty($comment_author_name) ? $comment_author_name : $current_user->user_login;
					$comment_author_name = wp_slash($comment_author_name);
					$comment_author_email = wp_slash($current_user->user_email);
					$comment_author_url = wp_slash($current_user->user_url);
					$comment_author_id = $current_user->ID;
					$output['logged'] = true;
				/** 
				 * visitor
				 */
				}else{
					$comment_author_name = isset($_POST['comment-name']) ? trim($_POST['comment-name']) : null;
					if(empty($comment_author_name)) $comment_author_name = wp_slash(___('Anonymous'));
					$comment_author_email = isset($_POST['comment-email']) ? trim($_POST['comment-email']) : null;
					$comment_author_url = isset($_POST['comment-url']) ? esc_url($_POST['comment-url']) : null;
					/** 
					 * check name and email required
					 */
					if(get_option('require_name_email') == 1 || !empty($comment_author_email)){
						/** invail email */
						if(!is_email($comment_author_email)){
							$output['status'] = 'error';
							$output['id'] = 'invalid_email';
							$output['msg'] = ___('Invail email address.');
							die(theme_features::json_format($output));
						}
					}
					
					$comment_author_id = 0;
					$output['logged'] = false;
					
				}
				
				$commentdata = array(
					'comment_post_ID' 		=> $comment_post_id,
					'comment_author' 		=> $comment_author_name, 
					'comment_author_email' 	=> $comment_author_email, 
					'comment_author_url' 	=> $comment_author_url, 
					'comment_content' 		=> $comment_content, 
					'comment_parent' 		=> $comment_parent,
					'user_id' 				=> $comment_author_id,
					'comment_type'			=> '',
				);
				$comment_id = wp_new_comment($commentdata);
				if($comment_id){
					$comment = get_comment($comment_id);
					$output['status'] = 'success';
					$output['comment'] = self::get_comment($comment_id);
					wp_update_comment_count($comment_post_id);
					
					do_action('set_comment_cookies',$comment,$current_user);
				}else{
					$output['status'] = 'error';
					$output['id'] = 'create_comment_error';
					$output['msg'] = ___('System can not create comment.');
				}
				break;
			/** 
			 * get-comments
			 */
			case 'get-comments':
				/** 
				 * get comments
				 */
				$comments = get_comments(array(
					'post_id' => $post_id,
					'order' => 'ASC',
					'status' => 'approve',
				));
				/** 
				 * if comment is empty
				 */
				if(empty($comments)){
					$output['status'] = 'error';
					$output['id'] = 'no_comment';
					$output['msg'] = ___('No comment yet.');
					die(theme_features::json_format($output));
				}
				foreach($comments as $comment){
					$new_comments[$comment->comment_ID] = apply_filters('api_comment',self::get_comment($comment),$comment);
				}
				$output['status'] = 'success';
				$output['comments'] = $new_comments;				
				break;
			default:
				$output['status'] = 'error';
				$output['id'] = 'invalid_type';
				$output['msg'] = ___('Invail type');
		}
		
		

		die(theme_features::json_format($output));
	}
	/**
	 * Get comment data and output for json
	 *
	 * @param stdClass|int $comment Commen obj or comment id
	 * @return stdClass Comment obj
	 * @version 1.0.1
	 * @author KM@INN STUDIO
	 */
	public static function get_comment($comment){
		if(is_int($comment)) $comment = get_comment($comment);
		$GLOBALS['comment'] = $comment;
		$new_comment = array(
			'comment_id' => get_comment_ID(),
			'comment_post_id' => $comment->comment_post_ID,
			'comment_author' => array(
				'name' => get_comment_author(),
				'gravatar' => get_img_source( get_avatar($comment->comment_author_email,40,false,esc_attr($comment->comment_author))),
				'url' => $comment->user_id ? theme_cache::get_author_posts_url($comment->user_id) : get_comment_author_url(),
				'user_id' => (int)$comment->user_id,
			),
			'comment_date' => get_comment_time('U'),
			'comment_friendly_date' => friendly_date(get_comment_time('U')),
			'comment_date_gmt' => $comment->comment_date_gmt,
			'comment_content' => get_comment_text(),
			'comment_parent' => (int)$comment->comment_parent,
			'comment_class' => get_comment_class(null,get_comment_ID(),$comment->comment_post_ID),
		);
		return $new_comment;
	}
	/** 
	 * Hook block_frontend_comment
	 */
	public static function block_frontend_comment($comment_post_ID){
		/** do NOT allow Contributor and Subscriber and Visitor to comment */
		if(current_user_can('edit_published_posts')) return false;
		if(strstr(get_current_url(),'wp-comments-post.php') !== false) die(___('Blocked comment from frontend.'));
	}
	/** 
	 * Hook pre_comment_on_post
	 */
	public static function pre_comment_on_post($comment_post_ID){
		// var_dump($comment_post_ID);exit;
		/** 
		 * check nonce
		 */
		theme_features::check_nonce();
		
		$comment_post_ID = $comment_post_ID ? $comment_post_ID : (isset($_POST['comment_post_ID']) ? (int) $_POST['comment_post_ID'] : 0);
		$post = get_post($comment_post_ID);
		/** 
		 * check comment_registration
		 */
				
		$post_status = get_post_status($post);
		if(get_option('comment_registration') || 'private' == $post_status){
			$output['status'] = 'error';
			$output['id'] = 'must_be_logged';
			$output['msg'] = ___('Sorry, you must be logged in to post a comment.');
			die(theme_features::json_format($output));
		}
		/**
		 * check comment_status
		 */
		if ( empty( $post->comment_status ) ) {
			do_action('comment_id_not_found', $comment_post_ID);
			$output['status'] = 'error';
			$output['id'] = 'post_not_exists';
			$output['msg'] = ___('Sorry, the post does not exist.');
			die(theme_features::json_format($output));
		}
		/** 
		 * check 
		 */
		$status = get_post_status($post);
		$status_obj = get_post_status_object($status);
		/** 
		 * check comment is closed
		 */
		if(!comments_open($comment_post_ID)){
			do_action('comment_closed', $comment_post_ID);
			$output['status'] = 'error';
			$output['id'] = 'comment_closed';
			$output['msg'] = ___('Sorry, comments are closed for this item.');
			die(theme_features::json_format($output));
		/**
		 * If the post is trash
		 */
		}else if('trash' == $status){
			do_action('comment_on_trash', $comment_post_ID);
			$output['status'] = 'error';
			$output['id'] = 'trash_post';
			$output['msg'] = ___('Sorry, can not comment on trash post.');
			die(theme_features::json_format($output));				
		/**
		 * If the post is draft
		 */
		} else if(!$status_obj->public && !$status_obj->private){
			do_action('comment_on_draft', $comment_post_ID);
			$output['status'] = 'error';
			$output['id'] = 'draft_post';
			$output['msg'] = ___('Sorry, can not comment draft post.'); 
			die(theme_features::json_format($output));
		/**
		 * If the post needs password
		 */
		} else if(post_password_required($comment_post_ID)){
			do_action('comment_on_password_protected', $comment_post_ID);
			$output['status'] = 'error';
			$output['id'] = 'need_pwd';
			$output['msg'] = ___('Sorry, the post needs password to comment.');
			die(theme_features::json_format($output));
		}
	}
	public static function frontend_display(){
		global $post;
		$comment_count = (int)get_comments_number();
		$com_hide_class = is_singular() ? null : 'hide';
		?>
		<dl id="comment-box-<?php echo $post->ID;?>" class="comment-box mod <?php echo $com_hide_class;?>">
			<dt class="comment-box-title mod-title">
				<?php echo sprintf(___('Total %d comments'),$comment_count);?>
				<?php if(comments_open()){ ?>
					<a href="javascript:void(0)" class="add-comment add-comment-top add-comment-<?php echo $post->ID;?>" data-post-id="<?php echo $post->ID;?>"><span class="icon-comment"></span><span class="after-icon"><?php echo ___('Join the comments');?></span></a>
				<?php } ?>
				
			</dt>
			<dd class="mod-body">
				<div class="comment-tip"><?php echo status_tip('loading',___('Loading comment list, please wait...'));?></div>
				<div class="comments-container"></div>
				<?php
				/** 
				 * check comment open
				 */
				if(comments_open()){ ?>
					<a href="javascript:void(0)" class="add-comment add-comment-bottom btn add-comment-<?php echo $post->ID;?>" data-post-id="<?php echo $post->ID;?>"><span class="icon-comment"></span><span class="after-icon"><?php echo ___('Join the comments');?></span></a>
				<?php } ?>
			</dd>
		</dl>
		<?php
	}
	public static function frontend_seajs_alias($alias){
		$alias[self::$style_id] = theme_features::get_theme_includes_js(__DIR__);
		return $alias;
	}
	public static function frontend_seajs_use(){
		// if(is_singular()) return false;
		?>
		seajs.use('<?php echo self::$style_id;?>',function(m){
			m.config.process_url = '<?php echo theme_features::get_process_url(array('action' => self::$iden));?>';
			<?php if(is_singular()){ ?>
				m.config.is_singular = true;
			<?php } ?>
			m.config.lang.M00001 = '<?php echo ___('Loading, please wait...');?>';
			m.config.lang.M00002 = '<?php echo ___('Commented successfully, thank you!');?>';
			m.config.lang.M00003 = '<?php echo ___('Message');?>';
			m.config.lang.M00004 = '<?php echo ___('No comment yet, you are first commenter.');?>';
			m.config.lang.M00005 = '<?php echo ___('Comment');?>';
			m.config.lang.M00006 = '<?php echo ___('Post comment');?>';
			m.config.lang.M00007 = '<?php echo ___('Nickname');?>';
			m.config.lang.M00008 = '<?php echo ___('Email');?>';
			m.config.lang.M00009 = '<?php echo ___('Closing tip after 3s');?>';
			m.config.lang.M00010 = '<?php echo ___('Reply');?>';
			m.config.lang.M00011 = '<?php echo ___('Error');?>';
			m.config.lang.E00001 = '<?php echo ___('Server error or network is disconnected.');?>';
			m.init();
		});
		<?php
	}
	
}
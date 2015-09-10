<?php
/*
Feature Name:	Comment AJAX
Feature URI:	http://www.inn-studio.com
Version:		2.0.9
Description:	Use AJAX when browse/add/reply comment. (Recommended enable)
Author:			INN STUDIO
Author URI:		http://www.inn-studio.com
*/
add_filter('theme_includes',function($fns){
	$fns[] = 'theme_comment_ajax::init';
	return $fns;
});
class theme_comment_ajax{
	public static $iden = 'theme_comment_ajax';
	public static function init(){
		
		add_filter('theme_options_default',			__CLASS__ . '::options_default');
		
		add_filter('theme_options_save',			__CLASS__ . '::options_save');

		add_action('page_settings',					__CLASS__ . '::display_backend');
		
		add_action('wp_footer',						__CLASS__ . '::thread_comments_js');
		
		if(!self::is_enabled()) 
			return;

		add_filter('js_cache_request',		__CLASS__ . '::js_cache_request');
		add_filter('cache_request',			__CLASS__ . '::cache_request');
		
		add_action('frontend_seajs_use',	__CLASS__ . '::frontend_seajs_use');
		add_filter('frontend_seajs_alias',	__CLASS__ . '::frontend_seajs_alias');
		
		add_action('pre_comment_on_post',	__CLASS__ . '::block_frontend_comment',1);
		add_action('pre_comment_on_post',	__CLASS__ . '::pre_comment_on_post');
		
		add_action('wp_ajax_' . self::$iden,	__CLASS__ . '::process');
		add_action('wp_ajax_nopriv_' . self::$iden,	__CLASS__ . '::process');
		
		
	}

	public static function thread_comments_js(){
		if (theme_cache::is_singular() && comments_open() && (theme_cache::get_option('thread_comments') == 1) && !self::is_enabled()){
			wp_enqueue_script('comment-reply');
		}
	}
	/**
	 * is_enabled
	 *
	 * @return bool
	 * @version 1.0.1
	 */
	private static function is_enabled(){
		return self::get_options('enabled') == 1 ? true : false;
	}
	public static function options_default(array $opts = []){
		$opts[self::$iden]['enabled'] = 1;
		return $opts;
	}
	public static function options_save(array $opts = []){
		if(isset($_POST[self::$iden])){
			$opts[self::$iden] = $_POST[self::$iden];
			/**
			 * if disable
			 */
			if(!isset($_POST[self::$iden]['enabled'])){
				$opts[self::$iden]['enabled'] = -1;
			}
			
		}
		return $opts;
	}
	public static function get_options($key = null){
		static $cache = null;
		if($cache === null)
			$cache = theme_options::get_options(self::$iden);

		if($key){
			return isset($cache[$key]) ? $cache[$key] : false;
		}else{
			return $cache;
		}
	}
	public static function display_backend(){
		$is_checked = self::is_enabled() ? ' checked="checked" ' : null;
		?>
		<fieldset>
			<legend><?= ___('Comment AJAX settings');?></legend>
			<p class="description"><?= ___('Submitted comment without refreshing page. Recommended enable to improve the user experience.');?></p>
			<table class="form-table">
				<tbody>
					<tr>
						<th scope="row"><label for="<?= self::$iden;?>-enabled"><?= ___('Enable comment AJAX');?></label></th>
						<td>
							<input id="<?= self::$iden;?>-enabled" name="<?= self::$iden;?>[enabled]" type="checkbox" value="1" <?= $is_checked;?>/>
							<label for="<?= self::$iden;?>-enabled"><?= ___('Enable');?></label>
							
						</td>
					</tr>
				</tbody>
			</table>
		</fieldset>

		<?php
	}
	public static function block_frontend_comment(){
		if(self::is_enabled()){
			if(basename($_SERVER['PHP_SELF']) === 'wp-comments-post.php')
				die(___('Blocked comment from frontend.'));
		}
	}
	public static function process(){

		theme_features::check_referer();
		theme_features::check_nonce();
	
		$output = [];
		
		/**
		 * Check the ajax comment post
		 */
		if(isset($_POST['comment_post_ID']) && is_string($_POST['comment_post_ID'])){
			
			$comment_post_ID = (int)$_POST['comment_post_ID'];
			
			do_action('pre_comment_on_post', $comment_post_ID);

			global $wp_query,$comment, $comments, $post, $wpdb;
			
			$user = wp_get_current_user();		
			/**
			 * Define comment values
			 */
			$comment_author = isset($_POST['author']) && is_string($_POST['author'])? trim($_POST['author']): null;
			
			$comment_author_email = isset($_POST['email']) && is_string($_POST['email']) && is_email($_POST['email']) ? trim($_POST['email']): null;
			
			$comment_author_url = isset($_POST['url']) && is_string($_POST['url']) ? trim($_POST['url']): null;
			
			$comment_content = isset($_POST['comment']) && is_string($_POST['comment']) ? trim($_POST['comment']): null;
			
			$comment_parent = isset($_POST['comment_parent']) && is_numeric($_POST['comment_parent']) ? $_POST['comment_parent'] : null;
			
			$output['status'] = 'success';
			kses_remove_filters();

			/**
			 * If logged
			 */
			if($user->exists()){
				if(empty($use->nickname)){
					if(empty($user->display_name)){
						$user->display_name	= $user->user_login;
					}
				}else{
					if(empty($user->display_name)){
						$user->display_name = $user->display_name;
					}
				}
				$comment_author			= wp_slash( $user->display_name );
				$comment_author_email	= wp_slash( $user->user_email );
				$comment_author_url		= wp_slash( $user->user_url );
				$user_id				= $user->ID ;
				//if(theme_cache::current_user_can('unfiltered_html')){
				//	if ( ! isset( $_POST['_wp_unfiltered_html_comment'] )
				//		|| ! wp_verify_nonce( $_POST['_wp_unfiltered_html_comment'], 'unfiltered-html-comment_' . $comment_post_ID )
				//	) {
						//kses_remove_filters(); // start with a clean slate
						//kses_init_filters(); // set up the filters
				//	}
				//}
			/**
			 * If not login, just visitor
			 */
			}else{
				if((int)theme_cache::get_option('comment_registration') === 1){
					$output['status'] = 'error';
					$output['msg'] = ___('Sorry, you must be logged in to post a comment.');
					die(theme_features::json_format($output));
				}
			}
			/**
			 * Check required 
			 */
			if(theme_cache::get_option('require_name_email')&& !$user->exists()){
				if(empty($comment_author)){
					$output['status'] = 'error';
					$output['code'] = 'invaild_name';
					$output['msg'] = ___('Error: please fill your name.');
					die(theme_features::json_format($output));
				}else if(!$comment_author_email){
					$output['status'] = 'error';
					$output['code'] = 'invaild_email';
					$output['msg'] = ___('Error: please enter a valid email address.');
					die(theme_features::json_format($output));
				}
			}
			/**
			 * If no comment content
			 */
			if(empty($comment_content)){
				$output['status'] = 'error';
				$output['code'] = 'invaild_content';
				$output['msg'] = ___('Error: please type a comment.');
				die(theme_features::json_format($output));
			}
			/**
			 * Compact the information
			 */
			$comment_type = null;
			$commentdata = compact(
				'comment_post_ID',
				'comment_author', 
				'comment_author_email', 
				'comment_author_url', 
				'comment_content',
				'comment_type',
				'comment_parent',
				'user_id'
			);
			/**
			 * Insert new comment and get the comment ID
			 */
			$comment_id = wp_new_comment($commentdata);
			
			/**
			 * Get new comment and set cookie
			 */
			$comment = get_comment($comment_id);
			
			$post = theme_cache::get_post($comment_post_ID);
			/** 
			 * hook
			 */
			do_action('after_theme_comment_ajax',$comment,$post);
			
			do_action( 'set_comment_cookies', $comment, $user );
			/** 
			 * set cookie
			 */
			wp_set_comment_cookies($comment,$user);
			/**
			 * Class style
			 */
			$comment_depth = 1;
			$tmp_c = $comment;
			while($tmp_c->comment_parent != 0){
				$comment_depth++;
				$tmp_c = get_comment($tmp_c->comment_parent);
			}
			
			/**
			 * Check if no error
			 */
			if($output['status'] === 'success'){
				$content = (wp_list_comments([
					'type' => 'comment',
					'callback'=>'theme_functions::theme_comment',
					'echo' => false,
				],[$comment]));
				/**
				 * Check if Reply comment
				 */
				if($comment_parent != 0){
					$output['comment_parent'] = $comment_parent;
					$output['comment'] = '<ul id="children-'.$comment->comment_ID.'" class="children">'.$content.'</ul>';
				}else{
					$output['comment'] = $content;
				}
				$output['msg'] = ___('Commented successfully, thank you!');
				$output['post_id'] = $comment_post_ID;
				die(theme_features::json_format($output));
			}
		
		}

		/**
		 * type
		 */
		$type = isset($_GET['type']) && is_string($_GET['type']) ? $_GET['type'] : null;
		switch($type){
			case 'get-comments':
				/**
				 * comments page
				 */
				$cpage = isset($_GET['cpage']) && is_numeric($_GET['cpage']) ? $_GET['cpage'] : 1;
				
				/**
				 * post id
				 */
				$post_id = isset($_GET['post-id']) && is_numeric($_GET['post-id']) ? $_GET['post-id'] : null;
				if(!$post_id){
					$output['status'] = 'error';
					$output['code'] = 'invaild_post_id';
					$output['msg'] = ___('Post ID is invaild.');
					die(theme_features::json_format($output));
				}
				
				global $post;
				/**
				 * check post exists
				 */
				$post = theme_cache::get_post($post_id);

				if(!$post || ($post->post_type !== 'post' && $post->post_type !== 'page')){
					$output['status'] = 'error';
					$output['code'] = 'invaild_post';
					$output['msg'] = ___('Post is not exist.');
					die(theme_features::json_format($output));
				}
				setup_postdata($post);
				$comments_str = self::get_comments_list($post_id,$cpage);
				//var_dump($comments_str);
				
				$output['status'] = 'success';
				$output['msg'] = ___('Data sent.');

				if($cpage > 0){
					$output['pagination'] = theme_functions::get_comment_pagination([
						'cpaged' => $cpage,
					]);
				}else{
					$output['pagination'] = theme_functions::get_comment_pagination([
						'cpaged' => 999,
					]);
				}
				$output['comments'] = $comments_str;
				$output['debug'] = [
					'cpage' => $cpage
				];
				break;
			
			
		}

		die(theme_features::json_format($output));
	}
	public static function get_comments_list($post_id, &$cpage = 1){

		global $wp_query,$post;

		$wp_query = new WP_Query([
			'p' => $post_id,
			'post_type' => ['post','page'],
		]);
		$post = $wp_query->posts[0];
		
		$comments = self::get_comments([
			'post_id' => $post_id,
		]);
		
		if(!$comments)
			return false;

		$wp_query->comments = $comments;
		/**
		 * set cpage
		 */
		$max_pages = theme_features::get_comment_pages_count($wp_query->comments);

		if( $cpage > $max_pages)
			$cpage = $max_pages;
			
		if( $cpage == 0 )
			$cpage = 1;

		set_query_var('cpage', $cpage);

		$comment_list = wp_list_comments(array(
			'callback'=>'theme_functions::theme_comment',
			'page' => $cpage,
			'echo' => false,
		),$comments);

		return html_minify($comment_list);
	}
	private static function get_comments(array $args = []){
		$args['order'] = 'asc';
		return get_comments($args);
	}
	/** 
	 * pre_comment_on_post
	 */
	public static function pre_comment_on_post($comment_post_ID){
		
		$comment_post_ID = isset($_POST['comment_post_ID']) ? (int) $_POST['comment_post_ID'] : 0;
		$post = theme_cache::get_post($comment_post_ID);
		/**
		 * check comment_status
		 */
		if ( empty( $post->comment_status ) ) {
			do_action('comment_id_not_found', $comment_post_ID);
			$output['status'] = 'error';
			$output['code'] = 'post_not_exists';
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
			$output['code'] = 'comment_closed';
			$output['msg'] = ___('Sorry, comments are closed for this item.');
			die(theme_features::json_format($output));
		/**
		 * If the post is trash
		 */
		}else if('trash' == $status){
			do_action('comment_on_trash', $comment_post_ID);
			$output['status'] = 'error';
			$output['code'] = 'trash_post';
			$output['msg'] = ___('Sorry, can not comment on trash post.');
			die(theme_features::json_format($output));				
		/**
		 * If the post is draft
		 */
		} else if(!$status_obj->public && !$status_obj->private){
			do_action('comment_on_draft', $comment_post_ID);
			$output['status'] = 'error';
			$output['code'] = 'draft_post';
			$output['msg'] = ___('Sorry, can not comment draft post.'); 
			die(theme_features::json_format($output));
		/**
		 * If the post needs password
		 */
		} else if(post_password_required($comment_post_ID)){
			do_action('comment_on_password_protected', $comment_post_ID);
			$output['status'] = 'error';
			$output['code'] = 'need_pwd';
			$output['msg'] = ___('Sorry, the post needs password to comment.');
			die(theme_features::json_format($output));
		}
	}
	public static function frontend_seajs_alias(array $alias = []){
		if(self::can_comment()){
			$alias[self::$iden] = theme_features::get_theme_includes_js(__DIR__);
		}
		return $alias;
	}
	private static function can_comment(){
		static $cache = null;
		if($cache === null)
			$cache = self::is_enabled() && theme_cache::is_singular() && !post_password_required() && comments_open();
		return $cache;
	}
	public static function frontend_seajs_use(){
		if(!self::can_comment())
			return false;
			
		global $post;
			?>
		seajs.use('<?= self::$iden;?>',function(m){
			m.config.pagi_process_url = '<?= theme_features::get_process_url([
				'action' => self::$iden,
				'type' => 'get-comments',
				'post-id' => $post->ID,
				'cpage' => 'n',
			]);?>';
			m.config.process_url = '<?= theme_features::get_process_url([
				'action' => self::$iden,
			]);?>';
			m.config.post_id = <?= $post->ID;?>;
			m.config.lang.M01 = '<?= ___('Loading, please wait...');?>';
			m.config.lang.M02 = '<?= ___('Commented successfully, thank you!');?>';
			
			m.config.lang.M03 = '<i class="fa fa-arrow-left"></i>';
			m.config.lang.M04 = '<i class="fa fa-arrow-right"></i>';
			m.config.lang.M05 = '<?= ___('{n} page');?>';
			
			m.config.lang.E01 = '<?= ___('Sorry, server is busy now, can not respond your request, please try again later.');?>';
			m.init();
		});
		<?php
	}
	public static function js_cache_request(array $output = []){
		if(self::can_comment()){
			global $post;
			$output[self::$iden] = [
				'type' => 'get-comments',
				'post-id' => $post->ID,
				'cpage' => get_query_var('cpage'),
			];
		}
		return $output;
	}

	public static function cache_request(array $output = []){

		if(isset($_GET[self::$iden]) && is_array($_GET[self::$iden])){
			$get = $_GET[self::$iden];
			
			$post_id = isset($get['post-id']) && is_string($get['post-id']) ? (int)$get['post-id'] : null;
			
			$type = isset($get['type']) && is_string($get['type']) ? $get['type'] : null;

			switch($type){
				case 'get-comments':
					if(!$post_id){
						return $output;
					}
					$post = theme_cache::get_post($post_id);
					$pages = theme_features::get_comment_pages_count(self::get_comments([
						'post_id' => $post->ID,
					]));
					/**
					 * cpage
					 */
					if(isset($get['capge']) && is_numeric($get['capge'])){
						$cpage = (int)$get['capge'];
					}else{
						$cpage = theme_cache::get_option('default_comments_page') == 'newest' ? $pages : 1;
					}

					if(!theme_cache::is_user_logged_in()){
						$commenter = wp_get_current_commenter();
						
						$user_name = $commenter['comment_author'];
						$user_url = $commenter['comment_author_url'];
						$avatar_url = theme_cache::get_avatar_url($commenter['comment_author_email']);
						$user_email = $commenter['comment_author_email'];
					}else{
						global $current_user;
						get_currentuserinfo();
						$user_name = $current_user->display_name;
						$user_url = theme_cache::get_author_posts_url($current_user->ID);
						$avatar_url =  theme_cache::get_avatar_url($current_user->ID);
					}
					$output[self::$iden] = [
						'comments' => self::get_comments_list($post_id,$cpage),
						'count' => $post ? $post->comment_count : 0,
						'pages' => $pages,
						'cpage' => $cpage,
						'logged' => theme_cache::is_user_logged_in(),
						'registration' => theme_cache::get_option('comment_registration'),
						'user-name' => esc_html($user_name),
						'user-url' => esc_url($user_url),
						'avatar-url' => $avatar_url,
					];
					if(isset($user_email))
						$output[self::$iden]['user-email'] = $user_email;
					break;
			}
		}
		return $output;
	}
}

?>
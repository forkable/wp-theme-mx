<?php
/*
Feature Name:	Comment AJAX
Feature URI:	http://www.inn-studio.com
Version:		2.0.5
Description:	Use AJAX when browse/add/reply comment. (Recommended enable)
Author:			INN STUDIO
Author URI:		http://www.inn-studio.com
*/
 theme_comment_ajax::init();
class theme_comment_ajax{
	private static $iden = 'theme_comment_ajax';
	public static function init(){
		
		add_filter('theme_options_default',			get_class() . '::backend_options_default');
		// add_filter('theme_options_save',			get_class() . '::backend_options_save');

		// add_action('page_settings',					get_class() . '::backend_options_display');
		
		add_action('wp_footer',						get_class() . '::thread_comments_js');
		if(!self::is_enabled()) return;
		add_action('frontend_seajs_use',			get_class() . '::frontend_seajs_use');
		add_action('pre_comment_on_post',			get_class() . '::block_frontend_comment',1);
		add_action('pre_comment_on_post',			get_class() . '::pre_comment_on_post');
		add_action('wp_ajax_' . get_class(),		get_class() . '::process');
		add_action('wp_ajax_nopriv_' . get_class(),	get_class() . '::process');
		
		
	}

	public static function thread_comments_js(){
		if (is_singular() && comments_open() && (get_option('thread_comments') == 1) && !self::is_enabled()){
			wp_enqueue_script('comment-reply');
		}
	}
	/**
	 * is_enabled
	 *
	 * @return bool
	 * @version 1.0.1
	 * @author KM@INN STUDIO
	 */
	private static function is_enabled(){
		// $opt = theme_options::get_options(self::$iden);
		// return isset($opt['on']) ? true : false;
		return true;
	}
	public static function backend_options_default($options){
		$options[self::$iden]['on'] = 1;
		return $options;
	}
	public static function backend_options_save($options){
		$options[self::$iden] = isset($_POST[self::$iden]) ? $_POST[self::$iden] : false;
		return $options;
	}
	public static function backend_options_display(){
		$options = theme_options::get_options();
		$is_checked = isset($options[self::$iden]['on']) ? ' checked="checked" ' : null;
		?>
		<fieldset>
			<legend><?php echo ___('Comment AJAX Settings');?></legend>
			<p class="description"><?php echo ___('Visitor submitted comment without refreshing page. Recommended enable to improve the user experience.');?></p>
			<table class="form-table">
				<tbody>
					<tr>
						<th scope="row"><label for="comment_ajax_enable"><?php echo ___('Enable comment AJAX');?></label></th>
						<td>
							<input id="comment_ajax_enable" name="comment_ajax[on]" type="checkbox" value="1" <?php echo $is_checked;?>/>
							<label for="comment_ajax_enable"><?php echo ___('Enable');?></label>
							
						</td>
					</tr>
				</tbody>
			</table>
		</fieldset>

		<?php
	}
	public static function block_frontend_comment($comment_post_ID){		
		if(strstr(get_current_url(),'wp-comments-post.php') !== false) die(___('Blocked comment from frontend.'));
	}
	public static function process(){
		!check_referer() && wp_die(___('Referer error'));
	
		$output = array();
		
		/**
		 * Check the ajax comment post
		 */
		if(isset($_POST['comment_post_ID'])){
			$comment_post_ID = isset($_POST['comment_post_ID']) ? (int) $_POST['comment_post_ID'] : 0;
			do_action('pre_comment_on_post', $comment_post_ID);

			global $wp_query,$comment, $comments, $post, $wpdb;
			
			$user = wp_get_current_user();		
			/**
			 * Define comment values
			 */
			$comment_author			= isset($_POST['author'])			? trim(strip_tags($_POST['author'])): null;
			$comment_author_email	= isset($_POST['email'])			? trim($_POST['email']): null;
			$comment_author_url		= isset($_POST['url'])				? trim($_POST['url']): null;
			$comment_content		= isset($_POST['comment'])			? trim($_POST['comment']): null;
			$comment_parent			= isset($_POST['comment_parent'])	? (int)$_POST['comment_parent']: null;
			
			$output['status'] = 'success';
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
				$user_id				= wp_slash( $user->ID );
				if(current_user_can('unfiltered_html')){
					if ( ! isset( $_POST['_wp_unfiltered_html_comment'] )
						|| ! wp_verify_nonce( $_POST['_wp_unfiltered_html_comment'], 'unfiltered-html-comment_' . $comment_post_ID )
					) {
						kses_remove_filters(); // start with a clean slate
						kses_init_filters(); // set up the filters
					}
				}
			/**
			 * If not login, just visitor
			 */
			}else{
				if((int)get_option('comment_registration') === 1){
					$output['status'] = 'error';
					$output['msg'] = ___('Sorry, you must be logged in to post a comment.');
					die(theme_features::json_format($output));
				}
			}
			/**
			 * Check required 
			 */
			if(get_option('require_name_email')&& !$user->exists()){
				if(6 > strlen($comment_author_email)|| '' == $comment_author){
					$output['status'] = 'error';
					$output['msg'] = ___('Error: please fill the required fields(name, email).');
					die(theme_features::json_format($output));
				}else if(!is_email($comment_author_email)){
					$output['status'] = 'error';
					$output['msg'] = ___('Error: please enter a valid email address.');
					die(theme_features::json_format($output));
				}
			}
			/**
			 * If no comment content
			 */
			if(empty($comment_content)){
				$output['status'] = 'error';
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
			
			$post = get_post($comment_post_ID);
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
				$content = html_compress(wp_list_comments(array(
					'callback'=>'theme_functions::theme_comment',
					'echo' => false,
				),array($comment)));
				/**
				 * Check if Reply comment
				 */
				if($comment_parent != 0){
					$output['des']['comment_parent'] = $comment_parent;
					$output['des']['comment'] = '<ul id="children-'.$comment->comment_ID.'" class="children">'.$content.'</ul>';
				}else{
					$output['des']['comment'] = $content;
				}
				$output['msg'] = ___('Commented successfully, thank you!');
				$output['des']['post_id'] = $comment_post_ID;
			}
		/** 
		 * if is pagination
		 */
		}else if(isset($_GET['type']) && 
			$_GET['type'] === 'comm_pagination' &&
			isset($_GET['url'])){
			$url = urldecode($_GET['url']);
			/** 
			 * check rewrite
			 */
			$parse_url = parse_url($url);
			if(!$parse_url) die();
			/** 
			 * if not rewrite, such as: http://localhost/?p=1&cpage=4#comments&pid=1
			 */
			if(isset($parse_url['query'])){
				parse_str($parse_url['query'],$query_arr);
				$cpage = isset($query_arr['cpage']) ? (int)$query_arr['cpage'] : null;
				$post_id = isset($query_arr['p']) ? (int)$query_arr['p'] : null;
			/** 
			 * is rewrite, such as: http://localhost/archives/1/comment-page-3#comments&pid=1
			 */
			}else{
				/** 
				 * match $cpage
				 */
				$url_path = $parse_url['path'];
				preg_match('/comment-page-(\d+)/i', $url_path, $matches_cp);
				$cpage = isset($matches_cp[1]) ? (int)$matches_cp[1] : null;
				/** 
				 * match $post_id
				 */
				$url_fragment =  $parse_url['fragment'];
				preg_match('/pid=(\d+)/i', $url_fragment, $matches_fr);
				$post_id = isset($matches_fr[1]) ? (int)$matches_fr[1] : null;
			}

			if(!$cpage || !$post_id){
				$output['status'] = 'error';
				$output['msg'] = ___('Param error');
			}else{
				global $post;
				$comments = get_comments(array(
					'post_id' => $post_id,
					'order' => 'asc',
					'status' => 'approve',
				));
				$post = get_post($post_id);
				$content = null;

				$comments_str = wp_list_comments(array(
					'callback'=>'theme_functions::theme_comment',
					'per_page' => get_option('comments_per_page'),
					'page' => $cpage,
					'echo' => false,
					'reverse_top_level' => get_option('comment_order') === 'asc' ? false : true,
					'reverse_children' => get_option('comment_order') === 'asc' ? false : true,
				),$comments);
				
				$output['status'] = 'success';
				$output['msg'] = ___('Data sent.');
				$output['des']['pagination'] = theme_functions::get_comment_pagination(array(
					'classes' => 'comments-pagination comment-pagination-above',
					'cpaged' => $cpage,
					'below' => true,
					'max_pages' => get_comment_pages_count($comments,get_option('comments_per_page'),get_option('thread_comments')),
				));
				$output['des']['comments'] = $comments_str;

			}
		}
		die(theme_features::json_format($output,true));
	}
	/** 
	 * pre_comment_on_post
	 */
	public static function pre_comment_on_post($comment_post_ID){
		/** 
		 * check nonce
		 */
		theme_features::check_nonce();
		
		$comment_post_ID = isset($_POST['comment_post_ID']) ? (int) $_POST['comment_post_ID'] : 0;
		$post = get_post($comment_post_ID);
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
	public static function frontend_seajs_use(){
		if(!self::is_enabled()) return false;
		if(is_singular()){
			?>
			seajs.use('<?php echo theme_features::get_theme_includes_js(__FILE__);?>',function(m){
				m.config.process_url = '<?php echo theme_features::get_process_url(array('action' => self::$iden));?>';
				m.config.lang.M00001 = '<?php echo ___('Loading, please wait...');?>';
				m.config.lang.M00002 = '<?php echo ___('Commented successfully, thank you!');?>';
				m.config.lang.M00003 = '<?php echo ___('Message');?>';
				m.config.lang.E00001 = '<?php echo ___('Server error or network is disconnected.');?>';
				m.init();
			});
			<?php
		}
	}
	/** 
	 * backend_cache_request
	 */
	public static function backend_cache_request($output){
		if($_GET[self::$iden] == 1){
			$nonce = wp_create_nonce(self::$iden . session_id());
			$output[self::$iden] = array(
				'nonce' => $nonce
			);
		}
	}
}

?>
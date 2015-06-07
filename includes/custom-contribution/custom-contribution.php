<?php
/** 
 * @version 1.0.1
 */
add_filter('theme_includes',function($fns){
	$fns[] = 'theme_custom_contribution::init';
	return $fns;
});
class theme_custom_contribution{
	public static $iden = 'theme_custom_contribution';
	public static $page_slug = 'account';
	public static $file_exts = array('png','jpg','gif');
	public static $thumbnail_size = 'large';
	public static $pages = [];
	public static $post_meta_key = array(
		'bdyun' => '_theme_ctb_bdyun'
	);
	public static function init(){
		add_filter('frontend_seajs_alias',	__CLASS__ . '::frontend_seajs_alias');
	
		add_action('frontend_seajs_use',	__CLASS__ . '::frontend_seajs_use');

		add_filter('theme_options_save', 	__CLASS__ . '::options_save');
		add_filter('theme_options_default', 	__CLASS__ . '::options_default');
		
		
		add_action('wp_ajax_' . self::$iden, __CLASS__ . '::process');

		add_action('wp_enqueue_scripts', 	__CLASS__ . '::frontend_css');

		
		foreach(self::get_tabs() as $k => $v){
			$nav_fn = 'filter_nav_' . $k; 
			add_filter('account_navs',__CLASS__ . "::$nav_fn",$v['filter_priority']);
		}

		add_filter('wp_title',				__CLASS__ . '::wp_title',10,2);

		add_action('page_settings',			__CLASS__ . '::display_backend');
	}
	public static function wp_title($title, $sep){
		if(!self::is_page()) 
			return $title;
			
		if(self::get_tabs(get_query_var('tab'))){
			$title = self::get_tabs(get_query_var('tab'))['text'];
		}
		return $title . $sep . get_bloginfo('name');
	}
	public static function filter_query_vars($vars){
		if(!in_array('tab',$vars)) $vars[] = 'tab';
		return $vars;
	}
	public static function filter_nav_post($navs){
		$navs['post'] = '<a href="' . esc_url(self::get_tabs('post')['url']) . '">
			<i class="fa fa-' . self::get_tabs('post')['icon'] . ' fa-fw"></i> 
			' . self::get_tabs('post')['text'] . '
		</a>';
		return $navs;
	}
	public static function get_des(){
		return stripslashes(self::get_options('description'));
	}
	public static function display_backend(){
		$opt = (array)self::get_options();
		?>
		<fieldset>
			<legend><?= ___('Contribution settings');?></legend>
			<table class="form-table">
				<tr>
					<th><?= ___('Shows categories');?></th>
					<td>
						<?php theme_features::cat_checkbox_list(self::$iden,'cats');?>
					</td>
				</tr>
				<tr>
					<th><label for="<?= self::$iden;?>-tags-number"><?= ___('Shows tags number');?></label></th>
					<td>
						<input class="short-text" type="number" name="<?= self::$iden;?>[tags-number]" id="<?= self::$iden;?>-tags-number" value="<?= isset($opt['tags-number']) ?  $opt['tags-number'] : 6;?>">
					</td>
				</tr>
				<tr>
					<th><label for="<?= self::$iden;?>-description"><?= ___('You can write some description for contribution page header. Please use tag <p> to wrap your HTML codes.');?></label></th>
					<td>
						<textarea name="<?= self::$iden;?>[description]" id="<?= self::$iden;?>-description" class="widefat" rows="5"><?= self::get_des();?></textarea>
					</td>
				</tr>
			</table>
		</fieldset>
		<?php
	}
	public static function options_save($opts){
		if(!isset($_POST[self::$iden]))
			return $opts;

		$opts[self::$iden] = $_POST[self::$iden];
		return $opts;
	}
	public static function options_default($opts){
		$opts[self::$iden]['tags-number'] = 6;
		return $opts;
	}
	public static function get_options($key = null){
		static $caches = null;
		if($caches === null)
			$caches = (array)theme_options::get_options(self::$iden);
			
		if(empty($key)){
			return $caches;
		}else{
			return isset($caches[$key]) ? $caches[$key] : null;
		}
	}
	public static function get_url(){
		static $caches = [];
		if(isset($caches[self::$iden]))
			return $caches[self::$iden];
			
		$page = theme_cache::get_page_by_path(self::$page_slug);
		$caches[self::$iden] = esc_url(get_permalink($page->ID));
		return $caches[self::$iden];
	}
	public static function get_tabs($key = null){
		$baseurl = self::get_url();
		$tabs = array(
			'post' => array(
				'text' => isset($_GET['post']) ? ___('Edit post') : ___('Post contribution'),
				'icon' => 'paint-brush',
				'url' => esc_url(add_query_arg('tab','post',$baseurl)),
				'filter_priority' => 20,
			),
		);
		if($key){
			return isset($tabs[$key]) ? $tabs[$key] : false;
		}
		return $tabs;
	}
	public static function is_page(){
		static $cache = null;
		if($cache === null)
			$cache = is_page(self::$page_slug) && self::get_tabs(get_query_var('tab'));
			
		return $cache;
	}
	private static function wp_get_attachment_image_src($attachment_id, $size = 'thumbnail'){
		static $caches = [];
		$cache_id = $attachment_id . $size;
		if(!isset($caches[$cache_id]))
			$caches[$cache_id] = call_user_func_array('wp_get_attachment_image_src',func_get_args());

		return $caches[$cache_id];
	}
	public static function in_edit_post_status($post_status){
		return in_array($post_status, ['publish','pending']);
	}
	public static function process(){
		$output = [];
		
		theme_features::check_referer();
		theme_features::check_nonce();
		
		$type = isset($_REQUEST['type']) ? $_REQUEST['type'] : null;
		switch($type){
			/**
			 * case upload
			 */
			case 'upload':
				/** 
				 * if not image
				 */
				$filename = isset($_FILES['img']['name']) ? $_FILES['img']['name'] : null;
				$file_ext = $filename ? array_slice(explode('.',$filename),-1,1)[0] : null;
				if(!in_array($file_ext,self::$file_exts)){
					$output['status'] = 'error';
					$output['code'] = 'invaild_file_type';
					$output['msg'] = ___('Invaild file type.');
					die(theme_features::json_format($output));
				}
				/** rename file name */
				$_FILES['img']['name'] = get_current_user_id() . '-' . current_time('YmdHis') . '-' . rand(100,999). '.' . $file_ext;
				
				/** 
				 * pass
				 */
				require_once( ABSPATH . 'wp-admin/includes/image.php' );
				require_once( ABSPATH . 'wp-admin/includes/file.php' );
				require_once( ABSPATH . 'wp-admin/includes/media.php' );

				$attach_id = media_handle_upload('img',0);
				if(is_wp_error($attach_id)){
					$output['status'] = 'error';
					$output['code'] = $attach_id->get_error_code();
					$output['msg'] = $attach_id->get_error_message();
					die(theme_features::json_format($output));
				}else{
					$output = self::get_thumbnail_data($attach_id,$output);
					$output['status'] = 'success';
					$output['attach-id'] = $attach_id;
					$output['msg'] = ___('Upload success.');
					die(theme_features::json_format($output));
				}
				break;
			/**
			 * post
			 */
			case 'post':
				$ctb = isset($_POST['ctb']) && is_array($_POST['ctb']) ? $_POST['ctb'] : null;
				
				$edit_post_id = isset($_POST['post-id']) && is_numeric($_POST['post-id']) ? (int)$_POST['post-id'] : 0;
				/**
				 * check edit
				 */
				if($edit_post_id != 0){
					/**
					 * check post exists
					 */
					$old_post = self::get_post($edit_post_id);
					if(!$old_post || 
						$old_post->post_type !== 'post' || 
						!self::in_edit_post_status($old_post->post_status)
					){
						die(theme_features::json_format([
							'status' => 'error',
							'code' => 'post_not_exist',
							'msg' => ___('Sorry, the post does not exist.'),
						]));
					}
					/**
					 * check post author is myself
					 */
					if($old_post->post_author != get_current_user_id()){
						die(theme_features::json_format([
							'status' => 'error',
							'code' => 'post_not_exist',
							'msg' => ___('Sorry, you are not the post author, can not edit it.'),
						]));
					}
					/**
					 * check post edit lock status
					 */
					$lock_user_id = self::wp_check_post_lock($edit_post_id);
					if($lock_user_id){
						die(theme_features::json_format([
							'status' => 'error',
							'code' => 'post_not_exist',
							'msg' => ___('Sorry, the post does not exist.'),
						]));
					}
				}
				if(is_null_array($ctb)){
					$output['status'] = 'error';
					$output['code'] = 'invaild_ctb_param';
					$output['msg'] = ___('Invaild contribution param.');
					die(theme_features::json_format($output));
				}
				/**
				 * post title
				 */
				$post_title = isset($ctb['post-title']) && is_string($ctb['post-title']) ? trim($ctb['post-title']) : null;
				if(!$post_title){
					$output['status'] = 'error';
					$output['code'] = 'invaild_post_title';
					$output['msg'] = ___('Please write the post title.');
					die(theme_features::json_format($output));
				}
				/**
				 * post content
				 */
				$post_content = isset($ctb['post-content']) && is_string($ctb['post-content']) ? trim($ctb['post-content']) : null;
				if(!$post_content){
					$output['status'] = 'error';
					$output['code'] = 'invaild_post_content';
					$output['msg'] = ___('Please write the post content.');
					die(theme_features::json_format($output));
				}
				/**
				 * check thumbnail cover
				 */
				$thumbnail_id = isset($ctb['thumbnail-id']) && is_numeric($ctb['thumbnail-id']) ? (int)$ctb['thumbnail-id'] : null;
				if(!$thumbnail_id){
					$output['status'] = 'error';
					$output['code'] = 'invaild_thumbnail_id';
					$output['msg'] = ___('Please set an image as post thumbnail');
					die(theme_features::json_format($output));
				}
				/**
				 * cats
				 */
				$cat_id = isset($ctb['cat']) && is_numeric($ctb['cat']) ?(int)$ctb['cat'] : null;
				if($cat_id < 1){
					$output['status'] = 'error';
					$output['code'] = 'invaild_cat_id';
					$output['msg'] = ___('Please select a category.');
					die(theme_features::json_format($output));
				}
				/**
				 * get all cats
				 */
				$all_cats = [];
				theme_features::get_all_cats_by_child($cat_id,$all_cats);
				/**
				 * tags
				 */
				$tags = isset($ctb['tags']) && is_array($ctb['tags']) ? $ctb['tags'] : [];
				if(!empty($tags)){
					$tags = array_map(function($tag){
						if(!is_string($tag)) return null;
						return $tag;
					},$tags);
				}
				/**
				 * post status
				 */
				
				if(current_user_can('publish_posts')){
					$post_status = 'publish';
				}else{
					$post_status = 'pending';
				} 
				/**
				 * old post
				 */
				/*****************************
				 * PASS ALL, WRITE TO DB
				 *****************************/
				/**
				 * update post
				 */
				if($edit_post_id != 0){
					/**
					 * update post
					 */
					$post_id = wp_update_post([
						'ID' => $edit_post_id,
						'post_title' => $post_title,
						'post_status' => $old_post->post_status,
						'post_type' => $old_post->post_type,
						'post_content' => fliter_script($post_content),
						'post_category' => $all_cats,
						'tags_input' => $tags,
					],true);
					
				/**
				 * insert post
				 */
				}else{
					$post_id = wp_insert_post([
						'post_title' => $post_title,
						'post_content' => fliter_script($post_content),
						'post_status' => $post_status,
						'post_author' => get_current_user_id(),
						'post_category' => $all_cats,
						'tags_input' => $tags,
					],true);
				}
				/**
				 * check error
				 */
				if(is_wp_error($post_id)){
					$output['status'] = 'error';
					$output['code'] = $post_id->get_error_code();
					$output['msg'] = $post_id->get_error_message();
					die(theme_features::json_format($output));
				}/** end post error */
				
				/**
				 * set thumbnail and post parent
				 */
				$attach_ids = isset($ctb['attach-ids']) && is_array($ctb['attach-ids']) ? array_map('intval',$ctb['attach-ids']) : null;
				if(!is_null_array($attach_ids)){
					/** set post thumbnail */
					set_post_thumbnail($post_id,$thumbnail_id);
					
					/** set attachment post parent */
					foreach($attach_ids as $attach_id){
						$post = self::get_post($attach_id);
						if(!$post || $post->post_type !== 'attachment')
							continue;
						wp_update_post([
							'ID' => $attach_id,
							'post_parent' => $post_id,
						]);
					}
				}/** end set post thumbnail */

				/**
				 * if new post
				 */
				if($edit_post_id == 0){
					/**
					 * pending status
					 */
					if($post_status === 'pending'){
						$output['status'] = 'success';
						$output['msg'] = ___('Your post submitted successful, it will be published after approve in a while.');
						die(theme_features::json_format($output));
					}else{
						$output['status'] = 'success';
						$output['msg'] = sprintf(
							___('Congratulation! Your post has been published. You can %s or %s.'),
							'<a href="' . esc_url(get_permalink($post_id)) . '" title="' . esc_attr(get_the_title($post_id)) . '">' . ___('View it now') . '</a>',
							'<a href="javascript:location.href=location.href;">' . ___('countinue to write a new post') . '</a>'
						);

						/**
						 * add point
						 */
						if(class_exists('theme_custom_point')){
							$post_publish_point = theme_custom_point::get_point_value('post-publish');
							$output['point'] = array(
								'value' => $post_publish_point,
								'detail' => ___('Post published'),
							);
						}/** end point */
					}/** end post status */
				}else{
					$output['status'] = 'success';
					if($old_post->post_status == 'publish'){
						$output['msg'] = ___('Your post has updated successful.') . ' <a href="' . esc_url(get_permalink($post_id)) . '" target="_blank">' . ___('Views it now') . '</a>';
					}else{
						$output['msg'] = ___('Your post has updated successful.');
					}
					die(theme_features::json_format($output));
				}/** end post edit */
					
				die(theme_features::json_format($output));
		}

		die(theme_features::json_format($output));
	}
	/**
	 * Get thumbnail data
	 *
	 * @param int $attach_id
	 * @param array $output
	 * @return array
	 * @version 1.0.0
	 */
	public static function get_thumbnail_data($attach_id, array $output = []){
		foreach([ 'thumbnail' ,'medium','large','full' ] as $size){
			$output[$size] = [
				'url' => 
				self::wp_get_attachment_image_src($attach_id,$size)[0],
				'width' => self::wp_get_attachment_image_src($attach_id,$size)[1],
				'height' => self::wp_get_attachment_image_src($attach_id,$size)[2],
			];
		}
		return $output;
	}
	public static function wp_check_post_lock( $post_id ) {
	    if(function_exists('wp_check_post_lock'))
	    	return wp_check_post_lock($post_id);
 
        if ( !$lock = get_post_meta($post_id, '_edit_lock', true ) )
        return false;
 
	    $lock = explode( ':', $lock );
	    $time = $lock[0];
	    $user = isset( $lock[1] ) ? $lock[1] : get_post_meta( $post_id, '_edit_last', true );
	 
	    /** This filter is documented in wp-admin/includes/ajax-actions.php */
	    $time_window = apply_filters( 'wp_check_post_lock_window', 150 );
	 
	    if ( $time && $time > time() - $time_window && $user != get_current_user_id() )
	        return $user;
	    return false;
    }
	public static function is_edit(){
		static $cache = null;
		if($cache !== null)
			return $cache;
			
		$post_id = isset($_GET['post']) && is_numeric($_GET['post']) ? (int)$_GET['post'] : false;
		if(!$post_id){
			$cache = false;
			return false;
		}
			
		$post = self::get_post($post_id);
		$cache = $post && self::in_edit_post_status($post->post_status) && $post->post_type === 'post' ? $post_id : false;
		return $cache;
	}
	public static function get_post_attachs($post_id){
		$post = self::get_post($post_id);
		if(!$post)
			return false;

		return get_children([
			'post_parent' => $post_id,
			'post_type' => 'attachment',
			'post_mime_type' => 'image',
			'posts_per_page' => -1,
		]);
	}
	public static function get_post($post_id){
		static $caches = [];
		if(!isset($caches[$post_id]))
			$caches[$post_id] = get_post($post_id);

		return $caches[$post_id];
	}
	public static function frontend_seajs_alias($alias){
		if(self::is_page()){
			$alias[self::$iden] = theme_features::get_theme_includes_js(__DIR__);
		}
		return $alias;
	}
	public static function frontend_seajs_use(){
		if(!self::is_page()) 
			return false;
		?>
		seajs.use('<?= self::$iden;?>',function(m){
			m.config.process_url = '<?= theme_features::get_process_url(array('action' => self::$iden));?>';
			m.config.default_size = '<?= self::$thumbnail_size;?>';
			m.config.lang = {
				M00001 : '<?= ___('Loading, please wait...');?>',
				M00002 : '<?= ___('Uploading {0}/{1}, please wait...');?>',
				M00003 : '<?= ___('Click to delete');?>',
				M00004 : '<?= ___('{0} files have been uploaded.');?>',
				M00005 : '<?= ___('Source');?>',
				M00006 : '<?= ___('Click to view source');?>',
				M00007 : '<?= ___('Set as cover.');?>',
				M00008 : '<?= ___('Optional: some description');?>',
				M00009 : '<?= ___('Insert');?>',
				M00010 : '<?= ___('Preview');?>',
				M00011 : '<?= ___('Large size');?>',
				M00012 : '<?= ___('Medium size');?>',
				M00013 : '<?= ___('Small size');?>',
				E00001 : '<?= ___('Sorry, server error please try again later.');?>'
			};
			<?php
			if(self::is_edit()){
				$thumbnail_id = get_post_thumbnail_id(self::is_edit());
				$attachs = [];
				
				$attachs_data = self::get_post_attachs(self::is_edit());
				foreach($attachs_data as $v){
					$attachs[$v->ID] = self::get_thumbnail_data($v->ID);
					$attachs[$v->ID]['attach-id'] = $v->ID;
				}

				if($thumbnail_id && !empty($attachs_data)){
					$unshift_attach = $attachs[$thumbnail_id];
					unset($attachs[$thumbnail_id]);
					array_unshift($attachs,$unshift_attach);
				}
				asort($attachs);
				?>
				m.config.edit = 1;
				m.config.thumbnail_id = <?= $thumbnail_id;?>;
				m.config.attachs = <?= json_encode($attachs);?>;
				<?php
			}
			?>
			m.init();
		});
		<?php
	}
	public static function frontend_css(){
		if(!self::is_page()) 
			return false;
			
		wp_enqueue_style(
			self::$iden,
			theme_features::get_theme_includes_css(__DIR__),
			'frontend',
			theme_file_timestamp::get_timestamp()
		);
	}

}
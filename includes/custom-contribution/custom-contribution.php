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
	public static function filter_nav_contribution($navs){
		$navs['contribution'] = '<a href="' . esc_url(self::get_tabs('contribution')['url']) . '">
			<i class="fa fa-' . self::get_tabs('contribution')['icon'] . ' fa-fw"></i> 
			' . self::get_tabs('contribution')['text'] . '
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
					<th><label for="<?= self::$iden;?>-description"><?= esc_html(___('You can write some description for contribution page header. Please use tag <p> to wrap your HTML codes.'));?></label></th>
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
		static $caches = [];
		if(empty($caches))
			$caches = theme_options::get_options(self::$iden);
			
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
			'contribution' => array(
				'text' => ___('Post contribution'),
				'icon' => 'paint-brush',
				'url' => esc_url(add_query_arg('tab','contribution',$baseurl)),
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
	private static function wp_get_attachment_image_src(){
		static $caches = [];
		$cache_id = md5(serialize(func_get_args()));
		if(!isset($caches[$cache_id]))
			$caches[$cache_id] = call_user_func_array('wp_get_attachment_image_src',func_get_args());

		return $caches[$cache_id];
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
					$output['status'] = 'success';
					$output['thumbnail'] = [
						'url' => 
						self::wp_get_attachment_image_src($attach_id,'thumbnail')[0],
						'width' => self::wp_get_attachment_image_src($attach_id,'thumbnail')[1],
						'height' => self::wp_get_attachment_image_src($attach_id,'thumbnail')[2],
					];
					$output['medium'] = [
						'url' => 
						self::wp_get_attachment_image_src($attach_id,'medium')[0],
						'width' => self::wp_get_attachment_image_src($attach_id,'medium')[1],
						'height' => self::wp_get_attachment_image_src($attach_id,'medium')[2],
					];
					$output['large'] = [
						'url' => 
						self::wp_get_attachment_image_src($attach_id,'large')[0],
						'width' => self::wp_get_attachment_image_src($attach_id,'large')[1],
						'height' => self::wp_get_attachment_image_src($attach_id,'large')[2],
					];
					$output['full'] = [
						'url' => 
						self::wp_get_attachment_image_src($attach_id,'full')[0],
						'width' => self::wp_get_attachment_image_src($attach_id,'full')[1],
						'height' => self::wp_get_attachment_image_src($attach_id,'full')[2],
					];
					
					$output['attach-id'] = $attach_id;
					$output['msg'] = ___('Upload success.');
					die(theme_features::json_format($output));
				}
				break;
			case 'post':
				$ctb = isset($_POST['ctb']) && is_array($_POST['ctb']) ? $_POST['ctb'] : null;
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
				 * insert
				 */
				$post_id = wp_insert_post(array(
					'post_title' => $post_title,
					'post_content' => fliter_script($post_content),
					'post_status' => $post_status,
					'post_author' => get_current_user_id(),
					'post_category' => $all_cats,
					'tags_input' => $tags,
				),true);
				if(is_wp_error($post_id)){
					$output['status'] = 'error';
					$output['code'] = $post_id->get_error_code();
					$output['msg'] = $post_id->get_error_message();
				}else{
					/**
					 * set thumbnail and post parent
					 */
					$attach_ids = isset($ctb['attach-ids']) && is_array($ctb['attach-ids']) ? array_map('intval',$ctb['attach-ids']) : null;
					if(!is_null_array($attach_ids)){
						/** set post thumbnail */
						set_post_thumbnail($post_id,$thumbnail_id);
						
						/** set attachment post parent */
						foreach($attach_ids as $attach_id){
							$post = get_post($attach_id);
							if(!$post || $post->post_type !== 'attachment')
								continue;
							wp_update_post([
								'ID' => $attach_id,
								'post_parent' => $post_id,
							]);
						}
					}
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
						}
						die(theme_features::json_format($output));
					}
					
				}
				break;
		}

		die(theme_features::json_format($output));
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
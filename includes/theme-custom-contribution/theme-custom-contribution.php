<?php
/** 
 * @version 1.0.0
 */
theme_custom_contribution::init();
class theme_custom_contribution{
	public static $iden = 'theme-custom-contribution';
	public static $page_slug = 'contribution';
	public static $file_exts = array('png','jpg','gif');
	public static $pages = array();
	public static $post_meta_key = array(
		'bdyun' => '_theme_ctb_bdyun'
	);
	public static function init(){
		/** filter */
		add_filter('frontend_seajs_alias',	get_class() . '::frontend_seajs_alias');

		
		/** action */
		add_action('init', 					get_class() . '::page_create');

		add_action('frontend_seajs_use',	get_class() . '::frontend_seajs_use');
		
		add_action('wp_ajax_' . self::$iden, get_class() . '::process');

		add_action('wp_enqueue_scripts', 	get_class() . '::frontend_css');

	}
	public static function page_create(){
		if(!current_user_can('manage_options')) return false;
		
		$page_slugs = array(
			self::$page_slug => array(
				'post_content' 	=> '[' . self::$page_slug . ']',
				'post_name'		=> self::$page_slug,
				'post_title'	=> ___('Contribution'),
				'page_template'	=> 'page-' . self::$page_slug . '.php',
			)
		);
		
		$defaults = array(
			'post_content' 		=> '[post_content]',
			'post_name' 		=> null,
			'post_title' 		=> null,
			'post_status' 		=> 'publish',
			'post_type'			=> 'page',
			'comment_status'	=> 'closed',
		);
		foreach($page_slugs as $k => $v){
			$page = get_page_by_path($k);
			if(!$page){
				$r = wp_parse_args($v,$defaults);
				$page_id = wp_insert_post($r);
			}
		}
	}
	public static function display_backend(){
		
	}
	public static function get_options($key = null){
		$opt = theme_options::get_options(self::$iden);
		if(empty($key)){
			return $opt;
		}else{
			return isset($opt[$key]) ? $opt[$key] : null;
		}
	}
	public static function get_url(){
		$page = get_page_by_path(self::$page_slug);
		return empty($page) ? null : get_permalink($page->ID);
	}
	public static function process(){
		$output = array();
		
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
					$output['thumbnail'] = wp_get_attachment_image_src($attach_id,'thumbnail')[0];
					$output['original'] = wp_get_attachment_image_src($attach_id,'full')[0];
					
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
					$output['msg'] = ___('Invaild post title.');
					die(theme_features::json_format($output));
				}
				/**
				 * attach
				 */
				$attach_ids = isset($ctb['attach-ids']) && is_array($ctb['attach-ids']) ? array_map('intval',$ctb['attach-ids']) : array();
				$attach_htmls = '';
				if(!is_null_array($attach_ids)){
					/**
					 * get attachment url
					 */
					foreach($attach_ids as $attach_id){
						$img_full_attrs = wp_get_attachment_image_src($attach_id,'full');
						if(!empty($img_full_attrs)){
							$img_large_attrs = wp_get_attachment_image_src($attach_id,'large');
							/**
							 * if thumbnail src = full src, do not echo <a>
							 */
							if($img_full_attrs[0] == $img_large_attrs[0]){
								$attach_html = '<img src="' . $img_large_attrs[0] . '" alt="' . esc_attr($post_title). '" width="' . $img_large_attrs[1] . '" height="' . $img_large_attrs[2] . '">';
							}else{
								$attach_html = '<a href="' . $img_full_attrs[0] . '" target="_blank" title="' . sprintf(___('Views source image: %d x %d'),$img_full_attrs[1],$img_full_attrs[2]) . '">
									<img src="' . $img_large_attrs[0] . '" alt="' . esc_attr($post_title). '" width="' . $img_large_attrs[1] . '" height="' . $img_large_attrs[2] . '">
								</a>';
							}
							$attach_htmls .= '<p>' . $attach_html . '</p>';
						}
					}
				} /** end if have attachment */
				/**
				 * post content
				 */
				$post_content = isset($ctb['post-content']) && is_string($ctb['post-content']) ? trim($ctb['post-content']) : null;
				if(!$post_content){
					$output['status'] = 'error';
					$output['code'] = 'invaild_post_content';
					$output['msg'] = ___('Invaild post content.');
					die(theme_features::json_format($output));
				}
				/**
				 * cats
				 */
				$cats = isset($ctb['cats']) && is_array($ctb['cats']) ? $ctb['cats'] : array();
				if(!empty($cats)){
					$cats = array_map('intval',$cats);
					
				}
				/**
				 * tags
				 */
				$tags = isset($ctb['tags']) && is_array($ctb['tags']) ? $ctb['tags'] : array();
				if(!empty($tags)){
					$tags = array_map(function($tag){
						if(!is_string($tag)) return null;
						return $tag;
					},$tags);
				}
				/**
				 * post status
				 */
				$cap = get_user_meta(get_current_user_id(),'wp_capabilities', true);
				switch($cap){
					case 'contributor':
					case 'subscriber':
						$post_status = 'pending';
						break;
					default:
						$post_status = 'publish';
				} 
				/**
				 * insert
				 */
				$post_id = wp_insert_post(array(
					'post_title' => $post_title,
					'post_content' => $post_content . $attach_htmls,
					'post_status' => $post_status,
					'post_author' => get_current_user_id(),
					'post_category' => $cats,
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
					if(!empty($attach_ids)){
						/** set post thumbnail */
						set_post_thumbnail($post_id,$attach_ids[0]);
						
						/** set attachment post parent */
						foreach($attach_ids as $attach_id){
							wp_update_post(array(
								'ID' => $attach_id,
								'post_parent' => $post_id,
							));
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
						 * update post meta
						 */
						if(class_exists('theme_custom_storage') && isset($ctb['storage']) && !is_null_array($ctb['storage'])){
							add_post_meta($post_id,theme_custom_storage::$post_meta_key['key'],$ctb['storage']);
						}
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
		if(!is_user_logged_in() || !is_page(self::$page_slug)) return $alias;

		$alias[self::$iden] = theme_features::get_theme_includes_js(__FILE__);
		return $alias;
	}
	public static function frontend_seajs_use(){
		if(!is_user_logged_in() || !is_page(self::$page_slug)) return false;
		?>
		seajs.use('<?php echo self::$iden;?>',function(m){
			m.config.process_url = '<?php echo theme_features::get_process_url(array('action' => self::$iden));?>';
			m.config.lang.M00001 = '<?php echo esc_js(___('Loading, please wait...'));?>';
			m.config.lang.E00001 = '<?php echo esc_js(___('Sorry, server error please try again later.'));?>';
			
			m.init();
		});
		<?php
	}
	public static function frontend_css(){
		if(!is_user_logged_in() || !is_page(self::$page_slug)) return;
		wp_enqueue_style(self::$iden,theme_features::get_theme_includes_css(__FILE__,'style',false),false,theme_features::get_theme_info('version'));
	}

}
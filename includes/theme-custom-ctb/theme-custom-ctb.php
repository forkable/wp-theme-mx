<?php
/** 
 * Í¶¸å
 */
theme_custom_ctb::init();
class theme_custom_ctb{
	public static $iden = 'theme_custom_ctb';
	public static $page_slug = 'contribution';
	public static $max_tags_number = 5;
	public static $allow_filetype = array('png','jpg','jpeg','gif');
	public static $pages = array();
	public static function init(){
		add_action('init', get_class() . '::page_create');
		/** filter */
		add_filter('frontend_seajs_alias',			get_class() . '::frontend_seajs_alias');
		/** action */
		add_action('frontend_seajs_use',			get_class() . '::frontend_seajs_use');
		add_action('wp_ajax_' . get_class(),		get_class() . '::process');
		add_action('wp_ajax_nopriv_' . get_class(),	get_class() . '::process');
		// add_action('page_settings',					get_class() . '::backend_display_page_settings');
	}
	public static function backend_display_page_settings(){
		?>
		<fieldset>
			<legend><?php echo ___('Contribution settings');?></legend>
			<p class="description"><?php echo esc_html(___('Which category will be added when contributes'));?></p>
			<table class="form-table">
				<tbody>
					<tr>
						<th scope="row"><label for="tpl-sticky-tags"><?php echo ___('Contribution category');?></label></th>
						<td>
							<?php echo theme_functions::get_cat_checkbox_list('ctb'); ?>
						</td>
					</tr>
				</tbody>
			</table>
		</fieldset>
		<?php
	}
	public static function get_url(){
		return get_permalink(get_page_by_path(self::$page_slug)->ID);
	}
	public static function current_user_can_post(){
		if(is_user_logged_in()){
			return true;
		}else if(get_option('comment_registration') == 0){
			return true;
		}else{
			return false;
		}
	}
	public static function page_create(){
		// var_dump(current_user_can('manage_options'));exit;
		if(!current_user_can('manage_options')) return false;
		
		$page_slugs = array(
			self::$page_slug => array(
				'post_content' 	=> '[' . self::$page_slug . ']',
				'post_name'		=> self::$page_slug,
				'post_title'	=> ___('Share your story'),
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
			$page_id = get_page_by_path($k);
			if(!$page_id){
				$r = wp_parse_args($v,$defaults);
				$page_id = wp_insert_post($r);
			}
			// self::$pages[$v]
		}
	}
	public static function process(){
		$output = array();
		
		theme_features::check_referer();
		theme_features::check_nonce();
		
		$type = isset($_REQUEST['type']) ? $_REQUEST['type'] : null;
		
		switch($type){
			/** 
			 * upload
			 */
			case 'upload':
				/** check user login */
				if(!self::current_user_can_post()){
					$output['status'] = 'error';
					$output['code'] = 'login_required';
					$output['msg'] = ___('Login required.');
					die(theme_features::json_format($output));
				}
				global $current_user;
				get_currentuserinfo();
				$file = isset($_FILES['img']) ? $_FILES['img'] : null;
				if(!$file){
					$output['status'] = 'error';
					$output['code'] = 'invald_param';
					$output['msg'] = ___('Invalid param.');
					die(theme_features::json_format($output));
				}
				if($file['error'] != 0){
					$output['status'] = 'error';
					$output['code'] = 'file_error';
					$output['msg'] = ___('File error.');
					die(theme_features::json_format($output));
				}
				
				/** rename */
				$tmp_filename = $file['tmp_name'];
				/** check file type */
				$file_mime = getimagesize($tmp_filename)['mime'];
				$file_type = strpos($file_mime,'image') === 0 ? explode('/',$file_mime)[1] : null;
				if(!$file_type || !in_array($file_type,self::$allow_filetype)){
					$output['status'] = 'error';
					$output['code'] = 'invald_filetype';
					$output['msg'] = ___('Invalid file type.');
					die(theme_features::json_format($output));
				}
				/** jpeg to jpg */
				if($file_type === 'jpeg') $file_type = 'jpg';
				/** random basename for file */
				$basename = $current_user->ID . '-' . date('YmdHis') . '-' . substr(explode(' ',microtime())[0],2,6) . '.' . $file_type;
				/** include files  */
				require_once( ABSPATH . 'wp-admin/includes/image.php' );
				require_once( ABSPATH . 'wp-admin/includes/file.php' );
				require_once( ABSPATH . 'wp-admin/includes/media.php' );
				/** use wp uploader */
				$_FILES['img']['name'] = $basename;
				$attach_id = media_handle_upload('img',0);
				/** check upload error */
				if(is_wp_error($attach_id)){
					$output['status'] = 'error';
					$output['code'] = $attach_id->get_error_codes();
					$output['msg'] = $attach_id->get_error_message();
					die(theme_features::json_format($output));
				}
				$output['status'] = 'success';
				$output['attach_id'] = $attach_id;
				$output['msg'] = ___('Image has been uploaded.');
				break;
			/** 
			 * post
			 */
			case 'post':
				/** post content */
				$post_content = isset($_POST['content']) ? trim(htmlspecialchars($_POST['content'])) : null;
				if(empty($post_content)){
					$output['status'] = 'error';
					$output['code'] = 'invald_post_content';
					$output['msg'] = ___('Invalid post content.');
					die(theme_features::json_format($output));
				}
				/** post title */
				$post_title = isset($_POST['title']) && trim($_POST['title']) != '' ? htmlspecialchars($_POST['title']) : $post_content;
				
				/** post tags */
				$post_tags = isset($_POST['tags']) ? $_POST['tags'] : null;
				if(!is_null_array($post_tags)){
					$post_tags = array_filter($post_tags);
					$post_tags = array_unique($post_tags);
					$post_tags = array_map('htmlspecialchars',$post_tags);
				}
				/** attachment */
				$attach_id = isset($_POST['attach-id']) ? (int)$_POST['attach-id'] : null;
				if(!empty($attach_id)){
					$attach_id = (int)$_POST['attach-id'];
					/** try to get the attachment */
					$thumbnail = wp_get_attachment_image_src($attach_id);
					/** has thumbnail */
					if($thumbnail){
						$thumbnail_real = wp_get_attachment_image_src($attach_id,'full');
						$thumbnail_html = '<p>
							<a href="' . esc_url($thumbnail_real[0]) . '" target="_blank" title="' . esc_attr($post_title) . '">
								<img src="' . esc_url($thumbnail[0]) . '" alt="' . esc_attr($post_title) . '"/>
							</a>
						</p>';
						$post_content .= html_compress($thumbnail_html);
					}
				}
				/** post category */
				$post_category = isset($_POST['cat']) ? array((int)$_POST['cat']) : null;
				if(!get_category($post_category)) $post_category = null;
				
				/** post status */
				$post_status = is_user_logged_in() ? 'publish' : 'pending';
				/** insert post */
				$post_id = wp_insert_post(array(
					'post_title' => $post_title,
					'post_content' => $post_content,
					'post_status' => $post_status,
					'post_category' => $post_category,
					'tags_input' => $post_tags,
				),true);
				// var_dump($post_id);exit;
				/** return */
				if(is_wp_error($post_id)){
					$output['status'] = 'error';
					$output['code'] = $post_id->get_error_code();
					$output['msg'] = $post_id->get_error_message();
				}else{
					/** set thumbnail and post parent */
					if($attach_id){
						/** set post thumbnail */
						set_post_thumbnail($post_id,$attach_id);
						
						/** set attachment post parent */
						wp_update_post(array(
							'ID' => $attach_id,
							'post_parent' => $post_id,
						));
					}

					$output['status'] = 'success';
					$output['msg'] = sprintf(___('Thank you for sharing, everybody will get your happiness in few minutes. %s'),'<a href="' . get_permalink(get_page_by_path(self::$page_slug)->ID) . '"><strong>' . ___('Share anthor one~') . '</strong></a>');
				}
				break;
			default:
				$output['status'] = 'error';
				$output['code'] = 'invald_type';
				$output['msg'] = ___('Invalid typ.');
				break;
		}
		die(theme_features::json_format($output));
	}
	public static function frontend_seajs_alias($alias){
		$alias[self::$iden] = theme_features::get_theme_includes_js(__FILE__);
		return $alias;
	}
	public static function frontend_seajs_use(){
		if(!self::current_user_can_post()) return false;
		if(!is_page(self::$page_slug)) return false;
		?>
		seajs.use('<?php echo self::$iden;?>',function(m){
			m.config.process_url = '<?php echo theme_features::get_process_url(array('action' => self::$iden));?>';
			m.config.upload_max_filesize = <?php echo esc_js((int)ini_get('upload_max_filesize'));?>;
			m.config.max_tags_number = <?php echo (int)self::$max_tags_number;?>;
			m.config.lang.M00001 = '<?php echo esc_js(___('Loading, please wait...'));?>';
			m.config.lang.M00002 = '<?php echo esc_js(___('Image has been uploaded.'));?>';
			m.config.lang.M00003 = '<?php echo esc_js(___('Click to delete this image'));?>';
			m.config.lang.M00004 = '<?php echo esc_js(___('Already reached max number of tags.'));?>';
			m.config.lang.M00005 = <?php echo json_encode(sprintf(___('Thank you for sharing, everybody will get your happiness in few minutes. %s'),'<a href="' . get_current_url() . '">' . ___('Now share anthor one again?') . '</a>'));?>;
			m.config.lang.E00001 = '<?php echo esc_js(___('Sorry, server error please try again later.'));?>';
			
			m.init();
		});
		<?php
	}

}
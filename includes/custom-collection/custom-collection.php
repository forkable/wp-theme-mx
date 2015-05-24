<?php
/** 
 * @version 1.0.0
 */
add_filter('theme_includes',function($fns){
	$fns[] = 'theme_custom_collection::init';
	return $fns;
});
class theme_custom_collection{
	public static $iden = 'theme_custom_collection';
	public static $page_slug = 'account';
	public static $file_exts = array('png','jpg','gif');
	public static $thumbnail_size = 'large';
	public static $pages = [];

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
	public static function filter_nav_collection($navs){
		$navs['collection'] = '<a href="' . esc_url(self::get_tabs('collection')['url']) . '">
			<i class="fa fa-' . self::get_tabs('collection')['icon'] . ' fa-fw"></i> 
			' . self::get_tabs('collection')['text'] . '
		</a>';
		return $navs;
	}
	public static function display_backend(){
		$opt = (array)self::get_options();
		?>
		<fieldset>
			<legend><?= ___('Collection settings');?></legend>
			<table class="form-table">
				<tr>
					<th><?= ___('Which categories will be added after submit?');?></th>
					<td>
						<?php theme_features::cat_checkbox_list(self::$iden,'cats');?>
					</td>
				</tr>
				<tr>
					<th><label for="<?= self::$iden;?>-tags-number"><?= ___('Shows tags number');?></label></th>
					<td>
						<input class="short-text" type="number" name="<?= self::$iden;?>[tags-number]" id="<?= self::$iden;?>-tags-number" value="<?= isset($opt['tags-number']) ?  (int)$opt['tags-number'] : 6;?>">
					</td>
				</tr>
				<tr>
					<th><label for="<?= self::$iden;?>-posts-min-number"><?= ___('Post boxes min number');?></label></th>
					<td>
						<input class="short-text" type="number" name="<?= self::$iden;?>[posts-min-number]" id="<?= self::$iden;?>-posts-min-number" value="<?= isset($opt['posts-min-number']) ?  (int)$opt['posts-min-number'] : 5;?>">
					</td>
				</tr>
				<tr>
					<th><label for="<?= self::$iden;?>-posts-max-number"><?= ___('Post boxes max number');?></label></th>
					<td>
						<input class="short-text" type="number" name="<?= self::$iden;?>[posts-max-number]" id="<?= self::$iden;?>-posts-max-number" value="<?= isset($opt['posts-max-number']) ?  (int)$opt['posts-max-number'] : 10;?>">
					</td>
				</tr>
				<tr>
					<th><label for="<?= self::$iden;?>-description"><?=esc_html(___('You can write some description for collection page header. Please use tag <p> to wrap your HTML codes.'));?></label></th>
					<td>
						<textarea name="<?= self::$iden;?>[description]" id="<?= self::$iden;?>-description" class="widefat" rows="5"><?= self::get_des();?></textarea>
					</td>
				</tr>
			</table>
		</fieldset>
		<?php
	}
	public static function get_list_tpl(array $args = []){
		$args = array_merge([
			'post_id' => null,
			'hash' => '%hash%',
			'url' => '%url%',
			'title' => '%title%',
			'thumbnail' => '%thumbnail%',
			'content' => '%content%',
			'preview' => true,
		],$args);
		
		if($args['preview'] === false){
			$args['url'] = get_the_permalink($args['post_id']);
		}
		
		$args['content'] = strip_tags($args['content'],'<b><strong><del><span><img>');

		$target = $args['preview'] === true ? ' target="_blank" ' : null;
		$href = $args['preview'] === true ? '#clt-list-' . $args['hash'] : $args['url'];

		$attr_title = $args['preview'] === true ? ___('Click to locate the source') : $args['title'];
		ob_start();
?>

<p class="list-group-item">
	<a href="<?= $href;?>" title="<?= $attr_title;?>">
		<span class="row">
			<span class="col-xs-12 col-sm-12 col-md-4 col-lg-3" >
				<span class="collection-list-thumbnail-container" >
					<img src="<?= theme_features::get_theme_images_url(theme_functions::$thumbnail_placeholder);?>" width="<?= theme_functions::$thumbnail_size[1];?>" height="<?= theme_functions::$thumbnail_size[2];?>" alt="<?= $args['title'];?>" class="placeholder">
					<img src="<?= $args['thumbnail'];?>" width="<?= theme_functions::$thumbnail_size[1];?>" height="<?= theme_functions::$thumbnail_size[2];?>" alt="<?= $args['title'];?>" class="collection-list-thumbnail">
				</span>
			</span>
			<span class="col-xs-12 col-sm-12 col-md-8 col-lg-9">
				<span class="list-group-item-heading"><?= $args['title'];?></span>
				<span class="list-group-item-text"><?= $args['content'];?></span>
			</span>
		</span>
	</a>
</p>
		<?php
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}
	public static function get_input_tpl($placeholder){
		$thumbnail_placeholder = theme_features::get_theme_images_url(theme_functions::$thumbnail_placeholder);
		ob_start();
		?>
<div class="clt-list row" id="clt-list-<?= $placeholder; ?>" data-id="<?= $placeholder;?>">
	<div class="col-xs-12 col-sm-5 col-md-3 col-lg-2">
		<div class="clt-list-thumbnail-container">
			<img src="<?= $thumbnail_placeholder;?>" alt="Placeholder" class="media-object placeholder">
			<div id="clt-list-thumbnail-preview-container-<?= $placeholder;?>" class="clt-list-thumbnail-preview-container">
				<img id="clt-list-thumbnail-<?= $placeholder;?>" src="<?= $thumbnail_placeholder;?>" title="<?= ___('Post preview');?>" alt="" class="clt-list-thumbnail-preview">
				<input type="hidden" id="clt-list-thumbnail-url-<?= $placeholder ;?>" name="clt[posts][<?= $placeholder;?>][thumbnail-url]" value="<?= $thumbnail_placeholder;?>">
			</div>
		</div>
		<a href="javascript:;" id="clt-list-del-<?= $placeholder;?>" class="clt-list-del btn btn-xs btn-danger btn-block"><i class="fa fa-trash"></i> <?= ___('Delete this item');?></a>
	</div>
	<div class="col-xs-12 col-sm-7 col-md-9 col-lg-10 clt-list-area-tx">
		<div class="input-group">
			<span class="input-group-input">
				<input type="number" class="form-control clt-list-post-id" id="clt-list-post-id-<?= $placeholder ;?>" name="clt[posts][<?= $placeholder;?>][post-id]" placeholder="<?= ___('Post ID');?>" title="<?= ___('Please write the post ID number, e.g. 4015.');?>" min="1" required >
			</span>
			<input type="text" name="clt[posts][<?= $placeholder;?>][post-title]" id="clt-list-post-title-<?= $placeholder;?>" class="form-control clt-list-post-title" placeholder="<?= ___('The recommended post title');?>" title="<?= ___('Please write the recommended post title.');?>" required >
		</div>
		<textarea name="clt[posts][<?= $placeholder;?>][post-content]" id="clt-list-post-content-<?= $placeholder;?>" rows="4" class="form-control clt-list-post-content" placeholder="<?= ___('Why recommend the post? Talking about your point.');?>" title="<?= ___('Why recommend the post? Talking about your point.');?>" required ></textarea>
	</div>
</div>
		<?php
		$content = ob_get_contents();
		ob_end_clean();

		return $content;
	}
	public static function options_save($opts){
		if(!isset($_POST[self::$iden]))
			return $opts;

		$opts[self::$iden] = $_POST[self::$iden];
		return $opts;
	}
	public static function options_default(array $opts = []){
		$opts[self::$iden]['posts-min-number'] = 5;
		$opts[self::$iden]['posts-max-number'] = 10;
		$opts[self::$iden]['tags-number'] = 10;
		$opts[self::$iden]['description'] = '<p>' . ___('Welcome to collection page, you can fill in the post ID and make them as a collection to share you favorite posts.') . '</p>';
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
	public static function get_des(){
		return stripslashes(self::get_options('description'));
	}
	public static function get_posts_number($type){
		return (int)self::get_options('posts-'. $type . '-number');
	}
	public static function get_tabs($key = null){
		$baseurl = self::get_url();
		$tabs = array(
			'collection' => array(
				'text' => ___('New collection'),
				'icon' => 'leanpub',
				'url' => esc_url(add_query_arg('tab','collection',$baseurl)),
				'filter_priority' => 25,
			),
		);
		if($key){
			return isset($tabs[$key]) ? $tabs[$key] : false;
		}
		return $tabs;
	}
	public static function is_page(){
		static $caches = [];
		if(isset($caches[self::$iden]))
			return $caches[self::$iden];
			
		$caches[self::$iden] = is_page(self::$page_slug) && self::get_tabs(get_query_var('tab'));
		return $caches[self::$iden];
	}
	private static function wp_get_attachment_image_src(...$args){
		static $caches = [];
		$cache_id = md5(serialize($args));
		if(!isset($caches[$cache_id]))
			$caches[$cache_id] = call_user_func_array('wp_get_attachment_image_src',$args);

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
			case 'add-cover':
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
						esc_url(self::wp_get_attachment_image_src($attach_id,'thumbnail')[0])
					];
				
					$output['attach-id'] = $attach_id;
					$output['msg'] = ___('Upload success.');
					die(theme_features::json_format($output));
				}
				break;
			/**
			 * post
			 */
			case 'post':
				$clt = isset($_POST['clt']) && is_array($_POST['clt']) ? $_POST['clt'] : null;
				if(is_null_array($clt)){
					$output['status'] = 'error';
					$output['code'] = 'invaild_ctb_param';
					$output['msg'] = ___('Invaild collection param.');
					die(theme_features::json_format($output));
				}
				/**
				 * get posts
				 */
				$posts = isset($clt['posts']) && is_array($clt['posts']) ? $clt['posts'] : null;
				if(empty($posts)){
					$output['status'] = 'error';
					$output['code'] = 'invaild_posts';
					$output['msg'] = ___('Sorry, posts can not be empty.');
					die(theme_features::json_format($output));
				}
				/**
				 * post title
				 */
				$post_title = isset($clt['post-title']) && is_string($clt['post-title']) ? esc_html(trim($clt['post-title'])) : null;
				if(empty($post_title)){
					$output['status'] = 'error';
					$output['code'] = 'invaild_post_title';
					$output['msg'] = ___('Please write the post title.');
					die(theme_features::json_format($output));
				}
				
				/**
				 * check thumbnail cover
				 */
				$thumbnail_id = isset($clt['thumbnail-id']) && is_numeric($clt['thumbnail-id']) ? (int)$clt['thumbnail-id'] : null;
				if(empty($thumbnail_id)){
					$output['status'] = 'error';
					$output['code'] = 'invaild_thumbnail_id';
					$output['msg'] = ___('Please set an image as post thumbnail');
					die(theme_features::json_format($output));
				}
				/**
				 * post content
				 */
				$post_content = isset($clt['post-content']) && is_string($clt['post-content']) ? strip_tags(trim($clt['post-content']),'<del><a><b><strong><em><i>') : null;
				if(empty($post_content)){
					$output['status'] = 'error';
					$output['code'] = 'invaild_post_content';
					$output['msg'] = ___('Please explain why you recommend this collection.');
					die(theme_features::json_format($output));
				}
				/**
				 * get posts template
				 */
				$post_content = '<p>' . $post_content . '</p>' . self::get_preview($posts);
				/**
				 * tags
				 */
				$tags = isset($clt['tags']) && is_array($clt['tags']) ? $clt['tags'] : [];
				if(!empty($tags)){
					$tags = array_map(function($tag){
						if(!is_string($tag)) return null;
						return $tag;
					},$tags);
				}
				/**
				 * post status
				 */
				if(current_user_can('editor') || current_user_can('administrator') || current_user_can('super_admin')){
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
					'post_category' => (array)self::get_options('cats'),
					'tags_input' => $tags,
				),true);
				if(is_wp_error($post_id)){
					$output['status'] = 'error';
					$output['code'] = $post_id->get_error_code();
					$output['msg'] = $post_id->get_error_message();
				}else{
					/** set post thumbnail */
					set_post_thumbnail($post_id,$thumbnail_id);
					/**
					 * pending status
					 */
					if($post_status === 'pending'){
						$output['status'] = 'success';
						$output['msg'] = ___('Your collection submitted successful, it will be published after approve in a while. Thank you very much!');
						die(theme_features::json_format($output));
					}else{
						$output['status'] = 'success';
						$output['msg'] = sprintf(
							___('Congratulation! Your post has been published. You can %s or %s.'),
							'<a href="' . esc_url(get_permalink($post_id)) . '" title="' . esc_attr(get_the_title($post_id)) . '">' . ___('View it now') . '</a>',
							'<a href="' . self::get_tabs('collection')['url'] . '">' . ___('countinue to write a new post') . '</a>'
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
			/**
			 * get post
			 */
			case 'get-post':
			
				$post_id = isset($_REQUEST['post-id']) && is_numeric($_REQUEST['post-id']) ? (int)$_REQUEST['post-id'] : null;
				if(!$post_id){
					$output['status'] = 'error';
					$output['code'] = 'invaild_post_id';
					$output['msg'] = ___('Sorry, the post id is invaild.');
					die(theme_features::json_format($output));
				}

				
				global $post;
				$query = new WP_Query([
					'p' => $post_id
				]);
				if(!$query->have_posts()){
					$output['status'] = 'error';
					$output['code'] = 'post_not_exist';
					$output['msg'] = ___('Sorry, the post do not exist, please type another post ID.');
					die(theme_features::json_format($output));
				}
				$post = $query->posts[0];
				setup_postdata($post);
				$output = [
					'status' 	=> 'success',
					'msg' 		=> ___('Finished get the post data.'),
					'thumbnail' => [
						'url' => theme_functions::get_thumbnail_src($post_id),
						'size' => [
							theme_functions::$thumbnail_size[1],
							theme_functions::$thumbnail_size[2],
						]
					],
					'title' 	=> esc_html(get_the_title($post_id)),
					'excerpt' 	=> str_sub(strip_tags(trim($post->post_content)),120),
				];
				wp_reset_postdata();
				break;

		}

		die(theme_features::json_format($output));
	}
	private static function get_preview(array $posts = []){
		
		/**
		 * check posts count number
		 */
		$count = count($posts);
		if($count < self::get_posts_number('min')){
			$output['status'] = 'error';
			$output['code'] = 'not_enough_posts';
			$output['msg'] = ___('Sorry, your posts are not enough, please add more posts.');
			die(theme_features::json_format($output));
		}
		if($count > self::get_posts_number('max')){
			$output['status'] = 'error';
			$output['code'] = 'too_many_posts';
			$output['msg'] = ___('Sorry, your post are too many, please reduce some posts and try again.');
			die(theme_features::json_format($output));
		}

		/**
		 * template
		 */
		$tpl = '';
		/**
		 * check each posts value
		 */
		foreach($posts as $k => $v){
			/** post id */
			$post_id = isset($v['post-id']) && is_string($v['post-id']) ? trim($v['post-id']) : null;
			if(empty($post_id)){
				$output['status'] = 'error';
				$output['code'] = 'invaild_post_content';
				$output['list-id'] = $k;
				$output['msg'] = ___('Sorry, the post id is invaild, please try again.');
				die(theme_features::json_format($output));
			}
			/** title */
			$title = isset($v['post-title']) && is_string($v['post-title']) ? strip_tags(trim($v['post-title'])) : null;
			if(empty($title)){
				$output['status'] = 'error';
				$output['code'] = 'invaild_post_title';
				$output['list-id'] = $k;
				$output['msg'] = ___('Sorry, the post title is invaild, please try again.');
				die(theme_features::json_format($output));
			}
			/** content */
			$content = isset($v['post-content']) && is_string($v['post-content']) ? trim($v['post-content']) : null;
			if(empty($content)){
				$output['status'] = 'error';
				$output['code'] = 'invaild_post_content';
				$output['list-id'] = $k;
				$output['msg'] = ___('Sorry, the post content is invaild, please try again.');
				die(theme_features::json_format($output));
			}
			/** thumbmail */
			$thumbnail = isset($v['thumbnail-url']) && is_string($v['thumbnail-url']) ? esc_url(trim($v['thumbnail-url'])) : null;
			if(empty($thumbnail)){
				$output['status'] = 'error';
				$output['code'] = 'invaild_post_thumbnail';
				$output['list-id'] = $k;
				$output['msg'] = ___('Sorry, the post thumbnail is invaild, please try again.');
				die(theme_features::json_format($output));
			}
			/** check post exists */
			$url = esc_url(get_the_permalink($v['post-id']));
			if(empty($url)){
				$output['status'] = 'error';
				$output['code'] = 'post_not_exist';
				$output['list-id'] = $k;
				$output['msg'] = ___('Sorry, the post do not exist, please try again.');
				die(theme_features::json_format($output));
			}
			/**
			 * create template
			 */
			$tpl .= self::get_list_tpl([
				'post_id' => $post_id,
				'preview' => false,
				'hash' => $k,
				'url' => $url,
				'thumbnail' => $thumbnail,
				'title' => $title,
				'content' => $content,
			]);
		}

		return '<div class="collection-list list-group">' . html_compress($tpl) . '</div>';
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
			m.config.min_posts = <?= self::get_posts_number('min');?>;
			m.config.max_posts = <?= self::get_posts_number('max');?>;
			m.config.tpl_input = <?= json_encode(self::get_input_tpl('%placeholder%'));?>;
			m.config.tpl_preview = <?= json_encode(self::get_list_tpl([
				'preview' => true,
			]));?>;
			m.config.lang.M01 = '<?= ___('Loading, please wait...');?>';
			m.config.lang.M02 = '<?= ___('A item has been deleted.');?>';
			m.config.lang.M03 = '<?= ___('Getting post data, please wait...');?>';
			m.config.lang.M04 = '<?= ___('Previewing, please wait...');?>';
			m.config.lang.E01 = '<?= ___('Sorry, server is busy now, can not respond your request, please try again later.');?>';
			m.config.lang.E02 = '<?= sprintf(___('Sorry, the minimum number of posts is %d.'),self::get_posts_number('min'));?>';
			m.config.lang.E03 = '<?= sprintf(___('Sorry, the maximum number of posts is %d.'),self::get_posts_number('max'));?>';
			m.config.lang.E04 = '<?= ___('Sorry, the post id must be number, please correct it.');?>';
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
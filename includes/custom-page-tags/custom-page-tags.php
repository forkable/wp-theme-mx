<?php
/**
 * theme_page_tags
 *
 * @version 1.0.0
 * @author KM@INN STUDIO
 */
add_filter('theme_includes',function($fns){
	$fns[] = 'theme_page_tags::init';
	return $fns;
});
class theme_page_tags{
	
	public static $iden = 'theme_page_tags';
	public static $page_slug = 'tags-index';

	private static $user_query;
	private static $tags_to_posts = [];
	
	public static function init(){
		add_action('init',					__CLASS__ . '::page_create');
		add_action('wp_enqueue_scripts', 	__CLASS__ . '::frontend_css');
		add_action('page_settings', 		__CLASS__ . '::display_backend');
		
		add_filter('theme_options_save', 	__CLASS__ . '::options_save');

		add_filter('frontend_seajs_alias' , __CLASS__ . '::frontend_seajs_alias');
		add_action('frontend_seajs_use' , __CLASS__ . '::frontend_seajs_use');

		add_action('wp_ajax_' . self::$iden, __CLASS__ . '::process');
		add_action('wp_ajax_nopriv_' . self::$iden, __CLASS__ . '::process');

		add_action('backend_seajs_alias',__CLASS__ . '::backend_seajs_alias');

		add_action('after_backend_tab_init',__CLASS__ . '::backend_seajs_use'); 
	}
	public static function get_options($key = null){
		static $caches = [];
		if(!isset($caches[self::$iden]))
			$caches[self::$iden] = theme_options::get_options(self::$iden);
		if(empty($key)){
			return $caches[self::$iden];
		}else{
			return isset($caches[self::$iden][$key]) ? $caches[self::$iden][$key] : null;
		}
	}
	public static function options_save($opts){
		if(isset($_POST[self::$iden])){
			$opts[self::$iden] = $_POST[self::$iden];
		}
		return $opts;
	}
	public static function display_backend(){
		$opt = self::get_options();
		?>
		<fieldset>
			<legend><?= ___('Tags index settings');?></legend>
			<p class="description"><?= ___('Display posts chinese pinyin title index on tags index page.')?></p>
			<table class="form-table">
				<tbody>
				<tr>
					<th><?= ___('Whitelist - users ');?></th>
					<td>
						<textarea name="<?= self::$iden;?>[whitelist][user-ids]" id="<?= self::$iden;?>-whitelist-user-ids" rows="3" class="widefat code"><?= isset($opt['whitelist']['user-ids']) ? esc_textarea($opt['whitelist']['user-ids']) : null;?></textarea>
						<p class="description"><?= ___('User ID, multiple users separated by ,(commas). E.g. 1,2,3,4');?></p>
					</td>
				</tr>
				<tr>
					<th><?= ___('Control');?></th>
					<td>
						<div id="<?= self::$iden;?>-tip-clean-cache"></div>
						<p><a href="javascript:;" class="button" id="<?= self::$iden;?>-clean-cache" data-tip-target="<?= self::$iden;?>-tip-clean-cache"><i class="fa fa-refresh"></i> <?= ___('Flush cache');?></a></p>
					</td>
				</tr>
				</tbody>
			</table>
		</fieldset>
		<?php
	}
	public static function process(){
		theme_features::check_referer();
		$output = [];

		$type = isset($_GET['type']) && is_string($_GET['type']) ? $_GET['type'] : null;
		
		switch($type){
			case 'clean-cache':
				wp_cache_delete('display-frontend',self::$iden);
				wp_cache_delete('urls',self::$iden);
				$output['status'] = 'success';
				$output['msg'] = ___('Cache has been cleaned.');
				break;
		}
		
		die(theme_features::json_format($output));
	}
	public static function page_create(){
		if(!current_user_can('manage_options')) return false;
		
		$page_slugs = array(
			self::$page_slug => array(
				'post_content' 	=> '',
				'post_name'		=> self::$page_slug,
				'post_title'	=> ___('Tags index'),
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
			$page = theme_cache::get_page_by_path($k);
			if(!$page){
				$r = array_merge($defaults,$v);
				$page_id = wp_insert_post($r);
			}
		}

	}
	private static function get_unserialize_thumbnail_url($meta){
		static $baseurl = null;
		if($baseurl === null)
			$baseurl = wp_upload_dir()['baseurl'];
			
		$meta = unserialize($meta);

		if(!$meta)
			return false;

		if(!isset($meta['sizes']['post-thumbnail']['file']))
			return false;
			
		$prefix = explode('/',$meta['file']);
		$prefix_url = '';
		for($i = 0, $len = count($prefix); $i<=$len; ++$i){
			if($i >= $len - 1)
				break;

			$prefix_url .= $prefix[$i] . '/';
		}
		return $baseurl . '/' . $prefix_url . $meta['sizes']['post-thumbnail']['file'];
	}
	public static function get_thumbnails($query){
		static $thumbnails = null;
		if($thumbnails !== null)
			return $thumbnails;
		/**
		 * get thumbnail src from db
		 */
		global $wpdb;
		
		$post_ids = array_map(function($v){
			return $v->ID;
		},$query->posts);
		
		$sql_post_ids = implode(',',$post_ids);

		/**
		 * 获取附件的 post id
		 */
		$tmp_post_attach_ids = $wpdb->get_results(
			"
			SELECT `post_id`,`meta_value` as attach_id
			FROM $wpdb->postmeta
			WHERE `meta_key` = '_thumbnail_id'
			AND `post_id` IN ($sql_post_ids)
			",ARRAY_A
		);
		$attach_ids = [];
		$attach_post_ids = [];
		foreach($tmp_post_attach_ids as $v){
			$attach_ids[$v['post_id']] = $v['attach_id'];
			$attach_post_ids[$v['attach_id']] = $v['post_id'];
		}
			
		$sql_attach_ids = implode(',',$attach_ids);
		$thumbnails_results = $wpdb->get_results(
			"
			SELECT `post_id`,`meta_value` as meta
			FROM $wpdb->postmeta
			WHERE `meta_key` = '_wp_attachment_metadata'
			AND `post_id` IN ($sql_attach_ids)
			",ARRAY_A
		);
		/**
		 * get thumbnails for loop
		 */
		$thumbnails = [];
		foreach($thumbnails_results as $v){
			if(isset($attach_post_ids[$v['post_id']])){
				$thumbnails[$attach_post_ids[$v['post_id']]] = self::get_unserialize_thumbnail_url($v['meta']);
			}
		}
		return $thumbnails;
	}
	public static function get_tags(){
		global $post;
		
		$whitelist = (array)self::get_options('whitelist');

		$new_tags = [];

		/**
		 * get all whitelist posts & tag ids
		 */
		self::$user_query = new WP_Query(array(
			'nopaging' => 1,
			'author__in' => isset($whitelist['user-ids']) ? explode(',',$whitelist['user-ids']) : [],
			'category__not_in' => array(1),
		));
		if(self::$user_query->have_posts()){
			$thumbnails = self::get_thumbnails(self::$user_query);
			/** load pinyin */
			include __DIR__ . '/inc/Pinyin/Pinyin.php';
			$double_pinyins = array('zh','ch','sh');
			//while(self::$user_query->have_posts()){
				foreach(self::$user_query->posts as $post){
				//self::$user_query->the_post();
				$tags = get_the_tags();
				/** skip empty tags */
				if(empty($tags)) 
					continue;
				/**
				 * get posts obj for add to tags
				 */
				$post_obj = [
					'id' => $post->ID,
					'title' => esc_html(get_the_title()),
					'thumbnail' => isset($thumbnails[$post->ID]) ? esc_url($thumbnails[$post->ID]) : null,
					'permalink' => esc_url(get_permalink()),
				];
				foreach($tags as $tag){
					
					/**
					 * 标签是字母开头
					 */
					$first_letter_pattern = '/^[a-z]/i';
					$first_letter = strtolower(mb_substr($tag->name,0,1));
					preg_match($first_letter_pattern,$first_letter,$matches);
					/**
					 * 存在字母开头的标签
					 */
					if(!empty($matches[0])){
						/**
						 * 如果是新标签，进行记录
						 */
						if(!isset($new_tags[$first_letter][$tag->term_id]))
							$new_tags[$first_letter][$tag->term_id] = $tag;
						
						/**
						 * set to tags
						 */
						self::save_tags($tag,$post_obj);
						/**
						 * 完成匹配，跳到下一个标签
						 */
						continue;
					}
					/**
					 * 标签是中文开头
					 */
					$utf8_tagname = mb_convert_encoding($tag->name,'utf-8','ascii,gb2312,gbk,utf-8');
					preg_match("/^[\x{4e00}-\x{9fa5}]/u",$utf8_tagname,$matches);
					/**
					 * 不是中文，跳到下一个
					 */
					if(empty($matches[0]))
						continue;
						
					$tag_pinyin = Overtrue\Pinyin\Pinyin::pinyin($tag->name);
					$tag_two_pinyin = strtolower(substr($tag_pinyin,0,2));
					/**
					 * 判断巧舌音
					 */
					if(in_array($tag_two_pinyin,$double_pinyins)){
						/**
						 * 如果是新标签，进行记录
						 */
						if(!isset($new_tags[$tag_two_pinyin][$tag->term_id]))
							$new_tags[$tag_two_pinyin][$tag->term_id] = $tag;
					/**
					 * 单音
					 */
					}else{
						$tag_one_pinyin = strtolower(mb_substr($tag_pinyin,0,1));
						/**
						 * 如果是新标签，进行记录
						 */
						if(!isset($new_tags[$tag_one_pinyin][$tag->term_id]))
							$new_tags[$tag_one_pinyin][$tag->term_id] = $tag;
					}
					/**
					 * set to tags
					 */
					self::save_tags($tag,$post_obj);
					continue;
				}
			}
			wp_reset_postdata();
		}else{
			return false;
		}
		ksort($new_tags);
		return $new_tags;
	}
	private static function save_tags($tag,$post_obj){
		if(!isset(self::$tags_to_posts[$tag->term_id]))
			self::$tags_to_posts[$tag->term_id] = [
				'name' => $tag->name,
				'posts' => [
					$post_obj['id'] => [$post_obj],
				]
			];
		
		self::$tags_to_posts[$tag->term_id]['posts'][$post_obj['id']] = $post_obj;
	}
	public static function display_frontend(){
		$cache_id = 'display-frontend';
		$cache = wp_cache_get($cache_id,self::$iden);
		if(!empty($cache)){
			echo $cache;
			return;
		}

		ob_start();
		$tags = self::get_tags();
		//print_r(self::$tags_to_posts);exit;
		if(is_null_array($tags)){
			?><div class="page-tip"><?= status_tip('info',___('No tag yet.'));?></div><?php
			return false;
		}

		foreach($tags as $k => $v){
			?>
			<div class="panel-tags-index panel panel-primary">
				<div class="panel-heading">
					<strong><?= $k;?></strong>
					<small> - <?= ___('Pinyin initial');?></small>
				</div>
				<div class="panel-body">
					<?php foreach($v as $tag){ ?>
						<h3 class="tags-title"><a href="<?= esc_url(get_tag_link($tag->term_id));?>">
							<?= self::$tags_to_posts[$tag->term_id]['name'];?>
							<small>(<?= count(self::$tags_to_posts[$tag->term_id]);?>)</small>
						</a></h3>
						<ul class="row">
							<?php foreach(self::$tags_to_posts[$tag->term_id]['posts'] as $k => $v){ 
								$thumbnail_url = $v['thumbnail'] ? $v['thumbnail'] : theme_features::get_theme_images_url(theme_functions::$thumbnail_placeholder);

							?>
								<li class="col-sm-6 tag-list">
									<a 
										class="tag-link" 
										href="<?= $v['permalink'];?>" 
										title="<?= $v['title'];?>" 
										target="_blank" 
										data-thumbnail-url="<?= $thumbnail_url;?>"
									><?= $v['title'];?></a>
									<div class="extra-thumbnail"></div>
								</li>
							<?php } ?>
						</ul>
					<?php } ?>
				</div> <!-- /.panel-bbody -->
			</div>

			<?php
		}
		$cache = html_compress(ob_get_contents());
		ob_end_clean();
		wp_cache_set($cache_id,$cache,self::$iden,86400);/** 24 hours */
		echo $cache;
	}
	public static function backend_seajs_alias($alias){
		$alias[self::$iden] = theme_features::get_theme_includes_js(__DIR__,'backend');
		return $alias;
	}
	public static function backend_seajs_use(){
		?>
		seajs.use('<?= self::$iden;?>',function(m){
			m.config.process_url = '<?= theme_features::get_process_url(array(
				'action'=>self::$iden,
				'type' => 'clean-cache',
			));?>';
			m.config.lang.M00001 = '<?= ___('Loading, please wait...');?>';
			m.init();
		});
		<?php
	}
	public static function is_page(){
		static $cache = null;
		if($cache === null)
			$cache = is_page(self::$page_slug);

		return $cache;
	}
	public static function frontend_seajs_alias(array $alias = []){
		if(self::is_page()){
			$alias[self::$iden] = theme_features::get_theme_includes_js(__DIR__);
		}
		return $alias;
	}
	public static function frontend_seajs_use(){
		if(!self::is_page())
			return false;

		?>
		seajs.use(['<?= self::$iden;?>'],function(m){
			m.config.process_url = '<?= theme_features::get_process_url([
				'action' => self::$iden,
				'type' => 'get-thumbnail-url',
			]);?>';
			m.config.lang.M00001 = '<?= ___('Preview image is loading...');?>';
			m.config.lang.E00001 = '<?= ___('ERROR: can not load the preview image.');?>';
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
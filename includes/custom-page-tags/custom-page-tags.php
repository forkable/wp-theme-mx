<?php
/**
 * theme_page_tags
 *
 * @version 1.0.0
 */
add_filter('theme_includes',function($fns){
	$fns[] = 'theme_page_tags::init';
	return $fns;
});
class theme_page_tags{
	
	public static $iden = 'theme_page_tags';
	public static $page_slug = 'tags-index';

	private static $user_query;
	private static $tags = [];
	
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
		static $caches = null;
		if($caches === null)
			$caches = theme_options::get_options(self::$iden);
		if($key)
			return isset($caches[$key]) ? $caches[$key] : false;
		return $caches;
	}
	public static function options_save(array $opts = []){
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
				$output['status'] = 'success';
				$output['msg'] = ___('Cache has been cleaned.');
				break;
		}
		
		die(theme_features::json_format($output));
	}
	public static function get_url(){
		static $cache = null;
		if($cache === null)
			$cache = theme_cache::get_permalink(theme_cache::get_page_by_path(self::$page_slug)->ID);
		return $cache;	
	}
	public static function page_create(){
		if(!theme_cache::current_user_can('manage_options')) 
			return false;
		
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
			theme_cache::get_page_by_path($k) || wp_insert_post(array_merge($defaults,$v));
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
	public static function get_thumbnails($sql_post_ids){
		/**
		 * get thumbnail src from db
		 */
		global $wpdb;
		
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
		if(empty($thumbnails_results[0]))
			return false;
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
	public static function get_tags($sql_post_ids){
		global $wpdb;
		$where = empty($sql_post_ids) ? '' : "AND tr.object_id IN ($sql_post_ids)";

		$tags = $wpdb->get_results(
			"
			SELECT 
				t.name tag_name,
				t.term_id tag_id,
				tr.object_id post_id
			FROM 
				$wpdb->term_relationships AS tr
			
			INNER JOIN $wpdb->term_taxonomy AS tt
				ON tt.term_taxonomy_id = tr.term_taxonomy_id
				
			INNER JOIN $wpdb->terms AS t
				ON t.term_id = tt.term_id
				
			WHERE
				tr.object_id IN ($sql_post_ids) AND
				tt.taxonomy = 'post_tag'
			",ARRAY_A
		);
		//self::sprint_r($wpdb);

		if(empty($tags)){
			return false;
		}
		/** 保存所有唯一的tags */
		foreach($tags as $v){
			self::save_tags($v['tag_id'],$v['tag_name'],$v['post_id']);
		}
		/**
		 * 提取 tags 拼音首字母
		 */
		include __DIR__ . '/inc/Pinyin/Pinyin.php';
		$double_pinyins = ['zh','ch','sh','ou','ai','ang','an'];
		$new_tags = [];
		foreach(self::$tags as $tag_id => $tag){
			/**
			 * 标签是字母开头
			 */
			$first_letter_pattern = '/^[a-z]/i';
			$first_letter = strtolower(mb_substr($tag['name'],0,1));
			preg_match($first_letter_pattern,$first_letter,$matches);
			/**
			 * 存在字母开头的标签
			 */
			if(!empty($matches[0])){
				/**
				 * 如果是新标签，进行记录
				 */
				if(!isset($new_tags[$first_letter][$tag_id]))
					$new_tags[$first_letter][$tag_id] = $tag;
				
				/**
				 * 完成匹配，跳到下一个标签
				 */
				continue;
			}
			/**
			 * 标签是中文开头
			 */
			$utf8_tagname = mb_convert_encoding($tag['name'],'utf-8','ascii,gb2312,gbk,utf-8');
			preg_match("/^[\x{4e00}-\x{9fa5}]/u",$utf8_tagname,$matches);
			/**
			 * 不是中文，跳到下一个
			 */
			if(empty($matches[0]))
				continue;
				
			$tag_pinyin = Overtrue\Pinyin\Pinyin::pinyin($tag['name']);
			$tag_two_pinyin = strtolower(substr($tag_pinyin,0,2));
			/**
			 * 判断巧舌音
			 */
			if(in_array($tag_two_pinyin,$double_pinyins)){
				/**
				 * 如果是新标签，进行记录
				 */
				if(!isset($new_tags[$tag_two_pinyin][$tag_id]))
					$new_tags[$tag_two_pinyin][$tag_id] = $tag;
			/**
			 * 单音
			 */
			}else{
				$tag_one_pinyin = mb_substr($tag_pinyin,0,1);
				/**
				 * 如果是新标签，进行记录
				 */
				if(!isset($new_tags[$tag_one_pinyin][$tag_id]))
					$new_tags[$tag_one_pinyin][$tag_id] = $tag;
			}
			continue;
		}
		
		
		ksort($new_tags);

		return $new_tags;
	}

	private static function save_tags($tag_id,$tag_name,$post_id){
		if(!isset(self::$tags[$tag_id]))
			self::$tags[$tag_id] = [
				'name' => $tag_name,
				'post_ids' => [],
			];
		self::$tags[$tag_id]['post_ids'][$post_id] = $post_id;
	}
	private static function sprint_r($data){
		echo '<pre>';
		print_r($data);
		echo '</pre>';die;
		
	}
	public static function display_frontend(){
		set_time_limit(0);
		
		$cache_id = 'display-frontend';
		$cache = wp_cache_get($cache_id,self::$iden);
		//$cache = false;
		if(!empty($cache)){
			echo $cache;
			return;
		}
		ob_start();



		global $wp_rewrite;

		$whitelist = (array)self::get_options('whitelist');
		if(empty($whitelist)){
			$whitelist_sql = null;
		}else{
			$whitelist_sql = explode(',',$whitelist['user-ids']);
		}


		$where = !empty($whitelist_sql[0]) ? " AND `post_author` IN (${whitelist['user-ids']})" : '';

		
		global $wpdb;
		$query_posts = $wpdb->get_results(
			"
			SELECT post_title,ID 
			FROM `$wpdb->posts` 
			WHERE 1=1 
				AND post_status = 'publish'
				AND post_type = 'post'
				$where
			ORDER BY post_title
			",ARRAY_A
		);
//		echo '<pre>';
//print_r($query_posts);
//echo '</pre>';die;
		if(!isset($query_posts[0])){
			self::no_content(__('No posts found.'));
			return false;
		}
		
		$posts = [];
		$post_ids = [];
		foreach($query_posts as $v){
			$post_ids[] = $v['ID'];
			$posts[$v['ID']] = [
				'title' => $v['post_title'],
				'permalink' => theme_cache::home_url() . str_replace('%post_id%',$v['ID'],$wp_rewrite->permalink_structure)
			];
		}
		
		$sql_post_ids = implode(',',$post_ids);

		$pinyin_tags = self::get_tags($sql_post_ids);
		$thumbnail_urls = self::get_thumbnails($sql_post_ids);

		


		if(empty($pinyin_tags)){
			self::no_content(__('No tags found.'));
			return false;
		}

		foreach($pinyin_tags as $initial => $tags){
			?>
			<div class="panel-tags-index panel panel-primary">
				<div class="panel-heading">
					<strong><?= $initial;?></strong>
					<small> - <?= ___('Pinyin initial');?></small>
				</div>
				<div class="panel-body">
					<?php foreach($tags as $tag_id => $tag){ ?>
						<h3 class="tags-title"><a href="<?= esc_url(get_tag_link($tag_id));?>">
							<?= $tag['name'];?>
							<small>(<?= count($tag['post_ids']);?>)</small>
						</a></h3>
						<ul class="row">
							<?php 
							foreach($tag['post_ids'] as $post_id){
								/** get thumbnail */
								if(isset($thumbnail_urls[$post_id])){
									$thumbnail_url = $thumbnail_urls[$post_id];
								}else{
									$thumbnail_url = theme_features::get_theme_images_url(theme_functions::$thumbnail_placeholder);
								}
							?>
								<li class="col-sm-6 tag-list">
									<a 
										class="tag-link" 
										href="<?= $posts[$post_id]['permalink'];?>" 
										title="<?= $posts[$post_id]['title'];?>" 
										target="_blank" 
										data-thumbnail-url="<?= $thumbnail_url;?>"
									><?= $posts[$post_id]['title'];?></a>
									<div class="extra-thumbnail"></div>
								</li>
							<?php } ?>
						</ul>
					<?php } ?>
				</div> <!-- /.panel-bbody -->
			</div>

			<?php
		}
		$cache = html_minify(ob_get_contents());
		ob_end_clean();
		wp_cache_set($cache_id,$cache,self::$iden,86400*7);/** 7days */
		echo $cache;
	}
	private static function no_content($msg){
		?>
		<div class="page-tip"><?= status_tip('info',$msg);?></div>
		<?php
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
			$cache = theme_cache::is_page(self::$page_slug);

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
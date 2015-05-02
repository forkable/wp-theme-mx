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
			<legend><?php echo ___('Tags index settings');?></legend>
			<p class="description"><?php echo ___('Display posts chinese pinyin title index on tags index page.')?></p>
			<table class="form-table">
				<tbody>
				<tr>
					<th><?php echo ___('Whitelist - users ');?></th>
					<td>
						<textarea name="<?php echo self::$iden;?>[whitelist][user-ids]" id="<?php echo self::$iden;?>-whitelist-user-ids" rows="3" class="widefat code"><?php echo isset($opt['whitelist']['user-ids']) ? esc_textarea($opt['whitelist']['user-ids']) : null;?></textarea>
						<p class="description"><?php echo ___('User ID, multiple users separated by ,(commas). E.g. 1,2,3,4');?></p>
					</td>
				</tr>
				<tr>
					<th><?php echo ___('Control');?></th>
					<td>
						<div id="<?php echo self::$iden;?>-tip-clean-cache"></div>
						<p><a href="javascript:;" class="button" id="<?php echo self::$iden;?>-clean-cache" data-tip-target="<?php echo self::$iden;?>-tip-clean-cache"><i class="fa fa-refresh"></i> <?php echo ___('Flush cache');?></a></p>
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
			case 'get-thumbnail-url':
				theme_features::check_nonce();
				$post_id = isset($_GET['post-id']) && is_numeric($_GET['post-id']) ? $_GET['post-id'] : null;
				
				if(!$post_id){
					die(theme_features::json_format([
						'status' => 'error',
						'code' => 'invaild_post_id',
						'msg' => ___('Invalid post id.'),
					]));
				}
				
				$caches = wp_cache_get('urls',self::$iden);
				if(isset($caches[$post_id])){
					$output['status'] = 'success';
					$output['url'] = $caches[$post_id];
				}else{
					$output['status'] = 'success';
					$caches[$post_id] = theme_functions::get_thumbnail_src($post_id);
					$output['url'] = $caches[$post_id];
					wp_cache_set('urls',$caches,self::$iden,3600*24);
				}
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
	public static function get_tags(){
		global $post;
		
		$whitelist = (array)self::get_options('whitelist');

		$new_tags = [];
		/**
		 * get all whitelist posts & tag ids
		 */
		//global $wpdb;
		//$wpdb->get_results($wpdb->prepare(
		//	"
		//	SELECT `$wpdb->posts.ID`
		//	WHERE 
		//	"
		//)
		$query = new WP_Query(array(
			'nopaging' => 1,
			'author__in' => isset($whitelist['user-ids']) ? explode(',',$whitelist['user-ids']) : [],
			'category__not_in' => array(1),
		));
		//print_r($wpdb->queries);exit;
		if($query->have_posts()){
			/** load pinyin */
			include __DIR__ . '/inc/Pinyin/Pinyin.php';
			$double_pinyins = array('zh','ch','sh');
			while($query->have_posts()){
				$query->the_post();
				$tags = get_the_tags();
				/** skip empty tags */
				if(empty($tags)) 
					continue;
				
				foreach($tags as $tag){
					/**
					 * 标签是字母开头
					 */
					$first_letter_pattern = '/^[a-z]{1}/i';
					$first_letter = mb_substr($tag->name,0,1);
					preg_match($first_letter_pattern,$first_letter,$matches);
					if(!empty($matches[0])){
						if(isset($new_tags[$first_letter][$tag->term_id]))
							continue;
						$new_tags[$first_letter][$tag->term_id] = $tag;
						continue;
					}
					/**
					 * 标签是中文开头
					 */
					$utf8_tagname = mb_convert_encoding($tag->name,'utf-8','ascii,gb2312,gbk,utf-8');
					preg_match("/^[\x{4e00}-\x{9fa5}]/u",$utf8_tagname,$matches);
					if(!empty($matches[0])){
						$tag_pinyin = Overtrue\Pinyin\Pinyin::pinyin($tag->name);
						$tag_two_pinyin = substr($tag_pinyin,0,2);
						/**
						 * 巧舌音
						 */
						if(in_array($tag_two_pinyin,$double_pinyins)){
							if(isset($new_tags[$tag_two_pinyin][$tag->term_id]))
								continue;
							$new_tags[$tag_two_pinyin][$tag->term_id] = $tag;
						/**
						 * 单音
						 */
						}else{
							$tag_one_pinyin = mb_substr($tag_pinyin,0,1);
							if(isset($new_tags[$tag_one_pinyin][$tag->term_id]))
								continue;
							$new_tags[$tag_one_pinyin][$tag->term_id] = $tag;
						}
						continue;
					}
				}
			}
			wp_reset_postdata();
		}else{
			return false;
		}
		//wp_reset_query();
		return $new_tags;
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
		if(is_null_array($tags)){
			?><div class="page-tip"><?php echo status_tip('info',___('No tag yet.'));?></div><?php
			return false;
		}
		global $post;
		//var_dump($tags);
		arsort($tags);
		foreach($tags as $k => $v){
			?>
			<div class="panel-tags-index panel panel-primary">
				<div class="panel-heading">
					<strong><?php echo $k;?></strong>
					<small> - <?php echo ___('Pinyin initial');?></small>
				</div>
				<div class="panel-body">
					<?php foreach($v as $tag){ ?>
						<h3 class="tags-title"><a href="<?php echo esc_url(get_tag_link($tag->term_id));?>">
							<?php echo esc_html($tag->name);?>
							<small>(<?php echo $tag->count;?>)</small>
						</a></h3>
						<ul class="row">
							<?php
							$query = new WP_Query(array(
								'nopaging' => true,
								'tag__in' => array($tag->term_id),
							));
		//var_dump(get_num_queries());
		//exit;
							while($query->have_posts()){
								$query->the_post();
								$title = esc_html(get_the_title());
								?>
								<li class="col-sm-6 tag-list">
									<a class="tag-link" href="<?php the_permalink();?>" title="<?php echo $title;?>" target="_blank" data-post-id="<?php echo $post->ID;?>"><?php echo $title;?></a>
									<div class="extra-thumbnail"></div>
								</li>
								<?php
							}
							wp_reset_postdata();
							?>
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
		seajs.use('<?php echo self::$iden;?>',function(m){
			m.config.process_url = '<?php echo theme_features::get_process_url(array(
				'action'=>self::$iden,
				'type' => 'clean-cache',
			));?>';
			m.config.lang.M00001 = '<?php echo ___('Loading, please wait...');?>';
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
		seajs.use(['<?php echo self::$iden;?>'],function(m){
			m.config.process_url = '<?php echo theme_features::get_process_url([
				'action' => self::$iden,
				'type' => 'get-thumbnail-url',
			]);?>';
			m.config.lang.M00001 = '<?php echo ___('Preview image is loading...');?>';
			m.config.lang.E00001 = '<?php echo ___('ERROR: can not load the preview image.');?>';
			m.init();
		});
		<?php
	}
	public static function frontend_css(){
		if(!self::is_page()) 
			return false;
		wp_enqueue_style(
			self::$iden,
			theme_features::get_theme_includes_css(__DIR__,'style',false),
			false,
			theme_features::get_theme_info('version')
		);

	}
}
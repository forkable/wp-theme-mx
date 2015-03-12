<?php
/**
 * theme_page_cats
 *
 * @version 1.0.0
 * @author KM@INN STUDIO
 */
add_filter('theme_includes',function($fns){
	$fns[] = 'theme_page_cats::init';
	return $fns;
});
class theme_page_cats{
	
	public static $iden = 'theme_page_cats';
	public static $page_slug = 'cats-index';
	
	public static function init(){
		add_action('init',get_class() . '::page_create');
		add_action('wp_enqueue_scripts', 	get_class() . '::frontend_css');
	}
	public static function get_options($key = null){
		$opt = theme_options::get_options(self::$iden);
		if(empty($key)){
			return $opt;
		}else{
			return isset($opt[$key]) ? $opt[$key] : null;
		}
	}
	public static function options_save($opts){
		if(isset($_POST[self::$iden])){
			$opts[self::$iden] = $_POST[self::$iden];
		}
		return $opts;
	}
	public static function display_backend(){
		$opt = theme_options::get_options(self::$iden);
		?>
		<fieldset>
			<legend><?php echo ___('Categories index settings');?></legend>
			<table class="form-table">
				<tbody>
					<tr>
						<th><?php echo ___('Index Categories');?></th>
						<td>
							<?php echo theme_features::cat_checkbox_list(self::$iden,'cats');?>
						</td>
					</tr>
					<tr>
						<th><?php echo ___('Control');?></th>
						<td>
							<a href="javascript:;" class="button button-primary" id="<?php echo self::$iden;?>-clean-cache" data-tip="#<?php echo self::$iden;?>-clean-cache-tip"><?php echo ___('Flush cache');?></a>
							<div id="<?php echo self::$iden;?>-clean-cache-tip"></div>
						</td>
					</tr>
				</tbody>
			</table>
		</fieldset>
		<?php
	}
	public static function page_create(){
		if(!current_user_can('manage_options')) return false;
		
		$page_slugs = array(
			self::$page_slug => array(
				'post_content' 	=> '',
				'post_name'		=> self::$page_slug,
				'post_title'	=> ___('Categories index'),
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
				$r = wp_parse_args($v,$defaults);
				$page_id = wp_insert_post($r);
			}
		}

	}
	public static function get_slugs(){
		global $wp_query,$post;
		
		$cats = (array)self::get_options('cats');

		$new_tags = [];
		/**
		 * get all whitelist posts & tag ids
		 */
		$wp_query = new WP_Query(array(
			'category__in' => $cat,
		));
		if(have_posts()){
			/** load pinyin */
			while(have_posts()){
				the_post();
				/** 提取别名是数字或英文开头的 */
				$first_letter_pattern = '/^[a-z][0-9]{1}/';
				$first_letter = $post->post_name[0];
				preg_match($first_letter_pattern,$first_letter,$matches);
				if(!empty($matches[0])){
					if(isset($new_tags[$first_letter][$post->ID]))
						continue;
					$new_tags[$first_letter][$post->ID] = $post->ID;
					continue;
				}
			}
		}else{
			return false;
		}
		wp_reset_query();
		wp_reset_postdata();
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
		global $wp_query,$post;
		//var_dump($tags);
		arsort($tags);
		foreach($tags as $k => $v){
			?>
			<div class="panel-tags-index panel panel-default">
				<div class="panel-heading">
					<strong><?php echo $k;?></strong>
					<small> - <?php echo ___('Pinyin initial');?></small>
				</div>
				<div class="panel-body">
					<div class="row">
						<?php
						foreach($v as $tag){
							?>
							<div class="col-sm-6">
								<h3 class="tags-title"><a href="<?php echo esc_url(get_tag_link($tag->term_id));?>">
									<?php echo esc_html($tag->name);?>
									<small>(<?php echo $tag->count;?>)</small>
								</a></h3>
								<ul class="row">
									<?php
									$wp_query = new WP_Query(array(
										'nopaging' => true,
										'tag__in' => array($tag->term_id),
									));
									while(have_posts()){
										the_post();
										?>
										<li class="col-sm-6">
											<a href="<?php the_permalink();?>" title="<?php the_title();?>"><?php the_title();?></a>
											<?php if(has_post_thumbnail()){ ?>
												<div class="extra-thumbnail">
<img src="<?php echo theme_features::get_theme_images_url('frontend/thumb-preview.jpg');?>" data-original="<?php echo esc_url(theme_functions::get_thumbnail_src());?>" alt="<?php the_title();?>" width="<?php echo theme_functions::$thumbnail_size[1];?>" height="<?php echo theme_functions::$thumbnail_size[2];?>"/>
												</div>
											<?php } ?>
										</li>
										<?php
									}
									wp_reset_query();
									wp_reset_postdata();
									?>
								</ul>
							</div>
						<?php } ?>
					</div><!-- /.row -->
				</div><!-- /.panel-body -->
			</div>

			<?php
		}
		$cache = ob_get_contents();
		ob_end_clean();
		wp_cache_set($cache_id,$cache,self::$iden,86400);/** 24 hours */
		echo $cache;
	}
	public static function frontend_css(){
		if(!is_page(self::$page_slug)) return false;
		wp_enqueue_style(
			self::$iden,
			theme_features::get_theme_includes_css(__FILE__,'style',false),
			false,
			theme_features::get_theme_info('version')
		);

	}
}
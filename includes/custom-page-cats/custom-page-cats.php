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
		add_action('init',__CLASS__ . '::page_create');

		add_action('page_settings', 		__CLASS__ . '::display_backend');

		add_action('wp_ajax_' . self::$iden, __CLASS__ . '::process');
		
		add_filter('theme_options_save', 	__CLASS__ . '::options_save');

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
			<legend><?= ___('Categories index settings');?></legend>
			<p class="description"><?= ___('Display posts number or alphabet slug index on categories index page.')?></p>
			<table class="form-table">
				<tbody>
					<tr>
						<th><?= ___('Index Categories');?></th>
						<td>
							<?= theme_features::cat_checkbox_list(self::$iden,'cats');?>
						</td>
					</tr>
					<tr>
						<th><?= ___('Control');?></th>
						<td>
							<div id="<?= self::$iden;?>-tip-clean-cache"></div>
							<p>
							<a href="javascript:;" class="button" id="<?= self::$iden;?>-clean-cache" data-tip-target="<?= self::$iden;?>-tip-clean-cache"><i class="fa fa-refresh"></i> <?= ___('Flush cache');?></a>
							</p>
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
		wp_cache_delete(self::$iden);
		$output['status'] = 'success';
		$output['msg'] = ___('Cache has been cleaned.');
		die(theme_features::json_format($output));
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
				$r = array_merge($defaults,$v);
				$page_id = wp_insert_post($r);
			}
		}

	}
	public static function get_slugs(){
		global $post;
		
		$cats = (array)self::get_options('cats');
		$new_tags = [];
		/**
		 * get all whitelist posts & tag ids
		 */
		$query = new WP_Query(array(
			'nopaging' => 1,
			'category__in' => $cats,
		));
		if($query->have_posts()){
			/** load pinyin */
			foreach($query->posts as $post){
				/** 提取别名是数字或英文开头的 */
				$first_letter_pattern = '/^[a-z0-9]/';
				$first_letter = $post->post_name[0];

				preg_match($first_letter_pattern,$first_letter,$matches);
				if(!empty($matches[0])){
					if(isset($new_tags[$first_letter][$post->ID]))
						continue;
					$new_tags[$first_letter][$post->ID] = $post->ID;
					continue;
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
		$cache = wp_cache_get(self::$iden);
		if(!empty($cache)){
			echo $cache;
			return;
		}

		ob_start();
		$slugs = self::get_slugs();
		if(is_null_array($slugs)){
			?><div class="page-tip"><?= status_tip('info',___('No cagtegory yet.'));?></div><?php
			return false;
		}
		global $post;
		//var_dump($tags);
		arsort($slugs);
		foreach($slugs as $k => $post_ids){
		?>
			<div class="panel-tags-index panel panel-primary">
				<div class="panel-heading">
					<strong><?= $k;?></strong>
					<small> - <?= ___('Initial');?></small>
				</div>
				<div class="panel-body">
					<ul class="row post-img-lists">
						<?php
						$query = new WP_Query(array(
							'nopaging' => true,
							'post__in' => $post_ids,
						));
						//while($query->have_posts()){
							foreach($query->posts as $post){
							//$query->the_post();
							theme_functions::archive_img_content(array(
								'classes' => array('col-xs-6 col-sm-4 col-md-3 col-lg-2'),
							));
						}
						//wp_reset_query();
						wp_reset_postdata();
						?>
					</ul>
				</div><!-- /.panel-body -->
			</div>
			<?php
		}
		$cache = ob_get_contents();
		ob_end_clean();
		wp_cache_set(self::$iden,$cache,null,86400);/** 24 hours */
		echo $cache;
	}
	public static function backend_seajs_alias($alias){
		$alias[self::$iden] = theme_features::get_theme_includes_js(__DIR__,'backend');
		return $alias;
	}
	public static function backend_seajs_use(){
		?>
		seajs.use('<?= self::$iden;?>',function(m){
			m.config.process_url = '<?= theme_features::get_process_url(array('action'=>self::$iden));?>';
			m.config.lang.M00001 = '<?= ___('Loading, please wait...');?>';
			m.init();
		});
		<?php
	}
}
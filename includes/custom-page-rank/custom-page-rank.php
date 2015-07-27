<?php
/**
 * theme_page_rank
 *
 * @version 1.0.0
 */
add_filter('theme_includes',function($fns){
	$fns[] = 'theme_page_rank::init';
	return $fns;
});
class theme_page_rank{
	
	public static $iden = 'theme_page_rank';
	public static $page_slug = 'rank';
	
	public static function init(){
		add_action('init',__CLASS__ . '::page_create');

		//add_action('page_settings', 		__CLASS__ . '::display_backend');

		//add_action('wp_ajax_' . self::$iden, __CLASS__ . '::process');
		
		//add_filter('theme_options_save', 	__CLASS__ . '::options_save');

		//add_action('backend_seajs_alias',__CLASS__ . '::backend_seajs_alias');

		//add_action('after_backend_tab_init',__CLASS__ . '::backend_seajs_use'); 

		add_action( 'wp_enqueue_scripts', __CLASS__  . '::frontend_enqueue_css');

		add_filter('query_vars',			__CLASS__ . '::filter_query_vars');
		
	}
	public static function get_options($key = null){
		static $caches = null;
		if($caches === null)
			$caches = (array)theme_options::get_options(self::$iden);
		
		if(empty($key)){
			return $caches;
		}else{
			return isset($caches[$key]) ? $caches[$key] : false;
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
		if(!theme_cache::current_user_can('manage_options')) 
			return false;
		
		$page_slugs = array(
			self::$page_slug => array(
				'post_content' 	=> '',
				'post_name'		=> self::$page_slug,
				'post_title'	=> ___('Rank'),
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
	public static function is_page(){
		static $cache = null;
		if($cache === null)
			$cache = theme_cache::is_page(self::$page_slug);

		return $cache;
	}
	public static function filter_query_vars($vars = []){
		if(!in_array('filter',$vars))
			$vars[] = 'filter';
			
		if(!in_array('tab',$vars))
			$vars[] = 'tab';
			
		return $vars;
	}
	public static function get_tabs($key = null){
		static $base_url = null , $tabs = null;
		if($base_url === null)
			$base_url = get_permalink(theme_cache::get_page_by_path(self::$page_slug)->ID);

		if($tabs === null)
			$tabs = [
				'recommend' => [
					'tx' => ___('Recommend'),
					'icon' => 'star',
					'url' => esc_url(add_query_arg([
						'tab' => 'recommend'
					],$base_url)),
				],
				
				'popular' => [
					'tx' => ___('Popular'),
					'icon' => 'bar-chart',
					'url' => esc_url(add_query_arg([
						'tab' => 'popular'
					],$base_url)),
					'filters' => [
						'day' => [
							'tx' => ___('Daily popular'),
							'url' => esc_url(add_query_arg([
								'tab' => 'popular',
								'filter' => 'day',
							],$base_url)),
						],
						'week' => [
							'tx' => ___('Weekly popular'),
							'url' => add_query_arg([
								'tab' => 'popular',
								'filter' => 'week',
							],$base_url),
						],
						'month' => [
							'tx' => ___('Monthly popular'),
							'url' => esc_url(add_query_arg([
								'tab' => 'popular',
								'filter' => 'month',
							],$base_url)),
						],
					],/** end filter */
				],/** end popular */
				'latest' => [
					'tx' => ___('Latest'),
					'icon' => 'refresh',
					'url' => esc_url(add_query_arg([
						'tab' => 'latest'
					],$base_url)),
				],
				

				'users' => [
					'tx' => ___('Users'),
					'icon' => 'users',
					'url' => esc_url(add_query_arg([
						'tab' => 'users'
					],$base_url)),
					'filter' => [
						'me' => [
							'tx' => ___('Me'),
							'url' => esc_url(add_query_arg([
								'tab' => 'user',
								'filter' => 'me',
							],$base_url)),
						],/** end me */
					],/** end filters */
				],/** end users */
			];/** end types */
			
		if($key)
			return isset($tabs[$key]) ? $tabs[$key] : false;
			
		return $tabs;
	}
	public static function the_users_rank(){
		
	}
	public static function the_latest_posts(array $args = []){
		$cache = theme_cache::get('latest','page-rank');
		if(!empty($cache)){
			echo $cache;
			return $cache;
		}
		global $post;
		$defaults = [
			'posts_per_page ' => 100,
			'paged' => 1,
		];
		$args = array_merge($defaults,$args);

		$query = new WP_Query($args);
		
		ob_start();
		if($query->have_posts()){
			?>
			<div class="list-group">
				<?php
				$i = 1;
				foreach($query->posts as $post){
					setup_postdata($post);
					self::rank_img_content([
						'index' => $i,
					]);
					++$i;
				}
				?>
			</div>
			<?php
			wp_reset_postdata();
		}else{
			
		}
		$cache = html_minify(ob_get_contents());
		ob_end_clean();

		theme_cache::set('latest',$cache,'page-rank',3600);
		echo $cache;
		return $cache;
	}
	public static function get_popular_posts(array $args = []){
		$active_filter_tab = get_query_var('filter');
		$filter_tabs = self::get_tabs('popular')['filters'];
		
		if(!isset($filter_tabs[$active_filter_tab]))
			$active_filter_tab = 'day';

		$cache_id = 'popular-' . $active_filter_tab; 
		$cache = theme_cache::get($cache_id,'page-rank');
		if(!empty($cache)){
			return $cache;
		}
		global $post;
		$defaults = [
			'posts_per_page ' => 30,
			'paged' => 1,
			'date_query' => [
				[
					'column' => 'post_date_gmt',
					'after'  => '1 ' . $active_filter_tab . ' ago',
				]
			],
		];
		$args = array_merge($defaults,$args);
		/**
		 * orderby points
		 */
		if(class_exists('custom_post_point')){
			$args['meta_key']  = custom_post_point::$post_meta_key['count_points'];
			$args['orderby'] = 'meta_value_num';
		/**
		 * orderby views
		 */
		}else if(class_exists('theme_post_views')){
			$args['meta_key']  = theme_post_views::$post_meta_key;
			$args['orderby'] = 'meta_value_num';
		/**
		 * orderby comment count
		 */
		}else{
			$args['orderby'] = 'comment_count';
		}
		
		$query = new WP_Query($args);
		
		ob_start();
		if($query->have_posts()){
			?>
			<div class="list-group">
				<?php
				$i = 1;
				foreach($query->posts as $post){
					setup_postdata($post);
					self::rank_img_content([
						'index' => $i,
						'lazyload' => $i <= 5 ? false : true,
					]);
					++$i;
				}
				?>
			</div>
			<?php
			wp_reset_postdata();
		}
		$cache = html_minify(ob_get_contents());
		ob_end_clean();

		theme_cache::set($cache_id,$cache,'page-rank',3600);
		return $cache;
	}
	public static function the_recommend_posts(array $args = []){
		$cache = theme_cache::get('recommend','page-rank');
		if(!empty($cache)){
			echo $cache;
			return $cache;
		}
		global $post;
		$defaults = [
			'posts_per_page ' => 100,
			'paged' => 1,
			'orderby' => 'rand',
			'post__in' => theme_recommended_post::get_ids(),
		];
		$args = array_merge($defaults,$args);

		$query = new WP_Query($args);
		
		ob_start();
		if($query->have_posts()){
			?>
			<div class="list-group">
				<?php
				$i = 1;
				foreach($query->posts as $post){
					setup_postdata($post);
					self::rank_img_content([
						'index' => $i,
						'lazyload' => $i <= 5 ? false : true,
					]);
					++$i;
				}
				?>
			</div>
			<?php
			wp_reset_postdata();
		}else{
			
		}
		$cache = html_minify(ob_get_contents());
		ob_end_clean();

		theme_cache::set('recommend',$cache,'page-rank',3600);
		echo $cache;
		return $cache;
	}
	public static function rank_img_content($args = []){
		global $post;
		
		$defaults = array(
			'classes' => '',
			'lazyload' => true,
			'excerpt' => true,
			'index' => false,
		);
		$args = array_merge($defaults,$args);

		$post_title = esc_html(get_the_title());

		$excerpt = get_the_excerpt();
		if(!empty($excerpt))
			$excerpt = esc_html($excerpt);

		$thumbnail_real_src = esc_url(theme_functions::get_thumbnail_src($post->ID));

		$thumbnail_placeholder = theme_features::get_theme_images_url(theme_functions::$thumbnail_placeholder);
		?>
		<a class="list-group-item <?= $args['classes'];?>" href="<?= esc_url(get_permalink());?>" title="<?= $post_title, empty($excerpt) ? null : ' - ' . $excerpt;?>">
			<div class="row">
				<div class="col-xs-12 col-sm-12 col-md-4 col-lg-3">
					<div class="thumbnail-container">
						<img src="<?= $thumbnail_placeholder;?>" alt="<?= $post_title;?>" class="media-object placeholder">
						<?php if($args['lazyload'] === true){ ?>
							<img class="post-list-img" src="<?= $thumbnail_placeholder;?>" data-src="<?= $thumbnail_real_src;?>" alt="<?= $post_title;?>"/>
						<?php }else{ ?>
							<img class="post-list-img" src="<?= $thumbnail_real_src;?>" alt="<?= $post_title;?>"/>
						<?php } ?>
					</div>
				</div>
				<div class="col-xs-12 col-sm-12 col-md-8 col-lg-9">
					<h4 class="media-heading"><?= $post_title;?></h4>
					<?php
					/**
					 * output excerpt
					 
					 */
					if($args['excerpt'] === true){
						?>
						<div class="excerpt hidden-xs"><?= str_sub(strip_tags(get_the_content()),200);?></div>
					<?php } ?>
					<div class="extra">
						<div class="metas row">
							<!-- author -->
							<div class="author meta col-xs-6 col-sm-2">
								<i class="fa fa-user"></i> 
								<?= theme_cache::get_the_author_meta('display_name',$post->post_author);?>
							</div>
							
							<!-- category -->
							<div class="category meta col-xs-6 col-sm-2">
								<i class="fa fa-folder-open"></i> 
								<?php
								$cats = array_map(function($v){
									return $v->name;
								},get_the_category($post->ID));
								echo implode(' / ',$cats);
								?>
							</div>

							<!-- views -->
							<?php if(class_exists('theme_post_views') && theme_post_views::is_enabled()){ ?>
								<div class="view meta col-xs-6 col-sm-2">
									<i class="fa fa-play-circle"></i> 
									<?= theme_post_views::get_views();?>
								</div>
							<?php } ?>

							<?php if(!wp_is_mobile()){ ?>
								<div class="comments meta col-xs-6 col-sm-2 hidden-xs">
									<i class="fa fa-comment"></i>
									<?= (int)$post->comment_count;?>
								</div>
							<?php } ?>
							
							<?php
							/**
							 * point
							 */
							if(class_exists('custom_post_point')){
								?>
								<div class="point meta col-xs-6 col-sm-2">
									<i class="fa fa-paw"></i>
									<?= (int)custom_post_point::get_post_points_count($post->ID);?>
								</div>
								<?php
							}
							?>


						</div><!-- /.metas -->
					</div>
					<?php if($args['index']){ ?>
						<i class="index"><?= $args['index'];?></i>
					<?php } ?>					
				</div>
			</div>
		</a>
		<?php
	}
	public static function display_frontend(){
		
	}
	public static function frontend_enqueue_css(){
		if(!self::is_page())
			return false;
			
		wp_enqueue_style(
			self::$iden,
			theme_features::get_theme_includes_css(__DIR__,'style'),
			'frontend',
			theme_file_timestamp::get_timestamp()
		);
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
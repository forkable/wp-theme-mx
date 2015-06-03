<?php
/**
 * theme_page_rank
 *
 * @version 1.0.0
 * @author INN STUDIO <inn-studio.com>
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
		if(!current_user_can('manage_options')) 
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
			$page = theme_cache::get_page_by_path($k);
			if(!$page)
				$page_id = wp_insert_post(array_merge($defaults,$v));
		}

	}
	public static function is_page(){
		static $cache = null;
		if($cache === null)
			$cache = is_page(self::$page_slug);

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
		$cache = html_compress(ob_get_contents());
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
			
		$cache = theme_cache::get($active_filter_tab,'page-rank');
		if(!empty($cache)){
			return $cache;
		}
		global $post;
		$defaults = [
			'posts_per_page ' => 100,
			'paged' => 1,
			'date_query' => [
				[
					'column' => 'post_date_gmt',
					'after'  => '1 ' . $active_filter_tab . ' ago',
				]
			],
			'orderby' => 'meta_value_num',
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
		}
		$cache = html_compress(ob_get_contents());
		ob_end_clean();

		theme_cache::set($active_filter_tab,$cache,'page-rank',3600);
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
		$cache = html_compress(ob_get_contents());
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
					if($args['excerpt'] === true && !wp_is_mobile()){
						?>
						<div class="excerpt hidden-xs"><?= str_sub(strip_tags(get_the_content(),'<del><b><strong><i><em>'),200);?></div>
					<?php } ?>
					<div class="extra">
						<div class="metas row">
							<!-- author -->
							<div class="author meta col-xs-6 col-sm-2">
								<i class="fa fa-user"></i> 
								<?= esc_html(get_the_author_meta('display_name',$post->post_author));?>
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
/**
 * Class Name: custom_page_rank_wp_bootstrap_navwalker
 * GitHub URI: https://github.com/twittem/wp-bootstrap-navwalker
 * Description: A custom WordPress nav walker class to implement the Bootstrap 3 navigation style in a custom theme using the WordPress built in menu manager.
 * Version: 2.0.4
 * Author: Edward McIntyre -
 * 
 * @twittem License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */
class custom_page_rank_wp_bootstrap_navwalker extends Walker_Nav_Menu{
	/**
	 * 
	 * @see Walker::start_lvl()
	 * @since 3.0.0
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param int $depth Depth of page. Used for padding.
	 */
	public function start_lvl(& $output, $depth = 0, $args = []){
		//$indent = str_repeat("\t", $depth);
		$output .= "<ul role=\"menu\" class=\" dropdown-menu\">";
	}
	/**
	 * 
	 * @see Walker::start_el()
	 * @since 3.0.0
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param object $item Menu item data object.
	 * @param int $depth Depth of menu item. Used for padding.
	 * @param int $current_page Menu item ID.
	 * @param object $args 
	 */
	public function start_el(& $output, $item, $depth = 0, $args = [], $id = 0){
		//$indent = ($depth) ? str_repeat("\t", $depth) : '';
		/**
		 * Dividers, Headers or Disabled
		 * =============================
		 * Determine whether the item is a Divider, Header, Disabled or regular
		 * menu item. To prevent errors we use the strcasecmp() function to so a
		 * comparison that is not case sensitive. The strcasecmp() function returns
		 * a 0 if the strings are equal.
		 */
		
		if (strcasecmp($item->attr_title, 'divider') == 0 && $depth === 1){
			$output .= '<li role="presentation" class="divider">';
		}else if (strcasecmp($item->title, 'divider') == 0 && $depth === 1){
			$output .= '<li role="presentation" class="divider">';
		}else if (strcasecmp($item->attr_title, 'dropdown-header') == 0 && $depth === 1){
			$output .= '<li role="presentation" class="dropdown-header">' . $item->title ;
		}else if (strcasecmp($item->attr_title, 'disabled') == 0){
			$output .= '<li role="presentation" class="disabled"><a href="javascript:;">' . $item->title . '</a>';
		}else{
			$class_names = $value = '';
			$classes = empty($item->classes) ? [] : (array) $item->classes;
			$classes[] = 'menu-item-' . $item->ID;
			$class_names = join(' ', apply_filters('nav_menu_css_class', array_filter($classes), $item, $args));
		if ($args->has_children)
			$class_names .= ' dropdown';
			
		/**
		 * current
		 */
		$curr_class = ['current-menu-item','current-post-ancestor','current-menu-parent'];
		$is_curr = false;
		foreach($classes as $v){
			if(strpos($v,'current') !== false){
				$is_curr = true;
				break;
			}
		}
		if ($is_curr)
			$class_names .= ' active';
			
			$class_names = $class_names ? ' class="' . $class_names . '"' : '';
			
			$id = apply_filters('nav_menu_item_id', 'menu-item-' . $item->ID, $item, $args);
			$id = $id ? ' id="' . $id . '"' : '';
			
			$output .= '<li' . $id . $value . $class_names . '>';
			
			$atts = [];
			
			$atts['title'] = ! empty($item->title) ? strip_tags($item->title) : '';
			
			$atts['target'] = ! empty($item->target) ? $item->target : '';
			
			$atts['rel'] = ! empty($item->xfn) ? $item->xfn : '';

			$atts['href'] = $item->url;

			
			//$atts['icon'] = isset($item->awesome) ? $item->awesome : null;
			
			// If item has_children add atts to a.
		if ($args->has_children && $depth === 0){
			$atts['data-toggle'] = 'dropdown';
			$atts['class'] = 'dropdown-toggle';
			//$atts['aria-haspopup'] = 'true';
		}
		$atts = apply_filters('nav_menu_link_attributes', $atts, $item, $args);
		$attributes = '';
		foreach ($atts as $attr => $value){
			if (! empty($value)){
				$value = ('href' === $attr) ? esc_url($value) : $value;
				$attributes .= ' ' . $attr . '="' . $value . '"';
				}
			}
		$item_output = $args->before;
		/**
		 * Glyphicons
		 * ===========
		 * Since the the menu item is NOT a Divider or Header we check the see
		 * if there is a value in the attr_title property. If the attr_title
		 * property is NOT null we apply it as the class name for the glyphicon.
		 */
		if (! empty($item->awesome))
			$item_output .= '<a' . $attributes . '><i class="fa fa-fw fa-' . $item->awesome . '"></i>&nbsp;';
		else
			$item_output .= '<a' . $attributes . '>';
			
		$item_output .= $args->link_before . apply_filters('the_title', $item->title, $item->ID) . $args->link_after;
		
		$item_output .= ($args->has_children && 0 === $depth) ? ' <span class="caret"></span></a>' : '</a>';
		
		$item_output .= $args->after;
		
		$output .= apply_filters('walker_nav_menu_start_el', $item_output, $item, $depth, $args);
		}
	}
	/**
	 * Traverse elements to create list from elements.
	 * 
	 * Display one element if the element doesn't have any children otherwise,
	 * display the element and its children. Will only traverse up to the max
	 * depth and no ignore elements under that depth.
	 * 
	 * This method shouldn't be called directly, use the walk() method instead.
	 * 
	 * @see Walker::start_el()
	 * @since 2.5.0
	 * @param object $element Data object
	 * @param array $children_elements List of elements to continue traversing.
	 * @param int $max_depth Max depth to traverse.
	 * @param int $depth Depth of current element.
	 * @param array $args 
	 * @param string $output Passed by reference. Used to append additional content.
	 * @return null Null on failure with no changes to parameters.
	 */
	public function display_element($element, & $children_elements, $max_depth, $depth, $args, & $output){
		if (! $element)
			return;
			
		$id_field = $this->db_fields['id'];
		// Display this element.
		if (is_object($args[0]))
			$args[0]->has_children = ! empty($children_elements[ $element->$id_field ]);
			
		parent::display_element($element, $children_elements, $max_depth, $depth, $args, $output);
		}
}
//custom_page_rank_wp_bootstrap_navwalker::custom_nav_menu_hook();
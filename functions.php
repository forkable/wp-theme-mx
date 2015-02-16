<?php
/** Theme options */
get_template_part('core/core-options');

/** Theme features */
get_template_part('core/core-features');

/** Theme functions */
get_template_part('core/core-functions');

/** 
 * theme_functions
 */
add_action('after_setup_theme','theme_functions::init');
class theme_functions{
	public static $iden = 'mx';
	public static $theme_edition = 1;
	public static $theme_date = '2015-02-01 00:00';
	public static $thumbnail_size = array('thumbnail',320,200);
	public static $comment_avatar_size = 60;
	public static $cache_expire = 3600;
	/** 
	 * theme_meta_translate(
	 */
	public static function theme_meta_translate(){
		return array(
			'name' => ___('MX'),
			'theme_url' => ___('http://inn-studio.com/mx'),
			'author_url' => ___('http://inn-studio.com'),
			'author' => ___('INN STUDIO'),
			'qq' => array(
				'number' => '272778765',
				'link' => 'http://wpa.qq.com/msgrd?v=3&amp;uin=272778765&amp;site=qq&amp;menu=yes',
			),
			'qq_group' => array(
				'number' => '170306005',
				'link' => 'http://wp.qq.com/wpa/qunwpa?idkey=d8c2be0e6c2e4b7dd2c0ff08d6198b618156d2357d12ab5dfbf6e5872f34a499',
			),
			'email' => 'kmvan.com@gmail.com',
			'edition' => ___('Professional edition'),
			'des' => ___('MX - Dream starts'),
		);
	}
	/** 
	 * init
	 */	
	public static function init(){
		/** 
		 * register menu
		 */
		register_nav_menus(
			array(
				'menu-header' 			=> ___('Header menu'),
				'menu-header-mobile' 	=> ___('Header menu mobile'),
				'menu-top-bar' 			=> ___('Top bar menu'),
				'menu-tools' 			=> ___('Header menu tools'),
			)
		);	
		/** 
		 * frontend_js
		 */
		add_action('frontend_seajs_use',get_class() . '::frontend_js',1);
		/** 
		 * other
		 */
		add_action('widgets_init',get_class() . '::widget_init');
		add_filter('use_default_gallery_style','__return_false');
		add_theme_support( 'html5', array( 'comment-list', 'comment-form', 'search-form' ) );
		add_theme_support('post-thumbnails');
		add_image_size(self::$thumbnail_size[0],self::$thumbnail_size[1],self::$thumbnail_size[2],true);
		set_post_thumbnail_size(self::$thumbnail_size[1],self::$thumbnail_size[2]);
		add_theme_support('title-tag');
		/** 
		 * query_vars
		 */
		//add_filter('query_vars', get_class() . '::filter_query_vars');
		/** 
		 * bg
		 */
		add_theme_support('custom-background',array(
			'default-color'			=> 'eeeeee',
			'default-image'			=> '',
			'default-position-x'	=> 'center',
			'default-attachment'	=> 'fixed',
			'wp-head-callback'		=> 'theme_features::_fix_custom_background_cb',
		));

	}
	
	public static function frontend_js(){
		?>
		seajs.use('frontend',function(m){
			m.init();
		});
		
		<?php
		/** 
		 * post toc
		 */
		if(is_singular()){
			?>
			seajs.use('modules/jquery.posttoc',function(m){
				m.config.lang.M00001 = '<?php echo  ___('Post Toc');?>';
				m.config.lang.M00002 = '<?php echo  ___('[Top]');?>';
				m.init();
			});
			<?php
		}
	}
	/** 
	 * widget_init
	 */
	public static function widget_init(){
		$sidebar = array(
			array(
				'name' 			=> ___('Home widget area'),
				'id'			=> 'widget-area-home',
				'description' 	=> ___('Appears on home in the sidebar.')
			),
			array(
				'name' 			=> ___('Archive page widget area'),
				'id'			=> 'widget-area-archive',
				'description' 	=> ___('Appears on archive page in the sidebar.')
			),
			array(
				'name' 			=> ___('Footer widget area'),
				'id'			=> 'widget-area-footer',
				'description' 	=> ___('Appears on all page in the footer.'),
				'before_widget' => '<div class="col-xs-12 col-sm-6 col-md-3"><aside id="%1$s"><div class="panel panel-default widget %2$s">',
				'after_widget'		=> '</div></aside></div>',
			),
			array(
				'name' 			=> ___('Singular post widget area'),
				'id'			=> 'widget-area-post',
				'description' 	=> ___('Appears on post in the sidebar.')
			),
			array(
				'name' 			=> ___('Singular page widget area'),
				'id'			=> 'widget-area-page',
				'description' 	=> ___('Appears on page in the sidebar.')
			),
			array(
				'name' 			=> ___('Sign page widget area'),
				'id'			=> 'widget-area-sign',
				'description' 	=> ___('Appears on sign page in the sidebar.')
			),
			array(
				'name' 			=> ___('404 page widget area'),
				'id'			=> 'widget-area-404',
				'description' 	=> ___('Appears on 404 no found page in the sidebar.')
			)
		);
		foreach($sidebar as $v){
			register_sidebar(array(
				'name'				=> $v['name'],
				'id'				=> $v['id'],
				'description'		=> $v['description'],
				'before_widget'		=> isset($v['before_widget']) ? $v['before_widget'] : '<aside id="%1$s"><div class="panel panel-default mx-panel widget %2$s">',
				'after_widget'		=> isset($v['after_widget']) ? $v['after_widget'] : '</div></aside>',
				'before_title'		=> isset($v['before_title']) ? $v['before_title'] : '<div class="panel-heading panel-heading-default mx-panel-heading clearfix"><h3 class="widget-title panel-title">',
				'after_title'		=> isset($v['after_title']) ? $v['after_widget'] : '</h3></div>',
			));
		}
	}
	public static function filter_query_vars($vars){
		//if(!in_array('paged',$vars)) $vars[] = 'paged';
		//if(!in_array('tab',$vars)) $vars[] = 'tab';
		// if(!in_array('orderby',$vars)) $vars[] = 'orderby'; /** = type */
		return $vars;
	}
	/**
	 * tab type
	 *
	 * @param string
	 * @return array|string|false
	 * @version 1.0.0
	 * @author KM@INN STUDIO
	 */
	public static function get_tab_type($key = null){
		$typies = array(
			'lastest' => array(
				'icon' => 'gauge',
				'text' => ___('Lastest')
			),
			'pop' => array(
				'icon' => 'happy',
				'text' => ___('Popular')
			),
			'rand' => array(
				'icon' => 'shuffle',
				'text' => ___('Random')
			),
		);
		if($key){
			return isset($typies[$key]) ? $typies[$key] : false;
		}else{
			return $typies;
		}
	}
	/**
	 * Output orderby nav in Neck position
	 *
	 * @return 
	 * @version 1.0.0
	 * @author KM@INN STUDIO
	 */
	public static function the_order_nav($args = null){
		$current_tab = get_query_var('tab');
		$current_tab = !empty($current_tab) ? $current_tab : 'lastest';
		$typies = self::get_tab_type();
		if(is_home()){
			$current_url = home_url();
		}else if(is_category()){
			$cat_id = theme_features::get_current_cat_id();
			$current_url = get_category_link($cat_id);
		}else if(is_tag()){
			$tag_id = theme_features::get_current_tag_id();
			$current_url = get_tag_link($tag_id);
		}else{
			$current_url = get_current_url();
		}
		?>
		<nav class="page-nav">
			<?php
			foreach($typies as $k => $v){
				$current_class = $current_tab === $k ? 'current' : null;
				$url = add_query_arg('tab',$k,$current_url);
				?>
				<a href="<?php echo esc_url($url);?>" class="item <?php echo $current_class;?>">
					<span class="icon-<?php echo $v['icon'];?>"></span><span class="after-icon"><?php echo esc_html($v['text']);?></span>
				</a>
				<?php
			}
			?>
		</nav>
		<?php
	}
	public static function get_home_posts($args = null){
		global $post,$wp_query;
		$options = theme_options::get_options();
		$home_data_filter = isset($options['home-data-filter']) ? $options['home-data-filter'] : null;
		$defaults = array(
			'date' => $home_data_filter,
			'paged' => get_query_var('paged') ? get_query_var('paged') : 1,
			'current_tab' => get_query_var('tab') ? get_query_var('tab') : 'lastest',
			'posts_per_page' => get_option('posts_per_page'),
		);
		$r = wp_parse_args($args,$defaults);
		extract($r);
		
		$query_args['paged'] = $paged;
		$query_args['date'] = $date;
		$query_args['posts_per_page'] = $posts_per_page;

		switch($current_tab){
			case 'pop':
				$query_args['orderby'] = 'thumb-up';
				break;
			case 'rand':
				$query_args['orderby'] = 'rand';
				break;
			default:
				$query_args['orderby'] = 'lastest';
				$query_args['date'] = 'all';
		}
		$wp_query = self::get_posts_query($query_args);
		
		return $wp_query;
	}
	public static function get_posts_query($args,$query_args = null){
		global $paged;
		$options = theme_options::get_options();
		$defaults = array(
			'orderby' => 'views',
			'order' => 'desc',
			'posts_per_page' => get_option('posts_per_page'),
			'paged' => 1,
			'category__in' => array(),
			'date' => 'all',
			
		);
		$r = wp_parse_args($args,$defaults);
		extract($r);
		$query_args = wp_parse_args($query_args,array(
			'posts_per_page' => $posts_per_page,
			'paged' => $paged,
			'ignore_sticky_posts' => 1,
			'category__in' => $category__in,
			'post_status' => 'publish',
			'post_type' => 'post',
			'has_password' => false,
		));
		
		switch($orderby){
			case 'views':
				$query_args['meta_key'] = 'views';
				$query_args['orderby'] = 'meta_value_num';
				break;
			case 'thumb-up':
			case 'thumb':
				$query_args['meta_key'] = 'post_thumb_count_up';
				$query_args['orderby'] = 'meta_value_num';
				break;
			case 'rand':
			case 'random':
				$query_args['orderby'] = 'rand';
				break;
			case 'latest':
				$query_args['orderby'] = 'date';
				break;
			case 'comment':
				$query_args['orderby'] = 'comment_count';
				break;
			case 'recomm':
			case 'recommended':
				if(class_exists('theme_recommended_post')){
					$query_args['post__in'] = (array)theme_options::get_options(theme_recommended_post::$iden);
				}else{
					$query_args['post__in'] = (array)get_option( 'sticky_posts' );
				unset($query_args['ignore_sticky_posts']);
				}
				unset($query_args['post__not_in']);
				break;
			default:
				$query_args['orderby'] = 'date';
		}
		if($date && $date != 'all'){
			/** 
			 * date query
			 */
			switch($date){
				case 'daily' :
					$after = 'day';
					break;
				case 'weekly' :
					$after = 'week';
					break;
				case 'monthly' :
					$after = 'month';
					break;
				default:
					$after = 'day';
			}
			$query_args['date_query'] = array(
				array(
					'column' => 'post_date_gmt',
					'after'  => '1 ' . $after . ' ago',
				)
			);
		}
		return theme_cache::get_queries($query_args);
	}
	/**
	 * archive_img_content
	 *
	 * @return
	 * @version 1.0.0
	 * @author KM@INN STUDIO
	 */
	public static function archive_img_content($args = array()){
		$defaults = array(
			'classes' => array('grid-50','tablet-grid-50','mobile-grid-50'),
			'lazyload' => true,
		);
		$r = wp_parse_args($args,$defaults);
		extract($r,EXTR_SKIP);

		global $post;
		$classes[] = 'post-list post-img-list';
		$post_title = get_the_title();

		$excerpt = get_the_excerpt() ? get_the_excerpt() : null;

		$thumbnail_real_src = theme_functions::get_thumbnail_src($post->ID);
		?>
		<li class="<?php echo esc_attr(implode(' ',$classes));?>">
			<a class="post-list-bg" href="<?php echo get_permalink();?>" title="<?php echo esc_attr($post_title), empty($excerpt) ? null : ' - ' . esc_attr($excerpt);?>">
				<img class="post-list-img" src="<?php echo theme_features::get_theme_images_url('frontend/thumb-preview.jpg');?>" data-original="<?php echo esc_url($thumbnail_real_src);?>" alt="<?php echo esc_attr($post_title);?>" width="<?php echo self::$thumbnail_size[1];?>" height="<?php echo self::$thumbnail_size[2];?>"/>
				
				<h3 class="post-list-title"><?php the_title();?></h3>
					
			</a>
		</li>
		<?php
	}
	/**
	 * get_meta_type
	 *
	 * @param string $type
	 * @return array
	 * @version 1.0.1
	 * @author KM@INN STUDIO
	 */
	public static function get_meta_type($type){
		global $post;
		$output = array();
		switch($type){
			case 'thumb-up':
				$output = array(
					'icon' => 'thumbs-o-up',
					'num' => (int)get_post_meta($post->ID,'post_thumb_count_up',true),
					'tx' => ___('Thumb up'),
				);
				break;
			case 'comments':
				$output = array(
					'icon' => 'comment',
					'num' => $post->comment_count,
					'tx' => ___('Comment count'),
				);
				break;
			case 'views':
			case 'view':
				$output = array(
					'icon' => 'play',
					'num' => (int)get_post_meta($post->ID,'views',true),
					'tx' => ___('Views'),
				);
				break;
			default:
				return false;
		}
		return $output;
	}
	public static function archive_tx_content($args = array()){
		global $post;
		$defaults = array(
			'classes'			=> array(),
			'meta_type'			=> 'views',
		);
		$r = wp_parse_args($args,$defaults);
		extract($r,EXTR_SKIP);
		
		$post_title = get_the_title();
		/** 
		 * classes
		 */
		$classes[] = 'post-list post-tx-list';
		$classes = implode(' ',$classes);
		
		$meta_type = self::get_meta_type($meta_type);
		
		?>
		<li class="<?php echo esc_attr($classes);?>">
			<a href="<?php echo esc_url(get_permalink());?>" title="<?php echo esc_attr($post_title);?>">
				<?php
				if(empty($meta_type)){
					echo esc_html($post_title);
				}else{
					?>
					<span class="post-list-meta" title="<?php echo esc_attr($meta_type['tx']);?>">
						<span class="icon-<?php echo $meta_type['icon'];?>"></span><span class="after-icon"><?php echo $meta_type['num'];?></span>
					</span>
					<span class="tx"><?php echo esc_html($post_title);?></span>
				<?php } ?>
			</a>
		</li>
		<?php
		
	}
	
	/** 
	 * archive_content
	 */
	public static function archive_content($args = array()){
		global $post;
		
		$defaults = array(
			'classes'			=> array('grid-50','tablet-grid-50','mobile-grid-100'),
			'show_author' 		=> true,
			'show_date' 		=> true,
			'show_views' 		=> true,
			'show_comms' 		=> true,
			'show_rating' 		=> true,
			'lazyload'			=> true,
			
		);
		$r = wp_parse_args($args,$defaults);
		extract($r,EXTR_SKIP);
		
		global $post;
		$classes[] = 'post-list post-mixed-list';
		$post_title = get_the_title();

		$excerpt = get_the_excerpt() ? get_the_excerpt() : null;
		/** 
		 * classes
		 */
		
		$thumbnail_real_src = theme_functions::get_thumbnail_src($post->ID);
		?>
		
		<div <?php post_class($classes);?>>
			<?php if(is_sticky()){ ?>
				<div class="sticky-post" title="<?php echo esc_attr(___('Sticky post'));?>"></div>
			<?php } ?>
			<a class="post-list-bg" href="<?php echo get_permalink();?>">
				<img class="post-list-img" src="<?php echo theme_features::get_theme_images_url('frontend/thumb-preview.jpg');?>" data-original="<?php echo esc_url($thumbnail_real_src);?>" alt="<?php echo esc_attr($post_title);?>" width="<?php echo self::$thumbnail_size[1];?>" height="<?php echo self::$thumbnail_size[2];?>"/>
				<div class="caption area-tx">
					<h3 class="post-list-title" title="<?php echo esc_attr($post_title);?>"><?php echo esc_html($post_title);?></h3>
					<p class="excerpt"><?php echo esc_html($excerpt);?></p>
					<div class="row hide">
						<?php if($show_views === true && function_exists('the_views')) { ?>
							<div class="col-xs-6">
								<i class="fa fa-play-circle"></i>
								<?php the_views();?>
							</div>
						<?php } ?>
						<?php if($show_comms === true) { ?>
							<div class="col-xs-6">
								<i class="fa fa-comment"></i>
								<?php echo (int)$post->comment_count;?>
							</div>
						<?php } ?>
					</div>
				</div>
			</a>
			<div class="extra"></div>
		</div>
		<?php
	}
	public static function widget_rank_tx_content($args){
		self::archive_tx_content($args);
	}
	public static function widget_rank_img_content(){
		global $post;
		
		$defaults = array(
			'classes' => array('grid-50','tablet-grid-50','mobile-grid-50'),
			'lazyload' => true,
		);
		$r = wp_parse_args($args,$defaults);
		extract($r,EXTR_SKIP);

		global $post;
		$classes[] = 'post-list post-img-list';
		$post_title = get_the_title();

		$excerpt = get_the_excerpt() ? get_the_excerpt() : null;

		$thumbnail_real_src = theme_functions::get_thumbnail_src($post->ID);
		?>
		<li <?php post_class(array('media'));?>>
			<a class="post-list-bg" href="<?php echo get_permalink();?>" title="<?php echo esc_attr($post_title), empty($excerpt) ? null : ' - ' . esc_attr($excerpt);?>">
				<div class="media-left">
					<img class="media-object" src="<?php echo theme_features::get_theme_images_url('frontend/thumb-preview.jpg');?>" data-original="<?php echo esc_url($thumbnail_real_src);?>" alt="<?php echo esc_attr($post_title);?>" width="<?php echo self::$thumbnail_size[1];?>" height="<?php echo self::$thumbnail_size[2];?>"/>
				</div>
				<div class="media-body">
					<h4 class="media-heading"><?php the_title();?></h4>
					<div class="extra row">
						<div class="metas">
							<div class="author meta col-xs-12">
								<i class="fa fa-user"></i>
								<?php the_author();?>
							</div>
							
							<?php if(function_exists('the_views')){ ?>
								<div class="view meta col-xs-6">
									<i class="fa fa-play-circle"></i>
									<?php the_views();?>
								</div>
							<?php } ?>

							<div class="comments meta col-xs-6">
								<i class="fa fa-comment"></i>
								<?php echo (int)$post->comment_count;?>
							</div>
						</div><!-- /.metas -->
					</div>					
				</div>
			</a>
		</li>
		<?php
	}
	public static function page_content($args = array()){
		global $post;
		
		$defaults = array(
			'target' 			=> '_blank',
			'classes'			=> array('grid-100','tablet-grid-100','mobile-grid-100'),
			'show_author' 		=> true,
			'show_date' 		=> true,
			'show_views' 		=> true,
			'show_comms' 		=> true,
			'show_rating' 		=> true,
			'lazyload'			=> true,
			
		);
		$r = wp_parse_args($args,$defaults);
		extract($r,EXTR_SKIP);
		
		$post_title = get_the_title();
		$target = $target ? ' target="' . $target . '" ' : null;
		/** 
		 * classes
		 */
		$classes[] = 'singluar-post';
		/** 
		 * cache author datas
		 */
		$author = get_user_by('id',$post->post_author);
		
		?>
		<article id="post-<?php the_ID();?>" <?php post_class($classes);?>>
			<?php if(!empty($post_title)){ ?>
				<h3 class="entry-title"><?php echo esc_html($post_title);?></h3>
			<?php } ?>
			<!-- post-content -->
			<div class="post-content content-reset">
				<?php the_content();?>
			</div>
			<?php// self::the_post_pagination();?>
		</article>
		<?php
	}
	/** 
	 * singular_content
	 */
	public static function singular_content($args = array()){
		global $post;
		
		$defaults = array(
			'classes'			=> array(''),
			'show_author' 		=> true,
			'show_date' 		=> true,
			'show_views' 		=> true,
			'show_comms' 		=> true,
			'show_rating' 		=> true,
			'lazyload'			=> true,
			
		);
		$r = wp_parse_args($args,$defaults);
		extract($r,EXTR_SKIP);
		
		$post_title = get_the_title();
		/** 
		 * classes
		 */
		$classes[] = 'singluar-post panel panel-default';
		?>
		<article id="post-<?php the_ID();?>" <?php post_class($classes);?>>
			<div class="panel-heading">
				<?php if(!empty($post_title)){ ?>
					<h3 class="entry-title panel-title"><?php echo esc_html($post_title);?></h3>
				<?php } ?>
				<header class="post-header post-metas clearfix">
					
					<!-- category -->
					<?php
					$cats = get_the_category_list('<i class="split"> / </i> ');
					if(!empty($cats)){
						?>
						<span class="post-meta post-category" title="<?php echo esc_attr(___('Category'));?>">
							<i class="fa fa-folder-open"></i>
							<?php echo $cats;?>
						</span>
					<?php } ?>
					
					<!-- time -->
					<time class="post-meta post-time" datetime="<?php echo esc_attr(get_the_time('Y-m-d H:i:s'));?>">
						<i class="fa fa-clock-o"></i>
						<?php echo esc_html(friendly_date((get_the_time('U'))));?>
					</time>
					<!-- author link -->
					<a class="post-meta post-author" href="<?php echo get_author_posts_url(get_the_author_meta('ID'));?>" title="<?php echo esc_attr(sprintf(___('Views all post by %s'),get_the_author()));?>">
						<i class="fa fa-user"></i>
						<?php the_author();?>
					</a>
					<!-- views -->
					<?php if(class_exists('theme_post_views') && theme_post_views::is_enabled()){ ?>
						<span class="post-meta post-views" title="<?php echo esc_attr(___('Views'));?>">
							<i class="fa fa-play-circle"></i>
							<?php echo esc_html(theme_post_views::display());?>
						</span>
					<?php } ?>
					<!-- edit link -->
					<?php if(is_user_logged_in() && current_user_can('edit_post',$post->ID)){ ?>
						<a href="<?php echo get_edit_post_link();?>" class="post-meta edit-post-link">
							<i class="fa fa-pencil-square"></i>
							<?php echo esc_html(___('Edit post'));?>
						</a>
					<?php } ?>
					<!-- permalink -->
					<a href="<?php echo get_permalink();?>" class="post-meta permalink" title="<?php echo esc_attr(___('Post link'));?>">
						<i class="fa fa-link"></i>
						<?php echo esc_html(___('Post link'));?>
					</a>

				</header>
			</div>

			<!-- post-content -->
			<div class="post-content content-reset panel-body clearfix">
				<?php the_content();?>
			</div>
			<?php echo theme_features::get_prev_next_pagination(array(
				'numbers_class' => array('btn btn-primary')
			));?>
			
			<?php
			/** 
			 * tags
			 */
			$tags = get_the_tags();
			if(!empty($tags)){
				?>
				<div class="post-tags btn-group btn-group-xs clearfix">
					<?php
					foreach($tags as $tag){
						?>
						<a href="<?php echo get_tag_link($tag->term_id);?>" class="tag btn btn-default" title="<?php echo sprintf(___('Views all posts by %s tag'),esc_attr($tag->name));?>">
							<i class="fa fa-tag"></i> 
							<?php echo esc_html($tag->name);?>
						</a>
						<?php
					}
					?>
				</div>
				<?php
			}
			?>
			
			<!-- post-footer -->
			<footer class="post-footer post-metas panel-footer clearfix">
				
				<?php
				/** 
				 * thumb-up
				 */
				if(class_exists('theme_post_thumb') && theme_post_thumb::is_enabled()){
					?>
					<div class="post-thumb post-meta">
						<a data-post-thumb="<?php echo $post->ID;?>,up" href="javascript:void(0);" class="theme-thumb theme-thumb-up" title="<?php echo ___('Good! I like it.');?>">
							<i class="fa fa-thumbs-up"></i> 
							<span class="count"><?php echo theme_post_thumb::get_thumb_up_count();?></span>
							 <span class="tx hidden-xs"><?php echo ___('Good');?></span>
						</a>
						<!-- <a data-post-thumb="<?php echo $post->ID;?>,down" href="javascript:void(0);" class="theme-thumb theme-thumb-down" title="<?php echo ___('Bad idea!');?>">
							<i class="fa fa-thumbs-down"></i> 
							<span class="count"><?php echo theme_post_thumb::get_thumb_down_count();?></span>
							<span class="tx hidden-xs"><?php echo ___('Bad');?></span>
						</a> -->
					</div>
				
				<?php } /** end thumb-up */ ?>

				<?php
				/**
				 * bookmark
				 */
				if(class_exists('theme_bookmark')){
					$is_marked = theme_bookmark::is_marked($post->ID,get_current_user_id());
					?>
					<div class="post-meta post-bookmark">
						<a 
							href="javascript:void(0);" 
							class="btn btn-primary <?php echo $is_marked ? 'marked' : null;?>" 
							data-post-id="<?php echo $post->ID;?>"
							
						>
							<i class="fa fa-heart"></i>
							<?php echo (int)theme_bookmark::get_count($post->ID);?>
						</a>
					</div>
				<?php } ?>

				<?php
				/** 
				 * post-share
				 */
				if(class_exists('theme_post_share') && theme_post_share::is_enabled()){
					?>
					<div class="post-meta post-share">
						<?php echo theme_post_share::display();?>
					</div>
					<?php
				} /** end post-share */
				?>
				<?php
				/** 
				 * comment
				 */
				$comment_count = (int)get_comments_number();
				$comment_tx = $comment_count <= 1 ? ___('comment') : ___('comments');
				?>
				<a href="javascript:void(0);" class="post-meta quick-comment comment-count" data-post-id="<?php echo $post->ID;?>">
					<i class="fa fa-comment"></i>
					<span class="comment-count-number"><?php echo esc_html($comment_count);?></span> <span class="hidden-xs"><?php echo esc_html($comment_tx);?></span>
				</a>
				
			</footer>
		</article>
		<?php
	}
	
	public static function the_post_tags(){
		global $post;
		$tags = get_the_tags();
		if(empty($tags)) return false;
		$first_tag = array_shift($tags);
		$split_str = '<span class="split">' . ___(', ') . '</span>';
		?>
		<div class="post-tags">
			<?php
			/** 
			 * first tag html
			 */
			ob_start();
			?>
			<a href="<?php echo get_tag_link($first_tag->term_id);?>" class="tag" title="<?php echo sprintf(___('Views all posts by %s tag'),esc_attr($first_tag->name));?>">
				<span class="icon-tags"></span><span class="after-icon"><?php echo esc_html($first_tag->name);?></span>
			</a>
			<?php
			$tags_str = array(ob_get_contents());
			ob_end_clean();
			// $i = 0;
			foreach($tags as $tag){
				// if($i === 0){
					// ++$i;
					// continue;
				// }
				ob_start();
				?>
				<a href="<?php echo get_tag_link($tag->term_id);?>" class="tag" title="<?php echo sprintf(___('Views all posts by %s tag'),esc_attr($tag->name));?>">
					<?php echo esc_html($tag->name);?>
				</a>
				<?php
				$tags_str[] = ob_get_contents();
				ob_end_clean();
			} 
			echo implode($split_str,$tags_str);
			?>
			
		</div>
		<?php
	}
	/**
	 * get_thumbnail_src
	 *
	 * @return 
	 * @version 1.0.1
	 * @author KM@INN STUDIO
	 */
	public static function get_thumbnail_src($post_id = null,$size = null){
		global $post;

		$size = $size ? $size : self::$thumbnail_size[0];
		$post_id = $post_id ? $post_id : $post->ID;
		$src = null;
		if(empty($src)){
			$src = get_img_source(get_the_post_thumbnail($post_id,$size));
		}
		if(!$src){
			$src = theme_features::get_theme_images_url('frontend/thumb-preview.jpg');
		}
		return $src;
	}
	/**
	 * get_content
	 *
	 * @return string
	 * @version 1.0.0
	 * @author KM@INN STUDIO
	 */
	private static function get_content(){
		global $post;
		$content = str_replace(']]>', ']]&raquo;', $post->post_content);				
		return $content;
	}

 	/**
	 * get_adjacent_posts
	 *
	 * @param string
	 * @return string
	 * @version 1.0.0
	 * @author KM@INN STUDIO
	 */
	public static function get_adjacent_posts($class = 'adjacent-posts'){
		global $post;
		$next_post = get_adjacent_post(true,null,false);
		$next_post = $next_post ? $next_post : get_adjacent_post(false,null,false);
		
		
		$prev_post = get_adjacent_post(true,null);
		$prev_post = $prev_post ? $prev_post : get_adjacent_post(false,null);
		
		if(!$next_post && ! $prev_post) return;
		
		ob_start();
		?>
		<nav class="grid-100 grid-parent <?php echo $class;?>">
			<ul>
				<li class="adjacent-post-prev grid-50 tablet-grid-50 mobile-grid-100">
					<?php if(!$prev_post){ ?>
						<span class="adjacent-post-not-found button"><?php echo ___('No more post found');?></span>
					<?php }else{ ?>
						<a href="<?php echo get_permalink($prev_post->ID);?>" title="<?php echo esc_attr(sprintf(___('Previous post: %s'),$prev_post->post_title));?>" class="button">
							<span class="aquo"><?php echo esc_html(___('&laquo;'));?></span>
							<?php echo esc_html($prev_post->post_title);?>
						</a>
					<?php } ?>
				</li>
				<li class="adjacent-post-next grid-50 tablet-grid-50 mobile-grid-100">
					<?php if(!$next_post){ ?>
						<span class="adjacent-post-not-found button"><?php echo ___('No more post found');?></span>
					<?php }else{ ?>
						<a href="<?php echo get_permalink($next_post->ID);?>" title="<?php echo esc_attr(sprintf(___('Next post: %s'),$next_post->post_title));?>"  class="button">
							<?php echo esc_html($next_post->post_title);?>
							<span class="aquo"><?php echo esc_html(___('&raquo;'));?></span>
						</a>
					<?php } ?>
				</li>
			</ul>
		</nav>
		<?php
		$content = ob_get_contents();
		ob_end_clean();
		return $content;

	}
   /**
     * get_crumb
     * 
     * 
     * @return string The html code
     * @version 2.0.5
     * @author KM@INN STUDIO
     * 
     */
    public static function get_crumb($args = null){
		$defaults = array(
			'header' => null,
			'footer' => null,
		);
		$r = wp_parse_args($args,$defaults);
		extract($r,EXTR_SKIP);
		
		
		$links = array();
    	if(is_home()) return null;
		
		$links['home'] = '<a href="' . home_url() . '" class="home" title="' .___('Back to Homepage'). '"><i class="fa fa-home"></i></a>';
		$split = '<span class="split"><i class="fa fa-angle-right"></i></span>';
		
    	/* category */
    	if(is_category()){
			$cat_curr = theme_features::get_current_cat_id();
			if($cat_curr > 1){
				$links_cat = get_category_parents($cat_curr,true,'%split%');
				$links_cats = explode('%split%',$links_cat);
				array_pop($links_cats);
				$links['category'] = implode($split,$links_cats);
				//$links['curr_text'] = esc_html(___('Category Browser'));
			}
    	/* tag */
    	}else if(is_tag()){
    		$tag_id = theme_features::get_current_tag_id();
			$tag_obj = get_tag($tag_id);
    		$links['tag'] = '<a href="'. esc_url(get_tag_link($tag_id)).'">' . esc_html(theme_features::get_current_tag_name()).'</a>';
    		//$links['curr_text'] = esc_html(___('Tags Browser'));
    		/* date */
    	}else if(is_date()){
    		global $wp_query;
    		$day = $wp_query->query_vars['day'];
    		$month = $wp_query->query_vars['monthnum'];
    		$year = $wp_query->query_vars['year'];
    		/* day */
    		if(is_day()){
    			$date_link = get_day_link(null,null,$day);
    		/* month */
    		}else if(is_month()){
    			$date_link = get_month_link($year,$month);
    		/* year */
    		}else if(is_year()){
    			$date_link = get_year_link($year);
    		}
    		$links['date'] = '<a href="'.$date_link.'">' . esc_html(wp_title('',false)).'</a>';
    		//$links['curr_text'] = esc_html(___('Date Browser'));
    	/* search*/
    	}else if(is_search()){
    		// $nav_link = null;
    		//$links['curr_text'] = esc_html(sprintf(___('Search Result: %s'),get_search_query()));
		/* author */
		}else if(is_author()){
			global $author;
			$user = get_user_by('id',$author);
			$links['author'] = '<a href="'.get_author_posts_url($author).'">'.esc_html($user->display_name).'</a>';
			//$links['curr_text'] = esc_html(___('Author posts'));
    	/* archive */
    	}else if(is_archive()){
    		$links['archive'] = '<a href="'.get_current_url().'">'.wp_title('',false).'</a>';
    		//$links['curr_text'] = esc_html(___('Archive Browser'));
    	/* Singular */
    	}else if(is_singular()){
			global $post;
			/* The page parent */
			if($post->post_parent){
				//$links['singluar'] = '<a href="' .get_page_link($post->post_parent). '">' .esc_html(get_the_title($post->post_parent)). '</a>';
			}
			/**
			 * post / page
			 */
    		if(theme_features::get_current_cat_id() > 1){
				$categories = get_the_category();
				foreach ($categories as $key => $row) {
							$parent_id[$key] = $row->category_parent;
				}
				array_multisort($parent_id, SORT_ASC,$categories);
				foreach($categories as $cat){
					$links['singluar'] = '<a href="' . esc_html(get_category_link($cat->cat_ID)) . '" title="' . esc_attr(sprintf(___('View all posts in %s'),$cat->name)) . '">' . esc_html($cat->name) . '</a>';
				}
    		}
    		//$links['curr_text'] = esc_html(get_the_title());
    	/* 404 */
    	}else if(is_404()){
    		// $nav_link = null;
    		$links['curr_text'] = esc_html(___('Not found'));
    	}
	
    $output = '
		<div class="crumb-container">
			' .$header. '
			<nav class="crumb">
				' . implode($split,apply_filters('crumb_home_link',$links)) . '
			</nav>
			' .$footer. '
		</div>
		';
		return $output;
    }
	/**
	 * get_post_pagination
	 * show pagination in archive or searching page
	 * 
	 * @param string The class of molude
	 * @return string
	 * @version 1.0.1
	 * @author KM@INN STUDIO
	 * 
	 */
	public static function get_post_pagination( $class = 'posts-pagination' ) {
		global $wp_query,$paged;
		if ( $wp_query->max_num_pages > 1 ){
			$big = 9999999;
			$args = array(
				'base'			=> str_replace( $big, '%#%', get_pagenum_link( $big ) ),
				'echo'			=> false, 
				'current' 		=> max( 1, get_query_var('paged') ),
				'prev_text'		=> ___('&laquo;'),
				'next_text'		=> ___('&raquo;'),
				'total'			=> $wp_query->max_num_pages,
			);
			$posts_page_links = paginate_links($args);
			
			$output = '<nav class="'.$class.'">'.$posts_page_links.'</nav>';
			return $output;
		}
	}
	//public static function bs_
	public static function get_theme_respond($args = null){
		global $post,$current_user;
		$defaults = array(
			'post_id' => $post->ID ? $post->ID : null,
			'parent_id' => 0,
		);
		$r = wp_parse_args($args,$defaults);
		extract($r);
		$current_commenter = wp_get_current_commenter();
		// $comment_author = isset($current_commenter['comment_author']) && !empty($$current_commenter['comment_author']) ? $current_commenter['comment_author'] : 
		get_currentuserinfo();
		
		ob_start();
		
		?>
		<div id="respond" class="comment-respond">
			<h3 id="reply-title" class="comment-reply-title">
				<span class="icon-bubble"></span><span class="after-icon"><?php echo esc_html(___('Leave a comment'));?></span>
				<small><a rel="nofollow" id="cancel-comment-reply-link" href="javascript:void(0);" style="display:none;"><span class="icon-cancel-circle"></span><span class="after-icon"><?php echo esc_html(___('Cancel reply'));?></span></a></small>
			</h3>
			<form action="javascript:void(0);" method="post" id="commentform" class="comment-form">
				<div class="area-user">
					<?php
					if(!is_user_logged_in()){
						if(empty($current_commenter['comment_author'])){
							?>
							<p><input type="text" name="author" id="comment-author" class="form-control mod" placeholder="<?php echo esc_attr(___('Name'));?>"/></p>
							<p><input type="email" name="email" id="comment-email" class="form-control mod" placeholder="<?php echo esc_attr(___('Email'));?>"/></p>
							
							<?php
						}else{
							?>
							<a href="<?php echo !empty($current_commenter['comment_author_url']) ? $current_commenter['comment_author_url'] : 'javascript:void(0);';?>" class="area-avatar" <?php echo !empty($current_commenter['comment_author_url']) ? 'target="_blank"' : null;?>">
								<img src="<?php echo esc_url(!empty($current_commenter['comment_author_email']) ? get_gravatar($current_commenter['comment_author_email']) : theme_features::get_theme_images_url('frontend/author-vcard.jpg'));?>" title="<?php echo esc_attr($current_commenter['comment_author']);?>" alt="<?php echo esc_attr($current_commenter['comment_author']);?>"/>
							</a>
						<?php } ?>
					<?php }else{ ?>
						<a href="<?php echo !empty($current_user->user_url) ? $current_user->user_url : 'javascript:void(0);';?>" class="area-avatar" <?php echo !empty($current_user->user_url) ? 'target="_blank"' : null;?>">
							<img src="<?php echo esc_url(!empty($current_user->user_email) ? get_gravatar($current_user->user_email) : theme_features::get_theme_images_url('frontend/author-vcard.jpg'));?>" title="<?php echo esc_attr($current_user->display_name);?>" alt="<?php echo esc_attr($current_user->display_name);?>"/>
						</a>
					<?php } ?>
				</div>
				<div class="area-comment mod">
					<textarea id="comment" name="comment" cols="45" rows="8" required placeholder="<?php echo esc_html(___('Write a omment'));?>" class="form-control"></textarea>
					
					<!-- #comment face system -->
					<?php
					$options = theme_options::get_options();
					$emoticons = theme_comment_face::get_emoticons();
					$a_content = null;
					if($emoticons){
						foreach($emoticons as $text){
							$a_content .= '<a href="javascript:void(0);" data-id="' . esc_attr($text) . '">' . esc_html($text) . '</a>';
						}
					}else{
						$a_content = '<a href="javascript:void(0);" data-id="' . esc_html(___('No data')) . '">' . esc_html(___('No data')) . '</a>';
					}
					?>
					<div id="comment-face" class="hide-no-js">
						<ul class="comment-face-btns grid-parent grid-40 tablet-grid-40 mobile-grid-30">
							<li class="btn grid-parent grid-50 tablet-grid-50 mobile-grid-50" data-faces="">
								<a title="<?php echo esc_attr(___('Pic-face'));?>" href="javascript:void(0);" class="comment-face-btn">
									<span class="icon-happy"></span><span class="after-icon hidden-xs"><?php echo esc_html(___('Pic-face'));?></span>
								</a>
								<div class="comment-face-box type-image"></div>
							</li>
							<li class="btn grid-parent grid-50 tablet-grid-50 mobile-grid-50">
								<a title="<?php echo esc_attr(___('Emoticons'));?>" href="javascript:void(0);" class="comment-face-btn">
									<span class="icon-happy2"></span><span class="after-icon hidden-xs"><?php echo esc_html(___('Emoticons'));?></span>
								</a>
								<div class="comment-face-box type-text"><?php echo $a_content;?></div>
							</li>
						</ul>
						<!-- submit -->
						<div class="grid-parent grid-60 tablet-grid-60 mobile-grid-70">
							<input class="btn btn-primary" type="submit" id="comment-submit" value="<?php echo esc_html(___('Post comment'));?>">
							<input type="hidden" name="comment_post_ID" value="<?php echo esc_html((int)$post_id);?>" id="comment_post_ID">
							<input type="hidden" name="comment_parent" id="comment_parent" value="<?php echo esc_html((int)$parent_id);?>">
						</div>
					</div>
					<!-- #comment face system -->
				</div>
			</form>
		</div>
		<?php		
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}
	/**
	 * get the comment pagenavi
	 * 
	 * 
	 * @param string $class Class name
	 * @param bool $below The position where show.
	 * @return string
	 * @version 1.0.0
	 * @author KM@INN STUDIO
	 * 
	 */
	public static function get_comment_pagination( $args ) {
		$options = theme_options::get_options();
		/** Check the comment is open */
		$page_comments = get_option('page_comments');

		/** if comment is closed, return */
		if(!$page_comments) return;
		/** 
		 * defaults args
		 */
		$defaults = array(
			'classes'			=> 'comment-pagination',
			'cpaged'			=> max(1,get_query_var('cpage')),
			'cpp' 				=> get_option('comments_per_page'),
			'thread_comments'	=> get_option('thread_comments') ? true : false,
			// 'default_comments_page' => get_option('default_comments_page'),
			'default_comments_page' => 'oldest',
			'max_pages' 		=> get_comment_pages_count(null,get_option('comments_per_page'),get_option('thread_comments')),
			
		);
		$r = wp_parse_args($args,$defaults);
		extract($r,EXTR_SKIP);
		/** 
		 * if enable ajax
		 */
		if(isset($options['comment_ajax']) && $options['comment_ajax']['on'] == 1){
			$add_fragment = '&amp;pid=' . get_the_ID();
		}else{
			$add_fragment = false;
		}
		/** If has page to show me */
		if ( $max_pages > 1 ){
			$big = 999;
			$args = array(
				'base' 			=> str_replace($big,'%#%',get_comments_pagenum_link($big)), 
				'total'			=> $max_pages,
				'current'		=> $cpaged,
				'echo'			=> false, 
				'prev_text'		=> ___('&laquo;'),
				'next_text'   	=> ___('&raquo;'),
				'add_fragment'	=> $add_fragment,
			);
			$comments_page_links = paginate_links($args);
			$output = '<div class="'. $classes .'">'.$comments_page_links.'</div>';
			return $output;
		}
	}
	
	/** 
	 * the_post_0
	 */ 
	public static function the_post_0(){
		global $post;
		?>
		<div id="post-0"class="post no-results not-found mod">
			<?php echo status_tip('info','large',___( 'Sorry, I was not able to find what you need, what about look at other content :)')); ?>
		</div><!-- #post-0 -->

	<?php
	}
	/** 
	 * get_rank_data
	 */
	public static function get_rank_data($id = null){
		$content = array(
			'all' 			=> ___('All'),
			'daily' 		=> ___('Daily'),
			'weekly' 		=> ___('Weekly'),
			'monthly' 		=> ___('Monthly'),
		);
		if($id) return isset($content[$id]) ? $content[$id] : false;
		return $content;
	}
	/** 
	 * smart_page_pagination
	 */
	public static function smart_page_pagination($args = null){
		global $post,$page,$numpages;
		$output = null;
	
		$defaults = array(
			'add_fragment' => 'post-' . $post->ID
		);
		$r = wp_parse_args($args,$defaults);
		extract($r);
		$output['numpages'] = $numpages;
		$output['page'] = $page;
		/** 
		 * prev post
		 */
		$prev_post = get_previous_post(true);
		$prev_post = empty($prev_post) ? get_previous_post() : $prev_post;
		if(!empty($prev_post)){
			$output['prev_post'] = $prev_post;
		}
		/** 
		 * next post
		 */
		$next_post = get_next_post(true);
		$next_post = empty($next_post) ? get_next_post() : $next_post;
		// var_dump($next_post);
		if(!empty($next_post)){
			$output['next_post'] = $next_post;
		}		
		/** 
		 * exists multiple page
		 */
		if($numpages != 1){
			/** 
			 * if has prev page
			 */
			if($page > 1){
				$prev_page_number = $page - 1;
				$output['prev_page']['url'] = theme_features::get_link_page_url($prev_page_number,$add_fragment);
				$output['prev_page']['number'] = $prev_page_number;
			}
			/** 
			 * if has next page
			 */
			if($page < $numpages){
				$next_page_number = $page + 1;
				$output['next_page']['url'] = theme_features::get_link_page_url($next_page_number,$add_fragment);
				$output['next_page']['number'] = $next_page_number;
			}
		}
		// var_dump(array_filter($output));
		return array_filter($output);
	}

	public static function filter_prev_pagination_link($link,$page,$numpages){
		global $post;
		// var_dump($page);
		// var_dump($numpages);
		if($page > 1) return $link;
		$prev_post = get_previous_post(true);
		// var_dump($prev_post);
		$prev_post = empty($prev_post) ? get_previous_post() : $prev_post;
		if(empty($prev_post)) return $link;
		
		ob_start();
		?>
		<a href="<?php echo get_permalink($prev_post->ID);?>" class="nowrap page-numbers page-next btn btn-success grid-40 tablet-grid-40 mobile-grid-50 numbers-first" title="<?php echo esc_attr($prev_post->post_title);?>">
			<?php echo esc_html(___('&lsaquo; Previous'));?>
			-
			<?php echo esc_html($prev_post->post_title);?>
		</a>
		<?php
		$link = ob_get_contents();
		ob_end_clean();
		return $link;
	}
	public static function filter_next_pagination_link($link,$page,$numpages){
		global $post;
		// var_dump($page);
		// var_dump($numpages);
		if($page < $numpages) return $link;
		$next_post = get_next_post(true);
		// var_dump($prev_post);
		$next_post = empty($next_post) ? get_next_post() : $next_post;
		if(empty($next_post)) return $link;
		
		ob_start();
		?>
		<a href="<?php echo get_permalink($next_post->ID);?>" class="nowrap page-numbers page-next btn btn-success grid-40 tablet-grid-40 mobile-grid-50 numbers-first" title="<?php echo esc_attr($next_post->post_title);?>">
			<?php echo esc_html($next_post->post_title);?>
			-
			<?php echo esc_html(___('Next &rsaquo;'));?>
		</a>
		<?php
		$link = ob_get_contents();
		ob_end_clean();
		return $link;
	}
	public static function the_post_pagination(){
		global $post,$page,$numpages;
		?>
		<nav class="prev-next-pagination">
			<?php
			$prev_next_pagination = theme_smart_pagination::get_post_pagination();
			
			/** 
			 * exists prev page and next page, just show them
			 */
			if(isset($prev_next_pagination['prev_page']) && isset($prev_next_pagination['next_page'])){
				?>
				<a href="<?php echo esc_url($prev_next_pagination['prev_page']['url']);?>" class="prev-page nowrap btn btn-primary grid-parent grid-50 tablet-grid-50 mobile-grid-50"><?php echo esc_html(___('&larr; Preview page'));?></a>
				<a href="<?php echo esc_url($prev_next_pagination['next_page']['url']);?>" class="next-page nowrap btn btn-primary grid-parent grid-50 tablet-grid-50 mobile-grid-50"><?php echo esc_html(___('Next page &rarr;'));?></a>
				<?php
			/** 
			 * exists prev page, show prev page and next post
			 */
			}else if(isset($prev_next_pagination['prev_page'])){
				$grid_class = isset($prev_next_pagination['prev_post']) ? ' grid-50 tablet-grid-50 mobile-grid-50 ' : ' grid-100 tablet-grid-100 mobile-grid-100';
				?>
				<a href="<?php echo esc_url($prev_next_pagination['prev_page']['url']);?>" class="prev-page nowrap btn btn-primary grid-parent <?php echo $grid_class;?>"><?php echo esc_html(___('&larr; Preview page'));?></a>
				<?php
				if(isset($prev_next_pagination['prev_post'])){
					?>
					<a href="<?php echo get_permalink($prev_next_pagination['prev_post']->ID);?>" class="next-page nowrap btn btn-success grid-parent <?php echo $grid_class;?>"><span class="tx"><?php echo ___('Next post &rarr;');?></span><span class="next-post-tx hide"><?php echo esc_html(sprintf(___('%s &rarr;'),$prev_next_pagination['prev_post']->post_title));?></span></a>
					<?php
				}
			/** 
			 * exists next page, show next page and prev post
			 */
			}else if(isset($prev_next_pagination['next_page'])){
				$grid_class = isset($prev_next_pagination['prev_post']) ? ' grid-50 tablet-grid-50 mobile-grid-50 ' : ' grid-100 tablet-grid-100 mobile-grid-100';
				
				if(isset($prev_next_pagination['next_post'])){
					?>
					<a href="<?php echo get_permalink($prev_next_pagination['next_post']->ID);?>" class="prev-post nowrap btn btn-success grid-parent <?php echo $grid_class;?>"><span class="tx"><?php echo ___('&larr; Preview post');?></span><span class="prev-post-tx hide"><?php echo esc_html(sprintf(___('&larr; %s'),$prev_next_pagination['next_post']->post_title));?></span></a>
					<?php
				}
				?>
				<a href="<?php echo esc_url($prev_next_pagination['next_page']['url']);?>" class="next-page nowrap btn btn-primary grid-parent <?php echo $grid_class;?>"><?php echo esc_html(___('Next page &rarr;'));?></a>
				<?php
			/** 
			 * only exists next post and prev post, show them
			 */
			}else{

				$grid_class = isset($prev_next_pagination['prev_post']) && isset($prev_next_pagination['next_post']) ? ' grid-50 tablet-grid-50 mobile-grid-50 ' : ' grid-100 tablet-grid-100 mobile-grid-100';
				
				if(isset($prev_next_pagination['next_post'])){
					?>
					<a href="<?php echo get_permalink($prev_next_pagination['next_post']->ID);?>" class="prev-post nowrap btn btn-success grid-parent <?php echo $grid_class;?>"><span class="tx"><?php echo ___('&larr; Preview post');?></span><span class="prev-post-tx hide"><?php echo esc_html(sprintf(___('&larr; %s'),$prev_next_pagination['next_post']->post_title));?></span></a>
				<?php
				}
				if(isset($prev_next_pagination['prev_post'])){
					?>
					<a href="<?php echo get_permalink($prev_next_pagination['prev_post']->ID);?>" class="next-page nowrap btn btn-success grid-parent <?php echo $grid_class;?>"><span class="tx"><?php echo ___('Next post &rarr;');?></span><span class="next-post-tx hide"><?php echo esc_html(sprintf(___('%s &rarr;'),$prev_next_pagination['prev_post']->post_title));?></span></a>

				<?php
				}
			}
			?>
		</nav>
		<?php
	}
	/**
	 * Theme comment
	 * 
	 * 
	 * @param object $comment
	 * @param n/a $args
	 * @param int $depth
	 * @return string
	 * @version 1.0.0
	 * 
	 * @author INN STUDIO
	 * 
	 */
	public static function theme_comment( $comment, $args, $depth ) {
		$GLOBALS['comment'] = $comment;
		$defaults = array(
			'lazy' => true,
		);
		$args = wp_parse_args($args,$defaults);
		// extract($r,EXTR_SKIP);
		// var_dump($args);
		switch ( $comment->comment_type ){
			default :
				$classes = array();
				if(!empty( $args['has_children'])) $classes[] = 'parent';
				if($comment->comment_approved == '0') $classes[] = 'moderation';
				$lazy_attr = $args['lazy'] === true ? 'data-original' : 'src';

				$author_avatar = '<img alt="' . get_comment_author() . '" ' . $lazy_attr . '="' . get_img_source(get_avatar($comment,self::$comment_avatar_size)) . '" class="comment-author-avatar avatar" witdh="' . self::$comment_avatar_size . '" height="' . self::$comment_avatar_size . '"/>';
				$author_avatar_sm = '<img alt="' . get_comment_author() . '" ' . $lazy_attr . '="' . get_img_source(get_avatar($comment,self::$comment_avatar_size)) . '" class="comment-author-avatar-sm avatar" witdh="20" height="20"/>';

				$author_avatar_html = !get_comment_author_url() ? $author_avatar : '<a rel="external nofollow" href="' . get_comment_author_url() . '" class="comment-author-vcard" target="_blank">' . $author_avatar . '</a>';
				?>
				<li <?php comment_class($classes);?> id="comment-<?php comment_ID();?>">
					<article id="comment-body-<?php comment_ID(); ?>" class="comment-body">
						<header class="comment-area-img">
							<?php echo $author_avatar_html;?>
						</header>
						<div class="comment-area-tx">
							<div class="comment-content content-reset">
								<?php comment_text(); ?>
								<?php if ($comment->comment_approved == '0'){ ?>
									<div class="comment-awaiting-moderation"><?php echo status_tip('info',___('Your comment is awaiting moderation.')); ?></div>
								<?php } ?>
							</div><!-- .comment-content -->
							<footer class="comment-meta">
								<span class="comment-meta-data comment-author-name">
									<?php echo $author_avatar_sm;?>
									<?php if(!get_comment_author_url()){ ?>
										<?php echo get_comment_author();?>
									<?php }else{ ?>
										<a href="<?php echo get_comment_author_url();?>" target="blank" rel="external nofollow"><?php echo get_comment_author();?></a>
									<?php } ?>
								</span>
								<time class="comment-meta-data comment-time" datetime="<?php echo get_comment_time('c');?>">
									<a href="<?php echo esc_url( get_comment_link( $comment->comment_ID ) ); ?>">
										<i class="fa fa-clock-o"></i> 
										<?php echo friendly_date(get_comment_time('U')); ?>
									</a>
								</time>
								<span class="comment-meta-data comment-reply reply">
									<?php
									comment_reply_link(array_merge($args,array(
										'add_below'		=> 'comment-body', 
										'depth' 		=> $depth,
										'reply_text' 	=>  '<i class="fa fa-comments-o"></i> ' . ___('Reply'),
										'login_text' 	=>  '<i class="fa fa-comments-o"></i> ' . ___('Log in to leave a comment'),
										'max_depth' 	=> $args['max_depth'],
									)));
									?>
								</span><!-- .reply -->
							</footer><!-- .comment-meta -->
						</div><!-- .comment-area-tx -->
					</article><!-- .comment-body -->
					<?php
		}
	}
	public static function the_related_posts_plus($args = null){
		global $wp_query,$post;
		$defaults = array(
			'posts_per_page' => 6,
			'orderby' => 'latest',
		);
		$query_args = array(
			'post__not_in' => array($post->ID),
		);
		$args = wp_parse_args($args,$defaults);
		$content_args = array(
			'classes' => array('col-xs-6 col-sm-4 col-md-2')
		);
		?>
		<div class="related-posts panel panel-default" role="tabpanel">
			<ul class="nav nav-tabs panel-heading" role="tablist">
				<li role="presentation" class="active"><a href="#related-same-tag" aria-controls="related-same-tag" role="tab" data-toggle="tab">
					<i class="fa fa-tag"></i> 
					<?php echo ___('Same tag');?>
				</a></li>
				<li role="presentation"><a href="#related-same-cat" aria-controls="related-same-cat" role="tab" data-toggle="tab">
					<i class="fa fa-folder-open"></i> 
					<?php echo ___('Same category');?>
				</a></li>
				<li role="presentation"><a href="#related-same-author" aria-controls="related-same-author" role="tab" data-toggle="tab">
					<i class="fa fa-user"></i> 
					<?php echo ___('Same author');?>
				</a></li>
			</ul>

			<div class="tab-content panel-body">
				<div role="tabpanel" class="tab-pane active" id="related-same-tag">
					<?php
					$same_tag_args = $args;
					$same_tag_query = $query_args;
					if(!get_the_tags()){
						$same_tag_query['category__in'] = array_map(function($term){
							return $term->term_id;
						},get_the_category());
						$same_tag_args['orderby'] = 'random';
					}else{
						$same_tag_query['tag__in'] = array_map(function($term){
							return $term->term_id;
						},get_the_tags());
					}
					$wp_query = self::get_posts_query($same_tag_args,$same_tag_query);
					if(have_posts()){
						?>
						<ul class="row post-img-lists">
							<?php
							while(have_posts()){
								the_post();
								self::archive_img_content($content_args);
							}
						?>
						</ul>
					<?php }else{ ?>
						<div class="page-tip"><?php echo status_tip('info',___('No data.'));?></div>
					<?php
					}
					wp_reset_postdata();
					?>
				</div>

				<div role="tabpanel" class="tab-pane" id="related-same-cat">
					<?php
					$same_cat_args = $args;
					$same_cat_query = $query_args;
					$same_cat_query['category__in'] = array_map(function($term){
						return $term->term_id;
					},get_the_category());
					$wp_query = self::get_posts_query($same_cat_args,$same_cat_query);
					if(have_posts()){
						?>
						<ul class="row post-img-lists">
							<?php
							while(have_posts()){
								the_post();
								self::archive_img_content($content_args);
							}
						?>
						</ul>
					<?php }else{ ?>
						<div class="page-tip"><?php echo status_tip('info',___('No data.'));?></div>
					<?php
					}
					wp_reset_postdata();
					?>
				</div>
				<div role="tabpanel" class="tab-pane" id="related-same-author">
					<?php
					$same_author_args = $args;
					$same_author_query = $query_args;
					$same_author_query['author'] = get_the_author_meta('ID');
					$wp_query = self::get_posts_query($same_author_args,$same_author_query);
					if(have_posts()){
						?>
						<ul class="row post-img-lists">
							<?php
							while(have_posts()){
								the_post();
								self::archive_img_content($content_args);
							}
						?>
						</ul>
					<?php }else{ ?>
						<div class="page-tip"><?php echo status_tip('info',___('No data.'));?></div>
					<?php
					}
					wp_reset_postdata();
					wp_reset_query();
					?>
				</div>
			</div>
		</div>
		<?php
		/**
		 * get same tags posts
		 */
		
		
	}
	/** 
	 * the_related_posts
	 */
	public static function the_related_posts($args_content = null,$args_query = null){
		global $post;
		
		$defaults_query = array(
			'posts_per_page' => 10
		);
		$args_query = wp_parse_args($args_query,$defaults_query);
		
		$defaults_content = array(
			'classes' => array('grid-20 tablet-grid-20 mobile-grid-50'),
		);
		$args_content = wp_parse_args($args_content,$defaults_content);
		
		$posts = theme_related_post::get_posts($args_query);
		if(!is_null_array($posts)){
			?>
			<ul class="related-posts-img post-img-lists">
				<?php
				foreach($posts as $post){
					setup_postdata($post);
						echo self::archive_img_content($args_content);
				}
				?>
			</ul>
			<?php
		}else{
			?>
			<div class="no-post page-tip"><?php echo status_tip('info',___('No data yet'));?></div>
			<?php
		}
		wp_reset_postdata();
	}



	/**
	 * get_page_pagenavi
	 * 
	 * 
	 * @return 
	 * @version 1.0.0
	 * @author KM@INN STUDIO
	 * 
	 */
	public static function get_page_pagenavi(){
		// var_dump( theme_features::get_pagination());
		global $page,$numpages;
		$output = null;
		if($numpages < 2) return;
		if($page < $numpages){
			$next_page = $page + 1;
			$output = '<a href="' . theme_features::get_link_page_url($next_page) . '" class="next_page">' . ___('Next page') . '</a>';
		}else{
			$prev_page = $page - 1;
			$output = '<a href="' . theme_features::get_link_page_url($prev_page) . '" class="prev_page">' . ___('Previous page') . '</a>';
		}
		$output = $output ? '<div class="singluar_page">' . $output . '</div>' : null;
		$args = array(
			'range' => 3
		);
		$output .= theme_features::get_pagination($args);
		return $output;
	}
	/**
	 * the_recommended
	 */
	public static function the_recommended(){
		$recomms = (array)theme_options::get_options(theme_recommended_post::$iden);
		if(empty($recomms)) return false;
		$cache_id = md5(serialize($recomms));
		$cache = theme_cache::get($cache_id);
		if(!empty($cache)){
			echo $cache;
			return $cache;
		}
		global $post,$wp_query;
		$wp_query = self::get_posts_query(array(
			'posts_per_page' => 8,
			'orderby' => 'recomm',
		));
		ob_start();
		if(have_posts()){
			?>
			<ul class="home-recomm row post-img-lists">
				<?php
				while(have_posts()){
					the_post();
					self::archive_img_content(array(
						'classes' => array('col-sm-4')
					));
				}
				?>
			</ul>
			<?php
		}else{
			
		}
		$cache = ob_get_contents();
		ob_end_clean();
		theme_cache::set($cache_id,null,$cache);
		wp_reset_query();
		wp_reset_postdata();
		echo $cache;
		return $cache;
	}
	public static function the_homebox($args = null){
		if(!class_exists('theme_custom_homebox')) return false;
		$opt = (array)theme_options::get_options(theme_custom_homebox::$iden);
		if(empty($opt)) return false;
		global $wp_query,$post;
		foreach($opt as $k => $v){
			?>
<div class="homebox panel panel-default mx-panel">
	
	<div class="panel-heading mx-panel-heading clearfix">
		<h3 class="panel-title mx-panel-title">
			<?php 
			if(empty($v['link'])){
				echo $v['title'];
			}else{
				?>
				<a href="<?php echo esc_url($v['link']);?>"><?php echo $v['title'];?> <small class="hidden-xs"><?php echo ___('&raquo; more');?></small></a>
				<?php
			}
			?>
		</h3>
		<div class="mx-panel-heading-extra">
			
			<?php if(!is_null_array($v['keywords'])){ ?>
				<div class="keywords hidden-xs">
					<?php foreach(theme_custom_homebox::keywords_to_html($v['keywords']) as $kw){ ?>
						<a class="" href="<?php echo esc_url($kw['url']);?>">
							<?php echo $kw['name'];?>
						</a>
					<?php } ?>
				</div>
			<?php } ?>
			
			<div class="nplink btn-group btn-group-xs">
				<a 
					title="<?php echo ___('Preview page');?>"
					href="javascript:void(0);" 
					class="btn btn-default preview disabled" 
					data-cat-id="<?php echo implode(',',$v['cats']);?>"
					data-paged="1"
				><i class="fa fa-caret-left"></i></a>
				<a 
					title="<?php echo ___('Next page');?>"
					href="javascript:void(0);" 
					class="btn btn-default next" 
					data-cat-id="<?php echo implode(',',$v['cats']);?>"
					data-paged="1"
				><i class="fa fa-caret-right"></i></a>
			</div>
			
		</div>
	</div>
	<div class="panel-body">
		<ul class="row mx-card-body post-img-lists">
			<?php
			$wp_query = self::get_posts_query(array(
				'orderby' => 'lastest',
				'category__in' => $v['cats'],
				'posts_per_page' => 12
			));
			if(have_posts()){
				while(have_posts()){
					the_post();
					self::archive_img_content(array(
						'classes' => array('col-xs-12 col-sm-3')
					));
				}
			}else{
				
			}
			wp_reset_postdata();
			wp_reset_query();
			?>
		</ul>
	</div>
</div>
	<?php
		} /** end foreach */
	}
}

/**
* Class Name: wp_bootstrap_navwalker
* GitHub URI: https://github.com/twittem/wp-bootstrap-navwalker
* Description: A custom WordPress nav walker class to implement the Bootstrap 3 navigation style in a custom theme using the WordPress built in menu manager.
* Version: 2.0.4
* Author: Edward McIntyre - @twittem
* License: GPL-2.0+
* License URI: http://www.gnu.org/licenses/gpl-2.0.txt
*/
class wp_bootstrap_navwalker extends Walker_Nav_Menu {
/**
* @see Walker::start_lvl()
* @since 3.0.0
*
* @param string $output Passed by reference. Used to append additional content.
* @param int $depth Depth of page. Used for padding.
*/
public function start_lvl( &$output, $depth = 0, $args = array() ) {
$indent = str_repeat( "\t", $depth );
$output .= "\n$indent<ul role=\"menu\" class=\" dropdown-menu\">\n";
}
/**
* @see Walker::start_el()
* @since 3.0.0
*
* @param string $output Passed by reference. Used to append additional content.
* @param object $item Menu item data object.
* @param int $depth Depth of menu item. Used for padding.
* @param int $current_page Menu item ID.
* @param object $args
*/
public function start_el( &$output, $item, $depth = 0, $args = array(), $id = 0 ) {
$indent = ( $depth ) ? str_repeat( "\t", $depth ) : '';
/**
* Dividers, Headers or Disabled
* =============================
* Determine whether the item is a Divider, Header, Disabled or regular
* menu item. To prevent errors we use the strcasecmp() function to so a
* comparison that is not case sensitive. The strcasecmp() function returns
* a 0 if the strings are equal.
*/
if ( strcasecmp( $item->attr_title, 'divider' ) == 0 && $depth === 1 ) {
$output .= $indent . '<li role="presentation" class="divider">';
} else if ( strcasecmp( $item->title, 'divider') == 0 && $depth === 1 ) {
$output .= $indent . '<li role="presentation" class="divider">';
} else if ( strcasecmp( $item->attr_title, 'dropdown-header') == 0 && $depth === 1 ) {
$output .= $indent . '<li role="presentation" class="dropdown-header">' . esc_attr( $item->title );
} else if ( strcasecmp($item->attr_title, 'disabled' ) == 0 ) {
$output .= $indent . '<li role="presentation" class="disabled"><a href="#">' . esc_attr( $item->title ) . '</a>';
} else {
$class_names = $value = '';
$classes = empty( $item->classes ) ? array() : (array) $item->classes;
$classes[] = 'menu-item-' . $item->ID;
$class_names = join( ' ', apply_filters( 'nav_menu_css_class', array_filter( $classes ), $item, $args ) );
if ( $args->has_children )
$class_names .= ' dropdown';
if ( in_array( 'current-menu-item', $classes ) )
$class_names .= ' active';
$class_names = $class_names ? ' class="' . esc_attr( $class_names ) . '"' : '';
$id = apply_filters( 'nav_menu_item_id', 'menu-item-'. $item->ID, $item, $args );
$id = $id ? ' id="' . esc_attr( $id ) . '"' : '';
$output .= $indent . '<li' . $id . $value . $class_names .'>';
$atts = array();
$atts['title'] = ! empty( $item->title ) ? $item->title	: '';
$atts['target'] = ! empty( $item->target ) ? $item->target	: '';
$atts['rel'] = ! empty( $item->xfn ) ? $item->xfn	: '';
// If item has_children add atts to a.
if ( $args->has_children && $depth === 0 ) {
$atts['href'] = '#';
$atts['data-toggle'] = 'dropdown';
$atts['class'] = 'dropdown-toggle';
$atts['aria-haspopup'] = 'true';
} else {
$atts['href'] = ! empty( $item->url ) ? $item->url : '';
}
$atts = apply_filters( 'nav_menu_link_attributes', $atts, $item, $args );
$attributes = '';
foreach ( $atts as $attr => $value ) {
if ( ! empty( $value ) ) {
$value = ( 'href' === $attr ) ? esc_url( $value ) : esc_attr( $value );
$attributes .= ' ' . $attr . '="' . $value . '"';
}
}
$item_output = $args->before;
/*
* Glyphicons
* ===========
* Since the the menu item is NOT a Divider or Header we check the see
* if there is a value in the attr_title property. If the attr_title
* property is NOT null we apply it as the class name for the glyphicon.
*/
if ( ! empty( $item->attr_title ) )
$item_output .= '<a'. $attributes .'><span class="fa fa-' . esc_attr( $item->attr_title ) . '"></span>&nbsp;';
else
$item_output .= '<a'. $attributes .'>';
$item_output .= $args->link_before . apply_filters( 'the_title', $item->title, $item->ID ) . $args->link_after;
$item_output .= ( $args->has_children && 0 === $depth ) ? ' <span class="caret"></span></a>' : '</a>';
$item_output .= $args->after;
$output .= apply_filters( 'walker_nav_menu_start_el', $item_output, $item, $depth, $args );
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
*
* @param object $element Data object
* @param array $children_elements List of elements to continue traversing.
* @param int $max_depth Max depth to traverse.
* @param int $depth Depth of current element.
* @param array $args
* @param string $output Passed by reference. Used to append additional content.
* @return null Null on failure with no changes to parameters.
*/
public function display_element( $element, &$children_elements, $max_depth, $depth, $args, &$output ) {
if ( ! $element )
return;
$id_field = $this->db_fields['id'];
// Display this element.
if ( is_object( $args[0] ) )
$args[0]->has_children = ! empty( $children_elements[ $element->$id_field ] );
parent::display_element( $element, $children_elements, $max_depth, $depth, $args, $output );
}
/**
* Menu Fallback
* =============
* If this function is assigned to the wp_nav_menu's fallback_cb variable
* and a manu has not been assigned to the theme location in the WordPress
* menu manager the function with display nothing to a non-logged in user,
* and will add a link to the WordPress menu manager if logged in as an admin.
*
* @param array $args passed from the wp_nav_menu function.
*
*/
public static function fallback( $args ) {
if ( current_user_can( 'manage_options' ) ) {
extract( $args );
$fb_output = null;
if ( $container ) {
$fb_output = '<' . $container;
if ( $container_id )
$fb_output .= ' id="' . $container_id . '"';
if ( $container_class )
$fb_output .= ' class="' . $container_class . '"';
$fb_output .= '>';
}
$fb_output .= '<ul';
if ( $menu_id )
$fb_output .= ' id="' . $menu_id . '"';
if ( $menu_class )
$fb_output .= ' class="' . $menu_class . '"';
$fb_output .= '>';
$fb_output .= '<li><a href="' . admin_url( 'nav-menus.php' ) . '">Add a menu</a></li>';
$fb_output .= '</ul>';
if ( $container )
$fb_output .= '</' . $container . '>';
echo $fb_output;
}
}
}
?>

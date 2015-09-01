<?php
remove_action( 'admin_init', '_wp_check_for_scheduled_split_terms' );

/** Theme options */
include __DIR__ . '/core/core-options.php';

/** Theme features */
include __DIR__ . '/core/core-features.php';

/** Theme functions */
include __DIR__ . '/core/core-functions.php';

/** 
 * theme_functions
 */
add_action('after_setup_theme','theme_functions::init');
class theme_functions{
	public static $iden = 'mx';
	public static $basename;
	public static $theme_edition = 1;
	public static $theme_date = '2015-02-01 00:00';
	public static $thumbnail_size = ['thumbnail',320,200,true];
	public static $medium_size = ['medium',600,600,false];
	public static $large_size = ['large',1024,1024,false];
	public static $comment_avatar_size = 60;
	public static $thumbnail_placeholder = 'frontend/thumbnail.png';
	public static $avatar_placeholder = 'frontend/avatar.jpg';
	public static $cache_expire = 3600;
	public static $colors = array(
		'61b4ca',	'e1b32a',	'ee916f',	'a89d84',
		'86b767',	'6170ca',	'c461ca',	'ca6161',
		'ca8661',	'333333',	'84a89e',	'a584a8'
	);
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
				'menu-mobile' 			=> ___('Mobile menu'),
				'menu-top-bar' 			=> ___('Top bar menu'),
			)
		);	
		/** 
		 * frontend_seajs_use
		 */
		add_action('frontend_seajs_use',__CLASS__ . '::frontend_seajs_use',1);
		/** 
		 * other
		 */
		add_action('widgets_init',__CLASS__ . '::widget_init');
		add_filter('use_default_gallery_style','__return_false');
		add_theme_support('html5',['comment-list','comment-form','search-form']);

		add_image_size(self::$thumbnail_size[0],self::$thumbnail_size[1],self::$thumbnail_size[2],self::$thumbnail_size[3]);
		
		add_image_size(self::$medium_size[0],self::$medium_size[1],self::$medium_size[2],self::$medium_size[3]);
		
		add_image_size(self::$large_size[0],self::$large_size[1],self::$large_size[2],self::$large_size[3]);

		add_theme_support('title-tag');
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

		/**
		 * filter filter_get_comment_text
		 */
		add_filter('get_comment_text' , __CLASS__ . '::filter_get_comment_text', 10, 2);
	}
	
	public static function frontend_seajs_use(){
		?>
		seajs.use('frontend',function(m){
			m.init();
		});
		<?php
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
				'before_widget'		=> isset($v['before_widget']) ? $v['before_widget'] : '<aside id="%1$s"><div class="panel panel-default widget %2$s">',
				'after_widget'		=> isset($v['after_widget']) ? $v['after_widget'] : '</div></aside>',
				'before_title'		=> isset($v['before_title']) ? $v['before_title'] : '<div class="panel-heading panel-heading-default"><h3 class="widget-title panel-title">',
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
	 */
	public static function the_order_nav($args = null){
		$current_tab = get_query_var('tab');
		$current_tab = !empty($current_tab) ? $current_tab : 'lastest';
		$typies = self::get_tab_type();
		if(is_home()){
			$current_url = theme_cache::home_url();
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
				<a href="<?= esc_url($url);?>" class="item <?= $current_class;?>">
					<span class="icon-<?= $v['icon'];?>"></span><span class="after-icon"><?= esc_html($v['text']);?></span>
				</a>
				<?php
			}
			?>
		</nav>
		<?php
	}
	
	public static function get_posts_query($args,array $query_args = []){
		global $paged;
		$defaults = array(
			'orderby' => 'views',
			'order' => 'desc',
			'posts_per_page' => get_option('posts_per_page'),
			'paged' => 1,
			'category__in' => [],
			'date' => 'all',
			
		);
		$r = array_merge($defaults,$args);
		extract($r);
		$query_args = array_merge([
			'posts_per_page' => $posts_per_page,
			'paged' => $paged,
			'ignore_sticky_posts' => 1,
			'category__in' => $category__in,
			'post_status' => 'publish',
			'post_type' => 'post',
			'has_password' => false,
		],$query_args);
		
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
					$query_args['post__in'] = (array)theme_recommended_post::get_ids();
				}else{
					$query_args['post__in'] = (array)theme_cache::get_option( 'sticky_posts' );
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
	 */
	public static function archive_img_content(array $args = []){
		global $post;
		$defaults = array(
			'classes' => [],
			'lazyload' => true,
		);
		$args = array_merge($defaults,$args);

		$args['classes'][] = 'post-list post-img-list';
		
		$excerpt = get_the_excerpt();
		
		if(!empty($excerpt))
			$excerpt = esc_html($excerpt);
			
		$thumbnail_real_src = esc_url(theme_functions::get_thumbnail_src($post->ID));

		$thumbnail_placeholder = theme_features::get_theme_images_url(theme_functions::$thumbnail_placeholder);
		?>
		<li class="<?= implode(' ',$args['classes']);?>">
			<a class="post-list-bg" href="<?= theme_cache::get_permalink($post->ID);?>" title="<?= theme_cache::get_the_title($post->ID), empty($excerpt) ? null : ' - ' . $excerpt;?>">
				<div class="thumbnail-container">
					<img class="placeholder" alt="Placeholder" src="<?= $thumbnail_placeholder;?>" width="<?= self::$thumbnail_size[1];?>" height="<?= self::$thumbnail_size[2];?>">
					<?php
					/**
					 * lazyload img
					 */
					if($args['lazyload']){
						?>
						<img class="post-list-img" src="<?= $thumbnail_placeholder;?>" data-src="<?= $thumbnail_real_src;?>" alt="<?= theme_cache::get_the_title($post->ID);?>" width="<?= self::$thumbnail_size[1];?>" height="<?= self::$thumbnail_size[2];?>"/>
					<?php }else{ ?>
						<img class="post-list-img" src="<?= $thumbnail_real_src;?>" alt="<?= theme_cache::get_the_title($post->ID);?>" width="<?= self::$thumbnail_size[1];?>" height="<?= self::$thumbnail_size[2];?>"/>
					<?php } ?>
				</div>
				<h3 class="post-list-title"><?= theme_cache::get_the_title($post->ID);?></h3>
					
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
	 */
	public static function get_meta_type($type){
		global $post;
		$output = [];
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
	public static function archive_tx_content($args = []){
		global $post;
		$defaults = array(
			'classes'			=> [],
			'meta_type'			=> 'views',
		);
		$args = array_merge($defaults,$args);
		/** 
		 * classes
		 */
		$args['classes'][] = 'post-list post-tx-list';
		$args['classes'] = implode(' ',$classes);
		
		$meta_type = self::get_meta_type($meta_type);
		
		?>
		<li class="<?= $classes;?>">
			<a href="<?= theme_cache::get_permalink($post->ID);?>" title="<?= theme_cache::get_the_title($post->ID);?>">
				<?php
				if(empty($meta_type)){
					echo theme_cache::get_the_title($post->ID);
				}else{
					?>
					<span class="post-list-meta" title="<?= $meta_type['tx'];?>">
						<span class="icon-<?= $meta_type['icon'];?>"></span><span class="after-icon"><?= $meta_type['num'];?></span>
					</span>
					<span class="tx"><?= theme_cache::get_the_title($post->ID);?></span>
				<?php } ?>
			</a>
		</li>
		<?php
		
	}
	

	public static function widget_rank_tx_content($args){
		self::archive_tx_content($args);
	}
	public static function widget_rank_img_content($args = []){
		global $post;
		
		$defaults = array(
			'classes' => '',
			'lazyload' => true,
			'excerpt' => false,
		);
		$args = array_merge($defaults,$args);

		$excerpt = get_the_excerpt();
		if(!empty($excerpt))
			$excerpt = esc_html($excerpt);

		$thumbnail_real_src = esc_url(theme_functions::get_thumbnail_src($post->ID));

		$thumbnail_placeholder = theme_features::get_theme_images_url(theme_functions::$thumbnail_placeholder);
		?>
		<li class="list-group-item <?= $args['classes'];?>">
			<a class="post-list-bg media" href="<?= theme_cache::get_permalink($post->ID);?>" title="<?= theme_cache::get_the_title($post->ID), empty($excerpt) ? null : ' - ' . $excerpt;?>">
				<div class="media-left">
					<img src="<?= $thumbnail_placeholder;?>" alt="<?= theme_cache::get_the_title($post->ID);?>" class="media-object placeholder">
					<img class="post-list-img" src="<?= $thumbnail_placeholder;?>" data-src="<?= $thumbnail_real_src;?>" alt="<?= theme_cache::get_the_title($post->ID);?>"/>
				</div>
				<div class="media-body">
					<h4 class="media-heading"><?= theme_cache::get_the_title($post->ID);?></h4>
					<?php
					/**
					 * output excerpt
					 */
					if($args['excerpt'] === true){
						echo $excerpt;
					}
					?>
					<div class="extra">
						<div class="metas row">
							
							<?php if(class_exists('theme_post_views') && theme_post_views::is_enabled()){ ?>
								<div class="view meta col-xs-6">
									<i class="fa fa-play-circle"></i>
									<?= theme_post_views::get_views();?>
								</div>
							<?php } ?>

							<div class="comments meta col-xs-6">
								<i class="fa fa-comment"></i>
								<?= (int)$post->comment_count;?>
							</div>
						</div><!-- /.metas -->
					</div>					
				</div>
			</a>
		</li>
		<?php
	}
	public static function page_content($args = []){
		global $post;
		
		$defaults = array(
			'classes'			=> [],
			'lazyload'			=> true,
			
		);
		$args = array_merge($defaults,$args);
		
		/** 
		 * classes
		 */
		$args['classes'][] = 'singluar-post panel panel-default';

		?>
		<article id="post-<?php $post->ID;?>" <?php post_class($args['classes']);?>>
			<div class="panel-heading">
				<?php if(theme_cache::get_the_title($post->ID) !== ''){ ?>
					<h3 class="entry-title panel-title"><?= theme_cache::get_the_title($post->ID);?></h3>
				<?php } ?>

			</div>

			<div class="panel-body">
				
				<!-- post-content -->
				<div class="post-content content-reset">
					<?php the_content();?>
				</div>

				<?php
				/**
				 * Hook fires after_singular_post_content
				 */
				do_action('after_singular_post_content');
				?>
				<?= theme_features::get_prev_next_pagination(array(
					'numbers_class' => array('btn btn-primary')
				));?>

			</div>
			
			
			<!-- post-footer -->
			<footer class="post-footer post-metas panel-footer clearfix">
		
				<?php
				/** 
				 * post-share
				 */
				if(class_exists('theme_post_share') && theme_post_share::is_enabled()){
					?>
					<div class="post-meta post-share">
						<?= theme_post_share::display();?>
					</div>
					<?php
				} /** end post-share */
				?>
				
			</footer>
		</article>
		<?php
	}
	/** 
	 * singular_content
	 */
	public static function singular_content(array $args = []){
		global $post;

		$defaults = array(
			'classes'			=> [],
			'lazyload'			=> true,
			
		);
		$args = array_merge($defaults,$args);
		
		/** 
		 * classes
		 */
		$args['classes'][] = 'singluar-post panel panel-default';

		$author_display_name = theme_cache::get_the_author_meta('display_name',$post->post_author);

		$author_url = theme_cache::get_author_posts_url($post->post_author);
		?>
		<article id="post-<?= $post->ID;?>" <?php post_class($args['classes']);?>>
			<div class="panel-heading">
				<div class="media">
					<div class="media-left">
						<a class="post-meta post-author" href="<?= $author_url;?>" title="<?= sprintf(___('Views all post by %s'),$author_display_name);?>">
							<img class="avatar" src="<?= get_avatar_url($post->post_author);?>" alt="<?= ___('Author avatar');?>" width="50" height="50">
						</a>
					</div>
					<div class="media-body">
						<?php if(theme_cache::get_the_title($post->ID) !== ''){ ?>
							<h3 class="entry-title panel-title"><?= theme_cache::get_the_title($post->ID);?></h3>
						<?php } ?>
						<header class="post-header post-metas clearfix">
							
							<!-- category -->
							<?php
							$cats = get_the_category_list('<i class="split"> / </i> ');
							if(!empty($cats)){
								?>
								<span class="post-meta post-category" title="<?= ___('Category');?>">
									<i class="fa fa-folder-open"></i>
									<?= $cats;?>
								</span>
							<?php } ?>
							
							<!-- time -->
							<time class="post-meta post-time" datetime="<?= get_the_time('Y-m-d H:i:s');?>" title="<?= get_the_time(___('M j, Y'));?>">
								<i class="fa fa-clock-o"></i>
								<?= friendly_date(get_the_time('U'));?>
							</time>
							<!-- author link -->
							<a class="post-meta post-author" href="<?= $author_url;?>" title="<?= sprintf(___('Views all post by %s'),$author_display_name);?>">
								<i class="fa fa-user"></i> 
								<?= $author_display_name;?>
							</a>
							
							<!-- views -->
							<?php if(class_exists('theme_post_views') && theme_post_views::is_enabled()){ ?>
								<span class="post-meta post-views" title="<?= ___('Views');?>">
									<i class="fa fa-play-circle"></i>
									<span class="number" id="post-views-number-<?= $post->ID;?>">-</span>
								</span>
							<?php } ?>
							<?php
							/** 
							 * comment
							 */
							$comment_count = (int)get_comments_number() . '';
							?>
							<a href="#comments" class="post-meta quick-comment comment-count" data-post-id="<?= $post->ID;?>">
								<i class="fa fa-comment"></i>
								<span class="comment-count-number"><?= $comment_count;?></span></span>
							</a>
						</header>
					</div><!-- /.media-body -->
				</div><!-- /.media -->
			</div><!-- /.panel-heading -->

			<div class="panel-body">

				<!-- post-excerpt -->
				<?php 
				$excerpt = $post->post_excerpt;
				if($excerpt !== ''){ 
					?>
					<blockquote class="post-excerpt well">
						<span class="qoe"><?= ___('Excerpt:');?></span>
						<?= $excerpt;?>
					</blockquote>
				<?php } ?>
				<!-- post-content -->
				<div class="post-content content-reset">
					<?php the_content();?>
				</div>

				<!-- theme_custom_post_source -->
				<?php if(class_exists('theme_custom_post_source') && theme_custom_post_source::is_enabled()){
					theme_custom_post_source::display_frontend($post->ID);
				}
				?>
				<?php
				/**
				 * Hook fires after_singular_post_content
				 */
				do_action('after_singular_post_content');
				?>
				<?php self::the_page_pagination();?>
				
				<div class="row">
					<div class="col-xs-12 col-lg-5">
						<?php
						/**
						 * post point
						 */
						if(class_exists('custom_post_point') && class_exists('theme_custom_point')){
							?>
							<div class="post-point">
								<?php custom_post_point::post_btn($post->ID);?>
								
							</div>
							<?php
						}
						?>						
					</div>
					<div class="col-xs-12 col-lg-7">
						<!-- theme_custom_storage -->
						<?php if(class_exists('theme_custom_storage') && theme_custom_storage::is_enabled()){
							theme_custom_storage::display_frontend($post->ID);
						}
						?>
						
					</div>
				</div>


			
			</div><!-- /.row -->
			
			
			<!-- post-footer -->
			<footer class="post-footer post-metas panel-footer clearfix">
				
				<?php
				/** 
				 * tags
				 */
				$tags = get_the_tags();
				if(!empty($tags)){
					?>
					<div class="post-tags post-meta">
						<?php
						the_tags('<i class="fa fa-tag"></i> ');
						?>
					</div>
					<?php
				}
				?>
					
				<?php
				/** 
				 * comment
				 */
				$comment_tx = $comment_count <= 1 ? ___('comment') : ___('comments');
				?>
				<a href="#comments" class="post-meta quick-comment comment-count" data-post-id="<?= $post->ID;?>">
					<i class="fa fa-comment"></i>
					<span class="comment-count-number"><?= $comment_count;?></span> <span class="hidden-xs"><?= $comment_tx;?></span>
				</a>

				<?php
				/** 
				 * post-share
				 */
				if(class_exists('theme_post_share') && theme_post_share::is_enabled()){
					?>
					<div class="post-meta post-share">
						<?= theme_post_share::display();?>
					</div>
					<?php
				} /** end post-share */
				?>
				
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
			<a href="<?= get_tag_link($first_tag->term_id);?>" class="tag" title="<?= sprintf(___('Views all posts by %s tag'),esc_attr($first_tag->name));?>">
				<span class="icon-tags"></span><span class="after-icon"><?= esc_html($first_tag->name);?></span>
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
				<a href="<?= get_tag_link($tag->term_id);?>" class="tag" title="<?= sprintf(___('Views all posts by %s tag'),esc_attr($tag->name));?>">
					<?= esc_html($tag->name);?>
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
	 * @version 1.0.2
	 */
	public static function get_thumbnail_src($post_id = null,$size = 'thumbnail',$placeholder = null){
		global $post;
		
		if(!$placeholder)
			$placeholder = self::$thumbnail_placeholder;
			
		if(!$size)
			$size = self::$thumbnail_size[0];

		if(!$post_id)
			$post_id = $post->ID;

		$src = null;
		
		if(has_post_thumbnail($post_id)){
			$src = wp_get_attachment_image_src(get_post_thumbnail_id($post_id),$size)[0];
		}
		
		if(!$src){
			$src = theme_features::get_theme_images_url($placeholder);
		}
		return esc_url($src);
	}
	/**
	 * get_content
	 *
	 * @return string
	 * @version 1.0.0
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
		<nav class="grid-100 grid-parent <?= $class;?>">
			<ul>
				<li class="adjacent-post-prev grid-50 tablet-grid-50 mobile-grid-100">
					<?php if(!$prev_post){ ?>
						<span class="adjacent-post-not-found button"><?= ___('No more post found');?></span>
					<?php }else{ ?>
						<a href="<?= theme_cache::get_permalink($prev_post->ID);?>" title="<?= sprintf(___('Previous post: %s'),theme_cache::get_the_title($prev_post->ID));?>" class="button">
							<span class="aquo"><?= ___('&laquo;');?></span>
							<?= theme_cache::get_the_title($prev_post->ID);?>
						</a>
					<?php } ?>
				</li>
				<li class="adjacent-post-next grid-50 tablet-grid-50 mobile-grid-100">
					<?php if(!$next_post){ ?>
						<span class="adjacent-post-not-found button"><?= ___('No more post found');?></span>
					<?php }else{ ?>
						<a href="<?= theme_cache::get_permalink($next_post->ID);?>" title="<?= sprintf(___('Next post: %s'),theme_cache::get_the_title($next_post->ID));?>"  class="button">
							<?= theme_cache::get_the_title($next_post->ID);?>
							<span class="aquo"><?= ___('&raquo;');?></span>
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
	 * @version 2.0.7
	 * 
	 */
	public static function get_crumb(array $args = []){
		$defaults = array(
			'header' => null,
			'footer' => null,
		);
		$args = array_merge($defaults,$args);
		
		$links = [];
		
		if(theme_cache::is_home())
			return null;
		
		$links['home'] = '<a href="' . theme_cache::home_url() . '" class="home" title="' . ___('Back to Homepage') . '">
			<i class="fa fa-home fa-fw"></i>
			<span class="hide">' . ___('Back to Homepage') . '</span>
		</a>';
		
		$split = '<span class="split"><i class="fa fa-angle-right"></i></span>';
		
		/* category */
		if(theme_cache::is_category()){
			$cat_curr = theme_features::get_current_cat_id();
			if($cat_curr > 1){
				$links_cat = get_category_parents($cat_curr,true,'%split%');
				$links_cats = explode('%split%',$links_cat);
				array_pop($links_cats);
				$links['category'] = implode($split,$links_cats);
				$links['curr_text'] = ___('Category Browser');
			}
		/* tag */
		}else if(theme_cache::is_tag()){
			$tag_id = theme_features::get_current_tag_id();
			$tag_obj = get_tag($tag_id);
			$links['tag'] = '<a href="'. esc_url(get_tag_link($tag_id)).'">' . esc_html(theme_features::get_current_tag_name()).'</a>';
			$links['curr_text'] = ___('Tags Browser');
			/* date */
		}else if(theme_cache::is_date()){
			global $wp_query;
			$day = $wp_query->query_vars['day'];
			$month = $wp_query->query_vars['monthnum'];
			$year = $wp_query->query_vars['year'];
			/* day */
			if(theme_cache::is_day()){
				$date_link = get_day_link(null,null,$day);
			/* month */
			}else if(theme_cache::is_month()){
				$date_link = get_month_link($year,$month);
			/* year */
			}else if(theme_cache::is_year()){
				$date_link = get_year_link($year);
			}
			$links['date'] = '<a href="'.$date_link.'">' . theme_cache::wp_title('',false).'</a>';
			$links['curr_text'] = ___('Date Browser');
		/* search*/
		}else if(theme_cache::is_search()){
			// $nav_link = null;
			$links['curr_text'] = sprintf(___('Search Result: %s'),esc_html(get_search_query()));
		/* author */
		}else if(theme_cache::is_author()){
			global $author;
			$user = get_user_by('id',$author);
			$links['author'] = '<a href="'.theme_cache::get_author_posts_url($author).'">' . theme_cache::get_the_author_meta('display_name',$user->ID) . '</a>';
			$links['curr_text'] = ___('Author posts');
		/* archive */
		}else if(theme_cache::is_archive()){
			$links['archive'] = '<a href="'.get_current_url().'">' . theme_cache::wp_title('',false) . '</a>';
			$links['curr_text'] = ___('Archive Browser');
		/* Singular */
		}else if(theme_cache::is_singular()){
			global $post;
			/* The page parent */
			if($post->post_parent){
				$links['singluar'] = '<a href="' . theme_cache::get_permalink($post->post_parent) . '">' . theme_cache::get_the_title($post->post_parent) . '</a>';
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
					$cat_name = esc_html($cat->name);
					$links['singluar'] = '<a href="' . esc_url(get_category_link($cat->cat_ID)) . '" title="' . sprintf(___('View all posts in %s'),$cat_name) . '">' . $cat_name . '</a>';
				}
			}
			//$links['curr_text'] = esc_html(theme_cache::get_the_title($post->ID));
		/* 404 */
		}else if(theme_cache::is_404()){
			// $nav_link = null;
			$links['curr_text'] = ___('Not found');
		}
	
	return '<div class="crumb-container">
		' . $args['header'] . '
		<nav class="crumb">
			' . implode($split,apply_filters('crumb_links',$links)) . '
		</nav>
		' . $args['footer'] . '
	</div>';
	}
	/**
	 * get_post_pagination
	 * show pagination in archive or searching page
	 * 
	 * @param string The class of molude
	 * @return string
	 * @version 1.0.1
	 * 
	 */
	public static function get_post_pagination( $class = 'posts-pagination') {
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
	public static function pagination( array $args = [] ) {
		
		$defaults = array(
			'custom_query'		=> false,
			'previous_string' 	=> '<i class="fa fa-arrow-left"></i>',
			'next_string'	 	=> '<i class="fa fa-arrow-right"></i>',
			'before_output'   	=> '<div class="posts-nav btn-group btn-group-justified" role="group" aria-label="' . ___('Posts pagination navigation') . '">',
			'after_output'		=> '</div>'
		);
		$args = array_merge($defaults,$args);

		$rand_id = rand(1000,9999);
		
		if ( !$args['custom_query'] )
			$args['custom_query'] = @$GLOBALS['wp_query'];
			
		$count = (int) $args['custom_query']->max_num_pages;
		$page  = intval( get_query_var( 'paged' ) );
	
		if ( $count <= 1 )
			return false;
		
		if ( !$page )
			$page = 1;
	   
		/**
		 * output before_output;
		 */
		echo $args['before_output'];
		
		/**
		 * prev page
		 */
		if ( $page > 1 ){
			$previous = intval($page) - 1;
			$previous_url = get_pagenum_link($previous);
			
		   echo '<a class="btn btn-success prev" href="' . esc_url($previous_url) . '" title="' . ___( 'Previous page') . '">' . $args['previous_string'] . '</a>';
		}
		/**
		 * middle
		 */
		if ( $count > 1 ) {
			?>
			<div class="btn-group" role="group">
				<label for="pagination-<?= $rand_id;?>" class="btn btn-default">
					<select id="pagination-<?= $rand_id;?>" class="form-control">
						<?php
						/**
						 * Previous 5 page
						 */
						for( $i = $page - 3; $i < $page; $i++){
							if($i < 1 )
								continue;
							?>
							<option value="<?= esc_url(get_pagenum_link($i));?>">
								<?= sprintf(___('Page %d'),$i);?>
							</option>
							<?php
						}
						?>
						<option selected value="<?= esc_url( get_pagenum_link($page) );?>">
							<?= sprintf(___('Page %d'),$page);?>
						</option>
						<?php
						for( $i = $page + 1; $i < $page + 4; $i++ ) {
							if($i > $count)
								break;
							?>
							<option value="<?= esc_url(get_pagenum_link($i));?>">
								<?= sprintf(___('Page %d'),$i);?>
							</option>
							<?php
						}
						?>
					</select>
				</label>
			</div>
			<?php
		}
		
		/**
		 * next page
		 */
		if ($page < $count ){
			$next = intval($page) + 1;
	   		$next_url = get_pagenum_link($next);
			echo '<a class="btn btn-success next" href="' . esc_url($next_url) . '" title="' . __( 'Next page') . '">' . $args['next_string'] . '</a>';
		}

		/**
		 * output
		 */
		echo $args['after_output'];

	}
	/**
	 * get the comment pagenavi
	 * 
	 * 
	 * @param string $class Class name
	 * @param bool $below The position where show.
	 * @return string
	 * @version 1.0.0
	 * 
	 */
	public static function get_comment_pagination(array $args = []) {
		global $post;
		/**
		 * post comment status
		 */
		static $page_comments = null,
			$cpp = null,
			$thread_comments = null,
			$max_pages = null;
			
		if($page_comments === null)
			$page_comments = theme_cache::get_option('page_comments');
		/** if comment is closed, return */
		if(!$page_comments) 
			return false;

		/**
		 * comments per page
		 */
		if(!$cpp === null)
			$cpp = theme_cache::get_option('comments_per_page');

		/**
		 * thread_comments
		 */
		if($thread_comments === null)
			$thread_comments = get_option('thread_comments');

		if($max_pages === null)
			$max_pages = get_comment_pages_count(null,get_option('comments_per_page'),theme_cache::get_option('thread_comments'));
			
		/** 
		 * defaults args
		 */
		$defaults = [
			'classes'			=> 'comment-pagination',
			'cpaged'			=> max(1,get_query_var('cpage')),
			'cpp' 				=> $cpp,
			'thread_comments'	=> $thread_comments ? true : false,
			// 'default_comments_page' => get_option('default_comments_page'),
			'default_comments_page' => 'oldest',
			'max_pages' 		=> $max_pages,
			
		];
		$r = array_merge($defaults,$args);
		extract($r,EXTR_SKIP);
				
		/** If has page to show me */
		if ( $max_pages > 1 ){
			$big = 999;
			$args = array(
				'base' 			=> str_replace($big,'%#%',get_comments_pagenum_link($big)), 
				'total'			=> $max_pages,
				'current'		=> $cpaged,
				'echo'			=> false, 
				'prev_text'		=> '<i class="fa fa-angle-left"></i>',
				'next_text'   	=> '<i class="fa fa-angle-right"></i>',
			);
			$comments_page_links = paginate_links($args);
			/**
			 * add data-* attribute
			 */
			$comments_page_links = str_replace(
				' href=',
				' data-post-id="' . $post->ID . '" data-cpage="' . $cpaged . '" href=',
				$comments_page_links
			);
			
			return '<div class="'. $classes .'">'.$comments_page_links.'</div>';
		}
	}
	
	/** 
	 * the_post_0
	 */ 
	public static function the_post_0(){
		global $post;
		?>
		<div id="post-0"class="post no-results not-found mod">
			<?= status_tip('info','large',___( 'Sorry, I was not able to find what you need, what about look at other content :)')); ?>
		</div><!-- #post-0 -->

	<?php
	}

	/** 
	 * smart_page_pagination
	 */
	public static function smart_page_pagination($args = []){
		static $cache = null;
		if($cache !== null)
			return $cache;
			
		global $post,$page,$numpages;

		//$cache = wp_cache_
		$output = [];
	
		$defaults = array(
			'add_fragment' => 'post-' . $post->ID,
			'same_category' => false,
		);
		$args = array_merge($defaults,$args);
		
		$output['numpages'] = $numpages;
		$output['page'] = $page;
		/** 
		 * prev post
		 */
		$prev_post = get_previous_post(true);
		
		if(empty($prev_post) && $args['same_category'] === false)
			$prev_post = get_previous_post();

		if(!empty($prev_post)){
			$output['prev_post'] = $prev_post;
		}
		/** 
		 * next post
		 */
		$next_post = get_next_post(true);

		if(empty($next_post) && $args['same_category'] === false)
			$next_post = get_next_post();
			
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
				$output['prev_page']['url'] = theme_features::get_link_page_url($prev_page_number,$args['add_fragment']);
				$output['prev_page']['number'] = $prev_page_number;
			}
			/** 
			 * if has next page
			 */
			if($page < $numpages){
				$next_page_number = $page + 1;
				$output['next_page']['url'] = theme_features::get_link_page_url($next_page_number,$args['add_fragment']);
				$output['next_page']['number'] = $next_page_number;
			}
		}
		$cache = array_filter($output);
		return $cache;
	}

	
	public static function the_page_pagination(){
		global $post,$page,$numpages;
		$cache_id = $post->ID . $page . $numpages;
		$cache_group = 'page-pagi';

		$cache = theme_cache::get($cache_id,$cache_group);
		if(!empty($cache)){
			echo $cache;
			return;
		}
		$page_pagination = self::smart_page_pagination([
			'same_category' => true,
		]);
		
		if(!isset($page_pagination['numpages']) || $page_pagination['numpages'] <= 1)
			return false;
			
		ob_start();
		?>
		<nav class="page-pagination">
			<?php
			$page_attr_str = $page_pagination['page'] . '/' . $page_pagination['numpages'];
			$page_str = '<span class="current-page">' . $page_pagination['page'] . '</span>' . '/' . $page_pagination['numpages'];
			if(isset($page_pagination['prev_page'])){
				?>
				<a 
					href="<?= $page_pagination['prev_page']['url'];?>" 
					class="prev" 
					title="<?= ___('Previous page');?> <?= $page_attr_str;?>" 
					data-number="<?= $page - 1;?>" 
				><i class="fa fa-chevron-left"></i><span class="tx"><?= ___('Previous page');?> <?= $page_str;?></span></a>
				<?php
			}else{
				?>
				<a 
					href="javascript:;" 
					class="prev" 
					title="<?= ___('Previous page');?> <?= $page_attr_str;?>" 
					data-number="1" 
				><i class="fa fa-chevron-left"></i><span class="tx"><?= ___('Previous page');?> <?= $page_str;?></span></a>
				<?php
			}
			if(isset($page_pagination['next_page'])){
				//$page_attr_str = $page_pagination['page'] . '/' . $page_pagination['numpages'];
				//$page_str = '<span class="current-page">' . $page_pagination['page'] . '</span>' . '/' . $page_pagination['numpages'];
				?>
				<a 
					href="<?= $page_pagination['next_page']['url'];?>" 
					class="next" 
					title="<?= ___('Next page');?> <?= $page_attr_str;?>" 
				><span class="tx"><?= $page_str;?> <?= ___('Next page');?></span><i class="fa fa-chevron-right"></i></a>
				<?php
			}else{
				?>
				<a 
					href="javascript:;" 
					class="next" 
					title="<?= ___('Next page');?> <?= $page_attr_str;?>" 
				><span class="tx"><?= $page_str;?> <?= ___('Next page');?></span><i class="fa fa-chevron-right"></i></a>
				<?php
			}
		?>
		</nav>
		<?php
		$cache = html_minify(ob_get_contents());
		ob_end_clean();

		theme_cache::set($cache_id,$cache,$cache_group,3600);
		echo $cache;	
	}
	public static function the_post_pagination(){
		global $post,$page;
		$cache_id = $post->ID . $page;
		$cache_group = 'post-pagi';

		$cache = theme_cache::get($cache_id,$cache_group);
		if(!empty($cache)){
			echo $cache;
			return;
		}
			
		$prev_next_pagination = self::smart_page_pagination([
			'same_category' => true,
		]);
		
		$has_prev = isset($prev_next_pagination['next_post']) ? 'has-prev' : 'no-prev';

		$has_next = isset($prev_next_pagination['prev_post']) ? 'has-next' : 'no-next';
		
		$prev_url = null;
		$next_url = null;
		
		ob_start();
		?>
		<nav class="prev-next-pagination <?= $has_prev;?> <?= $has_next;?>">
			<?php
			/**
			 * prev
			 */
			if(isset($prev_next_pagination['next_post'])){
				$prev_url = theme_cache::get_permalink($prev_next_pagination['next_post']->ID);
				$prev_title = theme_cache::get_the_title($prev_next_pagination['next_post']->ID);
				?>
				<a href="<?= $prev_url;?>#post-<?= $prev_next_pagination['next_post']->ID;?>" class="left next-post" title="<?= $prev_title;?>">
					<div class="post-thumbnail-area">
						<img class="post-thumbnail-placeholder" src="<?= theme_features::get_theme_images_url(self::$thumbnail_placeholder);?>" alt="<?= ___('Placeholder');?>" width="<?= self::$thumbnail_size[1];?>" height="<?= self::$thumbnail_size[2];?>">
						<img class="post-thumbnail" src="<?= theme_features::get_theme_images_url(self::$thumbnail_placeholder);?>" data-src="<?= self::get_thumbnail_src($prev_next_pagination['next_post']->ID);?>" alt="<?= $prev_title ;?>" width="<?= self::$thumbnail_size[1];?>" height="<?= self::$thumbnail_size[2];?>">
					</div>
					<span class="tx"><i class="fa fa-arrow-circle-left"></i> <?= sprintf(___('Previous post: %s'),$prev_title);?></span>
				</a>
				<?php
			}
			/**
			 * next
			 */
			if(isset($prev_next_pagination['prev_post'])){
				$next_url = theme_cache::get_permalink($prev_next_pagination['prev_post']->ID);
				$next_title = theme_cache::get_the_title($prev_next_pagination['prev_post']->ID);
				?>
				<a href="<?= $next_url;?>#post-<?= $prev_next_pagination['prev_post']->ID;?>" class="right prev-post" title="<?= $next_title;?>">
					<div class="post-thumbnail-area">
						<img class="post-thumbnail-placeholder" src="<?= theme_features::get_theme_images_url(self::$thumbnail_placeholder);?>" alt="<?= ___('Placeholder');?>" width="<?= self::$thumbnail_size[1];?>" height="<?= self::$thumbnail_size[2];?>">
						<img class="post-thumbnail" src="<?= theme_features::get_theme_images_url(self::$thumbnail_placeholder);?>" data-src="<?= self::get_thumbnail_src($prev_next_pagination['prev_post']->ID);?>" alt="<?= $next_title ;?>" width="<?= self::$thumbnail_size[1];?>" height="<?= self::$thumbnail_size[2];?>">
					</div>
					<span class="tx"><i class="fa fa-arrow-circle-right"></i> <?= sprintf(___('Next post: %s'),$next_title);?></span>
				</a>
				<?php
			}
			?>
		</nav>
		<?php
		$cache = html_minify(ob_get_contents());
		ob_end_clean();

		theme_cache::set($cache_id,$cache,$cache_group,3600);
		echo $cache;
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
		global $post;
		
		$GLOBALS['comment'] = $comment;

		switch ( $comment->comment_type ){
			default :
				$classes = ['media'];
				
				if(!empty( $args['has_children'])) 
					$classes[] = 'parent';
					
				if($comment->comment_approved == '0') 
					$classes[] = 'moderation';

				/**
				 * post author checker
				 */
				if($comment->user_id == $post->post_author){
					$is_post_author = true;
					$classes[] = 'is-post-author';
				}else{
					$is_post_author = false;
				}

				/**
				 * check is my comment
				 */
				if($comment->user_id != 0){
					if(theme_cache::get_current_user_id() == $comment->user_id)
						$classes[] = 'is-me';
				}

				/**
				 * author url
				 */
				$author_url = get_comment_author_url();
				if(!empty($author_url) && stripos($author_url,theme_cache::home_url()) === false){
					$author_nofollow = ' rel="external nofollow" ';
				}else{
					$author_nofollow = null;
				}
				?>
<li <?php comment_class($classes);?> id="comment-<?= $comment->comment_ID;?>">
	<div id="comment-body-<?= $comment->comment_ID; ?>" class="comment-body">
	
		<?php if($comment->comment_parent == 0){ ?>
			<div class="media-left">
				<?php if($author_url){ ?>
					<a href="<?= esc_url($author_url);?>" class="avatar-link" target="_blank" <?= $author_nofollow;?> >
						<?= get_avatar($comment,50);?>
					</a>
				<?php }else{
					echo get_avatar($comment,50);
				} ?>
			</div><!-- /.media-left -->
		<?php } ?>
		
		<div class="media-body">

			<div class="comment-content">
				<?php comment_text();?>
				<?php if ($comment->comment_approved == '0'){ ?>
					<div class="comment-awaiting-moderation"><?= status_tip('info',___('Your comment is awaiting moderation.')); ?></div>
				<?php } ?>
			</div>

			<h4 class="media-heading">
				<span class="comment-meta-data author">
					<?php
					if($comment->comment_parent != 0){
						echo get_avatar($comment,50);
						echo '&nbsp;';
					}
					comment_author_link();
					
					?>
				</span>
				<time class="comment-meta-data time" datetime="<?= get_comment_time('c');?>">
					<a href="<?= esc_url( get_comment_link( $comment->comment_ID ) ); ?>"><?= friendly_date(get_comment_time('U')); ?></a>
				</time>
				<?php
				if(!theme_cache::is_user_logged_in()){
					/**
					 * if needs register to comment
					 */
					if(theme_cache::get_option('comment_registration')){
						static $reply_link;
						if(!$reply_link)
							$reply_link = '<a rel="nofollow" class="comment-reply-login quick-login-btn" href="' . wp_login_url(theme_cache::get_permalink($comment->comment_post_ID)) . '">' . ___('Reply') . '</a>';
					}else{
						$reply_link = get_comment_reply_link(
							[
								'add_below'		=> 'comment-body', 
								'depth' 		=> $depth,
								'max_depth' 	=> $args['max_depth'],
							],
							$comment,
							$post->ID
						);
					}
				}else{
					$reply_link = get_comment_reply_link(
						[
							'add_below'		=> 'comment-body', 
							'depth' 		=> $depth,
							'max_depth' 	=> $args['max_depth'],
						],
						$comment,
						$post->ID
					);
				}

				$reply_link = preg_replace('/(href=)[^\s]+/','$1"javascript:;"',$reply_link);
				if(!empty($reply_link)){
					?>
					<span class="comment-meta-data comment-reply reply">
						<?= $reply_link;?>
					</span><!-- .reply -->
				<?php } ?>
			</h4>
			
		</div><!-- /.media-body -->
	</div><!-- /.comment-body -->
		<?php
		}
	}
	public static function filter_get_comment_text($comment_content,$comment){
		/**
		 * has parent
		 */
		if($comment->comment_parent != 0){
			$parent_comment = get_comment($comment->comment_parent);
			
			$parent_author = get_comment_author($parent_comment->comment_ID);
			
			$comment_content = '<a href="' . esc_url(theme_cache::get_permalink($parent_comment->comment_post_ID)) . '#comment-' . $parent_comment->comment_ID . '" class="at" rel="nofollow">@' . $parent_author . '</a> ' . $comment_content;
		}
		return $comment_content;
	}
	public static function the_related_posts_plus(array $args = []){
		global $post;

		/**
		 * cache
		 */
		$cache_group_id = 'related_posts';
		$cache = theme_dev_mode::is_enabled() ? false : wp_cache_get($post->ID,$cache_group_id);
		if($cache){
			echo $cache;
			return $cache;
		}
		
		$defaults = array(
			'posts_per_page' => 4,
			'orderby' => 'latest',
		);
		$query_args = array(
			'post__not_in' => array($post->ID),
		);
		$args = array_merge($defaults,$args);
		$content_args = array(
			'classes' => array('col-xs-6 col-sm-4 col-md-3')
		);
		
		ob_start();
		?>
		
		<div class="related-posts mod">
			<div class="mod-heading">
				<h3 class="mod-title">
					<i class="fa fa-heart-o"></i> <?= ___('Maybe you will like them');?>
				</h3>
			</div>
			<div class="mod-body">
				<?php
				$same_tag_args = $args;
				$same_tag_query = $query_args;
				/**
				 * if post not tags, get related post from same category
				 */
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
				$query = self::get_posts_query($same_tag_args,$same_tag_query);
				if($query->have_posts()){
					?>
					<ul class="row post-img-lists">
						<?php
						foreach($query->posts as $post){
							setup_postdata($post);
							self::archive_img_content($content_args);
						}
						wp_reset_postdata();
					?>
					</ul>
				<?php }else{ ?>
					<div class="page-tip"><?= status_tip('info',___('No data.'));?></div>
				<?php
				}
				//wp_reset_query();
				?>
			</div>

		</div>
		<?php
		$cache = ob_get_contents();
		ob_end_clean();
		wp_cache_set($post->ID,$cache,$cache_group_id,3600);
		echo $cache;
		return $cache;
	}
	/** 
	 * the_related_posts
	 */
	public static function the_related_posts(array $args_content = [],array  $args_query = []){
		global $post;
		
		$defaults_query = [
			'posts_per_page' => 10
		];
		$args_query = array_merge($defaults_query,$args_query);
		
		$defaults_content = [
			'classes' => [],
		];
		$args_content = array_merge($defaults_content,$args_content);
		
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
			wp_reset_postdata();
		}else{
			?>
			<div class="no-post page-tip"><?= status_tip('info',___('No data yet'));?></div>
			<?php
		}
	}



	/**
	 * get_page_pagenavi
	 * 
	 * 
	 * @return 
	 * @version 1.0.0
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
		$recomms = theme_recommended_post::get_ids();
		
		if(empty($recomms)){
			?>
			<div class="page-tip"><?= status_tip('info',___('Please set some recommended posts to display.'));?></div>
			<?php
			return false;
		}
		$cache = theme_recommended_post::get_cache();
		
		if(!empty($cache)){
			echo $cache;
			unset($cache);
			return;
		}
		global $post;
		$query = self::get_posts_query(array(
			'posts_per_page' => 8,
			'orderby' => 'recomm',
		));
		ob_start();
		if(have_posts()){
			?>
			<div class="mod home-recomm">
				<div class="mod-heading">
					<h2 class="mod-title">
						<?php if(class_exists('theme_page_rank')){ ?>
							<a href="<?= theme_page_rank::get_tabs('recommend')['url'];?>">
						<?php } ?>
						<i class="fa fa-star-o"></i> <?= ___('Recommend');?>
						<?php if(class_exists('theme_page_rank')){ ?>
							</a>
						<?php } ?>
					</h2>
					<?php if(class_exists('theme_page_rank')){ ?>
						<a href="<?= theme_page_rank::get_tabs('recommend')['url'];?>" class="more"><?= ___('more &raquo;');?></a>
					<?php } ?>
				</div>
				<ul class="home-recomm row post-img-lists">
					<?php
					foreach($query->posts as $post){
						setup_postdata($post);
						self::archive_stick_content(array(
							'classes' => ['col-sm-6 col-md-3'],
							//'lazyload' => false,
						));
					}
					wp_reset_postdata();
					?>
				</ul>
			</div>
			<?php if(class_exists('theme_page_rank')){ ?>
				<!-- <div class="mod-footer">
					<a class="more" href="<?= theme_page_rank::get_tabs('recommend')['url'];?>"><?= ___('Readmore...');?> <i class="fa fa-external-link"></i></a>
				</div> -->
			<?php } ?>
			<?php
		}
		unset($query);
		$cache = ob_get_contents();
		ob_end_clean();
		theme_recommended_post::set_cache($cache);

		echo $cache;
		unset($cache);
	}
	public static function archive_stick_content(array $args = []){
		global $post;
		$args = array_merge([
			'classes' => ['col-xs-12 col-md-3'],
			'lazyload' => true,
		],$args);

		$args['classes'][] = 'post-list post-stick-list';
		
		$excerpt = get_the_excerpt();
		
		if(!empty($excerpt))
			$excerpt = esc_html($excerpt);
			
		$thumbnail_real_src = theme_functions::get_thumbnail_src($post->ID);

		$thumbnail_placeholder = theme_features::get_theme_images_url(theme_functions::$thumbnail_placeholder);
		?>
		<li class="<?= implode(' ',$args['classes']);?>">
			<a class="post-list-bg" href="<?= theme_cache::get_permalink($post->ID);?>" title="<?= theme_cache::get_the_title($post->ID), empty($excerpt) ? null : ' - ' . $excerpt;?>">
				<div class="thumbnail-container">
					<img class="placeholder" alt="Placeholder" src="<?= $thumbnail_placeholder;?>" width="<?= self::$thumbnail_size[1];?>" height="<?= self::$thumbnail_size[2];?>">
					<?php
					/**
					 * lazyload img
					 */
					if($args['lazyload']){
						?>
						<img class="post-list-img" src="<?= $thumbnail_placeholder;?>" data-src="<?= $thumbnail_real_src;?>" alt="<?= theme_cache::get_the_title($post->ID);?>" width="<?= self::$thumbnail_size[1];?>" height="<?= self::$thumbnail_size[2];?>"/>
					<?php }else{ ?>
						<img class="post-list-img" src="<?= $thumbnail_real_src;?>" alt="<?= theme_cache::get_the_title($post->ID);?>" width="<?= self::$thumbnail_size[1];?>" height="<?= self::$thumbnail_size[2];?>"/>
					<?php } ?>

					<?php if(class_exists('theme_colorful_cats')){ ?>
						<div class="post-list-cat">
							<?php
							/**
							 * cats
							 */
							foreach(get_the_category($post->ID) as $cat){
								$color = theme_colorful_cats::get_cat_color($cat->term_id,true);
								?>
								<span style="background-color:rgba(<?= $color['r'];?>,<?= $color['g'];?>,<?= $color['b'];?>,.8);"><?= $cat->name;?></span>
							<?php } ?>
						</div>
					<?php } ?>

				</div>
				<h3 class="post-list-title"><?= theme_cache::get_the_title($post->ID);?></h3>
				<div class="post-list-meta">
					<span class="meta author" title="<?= theme_cache::get_the_author_meta('display_name',$post->post_author);?>">
						<img width="16" height="16" src="<?= theme_features::get_theme_images_url(self::$avatar_placeholder);?>" data-src="<?= get_avatar_url($post->post_author);?>" alt="<?= theme_cache::get_the_author_meta('display_name',$post->post_author);?>" class="avatar"> <span class="tx"><?= theme_cache::get_the_author_meta('display_name',$post->post_author);?></span>
					</span>
					<?php
					/**
					 * views
					 */
					if(class_exists('theme_post_views') && theme_post_views::is_enabled()){ ?>
						<span class="meta views" title="<?= ___('Views');?>"><i class="fa fa-play-circle"></i> <?= theme_post_views::get_views($post->ID);?></span>
					<?php } ?>

					<!-- comments count -->
					<span class="meta comments-count" title="<?= ___('Comments');?>">
						<i class="fa fa-comment"></i> <?= (int)$post->comment_count;?>
					</span>
				</div>
			</a>
		</li>
		<?php
	}
	public static function archive_mixed_content(array $args = []){
		global $post;
		$args = array_merge([
			'classes' => ['col-xs-12 col-md-3'],
			'lazyload' => true,
		],$args);
		
		$args['classes'][] = 'post-list post-mixed-list ';
		
		/** sticky */
		//static $stickies = null;
		//if($stickies === null)
		//	$stickies = theme_cache::get_option('sticky_posts');
		//if(is_array( $stickies ) && in_array( $post->ID, $stickies ))
		//	$args['classes'][] = 'sticky';
		
			
		$excerpt = get_the_excerpt();
		
		if(!empty($excerpt))
			$excerpt = esc_html($excerpt);
			
		$thumbnail_real_src = theme_functions::get_thumbnail_src($post->ID);

		$thumbnail_placeholder = theme_features::get_theme_images_url(theme_functions::$thumbnail_placeholder);
		?>
		<li <?php post_class($args['classes']);?>>
			<a class="post-list-bg" href="<?= theme_cache::get_permalink($post->ID);?>" title="<?= theme_cache::get_the_title($post->ID), empty($excerpt) ? null : ' - ' . $excerpt;?>">
				<div class="thumbnail-container">
					<img class="placeholder" alt="Placeholder" src="<?= $thumbnail_placeholder;?>" width="<?= self::$thumbnail_size[1];?>" height="<?= self::$thumbnail_size[2];?>">
					<?php
					/**
					 * lazyload img
					 */
					if($args['lazyload']){
						?>
						<img class="post-list-img" src="<?= $thumbnail_placeholder;?>" data-src="<?= $thumbnail_real_src;?>" alt="<?= theme_cache::get_the_title($post->ID);?>" width="<?= self::$thumbnail_size[1];?>" height="<?= self::$thumbnail_size[2];?>"/>
					<?php }else{ ?>
						<img class="post-list-img" src="<?= $thumbnail_real_src;?>" alt="<?= theme_cache::get_the_title($post->ID);?>" width="<?= self::$thumbnail_size[1];?>" height="<?= self::$thumbnail_size[2];?>"/>
					<?php } ?>

				</div>
				<h3 class="post-list-title"><?= theme_cache::get_the_title($post->ID);?></h3>
				
				<div class="post-list-meta">
					<span class="meta author" title="<?= theme_cache::get_the_author_meta('display_name',$post->post_author);?>">
						<img width="16" height="16" src="<?= theme_features::get_theme_images_url(self::$avatar_placeholder);?>" data-src="<?= get_avatar_url($post->post_author);?>" alt="<?= theme_cache::get_the_author_meta('display_name',$post->post_author);?>" class="avatar"> <span class="tx"><?= theme_cache::get_the_author_meta('display_name',$post->post_author);?></span>
					</span>
					<time class="meta time" datetime="<?= get_the_time('Y-m-d H:i:s',$post->ID);?>" title="<?= get_the_time(___('M j, Y'),$post->ID);?>">
						<?= friendly_date(get_the_time('U',$post->ID));?>
					</time>
				</div>
			</a>
		</li>
		<?php
	}
	public static function the_homebox(array $args = []){
		if(!class_exists('theme_custom_homebox')) 
			return false;
			
		$opt = (array)theme_custom_homebox::get_options();

		/**
		 * cache
		 */

		$cache = theme_custom_homebox::get_cache();
		if(!empty($cache)){
			echo $cache;
			unset($cache);
			return;
		}

		ob_start();
		
		if(is_null_array($opt)){
			?>
			<div class="panel panel-primary">
				<div class="panel-body">
					<div class="page-tip"><?= status_tip('info',___('Please add some homebox.'));?></div>
				</div>
			</div>
			<?php
			return false;
		}

		global $post;
		static $lazyload_i = 0;
		foreach($opt as $k => $v){
			if(!isset($v['title']) || trim($v['title']) === '')
				continue;
			?>
<div id="homebox-<?= $k;?>" class="homebox mod">
	
	<div class="mod-heading">
		<h2 class="mod-title">
			<?= stripcslashes($v['title']);?>
		</h2>
		<div class="extra">
			<?php if(!is_null_array($v['keywords'])){ ?>
				<div class="keywords hidden-xs">
					<?php foreach(theme_custom_homebox::keywords_to_html($v['keywords']) as $kw){ ?>
						<a href="<?= esc_url($kw['url']);?>"><?= $kw['name'];?></a>
					<?php } ?>
				</div>
			<?php } ?>
		</div>
	</div>
	<ul class="row post-img-lists">
		<?php
		$query = new WP_Query([
			'category__in' => isset($v['cats']) ? $v['cats'] : [],
			'posts_per_page' => isset($v['number']) ? (int)$v['number'] : 8,
			'ignore_sticky_posts' => false,
		]);
		if($query->have_posts()){
			$i = 0;
			foreach($query->posts as $post){
				setup_postdata($post);
				self::archive_mixed_content(array(
					'classes' => $i <= 2 ? ['col-xs-12 col-sm-4'] : ['col-xs-12 col-sm-3'],
					'lazyload' => wp_is_mobile() && $lazyload_i < 1 ? false : true,
				));
				++$i;
			}
			wp_reset_postdata();
		}else{
			echo status_tip('info',___('No data yet.'));
		}
		unset($query);
		?>
	</ul>
	<?php
	/**
	 * ad
	 */
	if(isset($v['ad']) || !empty($v['ad'])){
		?>
		<div class="homebox-ad"><?= stripslashes($v['ad']);?></div>
	<?php } ?>
</div>
			<?php
			++$lazyload_i;
		} /** end foreach */

		$cache = html_minify(ob_get_contents());
		ob_end_clean();
		
		theme_custom_homebox::set_cache($cache);
		echo $cache;
		unset($cache);
	}
	public static function theme_respond(){
		global $post;
		?>
<div id="respond" class="panel panel-default">
	<div class="panel-heading">
		<h3 id="reply-title" class="panel-title comment-reply-title">
			<span class="leave-reply">
				<i class="fa fa-commenting"></i> 
				<?= ___('Leave a comment');?>
			</span>
		</h3>		
		<a href="javascript:;" id="cancel-comment-reply-link" class="none" title="<?= ___('Cancel reply');?>">&times;</a>
	</div>
	<div class="panel-body">
		<div class="page-tip" id="respond-loading-ready">
			<?= status_tip('loading',___('Loading, please wait...'));?>
		</div>
		
		<p id="respond-must-login" class="well hide-on-logged none">
			<?php 
			echo sprintf(
				___('You must be %s to post a comment.'),
				'<a href="' . esc_url(wp_login_url(theme_cache::get_permalink($post->ID))) . '#respond' . '"><strong>' . ___('log-in') . '</strong></a>'
			);
			?>
		</p>
			
		<form 
			id="commentform" 
			action="javascript:;" 
			method="post" 
			class="comment-form media none"
		>
			<input type="hidden" name="comment_post_ID" id="comment_post_ID" value="<?= $post->ID;?>">
			<input type="hidden" name="comment_parent" id="comment_parent" value="0">
			
			<div class="media-left media-top hidden-xs">
				<img id="respond-avatar" src="<?= theme_features::get_theme_images_url(self::$avatar_placeholder);?>" alt="Avatar" class="media-object avatar" width="80" height="80">
			</div>
			<div class="media-body">
				<?php
				/**
				 * for visitor
				 */
				$req = theme_cache::get_option( 'require_name_email' );
				?>
				<!-- author name -->
				<div id="area-respond-visitor" class="row">
					<div class="col-sm-6">
						<div class="form-group">
							<input type="text" 
								class="form-control" 
								name="author" 
								id="comment-form-author" 
								placeholder="<?= ___('Nickname');?><?= $req ? ' * ' : null;?>"
								<?= $req ? ' required ' : null;?>
								title="<?= ___('Whats your nickname?');?>"
							>
						</div><!-- /.form-group -->
					</div><!-- /.col-sm-6 -->
					<!-- author email -->
					<div class="col-sm-6">
						<div class="form-group">
							<input type="email" 
								class="form-control" 
								name="email" 
								id="comment-form-email" 
								placeholder="<?= ___('Email');?><?= $req ? ' * ' : null;?>"
								<?= $req ? ' required ' : null;?>
								title="<?= ___('Whats your Email?');?>"
							>
						</div><!-- /.form-group -->
					</div><!-- /.col-sm-6 -->
				</div><!-- /.row -->				
				<div class="form-group btn-group-textarea">
					<textarea 
						name="comment" 
						id="comment-form-comment" 
						class="form-control" 
						rows="2" 
						placeholder="<?= ___('Hi, have something to say?');?>" 
						title="<?= ___('Nothing to say?');?>" 
						required 
					></textarea>
					<?php
					/**
					 * theme comment emotion pop btn
					 */
					if(class_exists('theme_comment_emotion') && (theme_comment_emotion::is_enabled('kaomoji') || theme_comment_emotion::is_enabled('img'))){
						theme_comment_emotion::display_frontend('pop');
					}
					?>
					<div class="btn-group btn-group-submit">
						<?php
						/**
						 * theme comment emotion
						 */
						if(class_exists('theme_comment_emotion') && (theme_comment_emotion::is_enabled('kaomoji') || theme_comment_emotion::is_enabled('img'))){
							theme_comment_emotion::display_frontend('pop-btn');
						}
						?>
						<button type="submit" class="submit btn btn-success" title="<?= ___('Post comment');?>">
							<i class="fa fa-check"></i> 
							<?= ___('Post comment');?>
						</button>
						
					</div>
				</div><!-- .form-group -->
			</div><!-- /.media-body -->
		</form>
	</div>
</div>
		<?php
	}
	/**
	 * Echo the user list within loop
	 *
	 * @param array $args
	 * @return 
	 * @version 1.0.1
	 */
	public static function the_user_list(array $args = []){
		$defaults = [
			'classes' => 'col-xs-4',
			'user' => null,
			'extra_title' => '', /** eg. You have % points */
			'extra' => 'point',
		];
		$args = array_merge($defaults,$args);
		
		$user = $args['user'];
		
		if(is_numeric($user))
			$user = get_user_by('id',$user);
			
		if(empty($user))
			return false;


		/**
		 * extra point value
		 */
		switch($args['extra']){
			/**
			 * user point
			 */
			case 'point':
				if(class_exists('theme_custom_point')){
					$point_value = theme_custom_point::get_point($user->ID);
				}
				break;
			/**
			 * user fav be_count
			 */
			case 'fav':
				if(class_exists('custom_post_fav')){
					$point_value = custom_post_fav::get_user_be_fav_count($user->ID);
				}
				break;
			/**
			 * user posts count
			 */
			case 'posts':
				if(class_exists('theme_custom_author_profile')){
					$point_value = theme_custom_author_profile::get_count('works',$user->ID);
				}else{
					$point_value = count_user_posts($user->ID);
				}
				break;
			default:
				$point_value = null;
		}

		if(!empty($args['extra_title']) && $point_value)
			$args['extra_title'] = str_replace('%',$point_value,$args['extra_title']);

		
		$display_name = theme_cache::get_the_author_meta('display_name',$user->ID);

		$avatar_placeholder = theme_features::get_theme_images_url(self::$avatar_placeholder);

		$avatar_url = get_avatar_url($user->ID);
		?>
		<div class="user-list <?= $args['classes'];?>">
			<a href="<?= theme_cache::get_author_posts_url($user->ID)?>" title="<?= $display_name;?>">
				<div class="avatar-container">
					<img src="<?= $avatar_placeholder;?>" alt="<?= $display_name;?>" class="placeholder">
					<img src="<?= $avatar_placeholder;?>" data-src="<?= $avatar_url;?>" alt="<?= $display_name;?>" class="avatar">
				</div>
				<h4 class="author"><?= $display_name;?></h4>
				<?php if($args['extra']){ ?>
					<div class="extra">
						<span class="<?= $args['extra'];?>" title="<?= $args['extra_title'];?>">
							<?= $point_value;?>
						</span>
					</div>
				<?php }/** end args extra */ ?>
			</a>
		</div>
		<?php
	}
}
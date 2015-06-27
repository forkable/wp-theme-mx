<?php

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

		/**
		 * jpeg_quality
		 */
		add_filter('jpeg_quality',function($args){
			return 75;
		});
		
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
	 */
	public static function archive_img_content(array $args = []){
		$defaults = array(
			'classes' => [],
			'lazyload' => true,
		);
		$args = array_merge($defaults,$args);

		global $post;
		$args['classes'][] = 'post-list post-img-list';
		
		$post_title = esc_html(get_the_title());

		$excerpt = get_the_excerpt();
		
		if(!empty($excerpt))
			$excerpt = esc_html($excerpt);
			
		$thumbnail_real_src = esc_url(theme_functions::get_thumbnail_src($post->ID));

		$thumbnail_placeholder = esc_url(theme_features::get_theme_images_url(theme_functions::$thumbnail_placeholder));
		?>
		<li class="<?= implode(' ',$args['classes']);?>">
			<a class="post-list-bg" href="<?= get_permalink();?>" title="<?= $post_title, empty($excerpt) ? null : ' - ' . $excerpt;?>">
				<div class="thumbnail-container">
					<img class="placeholder" alt="Placeholder" src="<?= $thumbnail_placeholder;?>" width="<?= self::$thumbnail_size[1];?>" height="<?= self::$thumbnail_size[2];?>">
					<?php
					/**
					 * lazyload img
					 */
					if($args['lazyload']){
						?>
						<img class="post-list-img" src="<?= $thumbnail_placeholder;?>" data-src="<?= $thumbnail_real_src;?>" alt="<?= $post_title;?>" width="<?= self::$thumbnail_size[1];?>" height="<?= self::$thumbnail_size[2];?>"/>
					<?php }else{ ?>
						<img class="post-list-img" src="<?= $thumbnail_real_src;?>" alt="<?= $post_title;?>" width="<?= self::$thumbnail_size[1];?>" height="<?= self::$thumbnail_size[2];?>"/>
					<?php } ?>
				</div>
				<h3 class="post-list-title"><?= $post_title;?></h3>
					
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
		
		$post_title = esc_html(get_the_title());
		/** 
		 * classes
		 */
		$args['classes'][] = 'post-list post-tx-list';
		$args['classes'] = implode(' ',$classes);
		
		$meta_type = self::get_meta_type($meta_type);
		
		?>
		<li class="<?= $classes;?>">
			<a href="<?= esc_url(get_permalink());?>" title="<?= $post_title;?>">
				<?php
				if(empty($meta_type)){
					echo $post_title;
				}else{
					?>
					<span class="post-list-meta" title="<?= $meta_type['tx'];?>">
						<span class="icon-<?= $meta_type['icon'];?>"></span><span class="after-icon"><?= $meta_type['num'];?></span>
					</span>
					<span class="tx"><?= $post_title;?></span>
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

		$post_title = esc_html(get_the_title());

		$excerpt = get_the_excerpt();
		if(!empty($excerpt))
			$excerpt = esc_html($excerpt);

		$thumbnail_real_src = esc_url(theme_functions::get_thumbnail_src($post->ID));

		$thumbnail_placeholder = theme_features::get_theme_images_url(theme_functions::$thumbnail_placeholder);
		?>
		<li class="list-group-item <?= $args['classes'];?>">
			<a class="post-list-bg media" href="<?= get_permalink();?>" title="<?= $post_title, empty($excerpt) ? null : ' - ' . $excerpt;?>">
				<div class="media-left">
					<img src="<?= $thumbnail_placeholder;?>" alt="<?= $post_title;?>" class="media-object placeholder">
					<img class="post-list-img" src="<?= $thumbnail_placeholder;?>" data-src="<?= $thumbnail_real_src;?>" alt="<?= $post_title;?>"/>
				</div>
				<div class="media-body">
					<h4 class="media-heading"><?= $post_title;?></h4>
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
		wp_reset_postdata();
		
		$defaults = array(
			'classes'			=> [],
			'lazyload'			=> true,
			
		);
		$args = array_merge($defaults,$args);
		
		/** 
		 * classes
		 */
		$args['classes'][] = 'singluar-post panel panel-default';

		$post_title = esc_html(get_the_title());
		?>
		<article id="post-<?php $post->ID;?>" <?php post_class($args['classes']);?>>
			<div class="panel-heading">
				<?php if(!empty($post_title)){ ?>
					<h3 class="entry-title panel-title"><?= $post_title;?></h3>
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

		wp_reset_postdata();

		
		$defaults = array(
			'classes'			=> [],
			'lazyload'			=> true,
			
		);
		$args = array_merge($defaults,$args);
		
		/** 
		 * classes
		 */
		$args['classes'][] = 'singluar-post panel panel-default';

		$post_title = esc_html(get_the_title());

		$author_display_name = esc_html(get_the_author());

		$author_url = esc_url(theme_cache::get_author_posts_url($post->post_author));
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
						<?php if(!empty($post_title)){ ?>
							<h3 class="entry-title panel-title"><?= $post_title;?></h3>
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

						</header>
					</div><!-- /.media-body -->
				</div><!-- /.media -->
			</div><!-- /.panel-heading -->

			<div class="panel-body">

				
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
				
				<?php //self::the_post_pagination();?>
				



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
				$comment_count = (int)get_comments_number();
				$comment_tx = $comment_count <= 1 ? ___('comment') : ___('comments');
				?>
				<a href="#comments" class="post-meta quick-comment comment-count" data-post-id="<?= $post->ID;?>">
					<i class="fa fa-comment"></i>
					<span class="comment-count-number"><?= $comment_count;?></span> <span class="hidden-xs"><?= esc_html($comment_tx);?></span>
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
						<a href="<?= get_permalink($prev_post->ID);?>" title="<?= esc_attr(sprintf(___('Previous post: %s'),$prev_post->post_title));?>" class="button">
							<span class="aquo"><?= esc_html(___('&laquo;'));?></span>
							<?= esc_html($prev_post->post_title);?>
						</a>
					<?php } ?>
				</li>
				<li class="adjacent-post-next grid-50 tablet-grid-50 mobile-grid-100">
					<?php if(!$next_post){ ?>
						<span class="adjacent-post-not-found button"><?= ___('No more post found');?></span>
					<?php }else{ ?>
						<a href="<?= get_permalink($next_post->ID);?>" title="<?= esc_attr(sprintf(___('Next post: %s'),$next_post->post_title));?>"  class="button">
							<?= esc_html($next_post->post_title);?>
							<span class="aquo"><?= esc_html(___('&raquo;'));?></span>
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
     * @version 2.0.6
     * 
     */
    public static function get_crumb(array $args = []){
		$defaults = array(
			'header' => null,
			'footer' => null,
		);
		$r = array_merge($defaults,$args);
		extract($r,EXTR_SKIP);
		
		
		$links = [];
		
    	if(is_home())
    		return null;
		
		$links['home'] = '<a href="' . home_url() . '" class="home" title="' . ___('Back to Homepage') . '">
			<i class="fa fa-home fa-fw"></i>
			<span class="hide">' . ___('Back to Homepage') . '</span>
		</a>';
		
		$split = '<span class="split"><i class="fa fa-angle-right"></i></span>';
		
    	/* category */
    	if(is_category()){
			$cat_curr = theme_features::get_current_cat_id();
			if($cat_curr > 1){
				$links_cat = get_category_parents($cat_curr,true,'%split%');
				$links_cats = explode('%split%',$links_cat);
				array_pop($links_cats);
				$links['category'] = implode($split,$links_cats);
				$links['curr_text'] = ___('Category Browser');
			}
    	/* tag */
    	}else if(is_tag()){
    		$tag_id = theme_features::get_current_tag_id();
			$tag_obj = get_tag($tag_id);
    		$links['tag'] = '<a href="'. esc_url(get_tag_link($tag_id)).'">' . esc_html(theme_features::get_current_tag_name()).'</a>';
    		$links['curr_text'] = ___('Tags Browser');
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
    		$links['curr_text'] = ___('Date Browser');
    	/* search*/
    	}else if(is_search()){
    		// $nav_link = null;
    		$links['curr_text'] = sprintf(___('Search Result: %s'),esc_html(get_search_query()));
		/* author */
		}else if(is_author()){
			global $author;
			$user = get_user_by('id',$author);
			$links['author'] = '<a href="'.theme_cache::get_author_posts_url($author).'">'.esc_html($user->display_name).'</a>';
			$links['curr_text'] = ___('Author posts');
    	/* archive */
    	}else if(is_archive()){
    		$links['archive'] = '<a href="'.get_current_url().'">'.wp_title('',false).'</a>';
    		$links['curr_text'] = ___('Archive Browser');
    	/* Singular */
    	}else if(is_singular()){
			global $post;
			/* The page parent */
			if($post->post_parent){
				$links['singluar'] = '<a href="' .get_page_link($post->post_parent). '">' .esc_html(get_the_title($post->post_parent)). '</a>';
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
    		$links['curr_text'] = ___('Not found');
    	}
	
    return '<div class="crumb-container">
		' .$header. '
		<nav class="crumb">
			' . implode($split,apply_filters('crumb_links',$links)) . '
		</nav>
		' .$footer. '
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
	        'next_string'     	=> '<i class="fa fa-arrow-right"></i>',
	        'before_output'   	=> '<div class="posts-nav btn-group btn-group-justified" role="group" aria-label="' . ___('Posts pagination navigation') . '">',
	        'after_output'    	=> '</div>'
	    );
	    $args = array_merge($defaults,$args);
	    
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
			    <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
				    <?= sprintf(___('Page %d'),$page);?>
				    <span class="caret"></span>
				</button>
				<ul class="dropdown-menu" role="menu">
					
					<?php
					/**
					 * Previous 5 page
					 */
					for( $i = $page - 3; $i < $page; $i++){
						if($i < 1 )
							continue;
						?>
						<li>
							<a href="<?= esc_url( get_pagenum_link($i) );?>">
								<?= sprintf(___('Page %d'),$i);?>
							</a>
						</li>
						<?php
					}
					?>
					<li class="active">
						<a href="<?= esc_url( get_pagenum_link($page) );?>">
							<?= sprintf(___('Page %d'),$page);?>
						</a>
					</li>
					<?php
			        for( $i = $page + 1; $i < $page + 4; $i++ ) {
				        if($i > $count)
				        	break;
			            ?>
						<li>
							<a href="<?= esc_url( get_pagenum_link($i) );?>">
								<?= sprintf(___('Page %d'),$i);?>
							</a>
						</li>
						<?php
			        }
			        ?>
	        	</ul>
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
			$page_comments = get_option('page_comments');
		/** if comment is closed, return */
		if(!$page_comments) 
			return false;

		/**
		 * comments per page
		 */
		if(!$cpp === null)
			$cpp = get_option('comments_per_page');

		/**
		 * thread_comments
		 */
		if($thread_comments === null)
			$thread_comments = get_option('thread_comments');

		if($max_pages === null)
			$max_pages = get_comment_pages_count(null,get_option('comments_per_page'),get_option('thread_comments'));
			
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
	public static function smart_page_pagination($args = []){
		global $post,$page,$numpages;

		//$cache = wp_cache_
		$output = null;
	
		$defaults = array(
			'add_fragment' => 'post-' . $post->ID
		);
		$args = array_merge($defaults,$args);
		
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
				$output['prev_page']['url'] = theme_features::get_link_page_url($prev_page_number,$args['add_fragment']);
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
		return array_filter($output);
	}

	
	
	public static function the_post_pagination(){
		global $post,$page;
		$cache_id = $post->ID . $page;
		$cache_group = 'post-pagi';

		$cache = wp_cache_get($cache_id,$cache_group);
		if(!empty($cache)){
			echo $cache;
			return;
		}
			
		ob_start();
		?>
		<nav class="prev-next-pagination btn-group btn-group-justified">
			<?php
			$prev_next_pagination = self::smart_page_pagination();
//var_dump($prev_next_pagination);
			$prev_url = null;
			$next_url = null;

			/**
			 * prev
			 */
			if(isset($prev_next_pagination['prev_page'])){
				$prev_url = $prev_next_pagination['prev_page']['url'];
				$prev_type = 'page';
			}else{
				$prev_url = get_permalink($prev_next_pagination['next_post']->ID);
				$prev_type = 'post';
			}
			/**
			 * next
			 */
			if(isset($prev_next_pagination['next_page'])){
				$next_url = $prev_next_pagination['next_page']['url'];
				$next_type = 'page';
			}else{
				$next_url = get_permalink($prev_next_pagination['prev_post']->ID);
				$next_type = 'post';
			}
			if($prev_url){
				$prev_btn = $prev_type === 'page' ? 'btn-success' : 'btn-primary';
				$prev_tx =  $prev_type === 'page' ? ___('Preview page') : ___('Preview post');
				?>
				<div class="btn-group btn-group-lg" role="group">
					<a href="<?= esc_url($prev_url);?>" class="prev-page btn btn-default"><i class="fa fa-arrow-left"></i> <?= $prev_tx;?></a>
				</div>
				<?php
			}
			if($next_url){
				$next_btn = $next_type === 'page' ? 'btn-success' : 'btn-primary';
				$next_tx =  $next_type === 'page' ? ___('Next page') : ___('Next post');
				?>
				<div class="btn-group btn-group-lg" role="group">
					<a href="<?= esc_url($next_url);?>" class="next-page btn btn-default"><?= $next_tx;?> <i class="fa fa-arrow-right"></i></a>
				</div>
				<?php
			}
			?>
		</nav>
		<?php
		$cache = html_minify(ob_get_contents());
		ob_end_clean();

		wp_cache_set($cache_id,$cache,$cache_group,3600);
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
					static $get_current_user_id = null;
					if($get_current_user_id === null)
						$get_current_user_id = get_current_user_id();

					if($get_current_user_id == $comment->user_id)
						$classes[] = 'is-me';
				}

				/**
				 * author url
				 */
				$author_url = get_comment_author_url();
				if(!empty($author_url) && stripos($author_url,home_url()) === false){
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
				<span class="comment-meta-data comment-reply reply">
					<?php
					if(!is_user_logged_in()){
						static $reply_link;
						if(!$reply_link)
							$reply_link = '<a rel="nofollow" class="comment-reply-login quick-login-btn" href="' . wp_login_url(get_permalink($comment->comment_post_ID)) . '">' . ___('Reply') . '</a>';
						echo $reply_link;
					}else{
						$reply_link = get_comment_reply_link(
							array_merge($args,[
								'add_below'		=> 'comment-body', 
								'depth' 		=> $depth,
								'reply_text' 	=> ___('Reply'),
								'max_depth' 	=> $args['max_depth'],
							]),
							$comment,
							$comment->comment_post_ID
						);
						echo preg_replace('/(href=)[^\s]+/','$1"javascript:;"',$reply_link);
					}
					?>
				</span><!-- .reply -->
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
			
			$comment_content = '<a href="' . esc_url(get_permalink($parent_comment->comment_post_ID)) . '#comment-' . $parent_comment->comment_ID . '" class="at" rel="nofollow">@' . $parent_author . '</a> ' . $comment_content;
		}
		return $comment_content;
	}
	public static function the_related_posts_plus(array $args = []){
		global $post;

		/**
		 * cache
		 */
		$cache_group_id = 'related_posts';
		$cache = wp_cache_get($post->ID,$cache_group_id);
		if($cache){
			echo $cache;
			return $cache;
		}
		
		$defaults = array(
			'posts_per_page' => 6,
			'orderby' => 'latest',
		);
		$query_args = array(
			'post__not_in' => array($post->ID),
		);
		$args = array_merge($defaults,$args);
		$content_args = array(
			'classes' => array('col-xs-6 col-sm-4 col-md-2')
		);
		
		ob_start();
		?>
		
		<div class="related-posts panel panel-default" role="tabpanel">
			<div class="panel-heading">
				<h3 class="panel-title"><i class="fa fa-heart-o"></i> <?= ___('Maybe you will like them');?></h3>
			</div>
			<div class="panel-body">
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
		$cache_id = md5(serialize($recomms));
		$cache = wp_cache_get($cache_id);
		
		if(!empty($cache)){
			echo $cache;
			return $cache;
		}
		global $post;
		$query = self::get_posts_query(array(
			'posts_per_page' => 6,
			'orderby' => 'recomm',
		));
		ob_start();
		if(have_posts()){
			?>
			<ul class="home-recomm row post-img-lists">
				<?php
				foreach($query->posts as $post){
					setup_postdata($post);
					self::archive_img_content(array(
						'classes' => array('col-sm-4'),
						'lazyload' => false,
					));
				}
				wp_reset_postdata();
				?>
			</ul>
			<?php
		}else{
			
		}
		$cache = ob_get_contents();
		ob_end_clean();
		wp_cache_set($cache_id,$cache,3600*24);

		echo $cache;
		return $cache;
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
			return $cache;
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
		static $i = 0;
		foreach($opt as $k => $v){
			?>
<div id="homebox-<?= $k;?>" class="homebox panel panel-primary mx-panel">
	
	<div class="panel-heading mx-panel-heading clearfix">
		<h3 class="panel-title mx-panel-title">
			<?php 
			
			if(empty($v['link'])){
				echo stripcslashes($v['title']);
			}else{
				?>
				<a href="<?= esc_url($v['link']);?>"><?= stripcslashes($v['title']);?> <small><?= ___('&raquo; more');?></small></a>
				<?php
			}
			?>
		</h3>
		<div class="extra">
			
			<?php if(!is_null_array($v['keywords'])){ ?>
				<div class="keywords hidden-xs">
					<?php 
					$i = 0;
					foreach(theme_custom_homebox::keywords_to_html($v['keywords']) as $kw){
						if($i !== 0){
							?>
							<i class="split"> &sdot; </i>
							<?php
						}
						++$i;
						?>
						<a href="<?= esc_url($kw['url']);?>"><?= $kw['name'];?></a>
					<?php } ?>
				</div>
			<?php } ?>
			
			
			
		</div>
	</div>
	<div class="panel-body">
		<ul class="row mx-card-body post-img-lists">
			<?php
			$query = new WP_Query([
				'category__in' => isset($v['cats']) ? $v['cats'] : [],
				'posts_per_page' => 8,
				'ignore_sticky_posts' => false,
			]);
			if($query->have_posts()){
				foreach($query->posts as $post){
					setup_postdata($post);
					self::archive_img_content(array(
						'classes' => array('col-xs-6 col-sm-3'),
						'lazyload' => $i < 1 ? false : true,
					));
				}
				wp_reset_postdata();
			}else{
				
			}
			?>
		</ul>
	</div>
</div>
		<?php
			++$i;
		} /** end foreach */

		$cache = html_minify(ob_get_contents());
		ob_end_clean();
		
		theme_custom_homebox::set_cache($cache);
		echo $cache;
		return $cache;
	}
	public static function theme_respond(){
		global $post;
		?>
<div id="respond" class="panel panel-primary">
	<div class="panel-heading">
		<h3 id="reply-title" class="panel-title comment-reply-title">
			<span class="leave-reply">
				<i class="fa fa-pencil-square-o"></i> 
				<?= ___('Leave a comment');?>
			</span>
			<a href="javascript:;" id="cancel-comment-reply-link" class="none" title="<?= ___('Cancel reply');?>">
				<i class="fa fa-times fa-fw"></i>
			</a>
		</h3>		
	</div>
	<div class="panel-body">
		<div class="page-tip" id="respond-loading-ready">
			<?= status_tip('loading',___('Loading, please wait...'));?>
		</div>
		
		<p id="respond-must-login" class="alert alert-info hide-on-logged none">
			<?php 
			echo sprintf(
				___('You must be %s to post a comment.'),
				'<a href="' . esc_url(wp_login_url(get_permalink($post->ID))) . '#respond' . '"><strong>' . ___('log-in') . '</strong></a>'
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
				<img id="respond-avatar" src="<?= theme_features::get_theme_images_url('frontend/avatar.jpg');?>" alt="Avatar" class="media-object avatar" width="80" height="80">
			</div>
			<div class="media-body">
				<?php
				/**
				 * for visitor
				 */
				$req = theme_features::get_option( 'require_name_email' );
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
						</div>
					</div>
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
				<div class="form-group">
					<div class="input-group">
						<textarea 
							name="comment" 
							id="comment-form-comment" 
							class="form-control" 
							rows="2" 
							placeholder="<?= ___('Hi, have something to say?');?>"
							title="<?= ___('Nothing to say?');?>"
							required 
						></textarea>
						<span class="input-group-btn">
							<button type="submit" class="submit btn btn-success" >
								<i class="fa fa-check fa-fw"></i>
							</button>
						</span>
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
	 * @version 1.0.0
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

		
		$display_name = esc_html($user->display_name);

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
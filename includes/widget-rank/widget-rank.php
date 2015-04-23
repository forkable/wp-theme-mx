<?php
/** 
 * version 1.0.3
 */

add_action('widgets_init','widget_rank::register_widget' );
class widget_rank extends WP_Widget{
	public static $iden = 'widget_rank';
	function __construct(){
		$this->alt_option_name = self::$iden;
		parent::__construct(
			self::$iden,
			___('Posts rank <small>(Custom)</small>'),
			array(
				'classname' => self::$iden,
				'description'=> ___('Posts rank'),
			)
		);
	}
	public static function frontend_display($args,$instance){
		$instance_defaults = array(
			'title' => ___('Posts rank'),
			'posts_per_page' => 6,
			'date' => 'all',
			'orderby' => 'views',
			'category__in' => [],
			'content_type' => 'tx',
		);
		$instance = wp_parse_args($instance,$instance_defaults);
		echo $args['before_title'];
		if(isset($instance['category__in'][0])){ ?>
			<a class="link" href="<?php echo get_category_link($instance['category__in'][0]);?>" title="<?php echo esc_attr(sprintf(___('Views more about %s'),$instance['title']));?>">
				<i class="fa fa-bar-chart"></i> 
				<?php echo esc_html($instance['title']);?>
			</a>
			<a href="<?php echo get_category_link($instance['category__in'][0]);?>" title="<?php echo esc_attr(sprintf(___('Views more about %s'),$instance['title']));?>" class="more"><?php echo esc_html(___('More &raquo;'));?></a>
		<?php }else{ ?>
			<i class="fa fa-bar-chart"></i> 
			<?php echo esc_html($instance['title']);?>
		<?php } ?>
		<?php
		echo $args['after_title'];
		
		global $post;
		$query = theme_functions::get_posts_query(array(
			'category__in' => (array)$instance['category__in'],
			'posts_per_page' => (int)$instance['posts_per_page'],
			'date' => $instance['date'],
			'orderby' => $instance['orderby'],
		));
		$content_type_class = $instance['content_type'] === 'tx' ? ' post-tx-lists ' : ' post-mixed-lists ';
		/** 
		 * set container tag
		 */
		switch($instance['orderby']){
			case 'latest':
			case 'rand':
			case 'random':
			case 'recommended':
			case 'sticky':
				$container_tag = 'ul';
				break;
			default:
				$container_tag = 'div';
		}
		if($query->have_posts()){
			?>
			<ul class="list-group <?php echo $content_type_class;?> widget-orderby-<?php echo $instance['orderby'];?>">
			
			<!-- <ul class="tabbody post-lists <?php echo $content_type_class;?> widget-orderby-<?php echo $instance['orderby'];?>"> -->
				<?php
				while($query->have_posts()){
					$query->the_post();
					if($instance['content_type'] === 'tx'){
						theme_functions::widget_rank_tx_content(array(
							'meta_type' => $instance['orderby'],
						));
					}else{
						theme_functions::widget_rank_img_content();
					}
				}
				wp_reset_postdata();
				?>
			</ul>
		<?php }else{ ?>
			<div class="page-tip not-found">
				<?php echo status_tip('info',___('No data yet.'));?>
			</div>
		<?php 
		}
		//wp_reset_query();
	}
	function widget($args,$instance){
		// var_dump($instance);
		extract($args);
		/** 
		 * theme cache
		 */
		// $cache_id = md5(serialize($args) .serialize($instance) . get_current_url());
		echo $before_widget;
		self::frontend_display($args,$instance);
		echo $after_widget;
		
	}
	
	function form($instance){
		$instance = wp_parse_args(
			(array)$instance,
			array(
				'title'=>___('Posts ranking'),
				'posts_per_page' => 6,
				'category__in' => [],
				'content_type' => 'tx',
				'orderby' => 'latest',
			)
		);
		?>
		<p>
			<label for="<?php echo esc_attr(self::get_field_id('title'));?>"><?php echo esc_html(___('Title (optional)'));?></label>
			<input 
				id="<?php echo esc_attr(self::get_field_id('title'));?>"
				class="widefat"
				name="<?php echo esc_attr(self::get_field_name('title'));?>" 
				type="text" 
				value="<?php echo esc_attr($instance['title']);?>" 
				placeholder="<?php echo esc_attr(___('Title (optional)'));?>"
			/>
		</p>
		<p>
			<label for="<?php echo esc_attr(self::get_field_id('posts_per_page'));?>"><?php echo esc_html(___('Post number (required)'));?></label>
			<input 
				id="<?php echo esc_attr(self::get_field_id('posts_per_page'));?>"
				class="widefat"
				name="<?php echo esc_attr(self::get_field_name('posts_per_page'));?>" 
				type="number" 
				value="<?php echo esc_attr($instance['posts_per_page']);?>" 
				placeholder="<?php echo esc_attr(___('Post number (required)'));?>"
			/>
		</p>
		<p>
			<?php echo esc_html(___('Categories: '));?>
			<?php echo self::get_cat_checkbox_list(
				self::get_field_name('category__in'),
				self::get_field_id('category__in'),
				$instance['category__in']
			);?>
		</p>
		<!-- date -->
		<p>
			<label for="<?php echo esc_attr(self::get_field_id('date'));?>"><?php echo esc_html(___('Date'));?></label>
			<select
				name="<?php echo esc_attr(self::get_field_name('date'));?>" 
				class="widefat"				
				id="<?php echo esc_attr(self::get_field_id('date'));?>"
			>
				<?php
				$dates = theme_functions::get_rank_data();
				foreach($dates as $k => $v){
					echo get_option_list($k,$v,$instance['date']);
				}
				?>
			</select>
		</p>
		<p>
			<label for="<?php echo esc_attr(self::get_field_id('content_type'));?>"><?php echo esc_html(___('Content type'));?></label>
			<select 
				name="<?php echo esc_attr(self::get_field_name('content_type'));?>" 
				class="widefat"
				id="<?php echo esc_attr(self::get_field_id('content_type'));?>"
			>
				<?php
				/** 
				 * image type
				 */
				echo get_option_list('img',___('Image type'),$instance['content_type']);
				
				/** 
				 * text type
				 */
				echo get_option_list('tx',___('Text type'),$instance['content_type']);?>
			</select>
		</p>
		<p>
			<label for="<?php echo esc_attr(self::get_field_id('orderby'));?>">
				<?php echo esc_html(___('Order by'));?>
			</label>
			<select 
				name="<?php echo esc_attr(self::get_field_name('orderby'));?>" 
				class="widefat"
				id="<?php echo esc_attr(self::get_field_id('orderby'));?>"
			>
				
				<?php
				
				/** 
				 * orderby views
				 */
				if(class_exists('theme_post_views') && theme_post_views::is_enabled()){
					echo get_option_list('views',___('Most views'),$instance['orderby']);
				}
				
				/** 
				 * orderby thumb-up
				 */
				if(class_exists('theme_post_thumb') && theme_post_thumb::is_enabled()){
					echo get_option_list('thumb-up',___('Thumb up'),$instance['orderby']);
				}
				
				/** 
				 * orderby recommended
				 */
				if(class_exists('theme_recommended_post')){
					echo get_option_list('recommended',___('Recommended'),$instance['orderby']);
				}
				/** 
				 * orderby random
				 */
				echo get_option_list('random',___('Random'),$instance['orderby']);
				
				/** 
				 * orderby latest
				 */
				echo get_option_list('latest',___('Latest'),$instance['orderby']);
				
				?>
			</select>
		</p>
		<?php
	}
	/**
	 * 
	 *
	 * @param 
	 * @return 
	 * @version 1.0.0
	 * @author KM@INN STUDIO
	 */
	private static function get_cat_checkbox_list($name,$id,$selected_cat_ids = []){
		$cats = get_categories(array(
			'hide_empty' => false,
			'orderby' => 'term_group',
			'exclude' => '1',
		));
		
		ob_start();
		if($cats){
			foreach($cats as $cat){
				if(in_array($cat->term_id,(array)$selected_cat_ids)){
					$checked = ' checked="checked" ';
					$selected_class = ' button-primary ';
				}else{
					$checked = null;
					$selected_class = null;
				}
			?>
			<label for="<?php echo $id;?>-<?php echo $cat->term_id;?>" class="item button <?php echo $selected_class;?>">
				<input 
					type="checkbox" 
					id="<?php echo esc_attr($id);?>-<?php echo esc_attr($cat->term_id);?>" 
					name="<?php echo esc_attr($name);?>[]" 
					value="<?php echo $cat->term_id;?>"
					<?php echo $checked;?>
				/>
					<?php echo esc_html($cat->name);?>
			</label>
			<?php 
			}
		}else{ ?>
			<p><?php echo esc_html(___('No category, pleass go to add some categories.'));?></p>
		<?php }
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}
	function update($new_instance,$old_instance){
		$instance = wp_parse_args($new_instance,$old_instance);
		
		return $instance;
	}
	public static function register_widget(){
		register_widget(self::$iden);
	}
}
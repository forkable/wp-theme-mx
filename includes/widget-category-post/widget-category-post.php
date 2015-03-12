<?php
/*
Feature Name:	theme-widget-category-post
Feature URI:	http://www.inn-studio.com
Version:		1.0.3
Description:	theme-widget-category-post
Author:			INN STUDIO
Author URI:		http://www.inn-studio.com
*/

add_action('widgets_init','widget_posts::register_widget' );
class widget_posts extends WP_Widget{
	public static $iden = 'widget_posts';
	function __construct(){
		
		$this->alt_option_name = self::$iden;
		$this->WP_Widget(
			self::$iden,
			___('Category posts <small>(custom)</small>'),
			array(
				'classname' => self::$iden,
				'description' => ___('Show your posts by category.')
			)
		);
	}
	/**
	 * display_frontend
	 *
	 * @param array
	 * @return 
	 * @version 1.0.0
	 * @author KM@INN STUDIO
	 */
	public static function display_frontend($args = null){
		
		$defaults = array(
			'title' => ___('Category posts'),
			'class' => 'category-posts',
			'selected' => -1,
			'posts_per_page' => 8,
			'show_date' => 0,
		);
		$r = wp_parse_args($args,$defaults);
		
		/** 
		 * theme cache
		 */
		// $cache_id = md5(serialize($r) . get_current_url());

		extract($r);
		
		/** 
		 * get category posts
		 */
		global $post;
		$query_args = array(
			'posts_per_page' 		=> $posts_per_page,
			// 'paged'					=> 1,
			'category' 				=> $selected,
		);

		$posts = get_posts($query_args);
		?>
		<div class="widget-container">
			<ul>
				<?php
				if($posts){
					foreach($posts as $post){
						setup_postdata($post);
						?>
						<li>
							<a href="<?php echo esc_url(get_permalink());?>">
								<span class="title"><?php echo esc_html(get_the_title());?></span>
								<?php if($show_date === 1){ ?>
								<small class="date"> - <?php echo esc_html(friendly_date(get_post_time('U', true)));?></small>
								<?php } ?>
							</a>
						</li>
						<?php
					}
				}else{
					?>
					<li><?php echo status_tip('info',___('No post in this category'));?></li>
					<?php
				}
				wp_reset_postdata();
				?>
			</ul>
		</div>
		<?php
	}
	function widget($args,$instance){
		
		$args = array_merge($args,$instance);
		extract($args);

		$title = apply_filters('widget_title',empty($instance['title']) ? null : $instance['title']);
		$class = empty($instance['class']) ? self::$iden : $instance['class'];
		
		$title = $title ? $title . '<a class="more" href="' . get_category_link((int)$instance['selected']) . '">' . ___('More &raquo;') . '</a>' : null;
		
		echo $before_widget;
		echo $title ? $before_title . $title . $after_title : null;
		echo self::display_frontend($args);
		echo $after_widget;
	}
	function form($instance){
		
		$defaults = array(
			'title' => ___('Category posts'),
			'selected' => -1,
			'posts_per_page' => 8,
			'show_date' => 0,
		);
		$instance = wp_parse_args((array)$instance,$defaults);	
		?>
		<p>
			<label for="<?php echo $this->get_field_id('title');?>"><?php echo esc_html(___('Title'));?></label>
			<input 
				id="<?php echo $this->get_field_id('title');?>" 
				type="text" 
				class="widefat"
				name="<?php echo $this->get_field_name('title');?>" 
				value="<?php echo esc_attr($instance['title']);?>"
			/>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('selected');?>"><?php echo esc_html(___('Category: '));?></label>
			<?php
			$cat_args = array(
				'name' => $this->get_field_name('selected'),
				'id' => $this->get_field_id('selected'),
				'show_option_none' => ___('Select category'),
				'hierarchical' => 1,
				'hide_empty' => false,
				'selected' => (int)$instance['selected'],
				'echo' => 0,
				'show_count' => true,
			);		
			echo wp_dropdown_categories($cat_args);
			?>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('show_date');?>"><?php echo esc_html(___('Show date: '));?></label>
			<select 
				name="<?php echo $this->get_field_name('show_date');?>" 
				id="<?php echo $this->get_field_id('show_date');?>"
				class="widefat"
			>
				<?php
				$selected = function($v,$current_v){
					return (int)$v === (int)$current_v ? ' selected ' : null;
				};
				?>
				<option value="0" <?php echo $selected(0,(int)$instance['show_date']);?>><?php echo esc_attr(___('Hide'));?></option>
				<option value="1" <?php echo $selected(1,(int)$instance['show_date']);?>><?php echo esc_attr(___('Show'));?></option>
			</select>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('posts_per_page');?>"><?php echo esc_html(___('Post number'));?></label>
			<input 
				id="<?php echo $this->get_field_id('posts_per_page');?>" 
				type="number" 
				class="widefat"
				name="<?php echo $this->get_field_name('posts_per_page');?>" 
				value="<?php echo esc_attr((int)$instance['posts_per_page']);?>"
				required
			/>
		</p>
		<?php
	}
	function update($new_instance,$old_instance){
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['class'] = strip_tags($new_instance['class']);
		$instance['selected'] = (int)($new_instance['selected']);
		$instance['posts_per_page'] = (int)$new_instance['posts_per_page'];
		$instance['show_date'] = (int)$new_instance['show_date'];

		return $instance;
	}
	public static function register_widget(){
		register_widget(self::$iden);
	}
}
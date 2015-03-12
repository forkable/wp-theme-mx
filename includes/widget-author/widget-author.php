<?php

/**
 * theme-widget-author
 *
 * @version 1.0.0
 * @author KM@INN STUDIO
 */
add_action('widgets_init','theme_widget_author::register_widget' );
class theme_widget_author extends WP_Widget{
	public static $iden = 'theme_widget_author';
	function __construct(){
		$this->alt_option_name = self::$iden;
		parent::__construct(
			self::$iden,
			___('Author card <small>(Custom)</small>'),
			array(
				'classname' => self::$iden,
				'description'=> ___('Show the author information.'),
			)
		);
	}
	function widget($args,$instance){
		global $author;
		if(empty($author)){
			global $post;
			$author_id = $post->post_author;
		}else{
			$author_id = $author;
		}

		//var_dump($post);
		echo $args['before_widget'];
		if(!empty($instance['title'])){
			echo $args['before_title'];
			?>
			<i class="fa fa-user"></i> 
			<?php
			echo esc_html($instance['title']);
			echo $args['after_title'];
		}
		/**
		 * avatar
		 */

		$avatar = get_img_source(get_avatar($author_id,'100'));
		/**
		 * author profile page url
		 */
		if(class_exists('theme_custom_author_profile')){
			$author_url = theme_custom_author_profile::get_tabs('profile')['url'];
		}else{
			$author_url = get_author_posts_url($author_id);
		}
		$description = get_the_author_meta('description',$author_id);
		?>
		<div id="widget-author-card" class="widget-container panel-body">
			<a href="<?php echo esc_url($author_url);?>" class="media" title="<?php echo ___('Views the author information detail');?>">
				<div class="media-left">
					<img src="<?php echo esc_url($avatar);?>" alt="<?php echo ___('Author avatar');?>" class="avatar media-object img-circle" width="100" height="100">
				</div>
				<div class="media-body">
					<h4 class="media-heading author-card-name"><?php echo esc_html(get_the_author_meta('display_name',$author_id));?></h4>
					<p class="author-card-description" <?php echo empty($description) ? null : ' title="' . $description . '"';?> >
						<?php
						if(empty($description)){
							echo ___('The author is lazy, nothing writes here.');
						}else{
							echo esc_html(str_sub($description,30));
						}
						?>
					</p>
				</div>
			</a><!-- ./media -->
			<?php if(class_exists('theme_custom_author_profile')){ ?>
				<div class="author-card-meta-links btn-group btn-group-justified" role="group" aria-label="<?php echo ___('Author meta link group');?>">
					<!-- works count -->
					<a href="<?php echo esc_url(theme_custom_author_profile::get_tabs('profile')['url']);?>" class="btn btn-default" role="button">
						<span class="tx"><?php echo  esc_html(theme_custom_author_profile::get_tabs('works')['text']);?></span>
						<span class="count"><?php echo (int)esc_html(theme_custom_author_profile::get_tabs('works')['count']);?></span>
					</a>
					<!-- comments count -->
					<a href="<?php echo esc_url(theme_custom_author_profile::get_tabs('comments')['url']);?>" class="btn btn-default" role="button">
						<span class="tx"><?php echo  esc_html(theme_custom_author_profile::get_tabs('comments')['text']);?></span>
						<span class="count"><?php echo (int)esc_html(theme_custom_author_profile::get_tabs('comments')['count']);?></span>
					</a>
					<!-- followers count -->
					<a href="<?php echo esc_url(theme_custom_author_profile::get_tabs('followers')['url']);?>" class="btn btn-default" role="button">
						<span class="tx"><?php echo  esc_html(theme_custom_author_profile::get_tabs('followers')['text']);?></span>
						<span class="count"><?php echo (int)esc_html(theme_custom_author_profile::get_tabs('followers')['count']);?></span>
					</a>
					<!-- following count -->
					<a href="<?php echo esc_url(theme_custom_author_profile::get_tabs('following')['url']);?>" class="btn btn-default" role="button">
						<span class="tx"><?php echo  esc_html(theme_custom_author_profile::get_tabs('following')['text']);?></span>
						<span class="count"><?php echo (int)esc_html(theme_custom_author_profile::get_tabs('following')['count']);?></span>
					</a>
				</div>
			<?php } ?>
			<div class="author-card-features btn-group btn-group-justified" role="group">
				<?php
				/**
				 * follow
				 */
				if(class_exists('theme_follow')){ ?>
					<a href="javascript:void(0);" class="btn btn-success" id="widget-author-card-follow" data-author-id="<?php echo $author_id;?>">
						<span class="followed">
							<i class="fa fa-check-circle"></i> 
							<?php echo ___('Followed');?>
						</span>
						<span class="unfollow">
							<i class="fa fa-plus-circle"></i> 
							<?php echo ___('Follow');?>
						</span>
					</a>
				<?php } ?>
				<?php
				/**
				 * PM
				 */
				if(class_exists('theme_pm')){ ?>
					<a href="javascript:void(0);" class="btn btn-danger" id="widget-author-card-pm" data-author-id="<?php echo $author_id;?>">
						<i class="fa fa-envelope-o"></i> 
						<?php echo ___('Message');?>
					</a>
				<?php } ?>
			</div>
		</div>
		<?php
		echo $args['after_widget'];
	}
	function form($instance){
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
		<?php
	}
	function update($new_instance,$old_instance){
		$instance = wp_parse_args($new_instance,$old_instance);
		return $instance;
	}
	public static function register_widget(){
		register_widget(self::$iden);
	}
}
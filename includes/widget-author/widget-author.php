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
			___('Author card <small>(custom)</small>'),
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
			echo $instance['title'];
			echo $args['after_title'];
		}
	
		/**
		 * author profile page url
		 */
		if(class_exists('theme_custom_author_profile')){
			$author_url = theme_custom_author_profile::get_tabs('profile',$author_id)['url'];
		}else{
			$author_url = theme_cache::get_author_posts_url($author_id);
		}
		$description = get_the_author_meta('description',$author_id);
		?>
	
		<div id="widget-author-card" class="widget-container panel-body">
			<a href="<?= esc_url($author_url);?>" class="media" title="<?= ___('Views the author information detail');?>">
				<div class="media-left">
					<?= get_avatar($author_id,'100');?>
				</div>
				<div class="media-body">
					<h4 class="media-heading author-card-name"><?= esc_html(get_the_author_meta('display_name',$author_id));?></h4>
					<p class="author-card-description" <?= empty($description) ? null : ' title="' . $description . '"';?> >
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
				<div class="author-card-meta-links btn-group btn-group-justified" role="group" aria-label="<?= ___('Author meta link group');?>">
					<!-- works count -->
					<a href="<?= theme_custom_author_profile::get_tabs('profile',$author_id)['url'];?>" class="btn btn-default" role="button">
						<span class="tx"><?=  theme_custom_author_profile::get_tabs('works',$author_id)['text'];?></span>
						<span class="count"><?= (int)theme_custom_author_profile::get_tabs('works',$author_id)['count'];?></span>
					</a>
					<!-- comments count -->
					<a href="<?= theme_custom_author_profile::get_tabs('comments',$author_id)['url'];?>" class="btn btn-default" role="button">
						<span class="tx"><?=  theme_custom_author_profile::get_tabs('comments',$author_id)['text'];?></span>
						<span class="count"><?= (int)theme_custom_author_profile::get_tabs('comments',$author_id)['count'];?></span>
					</a>
					<!-- followers count -->
					<a href="#<?= theme_custom_author_profile::get_tabs('followers',$author_id)['url'];?>" class="btn btn-default disabled" role="button">
						<span class="tx"><?=  theme_custom_author_profile::get_tabs('followers',$author_id)['text'];?></span>
						<span class="count"><?= (int)theme_custom_author_profile::get_tabs('followers',$author_id)['count'];?></span>
					</a>
					<!-- following count -->
					<a href="#<?= theme_custom_author_profile::get_tabs('following',$author_id)['url'];?>" class="btn btn-default disabled" role="button">
						<span class="tx"><?=  theme_custom_author_profile::get_tabs('following',$author_id)['text'];?></span>
						<span class="count"><?= (int)theme_custom_author_profile::get_tabs('following',$author_id)['count'];?></span>
					</a>
				</div>
			<?php } ?>
			<div class="author-card-features btn-group btn-group-justified" role="group">
				<?php
				/**
				 * follow
				 */
				if(class_exists('theme_follow')){ ?>
					<a href="javascript:;" class="btn btn-success" id="widget-author-card-follow" data-author-id="<?= $author_id;?>">
						<span class="followed">
							<i class="fa fa-check-circle"></i> 
							<?= ___('Followed');?>
						</span>
						<span class="unfollow">
							<i class="fa fa-plus-circle"></i> 
							<?= ___('Follow');?>
						</span>
					</a>
				<?php } ?>
				<?php
				/**
				 * PM
				 */
				if(class_exists('theme_pm')){ ?>
					<a href="javascript:;" class="btn btn-danger" id="widget-author-card-pm" data-author-id="<?= $author_id;?>">
						<i class="fa fa-envelope-o"></i> 
						<?= ___('Message');?>
					</a>
				<?php } ?>
			</div>
		</div>
		<?php
		echo $args['after_widget'];
	}
	function form($instance = []){
		$instance = array_merge([
			'title' => ___('Member information'),
		],$instance);
		?>
		<p>
			<label for="<?= esc_attr(self::get_field_id('title'));?>"><?= ___('Title (optional)');?></label>
			<input 
				id="<?= self::get_field_id('title');?>"
				class="widefat"
				name="<?= self::get_field_name('title');?>" 
				type="text" 
				value="<?= $instance['title'];?>" 
				placeholder="<?= ___('Title (optional)');?>"
			/>
		</p>
		<?php
	}
	function update($new_instance,$old_instance){
		return array_merge($old_instance,$new_instance);
	}
	public static function register_widget(){
		register_widget(self::$iden);
	}
}
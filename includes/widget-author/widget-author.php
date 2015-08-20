<?php

/**
 * theme-widget-author
 *
 * @version 1.0.0
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

		echo $args['before_widget'];

		/**
		 * author profile page url
		 */
		if(class_exists('theme_custom_author_profile')){
			$author_url = theme_custom_author_profile::get_tabs('profile',$author_id)['url'];
		}else{
			$author_url = theme_cache::get_author_posts_url($author_id);
		}
		$description = theme_cache::get_the_author_meta('description',$author_id);
		?>
	
		<div id="widget-author-card" class="widget-container panel-body">
			<a href="<?= esc_url($author_url);?>" class="media" title="<?= ___('Views the author information detail');?>">
				<div class="media-left">
					<?= get_avatar($author_id,'100');?>
				</div>
				<div class="media-body">
					<h4 class="media-heading author-card-name">
						<?= theme_cache::get_the_author_meta('display_name',$author_id);?>
						<?php if(class_exists('theme_custom_author_profile')){ ?>
							<small class="label label-<?= theme_custom_author_profile::get_roles($author_id)['label'];?>"><?= theme_custom_author_profile::get_roles($author_id)['name'];?></small>
						<?php } ?>
					</h4>
					<p class="author-card-description" <?= empty($description) ? null : ' title="' . $description . '"';?> >
						<?php
						if(empty($description)){
							echo ___('The author is lazy, nothing writes here.');
						}else{
							echo str_sub($description,30);
						}
						?>
					</p>
				</div>
			</a><!-- ./media -->
			<?php if(class_exists('theme_custom_author_profile')){ ?>
				<div class="author-card-meta-links">
					<!-- works count -->
					<a href="<?= theme_custom_author_profile::get_tabs('works',$author_id)['url'];?>" title="<?= ___('Views author posts');?>">
						<span class="tx"><i class="fa fa-<?= theme_custom_author_profile::get_tabs('works',$author_id)['icon'];?>"></i> <?= theme_custom_author_profile::get_tabs('works',$author_id)['text'];?></span>
						<span class="count"><?= (int)theme_custom_author_profile::get_tabs('works',$author_id)['count'];?></span>
					</a>
					<!-- comments count -->
					<a href="<?= theme_custom_author_profile::get_tabs('comments',$author_id)['url'];?>" title="<?= ___('Views author comments');?>">
						<span class="tx"><i class="fa fa-<?= theme_custom_author_profile::get_tabs('comments',$author_id)['icon'];?>"></i> <?= theme_custom_author_profile::get_tabs('comments',$author_id)['text'];?></span>
						<span class="count"><?= (int)theme_custom_author_profile::get_tabs('comments',$author_id)['count'];?></span>
					</a>
					<!-- point -->
					<?php 
						if(class_exists('theme_custom_point_bomb')){ 
						if(class_exists('number_user_nicename')){
							$target_id = number_user_nicename::$prefix_number + $author_id;
						}else{
							$target_id = $author_id;
						}
						?>
						<!-- followers count -->
						<a href="<?= theme_custom_point_bomb::get_tabs('bomb',$target_id)['url'];?>" rel="nofollow" title="<?= ___('Bomb!');?>">
							<span class="tx"><i class="fa fa-bomb"></i> <?= theme_custom_point::get_point_name();?></span>
							<span class="count"><?= theme_custom_point::get_point($author_id);?></span>
						</a>
					<?php } ?>
					
					<!-- pm -->
					<?php if(class_exists('theme_custom_pm')){ ?>
						<a target="_blank" href="<?= theme_custom_pm::get_user_pm_url($author_id);?>" title="<?= ___('Send a private message.');?>">
							<span class="tx"><i class="fa fa-<?= theme_custom_pm::get_tabs('pm')['icon'];?>"></i> <?= ___('P.M.');?></span>
						</a>
					<?php } ?>
				</div>
			<?php } ?>
		</div>
		<?php
		echo $args['after_widget'];
	}

	public static function register_widget(){
		register_widget(self::$iden);
	}
}
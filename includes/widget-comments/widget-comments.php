<?php
/*
Feature Name:	widget_comments
Feature URI:	http://www.inn-studio.com
Version:		1.1.0
Description:	widget_comments
Author:			INN STUDIO
Author URI:		http://www.inn-studio.com
*/

add_action('widgets_init','widget_comments::register_widget');
class widget_comments extends WP_Widget{
	public static $iden = 'widget_comments';
	public static $avatar_size = 50;
	function __construct(){
		$this->alt_option_name = self::$iden;
		parent::__construct(
			self::$iden,
			___('Latest comments <small>(custom)</small>'),
			[
				'classname' => self::$iden,
				'description'=> ___('Show the latest comments'),
			]
		);
	}
	private static function get_default_options(){
		return [
			'title' => ___('Latest comments'),
			'number' => 6
		];
	}
	function widget($args,$instance){
		$instance = array_merge(self::get_default_options(),$instance);
		echo $args['before_widget'];
		$comments = get_comments(array(
			'status' => 'approve',
			'number' => isset($instance['number']) ? (int)$instance['number'] : 6
		));
		if(!empty($instance['title'])){
			echo $args['before_title'];
			?>
			<i class="fa fa-comments-o"></i> 
			<?php 
			echo $instance['title'];
			echo $args['after_title'];
		}
		if(!empty($comments)){
			global $comment;
			$comment_bak = $comment;
			
			?>
			<ul class="list-group">
				<?php 
				foreach($comments as $comment){
					/**
					 * cache
					 */
					static $caches = [];
					
					/** author_name */
					if(!isset($caches['author_name'][$comment->comment_author]))
						$caches['author_name'][$comment->comment_author] = esc_html(get_comment_author());
						
					/** permalinks */
					if(!isset($caches['permalinks'][$comment->comment_post_ID]))
						$caches['permalinks'][$comment->comment_post_ID] = esc_url(get_permalink($comment->comment_post_ID));
						
					/** post_title */
					if(!isset($caches['post_title'][$comment->comment_post_ID]))
						$caches['post_title'][$comment->comment_post_ID] = get_the_title($comment->comment_post_ID);

					if(!isset($caches['avatar_placeholder']))
						$caches['avatar_placeholder'] = esc_url(theme_features::get_theme_images_url(theme_functions::$avatar_placeholder));

					
					?>
<li class="list-group-item">
	<a class="media" href="<?php echo $caches['permalinks'][$comment->comment_post_ID];?>#comment-<?php echo $comment->comment_ID;?>" title="<?php echo esc_attr($caches['post_title'][$comment->comment_post_ID]);?>">
		<div class="media-left">
			<img class="avatar media-object" data-src="<?php echo esc_url(get_avatar_url($comment));?>" src="<?php echo $caches['avatar_placeholder'];?>" alt="<?php echo $caches['author_name'][$comment->comment_author];?>" width="<?php echo self::$avatar_size;?>" height="<?php echo self::$avatar_size;?>"/>
		</div>
		<div class="media-body">
			<h4 class="media-heading">
				<span class="author"><?php echo $caches['author_name'][$comment->comment_author];?></span>
				<time datetime="<?php echo get_comment_time('c');?>">
					<small><?php echo friendly_date(get_comment_time('U'));?></small>
				</time>
			</h4>
			<div class="text"><?php comment_text();?></div>
		</div>
	</a>
</li>
				<?php } ?>
			</ul>
			<?php 
			$comment = $comment_bak;
		}else{ ?>
			<div class="panel-body">
				<div class="page-tip"><?php echo status_tip('info',___('No any comment yet.'));?></div>
			</div>
		<?php
		}
		echo $args['after_widget'];
	}
	function form($instance = []){
		$instance = array_merge(self::get_default_options(),$instance);
		?>
		<p>
			<label for="<?php echo self::get_field_id('title');?>"><?php echo ___('Title (optional)');?></label>
			<input 
				id="<?php echo self::get_field_id('title');?>"
				class="widefat"
				name="<?php echo self::get_field_name('title');?>" 
				type="text" 
				value="<?php echo $instance['title'];?>" 
				placeholder="<?php echo ___('Title (optional)');?>"
			/>
		</p>
		<p>
			<label for="<?php echo self::get_field_id('number');?>"><?php echo ___('Number');?></label>
			<input type="number" name="<?php echo self::get_field_name('number');?>" id="<?php echo self::get_field_id('number');?>" class="widefat" value="<?php echo (int)$instance['number'];?>"/>
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

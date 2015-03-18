<?php
/*
Feature Name:	widget_comments
Feature URI:	http://www.inn-studio.com
Version:		1.0.3
Description:	widget_comments
Author:			INN STUDIO
Author URI:		http://www.inn-studio.com
*/

add_action('widgets_init','widget_comments::register_widget');
class widget_comments extends WP_Widget{
	public static $iden = 'widget_comments';
	public static $avatar_size = 40;
	function __construct(){
		$this->alt_option_name = self::$iden;
		parent::__construct(
			self::$iden,
			___('Latest comments <small>(Custom)</small>'),
			array(
				'classname' => self::$iden,
				'description'=> ___('Show the latest comments'),
			)
		);
	}
	function widget($args,$instance){
		extract($args);
		echo $before_widget;
		$comments = get_comments(array(
			'status' => 'approve',
			'number' => isset($instance['number']) ? (int)$instance['number'] : 6
		));
		if(!empty($instance['title'])){
			echo $before_title;
			?>
			<span class="icon-comment"></span><span class="after-icon"><?php echo esc_html($instance['title']);?></span>
			<?php
			echo $after_title;
		}
		if(!empty($comments)){
			global $comment;
			$comment_bak = $comment;
			?>
			<ul class="lastest-comments">
				<?php foreach($comments as $comment){ ?>
					<li>
						<a href="<?php echo esc_url(get_permalink($comment->comment_post_ID));?>#comment-<?php echo $comment->comment_ID;?>" title="<?php echo esc_attr(get_the_title($comment->comment_post_ID));?>">
							<img class="avatar" data-original="<?php echo esc_url(get_img_source(get_avatar(get_comment_author_email())));?>" src="data:image/gif;base64,R0lGODlhAQABAIAAAMLCwgAAACH5BAAAAAAALAAAAAABAAEAAAICRAEAOw==" alt="<?php echo esc_attr(get_comment_author());?>" width="<?php echo self::$avatar_size;?>" height="<?php echo self::$avatar_size;?>"/>
							<div class="tx">
								<h3>
									<span class="author"><?php echo esc_html(get_comment_author());?></span>
									<time datetime="<?php echo esc_attr(get_comment_time('c'));?>">
										<small><?php echo esc_html(friendly_date(get_comment_time('U')));?></small>
									</time>
								</h3>
								<p class="comment-content" href="<?php echo esc_url(get_permalink($comment->comment_post_ID));?>"><?php comment_text();?></p>
							</div>
						</a>
					</li>
				<?php } ?>
			</ul>
			<?php 
			$comment = $comment_bak;
		}else{ ?>
			<div class="lastest-comments">
				<?php echo status_tip('info',esc_html(___('No any comment yet.')));?>
			</div>
		<?php
		}
		echo $after_widget;
	}
	function form($instance){
		$instance = wp_parse_args(
			(array)$instance,
			array(
				'title' => ___('Latest comments'),
				'number' => 6
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
			<label for="<?php echo esc_attr(self::get_field_id('number'));?>"><?php echo esc_html(___('Comments number'));?></label>
			<input type="number" name="<?php echo esc_attr(self::get_field_name('number'));?>" id="<?php echo esc_attr(self::get_field_id('number'));?>" class="widefat" value="<?php echo (int)$instance['number'];?>"/>
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

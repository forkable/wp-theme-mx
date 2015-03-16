<div class="panel panel-default">
	<div class="panel-heading">
		<h3 class="panel-title">
			<i class="fa fa-bell"></i>
			<?php echo ___('My notifications');?>
			<?php
			$unread = theme_notification::get_count(array(
				'type' => 'unread'
			));
			if($unread !== 0){
				echo "<span class='badge'>{$unread}</span>";
			}
			?>
		</h3>
	</div>

	<?php
	$notis = theme_notification::get_notifications(array(
		'user_id' => get_current_user_id(),
	));
	$unreads = theme_notification::get_notifications(array(
		'user_id' => get_current_user_id(),
		'type' => 'unread'
	));

	if(!empty($notis)){
		?>
		<ul class="list-group history-group">
		<?php
		foreach($notis as $k => $v){
			/**
			 * Check the noti is read or unread
			 */
			if(isset($v['id']) && isset($unreads[$v['id']])){
				$unread_class = ' unread ';
			}else{
				$unread_class = null;
			}
			?>
			<li class="list-group-item type-<?php echo $v['type'];?> <?php echo $unread_class;?>">
			<?php
			switch($v['type']){
				
				/****************************************
				 * special-event
				 */
				case 'special-event':
?>
<div class="media">
	<div class="media-left">
		<i class="fa fa-bullhorn"></i>
	</div>
	<div class="media-body">
		<h4 class="media-heading"><?php echo ___('Special event');?></h4>
		<?php echo sprintf(___('A special event happend: %s'),$v['event']);?>
	</div>
</div>
					<?php
					break;
				/****************************************
				 * post-reply
				 */
				case 'post-reply':
$comment = get_comment($v['comment-id']);
?>
<div class="media">
	<div class="media-left">
		<a href="<?php comment_author_url();?>">
		<img src="<?php echo esc_url(get_img_source(get_avatar($comment->user_id)));?>" class="avatar media-object" alt="avatar" width="60" height="60">
		</a>
	</div>
	<div class="media-body">
		<h4 class="media-heading">
			<?php
			echo sprintf(
				___('Your post %1$s has a comment by %2$s.'),
				'<a href="' . esc_url(get_permalink($comment->comment_post_ID)) . '">' . esc_html(get_the_title($comment->comment_post_ID)) . '</a>',
				get_comment_author_link()
			);
			?>
		</h4>
		<div class="excerpt"><?php comment_text($v['comment-id']);?></div>
	</div><!-- /.media-body -->
</div><!-- /.media -->
					<?php
					break;
				/****************************************
				 * comment-reply
				 */
				case 'comment-reply':
$comment = get_comment($v['comment-id']);
$parent_comment = get_comment($comment->comment_parent);
?>
<div class="media">
	<div class="media-left">
		<a href="<?php echo comment_author_url();?>">
		<img src="<?php echo esc_url(get_img_source(get_avatar($comment->user_id)));?>" class="avatar media-object" alt="avatar" width="60" height="60">
		</a>
	</div>
	<div class="media-body">
		<h4 class="media-heading">
		<?php
		echo sprintf(
			___('Your comment %1$s has a reply by %2$s in %3$s.'),
			'<a class="excerpt" href="' . esc_url(get_comment_link($parent_comment)) . '">
				' . esc_html(get_comment_excerpt()) . '
			</a>',
			get_comment_author_link(),
			'<a href="' . esc_url(get_permalink($comment->comment_post_ID)) . '">
				' . esc_html(get_the_title($comment->comment_post_ID)) . '
			</a>'
		);
		?>
		</h4>
		<div class="excerpt"><?php comment_text($v['comment-id']);?></div>
	</div><!-- /.media-body -->
</div><!-- /.media -->
					<?php
					break;
				/****************************************
				 * follow
				 */
				case 'follow':
$follower_id = $v['follower-id'];
?>
<div class="media">
	<div class="media-left">
		<a href="<?php comment_author_url();?>">
			<img src="<?php echo esc_url(get_img_source(get_avatar($follower_id)));?>" class="avatar media-object" alt="avatar" width="60" height="60">
		</a>
	</div>
	<div class="media-body">
		<h4 class="media-heading">
		<?php
		echo sprintf(
			___('%s is following you.'),
			get_comment_author_link()
		);
		?>
		</h4>
	</div><!-- /.media-body -->
</div><!-- /.media -->
<?php

				
					break;
				/****************************************
				 * post-publish
				 */
				case 'post-publish':
$post = get_post($v['post-id']);

?>
<div class="media">
	<div class="media-left">
		<a href="<?php comment_author_url();?>">
			<img src="<?php echo esc_url(get_img_source(get_avatar($post->post_author)));?>" class="avatar media-object" alt="avatar" width="60" height="60">
		</a>
	</div>
	<div class="media-body">
		<h4 class="media-heading">
		<?php
		echo sprintf(
		___('%s published a post %s.'),
		$author_html,
		'<a href="' . get_permalink($post->ID) . '">' . esc_html(get_the_title($post->ID)) . '</a>'
		);
		?>
		</h4>
		<div class="excerpt"><?php get_the_excerpt()?></div>
	</div><!-- /.media-body -->
</div><!-- /.media -->
<?php
$post = $post_bk;					
				default:
			}
			?>
			</li><!-- /.list-group-item -->
			<?php
		}
		?>
	</ul>
	<?php
	}else{
		?>
		<div class="panel-body">
			<div class="page-tip"><?php echo status_tip('info',___('You have not any notification yet'));?></div>
		</div>
		<?php
	}
	?>
</div><!-- /.panel -->
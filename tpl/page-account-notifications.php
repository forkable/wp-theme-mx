<div class="panel panel-default">
	<div class="panel-heading">
		<h3 class="panel-title">
			<i class="fa fa-bell"></i>
			<?= ___('My notifications');?>
			<?php
			$unread = theme_notification::get_count(array(
				'type' => 'unread'
			));
			if($unread !== 0){
				?>
				<span class='badge'><?= $unread;?></span>
				<?php
			}
			?>
		</h3>
	</div>

	<?php
	$current_user_id = get_current_user_id();
	$notis = theme_notification::get_notifications(array(
		'user_id' => $current_user_id,
	));
	$unreads = theme_notification::get_notifications(array(
		'user_id' => $current_user_id,
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
			<li class="list-group-item type-<?= $v['type'];?> <?= $unread_class;?>">
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
		<h4 class="media-heading">
			<?= ___('Special event');?>
			<?php
			if($v['point'] > 0){
				$tip_type = 'success';
				$sign = '+';
			}else{
				$tip_type = 'danger';
				$sign = '';
			}
			?>
			<span class="label label-<?= $tip_type;?>">
			<?= theme_custom_point::get_point_name();?>
			<?= $sign,$v['point'];?></label>
		</h4>
		<div class="excerpt"><p><?= $v['event'];?></p></div>
	</div>
</div>
					<?php
					break;
				/****************************************
				 * post-reply
				 */
				case 'post-reply':
$comment = theme_notification::get_comment($v['comment-id']);
?>
<div class="media">
	<div class="media-left">
		<a href="<?php comment_author_url();?>">
		<img src="<?= esc_url(get_avatar_url($comment->user_id));?>" class="avatar media-object" alt="avatar" width="60" height="60">
		</a>
	</div>
	<div class="media-body">
		<h4 class="media-heading">
			<?php
			echo sprintf(
				___('Your post %1$s has a comment by %2$s.'),
				'<a href="' . esc_url(get_permalink($comment->comment_post_ID)) . '#comment-' . $comment->comment_ID . '">' . esc_html(get_the_title($comment->comment_post_ID)) . '</a>',
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
$comment = theme_notification::get_comment($v['comment-id']);
$parent_comment = theme_notification::get_comment($comment->comment_parent);
?>
<div class="media">
	<div class="media-left">
		<a href="<?= comment_author_url();?>">
			<img src="<?= theme_features::get_theme_images_url('frontend/avatar.jpg');?>" data-src="<?= esc_url(get_avatar_url($comment->user_id));?>" class="avatar media-object" alt="avatar" width="60" height="60">
		</a>
	</div>
	<div class="media-body">
		<h4 class="media-heading">
		<?php
		echo sprintf(
			___('Your comment has a reply by %1$s in %2$s.'),
			get_comment_author_link(),
			'<a href="' . esc_url(get_permalink($comment->comment_post_ID)) . '#comment-' . $comment->comment_ID . '">
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
			<img src="<?= esc_url(get_avatar_url($follower_id));?>" class="avatar media-object" alt="avatar" width="60" height="60">
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
$post = theme_notification::get_post($v['post-id']);

?>
<div class="media">
	<div class="media-left">
		<a href="<?php comment_author_url();?>">
			<?= get_avatar($post->post_author,60);?>
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
			<div class="page-tip"><?= status_tip('info',___('You have not any notification yet'));?></div>
		</div>
		<?php
	}
	?>
</div><!-- /.panel -->
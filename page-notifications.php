<?php
/**
 * Template name: Notifications
 */
?>
<?php get_header();?>
<div class="container grid-container">
	<h3 class="crumb-title">
		<?php echo ___('My notifications');?>
		<?php
		$unread = theme_notification::get_count(array(
			'user_id' => get_current_user_id(),
			'type' => 'unread'
		));
		if($unread !== 0){
			echo " ( $unread )";
		}
		?>
	</h3>
	<div class="row">
		<div id="main" class="main col-md-9 col-sm-12">
			<div class="panel panel-default">
				<div class="panel-body">
					<?php
					$notis = theme_notification::get_notifications(array(
						'user_id' => get_current_user_id(),
					));
					if(!empty($notis)){
						foreach($notis as $k => $v){
							?>
							<li class="list-group-item">
							<?php
							switch($v['type']){
								case 'post_reply':
$comment = get_comment($v['comment_id']);

$author_url = class_exists('theme_custom_author_profile') ? theme_custom_author_profile::get_tabs('profile',$comment->user_id) : get_author_posts_url($comment->user_id);
$author_url = empty($author_url) ? 'javascript:void(0);' : esc_url($author_url);

$author_html = '<a href="' . $author_link . '">' . esc_html(get_comment_author($v['comment_id'])) . '</a>';
?>
<div class="media">
	<div class="media-left">
		<a href="<?php echo $author_link;?>">
			<img src="<?php echo esc_url(get_img_source(get_avatar($comment->user_id)));?>" class="avatar media-object" alt="avatar" width="60" height="60">
		</a>
	</div>
	<div class="media-body">
		<h4 class="media-heading">
			<?php
			echo sprintf(
				___('Your post %s has a comment by %s.'),
				'<a href="' . get_permalink($comment->comment_post_ID) . '">' . esc_html(get_the_title($comment->comment_post_ID)) . '</a>',
				$author_html
			);
			?>
		</h4>
		<div class="excerpt"><?php comment_text($v['comment_id']);?></div>
	</div><!-- /.media-body -->
</div><!-- /.media -->
									<?php
									break;
								case 'comment_reply':
$comment = get_comment($v['comment_id']);

$author_url = class_exists('theme_custom_author_profile') ? theme_custom_author_profile::get_tabs('profile',$comment->user_id) : get_author_posts_url($comment->user_id);
$author_url = empty($author_url) ? 'javascript:void(0);' : esc_url($author_url);

$author_html = '<a href="' . $author_link . '">' . esc_html(get_comment_author($v['comment_id'])) . '</a>';
?>
<div class="media">
	<div class="media-left">
		<a href="<?php echo $author_url;?>">
			<img src="<?php echo esc_url(get_img_source(get_avatar($comment->user_id)));?>" class="avatar media-object" alt="avatar" width="60" height="60">
		</a>
	</div>
	<div class="media-body">
		<h4 class="media-heading">
			<?php
			echo sprintf(
				___('Your comment %s has a reply by %s.'),
				'<a href="' . get_permalink($comment->comment_post_ID) . '">' . esc_html(get_the_title($comment->comment_post_ID)) . '</a>',
				$author_html
			);
			?>
		</h4>
		<div class="excerpt"><?php comment_text($v['comment_id']);?></div>
	</div><!-- /.media-body -->
</div><!-- /.media -->
									<?php
									break;
								case 'follow':
$follower_id = $v['follower_id'];
$author_link = class_exists('theme_custom_author_profile') ? theme_custom_author_profile::get_tabs('profile',$follower_id) : get_author_posts_url($follower_id);
$author_link = esc_url($author_link);

$author_html = '<a href="' . $author_link . '">' . esc_html(get_comment_author($v['comment_id'])) . '</a>';

?>
<div class="media">
	<div class="media-left">
		<a href="<?php echo $author_link;?>">
			<img src="<?php echo esc_url(get_img_source(get_avatar($follower_id)));?>" class="avatar media-object" alt="avatar" width="60" height="60">
		</a>
	</div>
	<div class="media-body">
		<h4 class="media-heading">
			<?php
			echo sprintf(
				___('%s is following you.'),
				$author_html
			);
			?>
		</h4>
	</div><!-- /.media-body -->
</div><!-- /.media -->
<?php

								
									break;
								/**
								 * post-publish
								 */
								case 'post-publish':
$post = get_post($v['post_id']);
$author_url = class_exists('theme_custom_author_profile') ? theme_custom_author_profile::get_tabs('profile',$post->post_author) : get_author_posts_url($post->post_author);
$author_url = empty($author_url) ? 'javascript:void(0);' : esc_url($author_url);

$author_html = '<a href="' . $author_link . '">' . esc_html(get_the_author_meta('display_name',$post->post_author)) . '</a>';
?>
<div class="media">
	<div class="media-left">
		<a href="<?php echo $author_link;?>">
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
					}else{
						echo status_tip('info',___('You have not any notification yet'));
					}
					?>
				</div><!-- /.panel-body -->
			</div><!-- /.panel -->
		</div><!-- /.main.col-->
		<?php get_sidebar();?>
	</div><!-- /.row -->
</div>
<?php get_footer();?>
<?php

$comments = get_comments(array(
	'user_id' => $author,
	'number' => 50
));
?>
	<?php if(empty($comments)){ ?>
		<div class="panel-body">
			<div class="page-tip"><?php echo status_tip('info',___('No comment yet.')); ?></div>
		</div>
		<?php 
	}else{ 
		global $comment;
		?>
		<ul class="list-group">
			<?php foreach($comments as $comment){ ?>
				<li class="list-group-item">
					<div class="media">
						<div class="media-left">
							<a href="<?php echo get_permalink($comment->comment_post_ID);?>">
								<?php the_post_thumbnail();?>
							</a>
						</div>
						<div class="media-body">
							<h4 class="media-heading">
								<?php 
								echo sprintf(
									___('%1$s published a comment in %2$s.'),
									get_comment_author_link(),
									'<a href="' . get_permalink($comment->comment_post_ID) . '">' . esc_html(get_the_title($comment->comment_post_ID)) . '</a>'
								);
								?>
							</h4>
							<div class="excerpt">
								<?php comment_text();?>
							</div>
						</div>
					</div>
				</li>
			<?php } ?>
		</ul>
	<?php } ?>

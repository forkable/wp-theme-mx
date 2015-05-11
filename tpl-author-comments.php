<?php

$comments = get_comments(array(
	'user_id' => $author,
	'number' => 50
));
?>
	<?php if(empty($comments)){ ?>
		<div class="panel-body">
			<div class="page-tip"><?= status_tip('info',___('No comment yet.')); ?></div>
		</div>
		<?php 
	}else{ 
		global $comment,$post;;
		?>
		<ul class="list-group">
			<?php 
			foreach($comments as $comment){
				$post = get_post($comment->comment_post_ID);
				$thumbnail_real_src = theme_functions::get_thumbnail_src($post->ID);
				?>
				<li class="list-group-item">
					<div class="media">
						<div class="media-left">
							<a href="<?= get_permalink();?>">
								<img class="post-list-img" src="<?= theme_features::get_theme_images_url(theme_functions::$thumbnail_placeholder);?>" data-src="<?= esc_url($thumbnail_real_src);?>" alt="<?= esc_attr(get_the_title());?>" width="80" height="50"/>
							</a>
						</div>
						<div class="media-body">
							<h4 class="media-heading">
								<?php 
								echo sprintf(
									___('%1$s published a comment in %2$s.'),
									get_comment_author_link(),
									'<a href="' . get_permalink() . '">' . esc_html(get_the_title()) . '</a>'
								);
								?>
							</h4>
							<div class="excerpt">
								<?php comment_text();?>
							</div>
						</div>
					</div>
				</li>
				<?php 
			}/** end foreach comment */
			wp_reset_postdata();
			?>
		</ul>
	<?php } ?>

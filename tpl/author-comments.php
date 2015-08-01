<?php
global $post;
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
				$post = theme_cache::get_post($comment->comment_post_ID);
				$thumbnail_real_src = theme_functions::get_thumbnail_src($post->ID);
				?>
				<li class="list-group-item">
					<div class="media">
						<div class="media-left">
							<a href="<?= theme_cache::get_permalink($post->ID);?>">
								<img class="post-list-img" src="<?= theme_features::get_theme_images_url(theme_functions::$thumbnail_placeholder);?>" data-src="<?= esc_url($thumbnail_real_src);?>" alt="<?= theme_cache::get_the_title($post->ID);?>" width="80" height="50"/>
							</a>
						</div>
						<div class="media-body">
							<div class="media-heading">
								<?php 
								echo sprintf(
									___('Published a comment in %1$s.'),
									'<a href="' . theme_cache::get_permalink($post->ID) . '">' . theme_cache::get_the_title($post->ID) . '</a>'
								);
								?>
							</div>
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

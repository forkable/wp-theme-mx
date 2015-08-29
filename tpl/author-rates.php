<div class="mod-body">
	<?php
	global $wp_query, $author, $post;
	
	if(class_exists('custom_post_point')){
		$page = (int)get_query_var('page');
		if(!$page || $page <= 0)
			$page = 1;
	
		query_posts([
			'author' => 0,
			'posts_per_page' => 120,
			'page' => $page,
			'post__in' => custom_post_point::get_user_post_ids($author),
		]);
		
		if($wp_query->have_posts()){
			?>
			<ul class="row post-img-lists">
				<?php
				foreach($wp_query->posts as $post){
					setup_postdata($post);
					theme_functions::archive_stick_content(array(
						'classes' => array('col-sm-4 col-md-3')
					));
				}
				?>
			</ul>
			<?php
		}else{
			?>
			<div class="page-tip"><?= status_tip('info',___('No post yet.')); ?></div>
			<?php 
		}
	}else{
		?>
		<div class="page-tip"><?= status_tip('error',___('Lack custom_post_point class'));?></div>
		<?php
	}
	?>
</div>
<?php 
if($GLOBALS['wp_query']->max_num_pages > 1){
	echo theme_functions::pagination();
} 
wp_reset_query();
?>
</div>
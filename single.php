<?php get_header();?>
<div class="container grid-container">
	<div class="row">
		<?php
		if(have_posts()){
			while(have_posts()){
				the_post();
				?>
				<div id="main" class="main col-md-8 col-sm-12">
					<?php theme_functions::singular_content();?>
					<div class="panel panel-default np-posts">
						<div class="panel-body">
							<?php theme_functions::the_post_pagination();?>
						</div>
					</div>
					<?php theme_functions::the_related_posts_plus();?>
					<?php comments_template();?>
				</div>
				<?php include __DIR__ . '/sidebar-post.php';?>
			<?php 
			}
		}else{ 
			?>
			
		<?php } ?>
	</div>
</div>
<?php get_footer();?>
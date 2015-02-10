<?php get_header();?>
<div class="container">
	<?php echo theme_functions::get_crumb();?>
	<div class="row">
		<div id="main" class="col-md-9 col-sm-12">
			<div class="panel panel-default mx-panel">
				<div class="panel-heading">
					<h3 class="panel-title">
						<i class="fa fa-folder-open"></i> 
						<?php echo sprintf(___('Current browsing: %s'),'<a href="' . get_category_link(theme_features::get_current_cat_id()) . '"><strong>' . single_cat_title(null,false) . '</strong></a>');?>
					</h3>
				</div>
				<div class="panel-body row mx-card-body post-mixed-list-container">
					<?php
					if(have_posts()){
						while(have_posts()){
							the_post();
							theme_functions::archive_content(array(
								'classes' => array('col-lg-6 col-md-6 col-sm-12 col-xs-12')
							));
						}
						?>
						<div class="area-pagination">
							<?php echo theme_functions::get_post_pagination('posts-pagination posts-pagination-bottom');?>
						</div>
					<?php }else{ ?>

					<?php } ?>
				</div>
			</div>
		</div><!-- /#main -->
		<?php get_sidebar() ;?>
	</div>
	
	<?php get_sidebar();?>
</div>
<?php get_footer();?>
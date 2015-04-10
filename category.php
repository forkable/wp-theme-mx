<?php get_header();?>
<div class="container">
	<?php echo theme_functions::get_crumb();?>
	<div class="row">
		<div id="main" class="col-md-9 col-sm-12">
			<div class="panel panel-default mx-panel">
				<div class="panel-heading">
					<h3 class="panel-title">
						<i class="fa fa-folder-open"></i> 
						<a href="<?php echo get_category_link(theme_features::get_current_cat_id());?>"><?php echo  single_cat_title(null,false);?></a>
					</h3>
				</div>
				<div class="panel-body">
					<?php
					if(have_posts()){
						?>
						<ul class="row mx-card-body post-img-lists">
							<?php
							while(have_posts()){
								the_post();
								theme_functions::archive_img_content(array(
									'classes' => array('col-lg-3 col-md-4 col-xs-6')
								));
							}
							?>
						</ul>
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
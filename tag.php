<?php get_header();?>
<div class="container">
	<?= theme_functions::get_crumb();?>
	<div class="row">
		<div id="main" class="col-md-9 col-sm-12">
			<div class="panel panel-default mx-panel">
				<div class="panel-heading">
					<h3 class="panel-title">
						<i class="fa fa-tag"></i> 
						<?php single_tag_title();?>
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
					<?php }else{ ?>

					<?php } ?>
				</div>
				<div class="panel-footer area-pagination">
					<?php theme_functions::pagination();?>
				</div>
			</div>
		</div><!-- /#main -->
		<?php get_sidebar() ;?>
	</div>
	
	<?php get_sidebar();?>
</div>
<?php get_footer();?>
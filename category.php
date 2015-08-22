<?php get_header();?>
<div class="container">
	<?= theme_functions::get_crumb();?>
	<div id="main">
		<div class="mod">
			<div class="mod-body">
				<?php
				if(have_posts()){
					?>
					<ul class="row mx-card-body post-img-lists">
						<?php
						$loop_i = 0;
						foreach($wp_query->posts as $post){
							setup_postdata($post);
							theme_functions::archive_mixed_content(array(
								'classes' => array('col-lg-3 col-md-4'),
								'lazyload' => $loop_i <= 8 ? false : true,
							));
							++$loop_i;
						}
						?>
					</ul>
				<?php }else{ ?>
					<?= status_tip('info',___('No content yet.'));?>
				<?php } ?>
			</div>
		</div>
		<div class="area-pagination">
			<?php theme_functions::pagination();?>
		</div>
	</div><!-- /#main -->
</div><!-- /.container -->
<?php get_footer();?>
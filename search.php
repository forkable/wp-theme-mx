<?php get_header();?>
<div class="container">
	<?= theme_functions::get_crumb();?>
	<div id="main">
		<div class="mod">
			<form class="mod-heading" action="<?= theme_cache::home_url('/'); ?>">
				<div class="input-group input-group-lg">
					<label class="input-group-addon" for="search-page-s"><i class="fa fa-search"></i> </label>
					<input type="search" id="search-page-s" name="s" class="form-control" value="<?= esc_attr(get_search_query());?>" placeholder="<?= ___('Search keywords');?>">
				</div>
			</form>
			<div class="mod-body">
				<?php
				if(have_posts()){
					?>
					<ul class="row post-img-lists">
						<?php
						$loop_i = 0;
						foreach($wp_query->posts as $post){
							setup_postdata($post);
							theme_functions::archive_stick_content(array(
								'classes' => array('col-lg-3 col-md-4 col-xs-6'),
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
			<div class="mod-footer area-pagination">
				<?php theme_functions::pagination();?>
			</div>
		</div>
	</div><!-- /#main -->
</div><!-- /.container -->
<?php get_footer();?>
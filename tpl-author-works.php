<div class="panel-body">
	<?php
	if(have_posts()){
		?>
		<ul class="row mx-card-body post-img-lists">
			<?php
			while(have_posts()){
				the_post();
				theme_functions::archive_img_content(array(
					'classes' => array('col-xs-6 col-sm-3 col-md-2')
				));
			}
			?>
		</ul>
	<?php }else{ ?>
		<div class="page-tip"><?= status_tip('info',___('No post yet.')); ?></div>
	<?php } ?>
</div>
<div class="panel-footer">
	<div class="area-pagination">
		<?php 
		//echo theme_functions::get_post_pagination(
			//'posts-pagination posts-pagination-bottom pagination');
		echo theme_functions::pagination();
		?>
	</div>
</div>
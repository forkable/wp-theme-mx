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
<?php if($GLOBALS['wp_query']->max_num_pages > 1){ ?>
	<div class="panel-footer">
		<?= theme_functions::pagination();?>
	</div>
<?php } ?>
</div>
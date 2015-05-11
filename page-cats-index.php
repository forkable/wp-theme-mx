<?php
/**
 * Template name: Tags index
 */
?>
<?php get_header();?>
<div class="container grid-container">
	<div class="content-reset">
		<?php the_content();?>
	</div>
	<div class="cats-index">
		<?php if(!class_exists('theme_page_cats')){ ?>
			<div class="page-tip"><?= status_tip('error',___('Missing initialization files.'));?></div>
		<?php }else{ ?>
			<?php theme_page_cats::display_frontend();?>
		<?php } ?>
	</div>
</div><!-- /.container -->
<?php get_footer();?>
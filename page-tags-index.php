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
	<div class="tags-index">
		<?php if(!class_exists('theme_page_tags')){ ?>
			<div class="page-tip"><?= status_tip('error',___('Missing initialization files.'));?></div>
		<?php }else{ ?>
			<?php theme_page_tags::display_frontend();?>
		<?php } ?>
	</div>
</div><!-- /.container -->
<?php get_footer();?>
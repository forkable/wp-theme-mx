<?php get_header();?>
<div class="container grid-container">
	<?php echo theme_functions::get_crumb();?>
	<div id="main" class="main grid-70 tablet-grid-70 mobile-grid-100">
		<?php theme_functions::page_content();?>
		<div class="hide quick-comment" data-post-id="<?php the_ID();?>"></div>
		<?php theme_quick_comment::frontend_display();?>
	</div>
	<?php get_sidebar();?>
</div>
<?php get_footer();?>
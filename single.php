<?php get_header();?>
<div class="container grid-container">
	<div class="row">
		<div id="main" class="main col-md-9 col-sm-12">
			<?php theme_functions::singular_content();?>
			<!-- related posts -->
			<?php theme_functions::the_related_posts_plus();?>
			<?php comments_template();?>
			<?php
			/** 
			 * theme_quick_comment
			 */
			//theme_quick_comment::frontend_display();
			?>

		</div>
		<?php get_sidebar();?>
	</div>
</div>
<?php get_footer();?>
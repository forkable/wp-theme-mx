<?php get_header();?>
<div class="container grid-container">
	<div class="row">
		<div id="main" class="main col-md-8 col-sm-12">
			<?php theme_functions::singular_content();?>
			<?php theme_functions::the_related_posts_plus();?>
			<?php comments_template();?>
		</div>
		<?php get_sidebar('post');?>
	</div>
</div>
<?php get_footer();?>
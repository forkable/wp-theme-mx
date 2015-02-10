<?php get_header();?>
<div class="container grid-container">
	<div class="row">
		<div id="main" class="main col-md-9 col-sm-12">
			<?php theme_functions::singular_content();?>
			<?php comments_template();?>
		</div>
		<?php get_sidebar();?>
	</div>
</div>
<?php get_footer();?>
<?php get_header();?>
<div class="container">
	<?php if(class_exists('theme_custom_slidebox')){ ?>
		<div class="slidebox-container col-sm-8">
			<?php theme_custom_slidebox::display_frontend();?>
		</div>
	<?php } ?>
</div>
<?php get_footer();?>
<?php get_header();?>
<?php 
if(class_exists('theme_custom_slidebox')){
	if(!theme_custom_slidebox::display_frontend()){
		?><div class="page-tip"><?= status_tip('info',___('Please set some slidebox posts.'));?></div>
	<?php 
	}
} 
?>
<div class="container">

	<?php if(!wp_is_mobile()){ ?>
		<div class="recomm-container">
			<?php
			if(method_exists('theme_functions','the_recommended')){
				theme_functions::the_recommended();
			}
			?>
		</div>
	<?php } ?>

	<div class="row">
		<div id="main" class="col-md-9 col-sm-12">
			
			<?php theme_functions::the_homebox();?>
			
		</div><!-- /#main -->
		<?php get_sidebar() ;?>
	</div>
	
</div>
<?php get_footer();?>

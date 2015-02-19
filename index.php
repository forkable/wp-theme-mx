<?php get_header();?>
<div class="container">
	<div class="panel panel-default">
		<div class="mx-card-body row neck">
			<div class="col-md-6 hidden-xs">
				<div class="slidebox-container">
					<?php if(class_exists('theme_custom_slidebox')){ ?>
						<?php theme_custom_slidebox::display_frontend();?>
					<?php } ?>
				</div>
			</div>
			<div class="col-md-6">
				<div class="recomm-container">
					<?php
					if(method_exists('theme_functions','the_recommended')){
						theme_functions::the_recommended();
					}
					?>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div id="main" class="col-md-9 col-sm-12">
			
			<?php theme_functions::the_homebox();?>
			
		</div><!-- /#main -->
		<?php get_sidebar() ;?>
	</div>
	
</div>
<?php get_footer();?>
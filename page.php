<?php get_header();?>
<div class="container grid-container">
	<div class="row">
		<div id="main" class="main col-md-9 col-sm-12">
			<?php 
			if(have_posts()){
				while(have_posts()){
					the_post();
					theme_functions::page_content();
					comments_template();
				}
			}
			?>
		</div>
		<?php include __DIR__ . '/sidebar.php';?>
	</div>
</div>
<?php get_footer();?>
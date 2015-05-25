<?php
/**
 * set home page cache without user logged
 */
if(!is_user_logged_in()){
	$cache = wp_cache_get('home-visitor');
	if($cache){
		echo $cache;
		return;
	}
	ob_start();
}

?>
<?php get_header();?>
<div class="container">
	<?php
	/**
	 * slidebox
	 */
	if(!theme_features::is_mobile()){
		?>
		<div class="panel panel-default hidden-xs">
			<div class="mx-card-body row neck">
				<div class="col-md-6 hidden-xs">
					<div class="slidebox-container">
						<?php 
						if(class_exists('theme_custom_slidebox')){
							if(!theme_custom_slidebox::display_frontend()){
								?><div class="page-tip"><?= status_tip('info',___('Please set some slidebox posts.'));?></div>
							<?php 
							}
						} 
						?>
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
	<?php } ?>
	<div class="row">
		<div id="main" class="col-md-9 col-sm-12">
			
			<?php theme_functions::the_homebox();?>
			
		</div><!-- /#main -->
		<?php get_sidebar() ;?>
	</div>
	
</div>
<?php get_footer();?>

<?php
if(!is_user_logged_in()){
	$content = html_compress(ob_get_contents());
	ob_end_clean();
	wp_cache_set('home-visitor',$content,null,900);
	echo $content;
}
?>
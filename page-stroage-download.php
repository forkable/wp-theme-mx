<?php
/**
 * Template name: Storage download page
 */
if(!class_exists('theme_custom_storage'))
	die(___('Lacking the class theme_custom_storage'));


$target_post = theme_custom_storage::get_decode_post();
if(!$target_post)
	wp_redirect(home_url());
?>
<?php get_header();?>
<div class="container grid-container">
	<div class="panel panel-default singular-download">
		<div class="panel-heading">
			<h3 class="panel-title"><?php echo sprintf(___('%s storage download'),get_the_title($target_post->ID));?></h3>
		</div>
		<div class="panel-body">
			<div class="post-content content-reset">
				<?php the_content();?>
			</div>
			<?php
			if(isset($_GET['code']) && !empty($_GET['code'])){
				$decode = authcode($_GET['code'],'decode');
				
				if(!$decode){
					echo status_tip('error',___('Sorry, the code is wrong.'));
				}else{
					$decode = unserialize($decode);
					$post = get_post($decode['post-id']);
					
				}
			}else{
				echo status_tip('error',___('Sorry, the code is wrong.'));
			}
			?>
		</div>
	</div>
</div>
<?php get_footer();?>
<?php get_header();?>
<?php
//wp_cache_set('b',1);
wp_cache_incr('b',1);
var_dump(wp_cache_get('b'));
//var_dump(wp_cache_get(get_the_ID(),'theme_post_views'));
?>
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
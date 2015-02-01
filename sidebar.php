<div id="sidebar-container" class="grid-30 tablet-grid-30 mobile-grid-100">
<div id="sidebar" class="widget-area" role="complementary">
	<?php
	/** 
	 * home widget
	 */
	if(is_home() && !theme_cache::dynamic_sidebar('widget-area-home')){
		the_widget('WP_Widget_Recent_Posts');
		the_widget('WP_Widget_Tag_Cloud');		
		the_widget('WP_Widget_Meta');
		the_widget('WP_Widget_Recent_Comments');
	/** 
	 * archive widget
	 */
	}else if((is_category() || is_archive() || is_search()) && !theme_cache::dynamic_sidebar('widget-area-archive')){
		the_widget('WP_Widget_Recent_Posts');
		the_widget('WP_Widget_Tag_Cloud');		
		the_widget('WP_Widget_Meta');
		the_widget('WP_Widget_Recent_Comments');	
	/** 
	 * post widget
	 */
	}else if(is_singular('post') && !theme_cache::dynamic_sidebar('widget-area-post')){
		the_widget('WP_Widget_Recent_Posts');
		the_widget('WP_Widget_Tag_Cloud');		
		the_widget('WP_Widget_Meta');
		the_widget('WP_Widget_Recent_Comments');		
	/** 
	 * page widget
	 */
	}else if(is_page() && !theme_cache::dynamic_sidebar('widget-area-page')){
		the_widget('WP_Widget_Recent_Posts');
		the_widget('WP_Widget_Tag_Cloud');		
		the_widget('WP_Widget_Meta');
		the_widget('WP_Widget_Recent_Comments');		
	/** 
	 * 404 widget
	 */
	}else if(is_404() && !theme_cache::dynamic_sidebar('widget-area-404')){
		the_widget('WP_Widget_Recent_Posts');
		the_widget('WP_Widget_Tag_Cloud');		
		the_widget('WP_Widget_Meta');
		the_widget('WP_Widget_Recent_Comments');
	}
	?>
</div><!-- /.widget-area -->
</div><!-- /#sidebar-container -->
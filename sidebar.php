<div id="sidebar-container" class="col-md-3 col-sm-12">
<div id="sidebar" class="widget-area" role="complementary">
	<?php
	/** 
	 * home widget
	 */
	if(is_home() && !theme_cache::dynamic_sidebar('widget-area-home')){
		?>
		<div class="col-xs-12">
			<div class="panel panel-default">
				<div class="panel-body">
					<?php echo status_tip('info', ___('Please set some widgets in homepage.'));?>
				</div>
			</div>
		</div>
		<?php
	/** 
	 * archive widget
	 */
	}else if((is_category() || is_archive() || is_search()) && !theme_cache::dynamic_sidebar('widget-area-archive')){
		?>
		<div class="col-xs-12">
			<div class="panel panel-default">
				<div class="panel-body">
					<?php echo status_tip('info', ___('Please set some widgets in archive.'));?>
				</div>
			</div>
		</div>
		<?php
	/** 
	 * post widget
	 */
	}else if(is_singular('post') && !theme_cache::dynamic_sidebar('widget-area-post')){
		?>
		<div class="col-xs-12">
			<div class="panel panel-default">
				<div class="panel-body">
					<?php echo status_tip('info', ___('Please set some widgets in singluar post.'));?>
				</div>
			</div>
		</div>
		<?php
	/** 
	 * page widget
	 */
	}else if(is_page() && !theme_cache::dynamic_sidebar('widget-area-page')){
		?>
		<div class="col-xs-12">
			<div class="panel panel-default">
				<div class="panel-body">
					<?php echo status_tip('info', ___('Please set some widgets in singluar page.'));?>
				</div>
			</div>
		</div>
		<?php
	/** 
	 * 404 widget
	 */
	}else if(is_404() && !theme_cache::dynamic_sidebar('widget-area-404')){
		?>
		<div class="col-xs-12">
			<div class="panel panel-default">
				<div class="panel-body">
					<?php echo status_tip('info', ___('Please set some widgets in 404 page.'));?>
				</div>
			</div>
		</div>
		<?php
	}
	?>
</div><!-- /.widget-area -->
</div><!-- /#sidebar-container -->
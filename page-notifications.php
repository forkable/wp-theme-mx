<?php
/**
 * Template name: Notifications
 */
?>
<?php get_header();?>
<div class="container grid-container">
	<h3 class="crumb-title">
		<?php echo esc_html(get_the_author_meta('display_name',$author_id));?> - <?php echo $tabs[$tab_active]['text'];?>
	</h3>
	<div class="row">
		<div id="main" class="main col-md-9 col-sm-12">
			<div class="panel panel-default">
				<div class="panel-body">
					<?php
					
					?>
				</div><!-- /.panel-body -->
			</div><!-- /.panel -->
		</div><!-- /.main.col-->
		<?php get_sidebar();?>
	</div><!-- /.row -->
</div>
<?php get_footer();?>
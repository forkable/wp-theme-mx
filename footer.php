<footer id="footer" class="">
	<div class="container">
		<div class="widget-area row">
			<?php if(!theme_cache::dynamic_sidebar('widget-area-footer')){ ?>
				<div class="col-xs-12">
					<div class="panel">
						<div class="panel-body">
							<div class="page-tip">
								<?php echo status_tip('info', ___('Please set some widgets in footer.'));?>
							</div>
						</div>
					</div>
				</div>
			<?php } ?>
		</div>
		<p class="footer-meta copyright text-center">
				<?php echo sprintf(___('Copyright &copy; %s %s.'),'<a href="' . esc_url(home_url()) . '">' .esc_html(get_bloginfo('name')) . '</a>',esc_html(current_time('Y')));?>
				<?php echo sprintf(___('Theme %s by %s.'),'<a href="' . esc_url(theme_features::get_theme_info('ThemeURI')) . '" target="_blank" rel="nofollow">' . theme_features::get_theme_info('name') . '</a>','<a href="http://inn-studio.com" target="_blank" rel="nofollow">' . esc_html(___('INN STUDIO')) . '</a>');?>
				<?php echo sprintf(___('Powered by %s.'),'<a href="http://www.wordpress.org" target="_blank" rel="nofollow">WordPress</a>');?>
			</p>
	</div>
</footer>
	<?php wp_footer();?>
</body></html>
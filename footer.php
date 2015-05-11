<footer id="footer" class="">
	<div class="container">
		<?php if(!theme_features::is_mobile()){ ?>
			<div class="widget-area row hiddex-xs">
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
		<?php } ?>
		<p class="footer-meta copyright text-center">
				<?php echo sprintf(___('&copy; %s %s.'),'<a href="' . home_url() . '">' .get_bloginfo('name') . '</a>',date('Y'));?>
				<?php echo sprintf(___('Theme %s by %s.'),'<a href="' . theme_features::get_theme_info('ThemeURI') . '" target="_blank" rel="nofollow">' . theme_features::get_theme_info('name') . '</a>','<a href="http://inn-studio.com" target="_blank" rel="nofollow">' . ___('INN STUDIO') . '</a>');?>
			</p>
	</div>
</footer>
	<?php wp_footer();?>
</body></html>
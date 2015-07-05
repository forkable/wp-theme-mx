<footer id="footer" class="">
	<div class="container">
		<?php if(!wp_is_mobile()){ ?>
			<div class="widget-area row hiddex-xs">
				<?php if(!theme_cache::dynamic_sidebar('widget-area-footer')){ ?>
					<div class="col-xs-12">
						<div class="panel">
							<div class="panel-body">
								<div class="page-tip">
									<?= status_tip('info', ___('Please set some widgets in footer.'));?>
								</div>
							</div>
						</div>
					</div>
				<?php } ?>
			</div>
		<?php } ?>
		<p class="footer-meta copyright text-center">
			<?= sprintf(
				___('&copy; %1$s %2$s. Theme %3$s.'),
				'<a href="' . home_url() . '">' .get_bloginfo('name') . '</a>',
				date('Y'),
				'<a title="' . theme_features::get_theme_info('Version') . '" href="' . theme_features::get_theme_info('ThemeURI') . '" target="_blank" rel="nofollow">' . theme_features::get_theme_info('name') . '</a>'
			);?>

			<?= sprintf(___('CDN by %s'),'<a href="http://blog.eqoe.cn" target="_blank" rel="nofollow">eqoe</a>');?>
		</p>
	</div>
</footer>
<a href="#" class="back-to-top" title="<?= ___('Back to top');?>"><i class="fa fa-chevron-circle-up fa-3x"></i></a>
	<?php wp_footer();?>
</body></html>
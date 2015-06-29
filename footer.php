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
			<?= sprintf(___('&copy; %s %s.'),'<a href="' . home_url() . '">' .get_bloginfo('name') . '</a>',date('Y'));?>
			
			<?= sprintf(___('Theme %s by %s.'),'<a title="' . theme_features::get_theme_info('Version') . '" href="' . theme_features::get_theme_info('ThemeURI') . '" target="_blank" rel="nofollow">' . theme_features::get_theme_info('name') . '</a>','<a href="http://inn-studio.com" target="_blank" rel="nofollow" title="' . ___('Top WordPress developer') . '">' . ___('INN STUDIO') . '</a>');?>

			<?= sprintf(___('CDN by %s'),'<a href="http://blog.eqoe.cn" target="_blank" rel="nofollow">eqoe</a>');?>
		</p>
	</div>
</footer>
	<?php wp_footer();?>
</body></html>
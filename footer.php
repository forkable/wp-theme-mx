<footer id="footer">
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
			<?php
			if(class_exists('theme_user_code')){
				echo theme_user_code::get_frontend_footer_code();
			}
			?>
			<?= sprintf(___('CDN %s'),'<a href="http://blog.eqoe.cn" target="_blank" rel="nofollow">eqoe</a>');?>
		</p>
	</div>
</footer>
<a href="#" class="back-to-top" title="<?= ___('Back to top');?>"><i class="fa fa-arrow-up fa-3x"></i></a>
<?php wp_footer();?>
</body></html>
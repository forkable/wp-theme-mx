<?php
/**
 * Template name: Account
 */
$active_tab = get_query_var('tab');
if(!$active_tab)
	$active_tab = 'dashboard';
?>
<?php get_header();?>
<div class="container grid-container">
	<div class="row">
		<div id="account-navbar" class="col-sm-3 col-lg-2 hidden-xs">
			<div class="navbar navbar-default" role="navigation">
				<div class="navbar-collapse account-navbar-collapse collapse">
					<ul class="nav navbar-nav">
						<?php
						$account_navs = apply_filters('account_navs',[]);
						if(!empty($account_navs)){
							foreach($account_navs as $k => $v){
								$active_class = $k === $active_tab ? ' active ' : null;
								?>
								<li class="<?= $active_class;?>"><?= $v;?></li>
								<?php
							}
						}
						?>
					</ul>
				</div>
			</div><!-- /#account-navbar -->
		</div>
		<div class="col-sm-9 col-lg-10">
			<div id="account-content">
				<?php include __DIR__ . '/tpl/page-account-' . $active_tab . '.php';?>
			</div>
		</div>
	</div><!-- /.row -->
</div>
<?php get_footer();?>
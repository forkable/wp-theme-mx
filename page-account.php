<?php
/**
 * Template name: Account
 */
$active_tab = get_query_var('tab') ? get_query_var('tab') : 'dashboard';
?>
<?php get_header();?>
<div class="container grid-container">
	<h3 class="crumb-title">
		<?php apply_filters('account-crumb-title',___('Account'));?>
	</h3>
	<div class="row">
		<div id="account-navbar" class="col-sm-3 col-lg-2">
			<div class="navbar navbar-default" role="navigation">
				<div class="navbar-header">
					<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target=".account-navbar-collapse">
						<span class="sr-only"><?php echo ___('Account menu');?></span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
					</button>
					<span class="navbar-brand">
						<?php echo ___('Account menu');?> 
						<span class="hidden-xs"><i class="fa fa-caret-down"></i></span>
					</span>
				</div>

				<div class="navbar-collapse account-navbar-collapse collapse">
					<ul class="nav navbar-nav">
						<?php
						$account_navs = apply_filters('account_navs',array());
						if(!empty($account_navs)){
							foreach($account_navs as $k => $v){
								$active_class = $k === $active_tab ? ' active ' : null;
								?>
								<li class="<?php echo $active_class;?>"><?php echo $v;?></li>
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
				<?php get_template_part('page',"account-{$active_tab}"); ?>
				<?php //do_action('account_content_' . get_query_var('tab'));?>
			</div>
		</div>
	</div><!-- /.row -->
</div>
<?php get_footer();?>
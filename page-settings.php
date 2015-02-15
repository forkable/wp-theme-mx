<?php
/**
 * Template name: Settings
 */
$tabs = theme_custom_user_settings::get_tabs();
$tab_active = get_query_var('tab');
$tab_active = isset($tabs[$tab_active]) ? $tab_active : 'history';
?>
<?php get_header();?>
<div class="container grid-container">
	<h3 class="crumb-title">
		<?php echo ___('My settings');?>
	</h3>
	<div class="row">
		<div id="main" class="main col-md-9 col-sm-12">
			<div class="panel panel-default">
				<div class="panel-heading">
					<ul class=" nav nav-pills">
						<?php 
						foreach($tabs as $k => $v){
							$class_active = $tab_active === $k ? ' active ' : null;
							?>
							<li role="presentation" class="<?php echo $class_active;?>">
								<a href="<?php echo esc_url($v['url']);?>">
									<i class="fa fa-<?php echo esc_attr($v['icon']);?>"></i> 
									<span class="tx <?php echo $class_active ? null : 'hidden-xs';?>">
										<?php echo esc_html($v['text']);?>
									</span>
								</a>
							</li>
							<?php
						}
						?>
					</ul>
				</div>
				<div class="panel-body">
					<?php
					switch($tab_active){
						case 'history':
if()
							?>

							<?php
							break;
						case 'settings':
							break;
						case 'password':
							break;
						default:
						
					}
					?>
				</div><!-- /.panel-body -->
			</div><!-- /.panel -->
		</div><!-- /.main.col-->
		<?php get_sidebar();?>
	</div><!-- /.row -->
</div>
<?php get_footer();?>
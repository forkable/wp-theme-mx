<?php get_header();?>
<?php
global $author;
$tab_active = get_query_var('tab');

$tabs = theme_custom_author_profile::get_tabs();

if(empty($tab_active) || !isset($tabs[$tab_active]))
	$tab_active = 'profile';
	
?>
<div class="container">
	<h3 class="crumb-title">
		<?php echo esc_html(get_the_author_meta('display_name',$author));?> - <?php echo $tabs[$tab_active]['text'];?>
	</h3>
	<div class="row">
		<div id="main" class="col-md-9 col-sm-12">
			<div class="panel panel-default">
				<div class="panel-heading">
					<div class="btn-group btn-group-justified" role="group">
						<?php 
						foreach($tabs as $k => $v){
							$class_active = $tab_active === $k ? ' btn-primary ' : null;
							?>
							<a href="<?php echo esc_url($v['url']);?>" class="btn btn-default <?php echo $class_active;?>" role="button">
								<i class="fa fa-<?php echo esc_attr($v['icon']);?>"></i> 
								<span class="tx <?php echo $class_active ? null : 'hidden-xs';?>">
									<?php echo esc_html($v['text']);?>
								</span>
							</a>
						<?php } ?>					
					</div>
				</div>
				<?php get_template_part('tpl',"author-{$tab_active}"); ?>
			</div>
		</div><!-- /#main -->
		<?php get_sidebar() ;?>
	</div>
</div>
<?php get_footer();?>
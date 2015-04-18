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
		<?php echo get_avatar($author);?>
		<?php echo esc_html(get_the_author_meta('display_name',$author));?> - <?php echo $tabs[$tab_active]['text'];?>
	</h3>
	<div class="panel panel-default">
		<div class="panel-heading">
			<ul class="nav nav-pills nav-justified">
				<?php 
				foreach($tabs as $k => $v){
					$class_active = $tab_active === $k ? ' active ' : null;
					?>
					<li role="presentation" class="<?php echo $class_active;?>">
						<a href="<?php echo esc_url($v['url']);?>">
							<i class="fa fa-<?php echo $v['icon'];?> fa-fw"></i> 
							<?php echo $v['text'];?>
						</a>
					</li>
				<?php } ?>					
			</ul>
		</div>
		<?php include __DIR__ . "/tpl-author-{$tab_active}.php";?>
	</div>
</div>
<?php get_footer();?>
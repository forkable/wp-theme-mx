<?php get_header();?>
<?php
global $author;
$tab_active = get_query_var('tab');

$tabs = theme_custom_author_profile::get_tabs(null,$author);

if(empty($tab_active) || !isset($tabs[$tab_active]))
	$tab_active = 'profile';
	
?>
<div class="container">
	<h3 class="crumb-title">
		<?= get_avatar($author);?>
		<?= esc_html(get_the_author_meta('display_name',$author));?> - <?= $tabs[$tab_active]['text'];?>
	</h3>
	<div class="panel panel-default">
		<div class="panel-heading">
			<ul class="nav nav-pills nav-justified">
				<?php 
				foreach($tabs as $k => $v){
					$class_active = $tab_active === $k ? ' active ' : null;
					?>
					<li role="presentation" class="<?= $class_active;?>">
						<a href="<?= esc_url($v['url']);?>">
							<i class="fa fa-<?= $v['icon'];?> fa-fw"></i> 
							<?= $v['text'];?>
						</a>
					</li>
				<?php } ?>					
			</ul>
		</div>
		<?php include __DIR__ . "/tpl-author-{$tab_active}.php";?>
	</div>
</div>
<?php get_footer();?>
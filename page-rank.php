<?php
/**
 * template: Page rank
 */

get_header();


$active_tab = get_query_var('tab','recommend');

if(!theme_page_rank::get_tabs($active_tab))
	$active_tab = 'recommend';
	

?>
<div class="container">
	<div class="panel panel-default panel-rank">
		<div class="panel-heading">
			<ul class="nav nav-pills">
				<?php
				foreach(theme_page_rank::get_tabs() as $k => $v){
					$active_class = $active_tab === $k ? 'class="active"' : null;
					?>
					<li role="presentation" <?= $active_class;?> >
						<a href="<?= theme_page_rank::get_tabs($k)['url'];?>">
							<i class="fa fa-<?= theme_page_rank::get_tabs($k)['icon'];?>"></i> 
							<?= theme_page_rank::get_tabs($k)['tx'];?>
						</a>
					</li>
					<?php
				}
				?>
			</ul>
		</div>
		<?php
		$include_filepath = __DIR__ . '/tpl/page-rank-' . $active_tab . '.php';
		if(is_file($include_filepath)){
			include $include_filepath;
		}else{
			?>
			<div class="panel-body">
				<div class="page-tip">
					<?= status_tip('error',___('Can not find the include file.'));?>
				</div>
		</div>
			<?php
		}
		?>
	</div>
</div>
<?php get_footer();?>
<div class="panel-body">
	<div class="btn-group btn-group-sm tab-filter">
		<?php
		$active_filter_tab = get_query_var('filter');
		$filter_tabs = theme_page_rank::get_tabs('popular')['filters'];

		if(!isset($filter_tabs[$active_filter_tab]))
			$active_filter_tab = 'day';
			
		foreach($filter_tabs as $k => $v){
			$active_class = $active_filter_tab === $k ? 'active' : null;
			?>
			<a class="btn btn-default <?= $active_class;?>" href="<?= $v['url'];?>"><?= $v['tx'];?></a>
			<?php	
		}
		?>
	</div>
	<?php
	$posts_content = theme_page_rank::get_popular_posts();
	if(empty($posts_content)){
		?>
		<div class="page-tip"><?= status_tip('info',___('No data yet.'));?></div>
		<?php
	}
	?>
</div>

<?= $posts_content;?>

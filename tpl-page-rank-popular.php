<div class="panel-body">
	<div class="btn-group btn-group-sm tab-filter">
		<?php
		$active_filter_tab = get_query_var('filter');
		$filter_tabs = theme_page_rank::get_tabs('popular')['filter'];
		var_dump($active_filter_tab);
		if(!isset($filter_tabs[$active_filter_tab]))
			$active_filter_tab = 'day';
			
		foreach($filter_tabs as $k => $v){
			$active_class = $active_filter_tab === $k ? 'active' : null;
			var_dump($active_filter_tab);
			?>
			<a class="btn btn-default <?= $active_class;?>" href="<?= $v['url'];?>"><?= $v['tx'];?></a>
			<?php	
		}
		?>
	</div>
</div>
<?php
theme_page_rank::the_popular_posts();
?>

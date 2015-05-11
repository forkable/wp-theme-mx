<div class="panel panel-default">
	<div class="panel-heading">
		<h3 class="panel-title">
			<i class="fa fa-<?= theme_custom_dashboard::get_tabs('dashboard')['icon'];?>"></i>
			<?= theme_custom_dashboard::get_tabs('dashboard')['text'];?>
		</h3>
	</div>
	<div class="panel-body">
		<div class="row">
			<?php 
			$dashboards = array(
				'left' => 'col-md-4 col-lg-4',
				'right' => 'col-md-8 col-lg-8',
				
			);
			foreach($dashboards as $k => $v){ ?>
				<div class="account-dashboard account-dashboard-<?= $k;?> <?= $v;?>">
					<?php do_action("account_dashboard_{$k}");?>
				</div>
			<?php } ?>
		</div>
	</div>
</div>
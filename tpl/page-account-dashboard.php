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
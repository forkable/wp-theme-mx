<div class="panel panel-default">
	<div class="panel-heading">
		<h3 class="panel-title">
			<i class="fa fa-bell"></i>
			<?= ___('My notifications');?>
			<?php
			$unread = theme_notification::get_count(array(
				'type' => 'unread'
			));
			if($unread !== 0){
				?>
				<span class='badge'><?= $unread;?></span>
				<?php
			}
			?>
		</h3>
	</div>

	<?php
	$current_user_id = theme_cache::get_current_user_id();
	$notis = theme_notification::get_notifications(array(
		'user_id' => $current_user_id,
	));
	$unreads = theme_notification::get_notifications(array(
		'user_id' => $current_user_id,
		'type' => 'unread'
	));

	if(!empty($notis)){
		?>
		<ul class="list-group history-group">
			<?php
			foreach($notis as $k =>$noti){
				/**
				 * Check the noti is read or unread
				 */
				if(isset($noti['id']) && isset($unreads[$noti['id']])){
					$unread_class = ' unread list-group-item-info';
				}else{
					$unread_class = null;
				}
				?>
				<li class="list-group-item type-<?= $noti['type'];?> <?= $unread_class;?>">
					<?php do_action('list_noti',$noti);?>
				</li><!-- /.list-group-item -->
			<?php } ?>
		</ul>
		<?php
	}else{
		?>
		<div class="panel-body">
			<div class="page-tip"><?= status_tip('info',___('You have not any notification yet'));?></div>
		</div>
		<?php
	}
	?>
</div><!-- /.panel -->
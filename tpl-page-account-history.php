<div class="panel panel-default">
	<div class="panel-heading">
		<h3 class="panel-title">
			<i class="fa fa-<?= theme_custom_user_settings::get_tabs(get_query_var('tab'))['icon'];?>"></i>
			<?= ___('My reward point histories');?>
		</h3>
	</div>
	<div class="panel-body">
<div class="media">
	<div class="media-left">
		<img class="media-object" src="<?= esc_url(theme_options::get_options(theme_custom_point::$iden)['point-img-url']);?>" alt="">
	</div>
	<div class="media-body">
		<h4 class="media-heading"><strong class="total-point"><?= theme_custom_point::get_point();?> </strong></h4>
		<!-- <p><?= theme_custom_point::get_point_des();?></p> -->
	</div>
</div>
</div><!-- /.panel-body -->
<?php
$history_list = theme_custom_point::get_history_list(array(
	'posts_per_page' => 20,
));
if(empty($history_list)){
	?>
	<div class="panel-body">
		<div class="page-tip"><?= status_tip('info',___('Your have not any history yet.')); ?></div>
	</div><!-- /.panel-body -->
	<?php
}else{
	echo $history_list;
}
?>
	</div>
</div>
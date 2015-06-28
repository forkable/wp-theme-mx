<?php
$current_user_id = get_current_user_id();
?>
<div class="panel panel-default">
	<div class="panel-heading">
		<h3 class="panel-title">
			<i class="fa fa-<?= theme_custom_user_settings::get_tabs(get_query_var('tab'))['icon'];?>"></i>
			<?= sprintf(___('%s\'s reward point histories'),esc_html( get_the_author_meta('display_name',$current_user_id)));?>
		</h3>
	</div>
	<div class="panel-body">
<div class="media">
	<div class="media-left">
		<img class="media-object" src="<?= theme_custom_point::get_point_img_url();?>" alt="">
	</div>
	<div class="media-body">
		<h4 class="media-heading">
			<strong class="total-point"><?= theme_custom_point::get_point($current_user_id);?></strong>
		</h4>
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
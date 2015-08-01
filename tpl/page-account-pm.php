<?php
global $current_user;
get_currentuserinfo();
?>
<div class="panel panel-default">
	<div class="panel-heading">
		<h3 class="panel-title">
			<i class="fa fa-<?= theme_custom_pm::get_tabs(get_query_var('tab'))['icon'];?>"></i>
			<?= ___('Private messages');?>
		</h3>
	</div>
	<div class="panel-body">
		<div class="row">
			<div class="pm-tab col-sm-2">
				<a href="javascript:;" data-target="pm-new">
					<i class="fa fa-plus fa-fw"></i> 
					<?= ___('New P.M.');?>
				</a>
				<a href="javascript:;" data-target="pm-dialog-100914">
					<img src="<?= get_avatar_url(914);?>" alt="avatar" class="avatar"> 
					<span class="author"><?= theme_cache::get_the_author_meta('display_name',914);?></span>
				</a>
			</div>
			<div class="pm-dialog-container col-sm-10">
				<!-- pm-new -->
				<form action="javascript:;" id="pm-new" class="pm-dialog">
					<p class="well"><?= ___('Add a receiver UID to send private message.');?></p>
					<div class="form-group text-center">
						<img src="<?= theme_features::get_theme_images_url(theme_functions::$avatar_placeholder);?>" alt="avatar" id="pm-new-avatar" class="avatar" width="32px" height="32px"> 
						<span id="pm-new-receiver-name"> - </span>
					</div>
					<div class="form-group">
						<input type="number" name="pm[new-receiver-id]" id="pm-new-receiver-id" class="form-control text-center" placeholder="<?= ___('Receiver UID, e.g. 10004');?>">
					</div>
					<div class="form-group">
						<button type="submit" class="btn btn-primary btn-block"><i class="fa fa-check"></i> <?= ___('Start');?></button>
					</div>
				</form>
				
				<form action="javascript:;" id="pm-dialog-10024" class="pm-dialog">
					<div class="pm-dialog-list">
						
					</div>
					<div class="form-group">
						<input name="pm[content]" class="pm-dialog-conteng form-control" placeholder="<?= ___('Enter to send P.M.');?>">
					</div>
				</form>
			</div>
		</div><!-- .row -->
	</div>
</div>
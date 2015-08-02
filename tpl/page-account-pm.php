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
			<nav id="pm-tab" class="pm-tab col-sm-2 nav nav-pills">
				<li>
					<a href="javascript:;" data-target="pm-new" class="">
						<i class="fa fa-plus fa-fw"></i> 
						<?= ___('New P.M.');?>
					</a>
				</li>
				<li class="active">
					<a href="javascript:;" data-target="pm-dialog-100914" class="">
						<img src="<?= theme_features::get_theme_images_url(theme_functions::$avatar_placeholder);?>" alt="avatar" class="avatar" width="24" height="24"> 
						<span class="author"><?= theme_cache::get_the_author_meta('display_name',914);?></span>
					</a>
				</li>
			</nav>
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
					<div class="form-group pm-dialog-list">
						<!-- list -->
						<div class="pm-dialog-sender media">
							<div class="media-left">
								<img src="<?= theme_features::get_theme_images_url(theme_functions::$avatar_placeholder);?>" alt="avatar" id="pm-new-avatar" class="avatar" width="64px" height="64px"> 
							</div>
							<div class="media-body">
								<div class="media-heading">
									<span class="name">小叫兽</span> 
									<span class="date">2015-12-12 05:32:11</span>
								</div>
								<div class="media-content">
									你好，这是测试信息
								</div>
							</div>
						</div>
						<div class="pm-dialog-me media">
							<div class="media-left">
								<img src="<?= theme_features::get_theme_images_url(theme_functions::$avatar_placeholder);?>" alt="avatar" id="pm-new-avatar" class="avatar" width="64px" height="64px"> 
							</div>
							<div class="media-body">
								<div class="media-heading">
									<span class="name">我</span> 
									<span class="date">2015-12-12 05:32:11</span>
								</div>
								<div class="media-content">
									你好，这是测试信息
								</div>
							</div>
						</div>
					</div>
					<div class="form-group">
						<textarea name="pm[content]" class="pm-dialog-conteng form-control" placeholder="<?= ___('Enter to send P.M.');?>"></textarea>
					</div>
					<div class="form-group">
						<button class="btn btn-success btn-block" type="submit"><i class="fa fa-check"></i>&nbsp;<?= ___('Send P.M.');?></button>
					</div>
				</form>
			</div>
		</div><!-- .row -->
	</div>
</div>
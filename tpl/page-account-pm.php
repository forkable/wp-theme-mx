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
			<div class="col-sm-2">
				<nav id="pm-tab" class="pm-tab">
					<a id="pm-tab-new" href="javascript:;" data-uid="new" class="active">
						<i class="fa fa-plus fa-fw"></i> 
						<?= ___('New P.M.');?>
						
					</a>
					<a id="pm-tab-100914" href="javascript:;" data-uid="100914">
						<img src="<?= theme_features::get_theme_images_url(theme_functions::$avatar_placeholder);?>" alt="avatar" class="avatar" width="24" height="24"> 
						<span class="author"><?= theme_cache::get_the_author_meta('display_name',914);?></span>
						<b class="close">&times;</b>
					</a>
				</nav>
			</div>
			<div class="col-sm-10">
				<div class="pm-dialog-container">
					<!-- pm-new -->
					<form action="javascript:;" id="pm-dialog-new" class="pm-dialog">
						<p class="well"><?= ___('Add a receiver UID to send private message.');?></p>
						<div class="form-group">
							<input type="number" name="pm[new-receiver-id]" id="pm-dialog-content-new" class="form-control text-center" placeholder="<?= ___('Receiver UID, e.g. 10004');?>" title="<?= ___('Please type receiver UID');?>" required >
						</div>
						<div class="form-group">
							<button type="submit" class="btn btn-primary btn-block"><i class="fa fa-check"></i> <?= ___('Start');?></button>
						</div>
					</form>
					
					<form action="javascript:;" id="pm-dialog-100914" class="pm-dialog">
						<div class="form-group pm-dialog-list">
							<!-- list -->
							<?php for($i=0;$i<10;$i++){ ?>
							<section class="pm-dialog-sender">
								<div class="pm-dialog-bg">
									<h4>
										<span class="name">小叫兽</span> 
										<span class="date">2015-12-12 05:32:11</span>
									</h4>
									<div class="media-content">
										你好，这是测试信息
									</div>
								</div>
							</section>
							<section class="pm-dialog-me">
								<div class="pm-dialog-bg">
									<h4>
										<span class="name">我</span> 
										<span class="date">2015-12-12 05:32:11</span>
									</h4>
									<div class="media-content">
										你好，这是测试信息
									</div>
								</div>
							</section>
							<?php } ?>
						</div>
						<div class="form-group">
							<textarea id="pm-dialog-content-100914" name="content" class="pm-dialog-conteng form-control" placeholder="<?= ___('Ctrl + enter to send P.M.');?>" required title="<?= ___('P.M. content');?>"></textarea>
						</div>
						<div class="form-group">
							<button class="btn btn-success btn-block" type="submit"><i class="fa fa-check"></i>&nbsp;<?= ___('Send P.M.');?></button>
						</div>
					</form>
				</div>
			</div><!-- col -->
		</div><!-- .row -->
	</div>
</div>
<?php
$current_user_id = theme_cache::get_current_user_id();
$pm_lists = theme_custom_pm::get_lists($current_user_id);
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
					<?php
					/**
					 * loop lists
					 */
					if($pm_lists){
						foreach($pm_lists as $user_id){ ?>
							<a id="pm-tab-<?= theme_custom_pm::get_niceid($user_id);?>" href="javascript:;" data-uid="<?= theme_custom_pm::get_niceid($user_id);?>">
								<img src="<?= get_avatar_url($user_id);?>" alt="<?= ___('Avatar');?>" class="avatar" width="24" height="24"> 
								<span class="author"><?= theme_cache::get_the_author_meta('display_name',$user_id);?></span>
								<b class="close">&times;</b>
							</a>
						<?php 
						}/** end foreach */
					}/** end if */
					?>
				</nav>
			</div>
			<div class="col-sm-10">
				<div class="pm-dialog-container">
					<!-- pm-new -->
					<form action="javascript:;" id="pm-dialog-new" class="pm-dialog">
						<p class="well"><?= ___('Add a receiver UID to send private message.');?></p>
						<div class="form-group">
							<input type="number" name="pm[new-receiver-id]" id="pm-dialog-content-new" class="form-control text-center" placeholder="<?= ___('Receiver UID, e.g. 100914');?>" title="<?= ___('Please type receiver UID');?>" required min="1">
						</div>
						<div class="form-group">
							<button type="submit" class="btn btn-success btn-block"><?= ___('Next');?> <i class="fa fa-chevron-right"></i></button>
						</div>
					</form>
					<?php
					/**
					 * loop lists
					 */
					if($pm_lists){
						$histories = theme_custom_pm::get_histories($current_user_id);
						$dialog_histories = [];
						foreach($histories as $history_user_id => $history){
							if($history->pm_author == $history_user_id || $history->pm_receiver == $history_user_id){
								if(!isset($dialog_histories[$history_user_id]))
									$dialog_histories[$history_user_id] = [
										$history->pm_id => $history
									];
								$dialog_histories[$history_user_id][$history->pm_id] = $history;
							}
						}
						/** sort */
						foreach($pm_lists as $v){
							ksort($dialog_histories[$v]);
						}
						?>
						
<form action="javascript:;" id="pm-dialog-<?= theme_custom_pm::get_niceid($user_id);?>" class="pm-dialog">
	<?php 
	foreach($dialog_histories as $history_user_id => $history){
		$name = $current_user_id == $history_user_id ? ___('Me') : get_the_author_meta('display_name',$history_user_id);
		?>
		<section class="pm-dialog-<?= $current_user_id == $user_id ? 'me' : 'sender' ;?>">
			<div class="pm-dialog-bg">
				<h4>
					<span class="name"><?= $name;?></span> 
					<span class="date"><?= strtotime('Y/m/d H:i:s',$history->pm_date);?></span>
				</h4>
				<div class="media-content">
					<?= $history->pm_content;?>
				</div>
			</div>
		</section>
		<?php
	}/** end dialog loop */
	?>
	<div class="form-group">
		<textarea id="pm-dialog-content-100914" name="content" class="pm-dialog-conteng form-control" placeholder="<?= ___('Ctrl + enter to send P.M.');?>" required title="<?= ___('P.M. content');?>"></textarea>
	</div>
	<div class="form-group">
		<button class="btn btn-success btn-block" type="submit"><i class="fa fa-check"></i>&nbsp;<?= ___('Send P.M.');?></button>
	</div>
</form>
						<?php
					}/** end pm lists loop */
					?>
				</div>
			</div><!-- col -->
		</div><!-- .row -->
	</div>
</div>
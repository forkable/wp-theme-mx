<div class="panel panel-default">
	<div class="panel-heading">
		<h3 class="panel-title">
			<i class="fa fa-<?php echo theme_custom_user_settings::get_tabs(get_query_var('tab'))['icon'];?>"></i>
			<?php echo ___('Change my password');?>
		</h3>
	</div>
	<div class="panel-body">
<form id="fm-change-password" class="user-form form-horizontal" method="post" action="javascript:;">
	<!-- current password -->
	<div class="form-group">
		<label class="control-label col-sm-2">
			<label for="user-old-pwd"><?php echo ___('Current password');?></label>
		</label>
		<div class="col-sm-10">
			<input id="user-old-pwd" name="user[old-pwd]" type="password" class="form-control" placeholder="<?php echo ___('Current password');?>" title="<?php echo ___('Type your current password');?>" required >
		</div>
	</div>
	<!-- new password -->
	<div class="form-group">
		<label class="control-label col-sm-2">
			<label for="user-new-pwd-1"><?php echo ___('New password');?></label>
		</label>
		<div class="col-sm-10">
			<input id="user-new-pwd-1" name="user[new-pwd-1]" type="password" class="form-control" placeholder="<?php echo ___('New password');?>" title="<?php echo ___('Type new password');?>" required >
		</div>
	</div>
	<div class="form-group">
		<label class="control-label col-sm-2">
			<label for="user-new-pwd-2"><?php echo ___('Re-type new password');?></label>
		</label>
		<div class="col-sm-10">
			<input id="user-new-pwd-2" name="user[new-pwd-2]" type="password" class="form-control" placeholder="<?php echo ___('Re-type new password');?>" title="<?php echo ___('Re-type new password');?>" required >
		</div>
	</div>
	<!-- submit -->
	<div class="form-group">
		<div class="col-sm-offset-2 col-sm-10">
			<div class="submit-tip"></div>
			<input type="hidden" name="type" value="pwd">
			<button type="submit" class="submit btn btn-success btn-block btn-lg" data-loading-text="<?php echo ___('Saving, please wait...');?>">
				<i class="fa fa-check"></i>
				<?php echo ___('Update password');?>
			</button>
		</div>
	</div>
</form>
	</div>
</div>
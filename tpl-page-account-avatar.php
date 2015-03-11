<div class="panel panel-default">
	<div class="panel-heading">
		<h3 class="panel-title">
			<i class="fa fa-<?php echo theme_custom_user_settings::get_tabs(get_query_var('tab'))['icon'];?>"></i>
			<?php echo ___('My reward point histories');?>
		</h3>
	</div>
	<div class="panel-body">
<form id="fm-change-avatar" class="user-form form-horizontal" method="post" action="javascript:;">
	<!-- current avatar -->
	<div class="form-group">
		<div class="control-label col-sm-2">
			<?php echo ___('Current avatar');?>
		</div>
		<div class="col-sm-10">
			<div class="current-avatar">
				<?php echo get_avatar(get_current_user_id(),100);?>
			</div>
		</div>
	</div>
	<!-- new avatar -->
	<div class="form-group">
		<div class="control-label col-sm-2">
			<?php echo ___('New avatar');?>
		</div>
		<div class="col-sm-10">
			<div class="row">
				<div class="col-sm-6">
					<div id="cropper-container"></div>
				</div>
				<div class="col-sm-6">
					<div id="avatar-preview"></div>
				</div>
			</div>
			

			<div class="submit-tip"></div>
			<textarea name="base64" id="avatar-base64" hidden></textarea>
			<input type="hidden" name="type" value="avatar">
			<button id="cropper-done-btn" type="submit" class="submit btn btn-success btn-block btn-lg" data-loading-text="<?php echo ___('Saving, please wait...');?>">
				<i class="fa fa-check"></i>
				<?php echo ___('Save my avatar');?>
			</button>
			
			<a href="javascript:;" id="new-avatar-btn" class="file-btn-container btn btn-default btn-block">
				<i class="fa fa-plus"></i>
				<?php echo ___('Upload a new avatar');?>
				<input type="file" id="file">
			</a>
		</div>
	</div>
	<!-- submit -->

</form>
	</div>
</div>
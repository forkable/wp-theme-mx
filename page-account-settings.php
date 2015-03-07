<?php
global $current_user;
get_currentuserinfo();
?>
<div class="panel panel-default">
	<div class="panel-heading">
		<h3 class="panel-title">
			<i class="fa fa-<?php echo theme_custom_user_settings::get_tabs(get_query_var('tab'))['icon'];?>"></i>
			<?php echo ___('Edit my profile');?>
		</h3>
	</div>
	<div class="panel-body">
<form id="fm-my-settings" class="user-form form-horizontal" method="post" action="javascript:;">
	<!-- avatar -->
	<div class="form-group">
		<div class="control-label col-sm-2">
			<?php 
			$avatar = get_avatar(get_current_user_id(),100);
			?>
			<a href="<?php echo esc_url(get_img_source($avatar));?>" target="_blank" title="<?php echo ___('Views source image');?>"><?php echo $avatar;?></a>
		</div>
		<div class="col-sm-10">
			<div class="form-control-static">
				<p><?php echo ___('My avatar');?></p>
				<p><a href="<?php echo esc_url(theme_custom_user_settings::get_tabs('avatar')['url']);?>" class="btn btn-success btn-xs"><?php echo ___('Change avatar');?> <i class="fa fa-external-link"></i></a></p>
			</div>
		</div>
	</div>
	<!-- uid -->
	<div class="form-group">
		<div class="control-label col-sm-2">
			<abbr title="<?php echo ___('Unique identifier');?>">
				<?php echo ___('UID');?>
			</abbr>
		</div>
		<div class="col-sm-10"><p class="form-control-static"><strong>
			<a href="<?php echo esc_url(get_author_posts_url(get_current_user_id()));?>"><?php echo $current_user->user_nicename;?></a>
			</strong></p></div>
	</div>
	<!-- nickname -->
	<div class="form-group">
		<label for="my-settings-nickname" class="control-label col-sm-2">
			<i class="fa fa-user"></i>
			<?php echo ___('Nickname');?>
		</label>
		<div class="col-sm-10">
			<input name="user[nickname]" type="text" class="form-control" id="my-settings-nickname" placeholder="<?php echo ___('Please type nickname (required)');?>" title="<?php echo ___('Please type nickname');?>" value="<?php echo esc_attr($current_user->display_name);?>" required tabindex="1" >
		</div>
	</div>
	<!-- url -->
	<div class="form-group">
		<label for="my-settings-url" class="control-label col-sm-2">
			<i class="fa fa-link"></i>
			<?php echo ___('Website / Blog');?>
		</label>
		<div class="col-sm-10">
			<input name="user[url]" type="url" class="form-control" id="my-settings-url" placeholder="<?php echo ___('Your blog url (include http://)');?>" title="<?php echo ___('Please type your blog url');?>" value="<?php echo esc_url($current_user->user_url);?>" tabindex="1" >
		</div>
	</div>
	<!-- description -->
	<div class="form-group">
		<label for="my-settings-des" class="control-label col-sm-2">
			<i class="fa fa-newspaper-o"></i>
			<?php echo ___('Description');?>
		</label>
		<div class="col-sm-10">
			<textarea name="user[description]" class="form-control" id="my-settings-des" placeholder="<?php echo ___('Introduce yourself to everyone');?>" tabindex="1"><?php echo esc_attr(get_user_meta(get_current_user_id(),'description',true));?></textarea>
		</div>
	</div>
	<!-- submit -->
	<div class="form-group">
		<div class="col-sm-offset-2 col-sm-10">
			<div class="submit-tip"></div>
			<input type="hidden" name="type" value="settings">
			<button type="submit" class="submit btn btn-success btn-block btn-lg" data-loading-text="<?php echo ___('Saving, please wait...');?>">
				<i class="fa fa-check"></i>
				<?php echo ___('Save my settings');?>
			</button>
		</div>
	</div>
</form>
	</div>
</div>
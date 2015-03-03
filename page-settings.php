<?php
/**
 * Template name: Settings
 */
global $current_user;
get_currentuserinfo();
$tabs = theme_custom_user_settings::get_tabs();
$tab_active = get_query_var('tab');
$tab_active = isset($tabs[$tab_active]) ? $tab_active : 'history';
?>
<?php get_header();?>
<div class="container grid-container">
	<h3 class="crumb-title">
		<i class="fa fa-<?php echo $tabs[$tab_active]['icon'];?>"></i>
		<?php echo $tabs[$tab_active]['text'];?>
	</h3>
	<div class="row">
		<div id="main" class="main col-md-9 col-sm-12">
			<div class="panel panel-default">
				<div class="panel-heading">
					<div class="btn-group btn-group-justified" role="group">
						<?php 
						foreach($tabs as $k => $v){
							$class_active = $tab_active === $k ? ' btn-primary ' : null;
							?>
							<a href="<?php echo esc_url($v['url']);?>" class="btn btn-default <?php echo $class_active;?>" role="button">
								<i class="fa fa-<?php echo esc_attr($v['icon']);?>"></i> 
								<span class="tx <?php echo $class_active ? null : 'hidden-xs';?>">
									<?php echo esc_html($v['text']);?>
								</span>
							</a>
						<?php } ?>					
					</div>
				</div>
					<?php
					switch($tab_active){
						/**
						 * settings
						 */
						case 'settings':
						?>
				<div class="panel-body my-settings">
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
			<a href="<?php echo esc_url(get_author_posts_url(get_current_user_id()));?>"><?php echo get_current_user_id();?></a>
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
			<input name="user[url]" type="url" class="form-control" id="my-settings-email" placeholder="<?php echo ___('Your blog url (include http://)');?>" title="<?php echo ___('Please type your blog url');?>" value="<?php echo esc_url($current_user->user_url);?>" tabindex="1" >
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


				</div><!-- /.my-settings -->
<?php
						
							break;
						/**
						 * avatar
						 */
						case 'avatar':
?>
				<div class="panel-body my-settings-avatar">
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
				<?php
							break;
						/**
						 * password
						 */
						case 'password':
?>
				<div class="panel-body my-settings-password">
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
<?php
							break;
						/**
						 * history
						 */
						default:
?>
				<div class="panel-body my-settings-history">
<div class="media">
	<div class="media-left">
		<img class="media-object" src="<?php echo esc_url(theme_options::get_options(theme_custom_point::$iden)['point-img-url']);?>" alt="">
	</div>
	<div class="media-body">
		<h4 class="media-heading"><strong class="total-point"><?php echo theme_custom_point::get_point();?> </strong></h4>
		<!-- <p><?php echo theme_custom_point::get_point_des();?></p> -->
	</div>
</div>
<?php
$histories = theme_custom_point::get_history();
if(empty($histories)){
	?>
				</div><!-- /.panel-body -->
	<?php 
	echo status_tip('info',___('Your have not any history yet.'));
}else{
	/** show 50 */
	$histories = array_slice($histories,0,19);
	?>
				</div><!-- /.panel-body -->
	<ul class="list-group history-group">
		<?php
		$point_name = theme_custom_point::get_point_name();
		foreach($histories as $k => $v){
			$type_point = theme_custom_point::get_point_value($v['type']);
			?>
			<li class="list-group-item">
				<span class="point-name">
					<?php echo esc_html($point_name);?>
				</span>
				<?php
				if($type_point >= 0){
					$cls = 'plus';
					$tx = '+' . $type_point;
				}else{
					$cls = 'minus';
					$tx = '-' . $type_point;
				}
				?>
				<span class="point-value <?php echo $cls;?>"><?php echo $tx;?></span>
			<?php
			switch($v['type']){
				/*****************************************
				 * signup
				 */
				case 'signup':
					?>
					<span class="history-text">
						<?php echo sprintf(___('I registered %s.'),'<a href="' . home_url() . '">' . get_bloginfo('name') . '</a>');?>
					</span>
					<span class="history-time">
						<?php echo esc_html(friendly_date(strtotime(get_the_author_meta('user_registered',get_current_user_id()))));?>
					</span>
					<?php
					break;
				/***************************************
				 * post-publish
				 */
				case 'post-publish':
					?>
					<span class="history-text">
						<?php echo sprintf(___('I published a post %s.'),'<a href="' . esc_url(get_permalink($v['post-id'])) . '">' . esc_html(get_the_title($v['post-id'])) . '</a>');?>
					</span>
					<span class="history-time">
						<?php echo esc_html(friendly_date(get_post_time('U',false,$v['post-id'])));?>
					</span>
					<?php
					break;
				/***************************************
				 * post-reply
				 */
				case 'post-reply':
					global $comment;
					$comment = get_comment($v['comment-id']);
					
					if($comment->user_id > 0){
						$comment_author_url = class_exists('theme_custom_author_profile') ? theme_custom_author_profile::get_tabs('profile',$comment->user_id) : get_author_posts_url($comment->user_id);
						
						$comment_author_html = '<a href="' . esc_url($comment_author_url) . '">' . esc_html(get_comment_author($v['comment-id'])) . '</a>';
					}else{
						$comment_author_html = esc_html(get_comment_author($v['comment-id']));
					}
					?>
					<span class="history-text">
						<?php echo sprintf(___('Your post %s has a new comment by %s.'),

						'<a href="' . esc_url(get_permalink($comment->comment_post_ID)) . '">' . esc_html(get_the_title($comment->comment_post_ID)) . '</a>',

						'<span class="comment-author">' . $comment_author_html . '</span>'
						);?>
					</span>
					<span class="history-time">
						<?php echo esc_html(friendly_date(get_comment_time('timestamp')));?>
					</span>
					<?php
					break;
				/****************************************
				 * signin-daily
				 */
				case 'signin-daily':
					?>
					<span class="history-text">
						<?php echo ___('Log-in daily reward.');?>
					</span>
					<span class="history-time">
						<?php echo esc_html(friendly_date($v['timestamp']));?>
					</span>
					<?php
					break;
				default:
				
			} /** end switch */
			?>
			</li>
			<?php
		} /** end foreach */
		?>
	</ul>
	<?php
} /** end have histories */
?>
<?php
					}
					?>
			</div><!-- /.panel -->
		</div><!-- /.main.col-->
		<?php get_sidebar();?>
	</div><!-- /.row -->
</div>
<?php get_footer();?>
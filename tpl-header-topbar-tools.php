<?php
/**
 * if user logged
 */
if(is_user_logged_in()){
	?>
	<div class="btn-group btn-group-xs">
		<!-- ctb -->
		<?php if(class_exists('theme_custom_contribution')){ ?>
			<a href="<?php echo esc_url(theme_custom_contribution::get_tabs('contribution')['url']);?>" class="btn btn-primary meta tool-contribution">
				<i class="fa fa-pencil-square-o"></i>
				<?php echo ___('Contribution');?>
			</a>
		<?php } ?>

		
		<!-- my profile -->
		<?php
		//if(class_exists('theme_custom_dashboard')){
		//	$profile_url = theme_custom_dashboard::get_tabs('dashboard')['url'];
		//}else{
		//	$profile_url = get_author_posts_url(get_current_user_id());
		//}
		?>
		<a href="<?php echo esc_url(get_author_posts_url(get_current_user_id()));?>" class="btn btn-default meta user-avatar" title="<?php echo ___('My profile');?>">
			<?php echo get_avatar(get_current_user_id());?>
			<span class="tx"><?php echo wp_get_current_user()->display_name;?></span>
		</a>

		
		<!-- notification -->
		<?php 
		if(class_exists('theme_notification')){
			$unread = theme_notification::get_count(array(
				'type' => 'unread'
			));
			?>
			<a href="<?php echo esc_url(theme_notification::get_tabs('notifications')['url']);?>" class="meta tool-notification btn btn-<?php echo $unread ? 'success' : 'default';?>" title="<?php echo ___('Notification');?>">
				<i class="fa fa-bell"></i> 
				<?php echo $unread > 0 ? $unread : null;?>
			</a>
		<?php } ?>

		
		<!-- my point -->
		<?php if(class_exists('theme_custom_point')){ ?>
			<a href="<?php echo esc_url(theme_custom_user_settings::get_tabs('history')['url']);?>" class="meta tool-point btn btn-default" title="<?php echo ___('My points');?>">
				<!-- <i class="fa fa-github-alt"></i> -->
				<img src="<?php echo esc_url(theme_options::get_options(theme_custom_point::$iden)['point-img-url']);?>" alt="" width="15" height="15">
				<?php echo theme_custom_point::get_point();?>
			</a>
		<?php } ?>

		
		<!-- favor -->
		<?php if(class_exists('theme_custom_favor')){ ?>
			<a href="<?php echo esc_url(theme_custom_favor::get_url());?>" class="meta tool-favor btn btn-default">
				<i class="fa fa-heart"></i>
				<?php echo ___('My favor');?>
			</a>
		<?php } ?>
		
		<!-- pm -->
		<?php if(class_exists('theme_pm')){ ?>
			<a href="<?php echo esc_url(theme_pm::get_url());?>" class="meta tool-favor btn btn-default">
				<i class="fa fa-envelope"></i>
				<?php echo ___('My favor');?>
				<?php if(theme_pm::get_unread_count() != 0){ ?>
					<span class="badge"><?php echo theme_pm::get_unread_count();?></span>
				<?php } ?>
			</a>
		<?php } ?>

		<!-- my settings -->
		<?php
		if(class_exists('theme_custom_user_settings')){
			$setting_url = theme_custom_user_settings::get_tabs('settings')['url'];
		}else{
			$setting_url = admin_url('profile.php');
		}
		?>
		<a href="<?php echo esc_url($setting_url);?>" class="btn btn-default meta user-settings" title="<?php echo ___('My settings');?>">
			<i class="fa fa-cog"></i> 
		</a>

		
		<!-- logout -->
		<a href="<?php echo wp_logout_url(get_current_url());?>" class="meta tool-logout btn btn-default">
			<i class="fa fa-power-off"></i> 
		</a>
	</div>
<?php
/**
 * is visitor
 */
}else{
	?>
	<div class="btn-group btn-group-xs">
		<a class="sign-in sign-in-meta btn btn-primary" href="<?php echo esc_url(wp_login_url(get_current_url()));?>">
			<i class="fa fa-user"></i>
			<?php echo ___('Login');?>
		</a>
		<?php
		/**
		 * open sign
		 */
		if(method_exists('theme_open_sign','get_login_url')){
			if(theme_open_sign::get_login_url('qq')){
				?>
				<a href="<?php echo esc_url(theme_open_sign::get_login_url('qq'));?>" class="open-sign sign-in-meta qq btn btn-primary" title="<?php echo ___('Login from QQ');?>">
					<i class="fa fa-qq"></i>
				</a>
			<?php
			}
			if(theme_open_sign::get_login_url('sina')){
				?>
				<a href="<?php echo esc_url(theme_open_sign::get_login_url('sina'));?>" class="open-sign sign-in-meta sina btn btn-primary" title="<?php echo ___('Login from Weibo');?>">
					<i class="fa fa-weibo"></i>
				</a>
			<?php 
			}
		}
		?>
		<a class="sign-up meta btn btn-success" href="<?php echo esc_url(wp_registration_url(get_current_url()));?>">
			<i class="fa fa-user-plus"></i>
			<?php echo ___('Register');?>
		</a>
	</div>
<?php
}
?>
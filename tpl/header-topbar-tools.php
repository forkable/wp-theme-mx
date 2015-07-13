<?php
/**
 * if user logged
 */
if(theme_cache::is_user_logged_in()){
	$current_user_id = theme_cache::get_current_user_id();
	?>
	<div class="btn-group btn-group-xs">
		
		<!-- my profile -->
		<a href="<?= esc_url(theme_cache::get_author_posts_url($current_user_id));?>" class="btn btn-default meta user-avatar" title="<?= ___('My profile');?>">
			<?= get_avatar($current_user_id);?>
			<span class="tx"><?= esc_html(get_the_author_meta('display_name',$current_user_id));?></span>
		</a>
		
		<!-- my point -->
		<?php if(class_exists('theme_custom_point')){ ?>
			<a href="<?= theme_custom_user_settings::get_tabs('history')['url'];?>" class="meta tool-point btn btn-default" title="<?= ___('My points');?>">
				<?php if(theme_custom_point::get_point_img_url()){ ?>
					<img src="<?= theme_custom_point::get_point_img_url();?>" alt="" width="15" height="15">
				<?php }else{ ?>
					<i class="fa fa-diamond fa-fw"></i> 
				<?php } ?>
				<?= theme_custom_point::get_point($current_user_id);?>
			</a>
		<?php } ?>
		<!-- my dashboard -->
		<?php if(class_exists('theme_custom_dashboard')){ ?>
			<a href="<?= theme_custom_dashboard::get_tabs('dashboard')['url'];?>" class="meta tool-dashboard btn btn-default" title="<?= theme_custom_dashboard::get_tabs('dashboard')['text'];?>">
				<i class="fa fa-<?= theme_custom_dashboard::get_tabs('dashboard')['icon'];?> fa-fw"></i>
			</a>
		<?php } ?>
		
		<!-- post edit -->
		<?php
		global $post;
		if(is_singular('post') && $post->post_author == $current_user_id){
			if(class_exists('theme_custom_edit')){
				$edit_post_link = theme_custom_edit::get_edit_post_link($post->ID);
			}else{
				$edit_post_link = get_edit_post_link($post->ID);
			}
			if(!empty($edit_post_link)){
				?>
				<a href="<?= $edit_post_link;?>" class="btn btn-default meta tool-post-edit" title="<?= ___('Edit post');?>"><i class="fa fa-pencil-square-o fa-fw"></i></a>
			<?php 
			}
		}
		?>
				
		<!-- ctb -->
		<?php if(class_exists('theme_custom_contribution')){ ?>
			<a href="<?= theme_custom_contribution::get_tabs('post')['url'];?>" class="btn btn-default meta tool-contribution" title="<?= ___('New post');?>">
				<i class="fa fa-paint-brush fa-fw"></i>
			</a>
		<?php } ?>

		<!-- my posts -->
		<?php if(class_exists('theme_custom_edit')) { ?>
			<a href="<?= theme_custom_edit::get_tabs('edit')['url'];?>" class="btn btn-default meta tool-my-posts" title="<?= theme_custom_edit::get_tabs('edit')['text'];?>"><i class="fa fa-<?= theme_custom_edit::get_tabs('edit')['icon'];?> fa-fw"></i></a>
		<?php } ?>
		<!-- bomb -->
		<?php if(class_exists('theme_custom_point_bomb')){ ?>
			<a title="<?= theme_custom_point_bomb::get_tabs('bomb')['text'];?>" href="<?= theme_custom_point_bomb::get_tabs('bomb')['url'];?>" class="btn btn-default meta tool-bomb"><i class="fa fa-<?= theme_custom_point_bomb::get_tabs('bomb')['icon'];?>"></i></a>
		<?php } ?>
		<!-- notification -->
		<?php 
		if(class_exists('theme_notification')){
			$unread = theme_notification::get_count(array(
				'type' => 'unread'
			));
			?>
			<a href="<?= theme_notification::get_tabs('notifications')['url'];?>" class="meta tool-notification btn btn-<?= $unread ? 'success' : 'default';?>" title="<?= ___('Notification');?>">
				<i class="fa fa-bell fa-fw"></i> 
				<?= $unread > 0 ? $unread : null;?>
			</a>
		<?php } ?>

		
		<!-- favor -->
		<?php if(class_exists('theme_custom_favor')){ ?>
			<a href="<?= theme_custom_favor::get_url();?>" class="meta tool-favor btn btn-default">
				<i class="fa fa-heart fa-fw"></i>
				<?= ___('My favor');?>
			</a>
		<?php } ?>
		
		<!-- pm -->
		<?php if(class_exists('theme_pm')){ ?>
			<a href="<?= theme_pm::get_url();?>" class="meta tool-favor btn btn-default">
				<i class="fa fa-envelope fa-fw"></i>
				<?= ___('My favor');?>
				<?php if(theme_pm::get_unread_count() != 0){ ?>
					<span class="badge"><?= theme_pm::get_unread_count();?></span>
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
		<a href="<?= $setting_url;?>" class="btn btn-default meta user-settings" title="<?= ___('My settings');?>">
			<i class="fa fa-cog fa-fw"></i> 
		</a>

		
		<!-- logout -->
		<a href="<?= esc_url(wp_logout_url(get_current_url()));?>" class="meta tool-logout btn btn-default" title="<?= ___('Log-out');?>">
			<i class="fa fa-power-off fa-fw"></i> 
		</a>
	</div>
<?php
/**
 * is visitor
 */
}else{
	?>
	<div class="btn-group btn-group-xs">
		<a class="sign-in sign-in-meta btn btn-primary" href="<?= esc_url(wp_login_url(get_current_url()));?>">
			<i class="fa fa-user fa-fw"></i>
			<?= ___('Login');?>
		</a>
		<div class="btn-group btn-group-xs">
			<a href="javascript:;" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
				<span class="caret"></span>
				<span class="sr-only"></span>
			</a>
			<ul class="dropdown-menu" role="menu">
				<?php
				/**
				 * open sign
				 */
				if(method_exists('theme_open_sign','get_login_url')){
					if(theme_open_sign::get_login_url('qq')){
						?>
						<li>
							<a href="<?= theme_open_sign::get_login_url('qq');?>" class="open-sign qq" title="<?= ___('Login from QQ');?>">
							<i class="fa fa-qq fa-fw"></i> 
							<?= ___('Login from QQ');?>
						</a>
						</li>
					<?php
					}
					if(theme_open_sign::get_login_url('sina')){
						?>
						<li>
							<a href="<?= theme_open_sign::get_login_url('sina');?>" class="open-sign sina" title="<?= ___('Login from Weibo');?>">
							<i class="fa fa-weibo fa-fw"></i> 
							<?= ___('Login from Weibo');?>
						</a>
						</li>
					<?php 
					}
				}
				?>
			</ul>
		</div>
	</div>
	<a class="sign-up meta btn btn-success btn-xs" href="<?= esc_url(wp_registration_url(get_current_url()));?>">
		<i class="fa fa-user-plus"></i> 
		<?= ___('Register');?>
	</a>
<?php
}
?>
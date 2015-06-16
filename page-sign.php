<?php
/**
 * Template name: Sign
 */
$tabs = theme_custom_sign::get_tabs();
$tab_active = get_query_var('tab');

if(!isset($tabs[$tab_active]))
	$tab_active = 'login';
	
$redirect = get_query_var('redirect');

$avatar = theme_custom_sign::get_options('avatar-url');

/**
 * open sign
 */
$open_sign_html = function(){
	if(!class_exists('theme_open_sign'))
		return;
	?>
	<div class="open-login btn-group btn-group-justified" role="group">
		<div class="btn-group" role="group">
			<a href="<?= esc_url(theme_open_sign::get_login_url('qq'));?>" class="btn btn-primary">
				<i class="fa fa-qq fa-fw"></i> 
				<?= ___('Login from QQ');?>
			</a>
		</div>
		<div class="btn-group" role="group">
			<a href="<?= esc_url(theme_open_sign::get_login_url('sina'));?>" class="btn btn-danger">
				<i class="fa fa-weibo fa-fw"></i> 
				<?= ___('Login from Weibo');?>
			</a>
		</div>
	</div>
	<?php
};
?>
<?php get_header();?>
<div class="container grid-container">
	<div class="row">
		<div id="main" class="main col-sm-6 col-lg-4 col-lg-offset-4 col-sm-offset-3 ">
		<?php
		switch($tab_active){
			/**
			 * register
			 */
			case 'register':
			?>
<div class="<?= $avatar ? 'has-avatar' : null;?> panel panel-default mx-sign-panel mx-sign-panel-<?= $tab_active;?>">
	<div class="panel-heading">
		
		<?php if(!empty($avatar)){ ?>
			<img class="avatar" src="<?= esc_url($avatar);?>" alt="avatar">
		<?php } ?>
		
		<h3><?= ___('Account register');?></h3>
	</div>
	<div class="panel-body">
		<form action="javascript:;" id="fm-sign-register" >
			<div class="form-group">
				<div class="input-group">
					<label for="sign-nickname" class="input-group-addon"><i class="fa fa-user fa-fw"></i></label>
					<input name="user[nickname]" type="text" class="form-control" id="sign-nickname" minlength="2" placeholder="<?= ___('Your nickname, at least 2 length');?>" title="<?= ___('Please type nickname, at least 2 length');?>" required tabindex="1" autofocus >
				</div>
			</div>
			<div class="form-group">
				<div class="input-group">
					<label for="sign-email" class="input-group-addon"><i class="fa fa-envelope fa-fw"></i></label>
					<input name="user[email]" type="email" class="form-control" id="sign-email" placeholder="<?= ___('Please type email');?>" title="<?= ___('Please type email');?>" required tabindex="1">
				</div>
			</div>
			<div class="form-group">
				<div class="input-group">
					<label for="sign-pwd" class="input-group-addon"><i class="fa fa-key fa-fw"></i></label>
					<input name="user[pwd]" type="password" class="form-control" id="sign-pwd" placeholder="<?= ___('Your password, at least 3 length');?>" title="<?= ___('Please type password, at least 3 length');?>" minlength="3" required tabindex="1">
				</div>
			</div>
			<div class="checkbox">
				<label for="sign-agree">
					<input type="checkbox" name="user[agree]" id="sign-agree" value="1" checked required onclick="return false" >
					<?= sprintf(___('I am agree the %s'),'<a href="' . theme_custom_sign::get_options('tos-url') . '" target="_blank">' . ___('TOS') . '</a>');?>
				</label>
			</div>
			<div class="form-group submit-tip"></div>
			<div class="form-group form-group-submit">
				<button type="submit" class="btn btn-success btn-block btn-lg submit" data-loading-text="<?= ___('Processing, please wait...');?>" tabindex="1">
					<i class="fa fa-check"></i>
					<?= ___('Register &amp; Log-in');?>
				</button>
				<input type="hidden" name="type" value="register">
			</div>
		</form>
	</div><!-- /.panel-body -->
</div><!-- /.panel -->
<div class="form-group row">
	<div class="col-sm-6">
		<a class="btn btn-default" href="<?= esc_url(theme_custom_sign::get_tabs('login')['url']);?>#main">
			<i class="fa fa-<?= theme_custom_sign::get_tabs('login')['icon'];?> fa-fw"></i>
			<?= ___('I have account');?>
		</a>
	</div>
	<div class="col-sm-6">
		<a class="btn btn-default" href="<?= esc_url(theme_custom_sign::get_tabs('recover')['url']);?>#main">
			<i class="fa fa-<?= theme_custom_sign::get_tabs('recover')['icon'];?> fa-fw"></i>
			<?= ___('Forgot password?');?>
		</a>
	</div>
</div>
<?php
/**
 * open sign
 */
$open_sign_html();

				break;
			/**
			 * recover
			 */
			case 'recover':
				?>
<div class="panel panel-default mx-sign-panel mx-sign-panel-<?= $tab_active;?>">
	<div class="panel-heading">
		<h3><?= ___('Recover password');?></h3>
	</div>
	<div class="panel-body">
		<form action="javascript:;" id="fm-sign-recover">
			<div class="form-group"><?= ___('If you forgot your account password, you can recover your password by your account email. Please entry your account email, we will send a confirm email to it and reset your password.');?></div>
			<div class="form-group">
				<div class="input-group">
					<label for="sign-email" class="input-group-addon"><i class="fa fa-envelope fa-fw"></i></label>
					<input type="email" name="user[email]" id="sign-email" class="form-control" title="<?= ___('Please type email');?>" required tabindex="1" autofocus placeholder="<?= ___('Please type email');?>">
				</div>
			</div>
			<div class="form-group submit-tip"></div>
			<div class="form-group form-group-submit">
				<button type="submit" class="btn btn-success btn-block btn-lg submit" tabindex="1">
					<i class="fa fa-send"></i> 
					<?= ___('Send an email to confirm');?>
				</button>
				<input type="hidden" name="type" value="recover">
			</div>
		</form>
	</div><!-- /.panel-body -->
</div><!-- /.panel -->
<div class="form-group">
	<div class="btn-group btn-group-justified">
		<div class="btn-group" role="group">
			<a class="btn btn-info btn-block" href="<?= esc_url(theme_custom_sign::get_tabs('login')['url']);?>#main">
				<i class="fa fa-<?= theme_custom_sign::get_tabs('login')['icon'];?> fa-fw"></i>
				<?= ___('I have account');?>
			</a>
		</div>
		<div class="btn-group" role="group">
			<a class="btn btn-info btn-block" href="<?= esc_url(theme_custom_sign::get_tabs('register')['url']);?>#main">
				<i class="fa fa-<?= theme_custom_sign::get_tabs('register')['icon'];?> fa-fw"></i>
				<?= ___('Register new account');?>
			</a>
		</div>
	</div>
</div>
				<?php
				break;
			/**
			 * login
			 */
			case 'login':
			default:
				?>
<div class="<?= $avatar ? 'has-avatar' : null;?> panel panel-default mx-sign-panel mx-sign-panel-<?= $tab_active;?>">
	<div class="panel-heading">

		<?php if(!empty($avatar)){ ?>
			<img class="avatar" src="<?= $avatar;?>" alt="avatar">
		<?php } ?>

		
		<h3><?= ___('Account login');?></h3>
	</div>
	<div class="panel-body">
		<form action="javascript:;" id="fm-sign-login">
			<div class="form-group">
				<div class="input-group">
					<label for="sign-email" class="input-group-addon"><i class="fa fa-envelope fa-fw"></i></label>
					<input name="user[email]" type="email" class="form-control" id="sign-email" placeholder="<?= ___('Please type email');?>" title="<?= ___('Please type email');?>" required tabindex="1" autofocus>
				</div>
			</div>
			<div class="form-group">
				<div class="input-group">
					<label for="sign-pwd" class="input-group-addon"><i class="fa fa-key fa-fw"></i></label>
					<input name="user[pwd]" type="password" class="form-control" id="sign-pwd" placeholder="<?= ___('Please type password');?>" title="<?= ___('Please type password, at least 3 length');?>" minlength="3" required tabindex="1">
				</div>
			</div>
			<div class="checkbox">
				<label for="sign-remember">
					<input type="checkbox" name="user[remember]" id="sign-remember" value="1" checked tabindex="1">
					<?= ___('Remember me');?>
				</label>
			</div>
			<div class="form-group submit-tip"></div>
			<div class="form-group form-group-submit">
				<button type="submit" class="btn btn-lg btn-success btn-block submit" data-loading-text="<?= ___('Logging in, please wait...');?>" tabindex="1">
					<i class="fa fa-check"></i>
					<?= ___('Login');?>
				</button>
				<input type="hidden" name="type" value="login">
			</div>
		</form>
	</div><!-- /.panel-body -->
</div><!-- /.panel -->
<div class="form-group row">
	<div class="col-sm-6">
		<a class="btn btn-default" href="<?= esc_url(theme_custom_sign::get_tabs('register')['url']);?>#main">
			<i class="fa fa-<?= theme_custom_sign::get_tabs('register')['icon'];?> fa-fw"></i>
			<?= ___('Register new account');?>
		</a>
	</div>
	<div class="col-sm-6">
		<a class="btn btn-default" href="<?= esc_url(theme_custom_sign::get_tabs('recover')['url']);?>#main">
			<i class="fa fa-<?= theme_custom_sign::get_tabs('recover')['icon'];?> fa-fw"></i>
			<?= ___('Forgot password?');?>
		</a>
	</div>
</div>
<?php
/**
 * open sign
 */
$open_sign_html();

		} /** end switch */ ?>
		</div><!-- /.main.col-->
	</div><!-- /.row -->
</div>
<?php get_footer();?>
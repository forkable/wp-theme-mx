<?php
/**
 * Template name: Sign
 */
$tabs = theme_custom_sign::get_tabs();
$tab_active = get_query_var('tab');
$tab_active = isset($tabs[$tab_active]) ? $tab_active : 'login';
$redirect = get_query_var('redirect');
$error = get_query_var('error');

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
<div class="panel panel-default mx-sign-panel mx-sign-panel-<?php echo $tab_active;?>">
	<div class="panel-heading">
		<h3><?php echo ___('Account register');?></h3>
	</div>
	<div class="panel-body">
		<form action="javascript:;" id="fm-sign-register" >
			<div class="form-group">
				<div class="input-group">
					<label for="sign-nickname" class="input-group-addon"><i class="fa fa-user fa-fw"></i></label>
					<input name="user[nickname]" type="text" class="form-control" id="sign-nickname" minlength="2" placeholder="<?php echo ___('Your nickname, at least 2 length');?>" title="<?php echo ___('Please type nickname, at least 2 length');?>" required tabindex="1" autofocus >
				</div>
			</div>
			<div class="form-group">
				<div class="input-group">
					<label for="sign-email" class="input-group-addon"><i class="fa fa-envelope fa-fw"></i></label>
					<input name="user[email]" type="email" class="form-control" id="sign-email" placeholder="<?php echo ___('Please type email');?>" title="<?php echo ___('Please type email');?>" required tabindex="1">
				</div>
			</div>
			<div class="form-group">
				<div class="input-group">
					<label for="sign-pwd" class="input-group-addon"><i class="fa fa-key fa-fw"></i></label>
					<input name="user[pwd]" type="password" class="form-control" id="sign-pwd" placeholder="<?php echo ___('Your password, at least 3 length');?>" title="<?php echo ___('Please type password, at least 3 length');?>" minlength="3" required tabindex="1">
				</div>
			</div>
			<div class="checkbox">
				<label for="sign-agree">
					<input type="checkbox" name="user[agree]" id="sign-agree" value="1" checked required onclick="return false" >
					<?php echo sprintf(___('I am agree the %s'),'<a href="###" target="_blank">' . ___('TOS') . '</a>');?>
				</label>
			</div>
			<div class="form-group submit-tip"></div>
			<div class="form-group form-group-submit">
				<button type="submit" class="btn btn-success btn-block btn-lg submit" data-loading-text="<?php echo ___('Processing, please wait...');?>" tabindex="1">
					<i class="fa fa-check"></i>
					<?php echo ___('Register &amp; Log-in');?>
				</button>
				<input type="hidden" name="type" value="register">
			</div>
		</form>
	</div><!-- /.panel-body -->
</div><!-- /.panel -->
<div class="form-group row">
	<div class="col-sm-6">
		<a class="btn" href="<?php echo esc_url(theme_custom_sign::get_tabs('login')['url']);?>#main">
			<i class="fa fa-<?php echo theme_custom_sign::get_tabs('login')['icon'];?> fa-fw"></i>
			<?php echo ___('I have account');?>
		</a>
	</div>
	<div class="col-sm-6">
		<a class="btn" href="<?php echo esc_url(theme_custom_sign::get_tabs('recover')['url']);?>#main">
			<i class="fa fa-<?php echo theme_custom_sign::get_tabs('recover')['icon'];?> fa-fw"></i>
			<?php echo ___('Forgot password?');?>
		</a>
	</div>
</div>
<?php
/**
 * open sign
 */
if(class_exists('theme_open_sign')){ ?>
	<div class="open-login btn-group btn-group-justified" role="group">
		<div class="btn-group" role="group">
			<a href="<?php echo esc_url(theme_open_sign::get_login_url('qq'));?>" class="btn btn-info">
				<i class="fa fa-qq"></i> 
				<?php echo ___('Login from QQ');?>
			</a>
		</div>
		<div class="btn-group" role="group">
			<a href="<?php echo esc_url(theme_open_sign::get_login_url('sina'));?>" class="btn btn-danger">
				<i class="fa fa-weibo"></i> 
				<?php echo ___('Login from Weibo');?>
			</a>
		</div>
	</div>
<?php } ?>
				<?php
				break;
			/**
			 * recover
			 */
			case 'recover':
				?>
<div class="panel panel-default mx-sign-panel mx-sign-panel-<?php echo $tab_active;?>">
	<div class="panel-heading">
		<h3><?php echo ___('Recover password');?></h3>
	</div>
	<div class="panel-body">
		<form action="javascript:;" id="fm-sign-recover">
			<div class="form-group"><?php echo ___('If you forgot your account password, you can recover your password by your account email. Please entry your account email, we will send a confirm email to it and reset your password.');?></div>
			<div class="form-group">
				<div class="input-group">
					<label for="sign-email" class="input-group-addon"><i class="fa fa-envelope fa-fw"></i></label>
					<input type="email" name="user[email]" id="sign-email" class="form-control" title="<?php echo ___('Please type email');?>" required tabindex="1" autofocus placeholder="<?php echo ___('Please type email');?>">
				</div>
			</div>
			<div class="form-group submit-tip"></div>
			<div class="form-group form-group-submit">
				<button type="submit" class="btn btn-success btn-block btn-lg submit" tabindex="1">
					<i class="fa fa-send"></i> 
					<?php echo ___('Send an email to confirm');?>
				</button>
				<input type="hidden" name="type" value="recover">
			</div>
		</form>
	</div><!-- /.panel-body -->
</div><!-- /.panel -->
<div class="form-group">
	<div class="btn-group btn-group-justified">
		<div class="btn-group" role="group">
			<a class="btn btn-info btn-block" href="<?php echo esc_url(theme_custom_sign::get_tabs('login')['url']);?>#main">
				<i class="fa fa-<?php echo theme_custom_sign::get_tabs('login')['icon'];?> fa-fw"></i>
				<?php echo ___('I have account');?>
			</a>
		</div>
		<div class="btn-group" role="group">
			<a class="btn btn-info btn-block" href="<?php echo esc_url(theme_custom_sign::get_tabs('register')['url']);?>#main">
				<i class="fa fa-<?php echo theme_custom_sign::get_tabs('register')['icon'];?> fa-fw"></i>
				<?php echo ___('Register new account');?>
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
<div class="panel panel-default mx-sign-panel mx-sign-panel-<?php echo $tab_active;?>">
	<div class="panel-heading">
		<h3><?php echo ___('Account login');?></h3>
	</div>
	<div class="panel-body">
		<form action="javascript:;" id="fm-sign-login">
			<div class="form-group">
				<div class="input-group">
					<label for="sign-email" class="input-group-addon"><i class="fa fa-envelope fa-fw"></i></label>
					<input name="user[email]" type="email" class="form-control" id="sign-email" placeholder="<?php echo ___('Please type email');?>" title="<?php echo ___('Please type email');?>" required tabindex="1" autofocus>
				</div>
			</div>
			<div class="form-group">
				<div class="input-group">
					<label for="sign-pwd" class="input-group-addon"><i class="fa fa-key fa-fw"></i></label>
					<input name="user[pwd]" type="password" class="form-control" id="sign-pwd" placeholder="<?php echo ___('Please type password');?>" title="<?php echo ___('Please type password, at least 3 length');?>" minlength="3" required tabindex="1">
				</div>
			</div>
			<div class="checkbox">
				<label for="sign-remember">
					<input type="checkbox" name="user[remember]" id="sign-remember" value="1" checked tabindex="1">
					<?php echo ___('Remember me');?>
				</label>
			</div>
			<div class="form-group submit-tip"></div>
			<div class="form-group form-group-submit">
				<button type="submit" class="btn btn-lg btn-success btn-block submit" data-loading-text="<?php echo ___('Logging in, please wait...');?>" tabindex="1">
					<i class="fa fa-check"></i>
					<?php echo ___('Login');?>
				</button>
				<input type="hidden" name="type" value="login">
			</div>
		</form>
	</div><!-- /.panel-body -->
</div><!-- /.panel -->
<div class="form-group row">
	<div class="col-sm-6">
		<a class="btn" href="<?php echo esc_url(theme_custom_sign::get_tabs('register')['url']);?>#main">
			<i class="fa fa-<?php echo theme_custom_sign::get_tabs('register')['icon'];?> fa-fw"></i>
			<?php echo ___('Register new account');?>
		</a>
	</div>
	<div class="col-sm-6">
		<a class="btn" href="<?php echo esc_url(theme_custom_sign::get_tabs('recover')['url']);?>#main">
			<i class="fa fa-<?php echo theme_custom_sign::get_tabs('recover')['icon'];?> fa-fw"></i>
			<?php echo ___('Forgot password?');?>
		</a>
	</div>
</div>
<?php
/**
 * open sign
 */
if(class_exists('theme_open_sign')){ ?>
	<div class="open-login btn-group btn-group-justified" role="group">
		<div class="btn-group" role="group">
			<a href="<?php echo esc_url(theme_open_sign::get_login_url('qq'));?>" class="btn btn-info">
				<i class="fa fa-qq fa-fw"></i> 
				<?php echo ___('Login from QQ');?>
			</a>
		</div>
		<div class="btn-group" role="group">
			<a href="<?php echo esc_url(theme_open_sign::get_login_url('sina'));?>" class="btn btn-danger">
				<i class="fa fa-weibo fa-fw"></i> 
				<?php echo ___('Login from Weibo');?>
			</a>
		</div>
	</div>
<?php } ?>
		<?php } ?>
		</div><!-- /.main.col-->
	</div><!-- /.row -->
</div>
<?php get_footer();?>
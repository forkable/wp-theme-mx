<?php
/**
 * Template name: Sign
 */
$tabs = theme_custom_sign::get_tabs();
$tab_active = get_query_var('tab');
$tab_active = isset($tabs[$tab_active]) ? $tab_active : 'login';
$return_url = get_query_var('return');
$error = get_query_var('error');

?>
<?php get_header();?>
<div class="container grid-container">
	<div class="row">
		<div id="main" class="main col-md-9 col-sm-12">
			<div class="panel panel-default">
				<div class="panel-heading">
					<ul class="nav nav-pills">
						<?php
						foreach($tabs as $k => $v){
							$class_active = $tab_active === $k ? ' active ' : null;
							?>
							<li role="presentation" class="<?php echo $class_active;?>">
								<a href="<?php echo esc_url($v['url']);?>">
									<i class="fa fa-<?php echo esc_attr($v['icon']);?>"></i> 
									<?php echo esc_html($v['text']);?>
								</a>
							</li>
							<?php
						}
						?>
					</ul>
				</div>
				<div class="panel-body">
					<div class="row">
					<?php
					switch($tab_active){
						case 'register':
						?>
							<form action="javascript:void(0);" id="fm-sign-register" class="col-sm-12 col-md-6">
								<div class="form-group">
									<div class="input-group">
										<label for="sign-nickname" class="input-group-addon"><i class="fa fa-user"></i></label>
										<input name="user[nickname]" type="text" class="form-control" id="sign-nickname" minlength="2" placeholder="<?php echo ___('Your nickname, at least 2 length');?>" required tabindex="1" autofocus >
									</div>
								</div>
								<div class="form-group">
									<div class="input-group">
										<label for="sign-email" class="input-group-addon"><i class="fa fa-envelope"></i></label>
										<input name="user[email]" type="email" class="form-control" id="sign-email" placeholder="<?php echo ___('Your email address');?>" required tabindex="1">
									</div>
								</div>
								<div class="form-group">
									<div class="input-group">
										<label for="sign-pwd" class="input-group-addon"><i class="fa fa-lock"></i></label>
										<input name="user[pwd]" type="password" class="form-control" id="sign-pwd" placeholder="<?php echo ___('Your password, at least 3 length');?>" minlength="3" required tabindex="1">
									</div>
								</div>
								<div class="checkbox">
									<label for="sign-agree">
										<input type="checkbox" name="user[agree]" id="sign-agree" value="1" checked required onclick="return false" >
										<?php echo sprintf(___('I am agree the %s'),'<a href="###" target="_blank">' . ___('TOS') . '</a>');?>
									</label>
								</div>
								<div class="form-group">
									<button type="submit" class="btn btn-success" tabindex="1">
										<?php echo ___('Register &amp; Log-in');?>
									</button>
								</div>
								<input type="hidden" name="type" value="register">
							</form>
							<div class="col-sm-12 col-md-6">
								
							</div>
							<?php
							break;
						case 'recover':
							?>
							<form action="javascript:void(0);" id="fm-sign-recover" class="col-sm-12 col-md-6">
								<div class="form-group"><?php echo status_tip('info',___('If you forgot your account password, you can recover your password by your account email. Please entry your account email, we will send a confirm email to it and reset your password.'));?></div>
								<div class="form-group">
									<div class="input-group">
										<label for="sign-email" class="input-group-addon"><i class="fa fa-envelope"></i></label>
										<input type="email" name="user[email]" id="sign-email" class="form-control" required tabindex="1" autofocus placeholder="<?php echo ___('Your account email');?>">
									</div>
								</div>
								<div class="form-group">
									<button type="submit" class="btn btn-success" tabindex="1">
										<i class="fa fa-send"></i> 
										<?php echo ___('Send an email to confirm');?>
									</button>
								</div>
								<input type="hidden" name="type" value="recover">
							</form>
							<div class="col-sm-12 col-md-6">
								
							</div>
							<?php
							break;
						default:
							?>
							<form action="javascript:void(0);" id="fm-sign-login" class="col-sm-12 col-md-6">
								<div class="form-group"><?php echo status_tip('info',___('Welcome to log-in '));?></div>
								<div class="form-group">
									<div class="input-group">
										<label for="sign-email" class="input-group-addon"><i class="fa fa-envelope"></i></label>
										<input name="user[email]" type="email" class="form-control" id="sign-email" placeholder="<?php echo ___('Your email address');?>" required tabindex="1" autofocus>
									</div>
								</div>
								<div class="form-group">
									<div class="input-group">
										<label for="sign-pwd" class="input-group-addon"><i class="fa fa-lock"></i></label>
										<input name="user[pwd]" type="password" class="form-control" id="sign-pwd" placeholder="<?php echo ___('Your password');?>" minlength="3" required tabindex="1">
									</div>
								</div>
								<div class="checkbox">
									<label for="sign-remember">
										<input type="checkbox" name="user[remember]" id="sign-remember" value="1" checked tabindex="1">
										<?php echo ___('Remember me');?>
									</label>
								</div>
								<div class="form-group">
									<button type="submit" class="btn btn-primary" tabindex="1">
										<?php echo ___('Login');?>
									</button>
								</div>
								<input type="hidden" name="type" value="login">
							</form>
							<div class="col-sm-12 col-md-6">
								
							</div>
							<?php
					}
					?>

					</div><!-- /.row -->
				</div><!-- /.panel-body -->
			</div><!-- /.panel -->
		</div><!-- /.main.col-->
		<?php get_sidebar();?>
	</div><!-- /.row -->
</div>
<?php get_footer();?>
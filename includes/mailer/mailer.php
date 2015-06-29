<?php
/** 
 * @version 1.0.0
 */
add_filter('theme_includes',function($fns){
	$fns[] = 'theme_mailer::init';
	return $fns;
});
class theme_mailer{

	public static $iden = 'theme_mailer';

	private static $debug = false;
	
	public static function init(){

		/** ajax */
		add_action('wp_ajax_nopriv_' . self::$iden, __CLASS__ . '::process');
		add_action('wp_ajax_' . self::$iden, __CLASS__ . '::process');

		add_filter('theme_options_save' , __CLASS__ . '::options_save');
		
		add_action('base_settings' , __CLASS__ . '::display_backend',90);

		add_action('backend_seajs_alias',__CLASS__ . '::backend_seajs_alias');
		add_action('after_backend_tab_init',__CLASS__ . '::backend_seajs_use');

		
		if(!self::is_enabled())
			return false;

		add_action('phpmailer_init', __CLASS__ . '::phpmailer_init_smtp');
	}
	public static function options_save(array $opts = []){
		if(isset($_POST[self::$iden])){
			$opts[self::$iden] = $_POST[self::$iden];
		}
		return $opts;
	}
	public static function get_options($key = null){
		static $caches = null;
		if($caches === null)
			$caches = theme_options::get_options(self::$iden);
		if($key)
			return isset($caches[$key]) ? $caches[$key] : false;
		return $caches;
	}
	public static function get_types($key = null){
		$types = [
			'From' => [
				'title' => ___('Form mail'),
				'type' => 'email',
				'placeholder' => ___('E.g., Jack@gmail.com'),
				'des' => ___('You can specify the email address that emails should be sent from. If you leave this blank, the default email will be used.'),
			],
			'FromName' => [
				'title' => ___('From Name'),
				'placeholder' => ___('E.g., Jack'),
				'des' => ___('You can specify the name that emails should be sent from. If you leave this blank, the emails will be sent from your blog name.'),
			],
			'Host' => [
				'title' => ___('SMTP host'),
				'placeholder' => ___('E.g., smtp.gmail.com'),
				'des' => ___('Send all emails via this SMTP server.'),
			],
			'Port' => [
				'title' => ___('SMTP port'),
				'type' => 'number',
				'placeholder' => ___('E.g., 25'),
				'des' => ___('TCP port to connect to.'),
			],
			'SMTPSecure' => [
				'title' => ___('SMTP secure type'),
				'placeholder' => ___('E.g., tls'),
				'des' => ___('Enable TLS encryption, `ssl` also accepted'),
			],
			'Username' => [
				'title' => ___('Username'),
				'placeholder' => ___('E.g., Jack@gmail.com'),
				'des' => ___('SMTP username.'),
			],
			'Password' => [
				'title' => ___('Password'),
				'type' => 'password',
				'placeholder' => ___('Your mail password'),
				'des' => ___('SMTP password.'),
			],
			
		];
		if($key)
			return isset($type[$key]) ? $type[$key] : false;
		return $types;
	}
	public static function display_backend(){
		?>
		<fieldset id="<?= self::$iden;?>">
			<legend><?= ___('SMTP mail settings');?></legend>
			<p class="description"><?= ___('Send mail using smtp server instead of the default mode.');?></p>
			<table class="form-table">
				<tbody>
					<tr>
						<th><label for="<?= self::$iden;?>-enabled"><?= ___('Enable?');?></label></th>
						<td>
							<label for="<?= self::$iden;?>-enabled">
								<input type="checkbox" name="<?= self::$iden;?>[enabled]" id="<?= self::$iden;?>-enabled" value="1" <?= self::is_enabled() ? 'checked' : null;?>>
								<?= ___('Enable');?>
							</label>
						</td>
					</tr>
					<?php foreach(self::get_types() as $k => $v){ ?>
						<tr>
							<th><label for="<?= self::$iden;?>-<?= $k;?>"><?= $v['title'];?></label></th>
							<td>
								<input type="<?= isset($v['type']) ? $v['type'] : 'text';?>" name="<?= self::$iden;?>[<?= $k;?>]" id="<?= self::$iden;?>-<?= $k;?>" value="<?= self::get_options($k);?>" placeholder="<?= $v['placeholder'];?>" class="<?= isset($v['type']) && $v['type'] === 'number' ? 'short-text' : 'widefat';?>">
								<p class="description"><?= $v['des'];?></p>
							</td>
						</tr>
					<?php } ?>
					<tr>
						<th><label for="<?= self::$iden;?>-test-mail"><?= ___('Test');?></label></th>
						<td>
							<div id="<?= self::$iden;?>-tip" class="page-tip none"></div>
							<div id="<?= self::$iden;?>-area-btn">
								<input type="email" id="<?= self::$iden;?>-test-mail" placeholder="<?= ___('Type your test mail');?>">
								<a id="<?=self::$iden;?>-test-btn" href="javascript:;" class="button button-primary"><?= ___('Send a test mail');?></a>
							</div>
						</td>
					</tr>
					
				</tbody>
			</table>
		</fieldset>
		<?php
	}
	public static function is_enabled(){
		return self::get_options('enabled') == 1;
	}
	public static function process(){
		//theme_features::check_nonce();
		theme_features::check_referer();
		$output = [];

		$type = isset($_REQUEST['type']) && is_string($_REQUEST['type']) ? $_REQUEST['type'] : null;
		
		switch($type){
			/**
			 * test
			 */
			case 'test':
				if(!current_user_can('manage_options')){
					die(theme_features::json_format([
						'status' => 'error',
						'code' => 'invaild_permission',
						'msg' => ___('Sorry, your permission is invaild.'),
					]));
				}
				$test = isset($_GET['test']) && is_email($_GET['test']) ? $_GET['test'] : false;

				if(!$test){
					die(theme_features::json_format([
						'status' => 'error',
						'code' => 'invaild_test_mail',
						'msg' => ___('Sorry, test mail is invaild.'),
					]));
				}
				self::$debug = true;
				
				ob_start();
				echo wp_mail(
					$test,
					___('This is a test email.'),
					___('This is a test email generated by your blog.')
				);
				$mail = ob_get_contents();
				ob_end_clean();

				if($mail != 1){
					die(theme_features::json_format([
						'status' => 'error',
						'code' => 'unknow',
						'msg' => sprintf(___('Error message: %s'), $mail),
					]));
				}else{
					die(theme_features::json_format([
						'status' => 'success',
						'msg' => ___('The email has been sent, please check.'),
					]));
				}
			default:
				die(theme_features::json_format([
					'status' => 'error',
					'code' => 'invaild_param',
					'msg' => ___('Sorry, param is invaild.'),
				]));
					
		}
	}
	public static function phpmailer_init_smtp($phpmailer){
		$phpmailer->isSMTP();
		foreach(self::get_types() as $k => $v){
			$phpmailer->$k = self::get_options($k);
		}
		$phpmailer->SMTPDebug = 3;
		$phpmailer->SMTPAuth = true;
	}
	public static function backend_seajs_alias(array $alias = []){
		$alias[self::$iden] = theme_features::get_theme_includes_js(__DIR__,'backend');
		return $alias;
	}
	public static function backend_seajs_use(){
		?>
		seajs.use('<?= self::$iden;?>',function(m){
			m.config.process_url = '<?= theme_features::get_process_url([
				'action' => self::$iden,
				'type' => 'test',
			]);?>';
			m.config.lang.M01 = '<?= ___('Loading, please wait...');?>';
			m.config.lang.E01 = '<?= ___('Server error or network is disconnected.');?>';
			m.init();
		});
		<?php
	}
}
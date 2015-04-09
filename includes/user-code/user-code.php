<?php
/**
 * @version 1.0.0
 */
add_filter('theme_includes',function($fns){
	$fns[] = 'theme_user_code::init';
	return $fns;
});
class theme_user_code{
	public static $iden = 'theme_user_code';
	public static function init(){
		add_action('wp_head',__CLASS__ . '::display_frontend_header',99);
		add_action('wp_footer',__CLASS__ . '::display_frontend_footer',99);
		add_filter('theme_options_save', 	__CLASS__ . '::options_save');
		add_action('base_settings', 		__CLASS__ . '::display_backend');

		add_action('customize_register', __CLASS__ . '::customize');
		
	}
	public static function display_frontend_header(){
		echo stripslashes(self::get_options('header'));
	}
	public static function display_frontend_footer(){
		echo stripslashes(self::get_options('footer'));
	}
	public static function display_backend(){
		$opt = self::get_options();
		?>
		<fieldset>
			<legend><?php echo ___('User custom code settings');?></legend>
			<p class="description"><?php echo ___('You can write some HTML code for your frontend page. Including javascript or css code.');?></p>
			<table class="form-table">
				<tbody>
					<tr>
						<th><label for="<?php echo self::$iden;?>-header"><?php echo ___('Header code');?></label></th>
						<td>
							<textarea name="<?php echo self::$iden;?>[header]" id="<?php echo self::$iden;?>-header" class="widefat code" rows="10"><?php echo isset($opt['header']) ? stripslashes($opt['header']) : null;?></textarea>
							<p class="description"><?php echo esc_html(___('This code will be put between <header> and </header>.'));?></p>
						</td>
					</tr>
					<tr>
						<th><label for="<?php echo self::$iden;?>-footer"><?php echo ___('Footer code');?></label></th>
						<td>
							<textarea name="<?php echo self::$iden;?>[footer]" id="<?php echo self::$iden;?>-footer" class="widefat code" rows="10"><?php echo isset($opt['footer']) ? stripslashes($opt['footer']) : null;?></textarea>
							<p class="description"><?php echo ___('This code will be display on frontend page footer. You can put some statistics code in here.');?></p>
						</td>
					</tr>
				</tbody>
			</table>
		</fieldset>
		<?php
	}
	public static function options_save($opts){
		if(isset($_POST[self::$iden])){
			$opts[self::$iden] = $_POST[self::$iden];
		}
		return $opts;
	}
	public static function get_options($key = null){
		static $caches;
		if(!$caches)
			$caches = theme_options::get_options(self::$iden);
		if($key){
			return isset($caches[$key]) ? $caches[$key] : null;
		}
		return $caches;
	}

	public static function customize($wp_customize){
		$wp_customize->add_section(self::$iden,[
			'title' 		=> ___('User custom code settings'),
			'description' 	=> ___('You can write some HTML code for your frontend page. Including javascript or css code.'),
			'priority' 		=> 120,
		]);
		$wp_customize->add_setting(self::$iden . '[footer]',[
			'capability'	=> 'edit_theme_options',
			'type'			=> 'theme_mod',
		]);
		$wp_customize->add_control(self::$iden . '-footer',[
			'label'			=> ___('Footer codes'),
			'section'		=> self::$iden,
			'settings'		=> self::$iden . '[footer]',
			'type'			=> 'textarea',
		]);
		$wp_customize->add_setting(self::$iden . '[header]',[
			'capability'	=> 'edit_theme_options',
			'type'			=> 'theme_mod',
		]);
		$wp_customize->add_control(self::$iden . '-header',[
			'label'			=> ___('Header codes'),
			'section'		=> self::$iden,
			'settings'		=> self::$iden . '[header]',
			'type'			=> 'textarea',
		]);
	}
}

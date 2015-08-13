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
		add_action('wp_head',__CLASS__ . '::the_frontend_header',99);
		add_filter('theme_options_save', 	__CLASS__ . '::options_save');
		add_filter('theme_options_default', 	__CLASS__ . '::options_default');
		add_action('base_settings', 		__CLASS__ . '::display_backend');

		//add_action('customize_register', __CLASS__ . '::customize');
		
	}
	public static function get_frontend_header_code(){
		return stripslashes(self::get_options('header'));
	}
	public static function get_frontend_footer_code(){
		return stripslashes(self::get_options('footer'));
	}
	public static function the_frontend_header(){
		echo self::get_frontend_header_code();
	}
	public static function display_backend(){
		?>
		<fieldset>
			<legend><?= ___('User custom code settings');?></legend>
			<p class="description"><?= ___('You can write some HTML code for your frontend page. Including javascript or css code.');?></p>
			<table class="form-table">
				<tbody>
					<tr>
						<th><label for="<?= __CLASS__;?>-header"><?= ___('Header code');?></label></th>
						<td>
							<textarea name="<?= __CLASS__;?>[header]" id="<?= __CLASS__;?>-header" class="widefat code" rows="10"><?= self::get_frontend_header_code();?></textarea>
							<p class="description"><?= ___('This code will be put between <header> and </header>.');?></p>
						</td>
					</tr>
					<tr>
						<th><label for="<?= __CLASS__;?>-footer"><?= ___('Footer code');?></label></th>
						<td>
							<textarea title="<?= ___('Default codes');?>" name="<?= __CLASS__;?>[footer]" id="<?= __CLASS__;?>-footer" class="widefat code" rows="10"><?= self::get_frontend_footer_code();?></textarea>
							<p class="description"><?= ___('This code will be display on frontend page footer. You can put some statistics code in here.');?></p>
							<p><textarea rows="2" class="widefat code" readonly ><?= stripslashes(self::get_footer_default());?></textarea></p>
						</td>
					</tr>
				</tbody>
			</table>
		</fieldset>
		<?php
	}
	public static function options_save(array $opts = []){
		if(isset($_POST[__CLASS__]))
			$opts[__CLASS__] = $_POST[__CLASS__];
		return $opts;
	}
	public static function get_footer_default(){
		return sprintf(
			___('&copy; %1$s %2$s. Theme %3$s.'),
			'<a href="' . theme_cache::home_url() . '">' . theme_cache::get_bloginfo('name') . '</a>',
			date('Y'),
			'<a title="' . ___('Views theme homepage') . '" href="' . theme_features::get_theme_info('ThemeURI') . '" target="_blank" rel="nofollow">' . theme_features::get_theme_info('name') . '</a>');
	}
	public static function options_default(array $opts = []){
		$opts[__CLASS__] = [
			'header' => '',
			'footer' => self::get_footer_default()
		];
		return $opts;
	}
	public static function get_options($key = null){
		static $caches;
		if(!$caches)
			$caches = theme_options::get_options(__CLASS__);
		if($key)
			return isset($caches[$key]) ? $caches[$key] : null;
		return $caches;
	}

	public static function customize($wp_customize){
		$opt_prefix = theme_options::$iden . '[' . __CLASS__ . ']';
		$wp_customize->add_section(__CLASS__,[
			'title' 		=> ___('User custom code settings'),
			'description' 	=> ___('You can write some HTML code for your frontend page. Including javascript or css code.'),
			'priority' 		=> 120,
		]);
		$wp_customize->add_setting($opt_prefix . '[footer]',[
			'capability'	=> 'edit_theme_options',
			'type'			=> 'theme_mod',
		]);
		$wp_customize->add_control(__CLASS__ . '-footer',[
			'label'			=> ___('Footer codes'),
			'section'		=> __CLASS__,
			'settings'		=> $opt_prefix . '[footer]',
			'type'			=> 'textarea',
		]);
		$wp_customize->add_setting($opt_prefix . '[header]',[
			'capability'	=> 'edit_theme_options',
			'type'			=> 'theme_mod',
		]);
		$wp_customize->add_control(__CLASS__ . '-header',[
			'label'			=> ___('Header codes'),
			'section'		=> __CLASS__,
			'settings'		=> $opt_prefix . '[header]',
			'type'			=> 'textarea',
		]);
	}
}

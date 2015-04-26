<?php
/**
 * @version 1.0.0
 */
add_filter('theme_includes',function($fns){
	$fns[] = 'default_gravatar::init';
	return $fns;
});
class default_gravatar{
	public static $iden = 'default_gravatar';
	public static function init(){
		add_filter('avatar_defaults', __CLASS__ . '::new_default_gravatar');

		add_action('page_settings', __CLASS__ . '::display_backend');

		add_filter('theme_options_save', __CLASS__ . '::options_save');
	}
	public static function options_save(array $opts = []){
		if(isset($_POST[self::$iden])){
			$opts[self::$iden] = $_POST[self::$iden];
		}
		return $opts;
	}
	public static function new_default_gravatar($default_urls){
		//var_dump(self::get_url());
		if(!empty(self::get_url())){
			$default_urls[self::get_url()] = ___('Custom default gravatar');
		}
		return $default_urls;
	}
	public static function get_url(){
		static $caches = [];
		if(isset($caches['url']))
			return $caches['url'];
			
		$caches['url'] = !empty(self::get_option('url')) ? self::get_option('url') : null;

		return $caches['url'];
	}
	public static function display_backend(){
		?>
		<fieldset>
			<legend><?php echo ___('Custom default gravatar settings');?></legend>
			<p class="description"><?php echo sprintf(___('You can change the default gravatar image using custom avatar url address. After save settings please click here to %s select the custom gravatar.'),'<a href="' . admin_url('options-discussion.php') . '">' . ___('here') . '</a>');?></p>
			<table class="form-table">
				<tbody>
					<tr>
						<th><label for="<?php echo self::$iden;?>-url"><?php echo ___('Custom avatar URL');?></label></th>
						<td>
							<input class="widefat code" type="url" id="<?php echo self::$iden;?>-url" name="<?php echo self::$iden;?>[url]" value="<?php echo self::get_option('url');?>">
						</td>
					</tr>
				</tbody>
			</table>
		</fieldset>
		<?php
	}
	public static function get_option($key = null){
		static $caches;
		if(!is_array($caches))			
			$caches = (array)theme_options::get_options(self::$iden);
			
		if($key){
			return isset($caches[$key]) ? $caches[$key] : null;
		}
		return $caches;
	}
}
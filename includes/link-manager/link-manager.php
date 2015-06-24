<?php
/** 
 * Enables the Link Manager that existed in WordPress until version 3.5.
 */
add_filter('theme_includes',function($fns){
	$fns[] = 'theme_link_manager::init';
	return $fns;
});
class theme_link_manager{
	public static $iden = 'theme_link_manager';
	
	public static function init(){
		add_action('base_settings',__CLASS__ . '::backend_display');
		add_filter('theme_options_save',__CLASS__ . '::options_save');
		
		if(self::is_enabled()){
			add_filter( 'pre_option_link_manager_enabled', '__return_true' );
		}
	}
	public static function backend_display(){
		?>
		<fieldset>
			<legend><?= ___('Link manager');?></legend>
			<p class="description">
				<?= ___('Enables the Link manager that existed in WordPress until version 3.5. But in fact it is not recommend to enable, because you can use Menu instead of Link manager.');?>
			</p>
			<table class="form-table">
				<tbody>
					<tr>
						<th scope="row"><label for="<?= self::$iden;?>-enabled"><?= ___('Enable or not?');?></label></th>
						<td>
							<label for="<?= self::$iden;?>-enabled">
								<input type="checkbox" name="<?= self::$iden;?>[enabled]" id="<?= self::$iden;?>-enabled" value="1" <?= self::is_enabled() ? 'checked' : null;?> />
								<?= ___('Enable');?>
							</label>
						</td>
					</tr>
				</tbody>
			</table>
		</fieldset>
		<?php
	}
	public static function get_options($key = null){
		static $caches = null;
		if($caches === null)
			$caches = theme_options::get_options(self::$iden);

		if($key)
			return isset($caches[$key]) ? $caches[$key] : false;

		return $caches;
	}
	public static function is_enabled(){
		return self::get_options('enabled');
	}
	public static function options_save(array $opts = []){
		if(isset($_POST[self::$iden])){
			$opts[self::$iden] = $_POST[self::$iden];
		}
		return $opts;
	}
}

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
		add_action('base_settings',get_class() . '::backend_display');
		add_filter('theme_options_save',get_class() . '::options_save');
		if(self::is_enabled()){
			add_filter( 'pre_option_link_manager_enabled', '__return_true' );
		}
	}
	public static function backend_display(){
		$options = theme_options::get_options();
		$is_checked = self::is_enabled() ? ' checked ' : null;
		?>
		<fieldset>
			<legend><?php echo ___('Link manager');?></legend>
			<p class="description">
				<?php echo ___('Enables the Link manager that existed in WordPress until version 3.5. But in fact it is not recommend to enable, because you can use Menu instead of Link manager.');?>
			</p>
			<table class="form-table">
				<tbody>
					<tr>
						<th scope="row"><label for="link-manager-on"><?php echo ___('Enable or not?');?></label></th>
						<td><input type="checkbox" name="link-manager[on]" id="link-manager-on" value="1" <?php echo $is_checked;?> /><label for="link-manager-on"><?php echo ___('Enable');?></label></td>
					</tr>
				</tbody>
			</table>
		</fieldset>
		<?php
	}
	public static function is_enabled(){
		$options = theme_options::get_options('link-manager');
		if(isset($options['on'])){
			return true;
		}else{
			return false;
		}
	}
	public static function options_save($options){
		if(isset($_POST['link-manager']['on'])){
			$options['link-manager'] = $_POST['link-manager'];
		}
		return $options;
	}
}

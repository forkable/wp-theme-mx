<?php
/**
 * @version 1.0.0
 */
add_filter('theme_includes',function($fns){
	$fns[] = 'theme_file_timestamp::init';
	return $fns;
});
class theme_file_timestamp{
	public static $iden = 'theme_file_timestamp';
	private static $timestamp;
	
	public static function init(){
		add_action('advanced_settings' , __CLASS__ . '::display_backend');
		add_action('wp_ajax_' . self::$iden, __CLASS__ . '::process');
	}

	public static function process(){
		if(!current_user_can('manage_options'))
			die(___('You have not permission.'));

		theme_options::set_options(self::$iden,$_SERVER['REQUEST_TIME']);

		header('location: ' . theme_options::get_url() . '&' . self::$iden);
		die();
	}

	public static function get_timestamp(){
		if(!self::$timestamp)
			self::$timestamp = theme_options::get_options(self::$iden);
			
		return self::$timestamp;
	}
	public static function set_timestamp($value = null){
		if(!$value)
			$value = $_SERVER['REQUEST_TIME'];

		self::$timestamp = $value;
		theme_options::set_options(self::$iden,self::$timestamp);
	}
	public static function display_backend(){
		?>
		<fieldset>
			<legend><?= ___('File timestamp');?></legend>
			<p class="description"><?= ___('All theme js, css and images static files are output with timestamp, you can refresh these files after theme updates or when you want.');?></p>
			<table class="form-table">
				<tbody>
					<tr>
						<th><?= ___('Control');?></th>
						<td>
							<?php 
							if(isset($_GET[self::$iden])){ 
								echo status_tip('success',___('The file timestamp has been refresh.'));
							}
							?>
							<a href="<?= esc_url(theme_features::get_process_url([
								'action' => self::$iden
							]));?>" class="button button-primary"><?= ___('Refresh now');?></a>
							<span class="description"><i class="fa fa-warning"></i> <?= ___('Save your settings before click');?></span>
						</td>
					</tr>
				</tbody>
			</table>
		</fieldset>
		<?php
	}
}
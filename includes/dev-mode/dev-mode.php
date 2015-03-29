<?php
/*
Feature Name:	Developer mode
Feature URI:	http://www.inn-studio.com
Version:		2.0.0
Description:	启用开发者模式，助于维护人员进行调试，运营网站请禁用此模式
Author:			INN STUDIO
Author URI:		http://www.inn-studio.com
*/
add_filter('theme_includes',function($fns){
	$fns[] = 'theme_dev_mode::init';
	return $fns;
});
class theme_dev_mode{
	public static $iden = 'theme_dev_mode';
	public static $is_enabled;
	public static $opt;
	private static $data = [];
	public static function init(){

		self::$opt = (array)theme_options::get_options(self::$iden);
		self::$is_enabled = isset(self::$opt['on']) ? true : false;
		
		add_filter('theme_options_save',__CLASS__ . '::options_save');
		add_action('after_setup_theme',__CLASS__ . '::mark_start_data',0);
		add_action('wp_footer',__CLASS__ . '::hook_footer',9999);
		add_action('dev_settings',__CLASS__ . '::display_backend');

		
	}

	public static function mark_start_data(){
		// if(!self::is_enabled()) return false;
		self::$data = array(
			'start-time' => timer_stop(0),
			'start-query' => get_num_queries(),
			'start-memory' => sprintf('%01.3f',memory_get_usage()/1024/1024)
		);
	}
	public static function is_enabled(){
		return self::$is_enabled;
	}
	public static function display_backend(){
		
		$checked = self::is_enabled() ? ' checked ' : null;
		
		?>
		<fieldset>
			<legend><?php echo ___('Related Options');?></legend>
			<p class="description"><?php echo ___('For developers to debug the site and it will affect the user experience if enable, please note.');?></p>
			<table class="form-table">
				<tbody>
					<tr>
						<th scope="row">
							<label for="<?php echo self::$iden;?>"><?php echo ___('Developer mode');?></label>
						</th>
						<td>
							<label for="<?php echo self::$iden;?>"><input id="<?php echo self::$iden;?>" name="<?php echo self::$iden;?>[on]" type="checkbox" value="1" <?php echo $checked;?> /> <?php echo ___('Enabled');?></label>
						</td>
					</tr>
				</tbody>
			</table>
		</fieldset>

		<fieldset>
			<legend><?php echo ___('Theme Options');?></legend>
			<textarea class="code widefat" cols="50" rows="50" ><?php esc_textarea(print_r(theme_options::get_options()));?></textarea>
		</fieldset>
		<?php
	}

	/**
	 * save
	 * 
	 * 
	 * @params array
	 * @return array
	 * @version 1.0.2
	 * @author KM@INN STUDIO
	 * 
	 */
	public static function options_save($options){
		if(isset(self::$opt['on']) && !isset($_POST[self::$iden]['on'])){
		
			@ini_set('max_input_nesting_level','9999');
			@ini_set('max_execution_time','300'); 
			
			remove_dir(get_stylesheet_directory() . theme_features::$basedir_js_min);
			remove_dir(get_stylesheet_directory() . theme_features::$basedir_css_min);
			
			theme_features::minify_force(get_stylesheet_directory() . theme_features::$basedir_js_src);
			theme_features::minify_force(get_stylesheet_directory() . theme_features::$basedir_css_src);
			theme_features::minify_force(get_stylesheet_directory() . theme_features::$basedir_includes);
		}
		if(isset($_POST[self::$iden]['on'])){
			$options[self::$iden] = $_POST[self::$iden];
		}
		return $options;
	}

	public static function hook_footer(){
		?>
		<script>
		try{
			<?php
			self::$data['end-time'] =  timer_stop(0);
			self::$data['end-query'] = get_num_queries();
			self::$data['end-memory'] = sprintf('%01.3f',memory_get_usage()/1024/1024);
			
			self::$data['theme-time'] = self::$data['end-time'] - self::$data['start-time'];
			self::$data['theme-query'] = self::$data['end-query'] - self::$data['start-query'];
			self::$data['theme-memory'] = self::$data['end-memory'] - self::$data['start-memory'];

			$data = array(
				___('Theme Performance') => array(
					___('Time (second)') => self::$data['theme-time'],
					___('Query') => self::$data['theme-query'],
					___('Memory (MB)') => self::$data['theme-memory'],
				),
				___('Basic Performance') => array(
					___('Time (second)') => (float)self::$data['start-time'],
					___('Query') => (float)self::$data['start-query'],
					___('Memory (MB)') => (float)self::$data['start-memory'],
				),
				___('Final Performance') => array(
					___('Time (second)') => (float)self::$data['end-time'],
					___('Query') => (float)self::$data['end-query'],
					___('Memory (MB)') => (float)self::$data['end-memory'],
				),
			);
			?>
			(function(){
				var data = <?php echo json_encode($data);?>;
				console.table(data);
			})();
		}catch(e){}
		</script>
		<?php
	}
}
?>
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
	private static $data = [];
	public static function init(){
		
		add_filter('theme_options_save',__CLASS__ . '::options_save');

		add_action('dev_settings',__CLASS__ . '::display_backend');
		
		add_action('dev_settings',__CLASS__ . '::display_backend_options_list',90);
		
		if(self::get_options('performance')){
			add_action('after_setup_theme',__CLASS__ . '::mark_start_data',1);
			add_action('wp_footer',__CLASS__ . '::hook_footer_performance',90);
		}
		
		if(self::get_options('queries')){
			add_action('wp_footer',__CLASS__ . '::hook_footer_queries',99);
		}
		
	}
	public static function is_enabled(){
		return self::get_options('enabled');
	}
	public static function get_options($key = null){
		static $caches = [];
		if(!isset($caches[self::$iden]))
			$caches[self::$iden] = (array)theme_options::get_options(self::$iden);
			
		
		
		if($key === null){
			return $caches[self::$iden];
		}else{
			return isset($caches[self::$iden][$key]) ? $caches[self::$iden][$key] : false;
		}
	}
	public static function mark_start_data(){
		self::$data = array(
			'start-time' => microtime(),
			'start-query' => get_num_queries(),
			'start-memory' => memory_get_usage(),
		);
	}

	public static function display_backend(){
		$opt = self::get_options();
		$checked = self::is_enabled() ? ' checked ' : null;
		
		$checked_queries = isset($opt['queries']) && $opt['queries'] == 1 ? ' checked ' : null;
		
		$checked_performance = isset($opt['performance']) && $opt['performance'] == 1 ? ' checked ' : null;
		?>
		<fieldset>
			<legend><?= ___('Related Options');?></legend>
			<p class="description"><?= ___('For developers to debug the site and it will affect the user experience if enable, please note.');?></p>
			<table class="form-table">
				<tbody>
					<tr>
						<th scope="row">
							<label for="<?= self::$iden;?>-enabled"><?= ___('Developer mode');?></label>
						</th>
						<td>
							<label for="<?= self::$iden;?>-enabled"><input id="<?= self::$iden;?>-enabled" name="<?= self::$iden;?>[enabled]" type="checkbox" value="1" <?= $checked;?> /> <?= ___('Enabled');?></label>

							<span class="description"><?= ___('If enable, your site will work within development mode, or works within high performance product mode.');?></span>

							<input type="hidden" name="<?= self::$iden;?>[old-enabled]" value="<?= $checked ? 1 : -1 ;?>">
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="<?= self::$iden;?>-performance"><?= ___('Display frontend performance');?></label>
						</th>
						<td>
							<label for="<?= self::$iden;?>-performance"><input id="<?= self::$iden;?>-performance" name="<?= self::$iden;?>[performance]" type="checkbox" value="1" <?= $checked_performance;?> /> <?= ___('Enabled');?></label>
							<span class="description"><?= ___('Display theme performance information on frontend from F12 console.');?></span>

						</td>
					</tr>
					
					<tr>
						<th scope="row">
							<label for="<?= self::$iden;?>-queries"><?= ___('Display database queries detail');?></label>
						</th>
						<td>
							<label for="<?= self::$iden;?>-queries"><input id="<?= self::$iden;?>-queries" name="<?= self::$iden;?>[queries]" type="checkbox" value="1" <?= $checked_queries;?> /> <?= ___('Enabled');?></label>
							<span class="description"><?= ___('Display database queries detail on frontend from F12 console.');?></span>

						</td>
					</tr>
				</tbody>
			</table>
		</fieldset>
		<?php
	}
	public static function display_backend_options_list(){
		?>
		<fieldset>
			<legend><?= ___('Theme options debug');?></legend>
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
	 * @version 2.0.0
	 * @author KM@INN STUDIO
	 * 
	 */
	public static function options_save(array $options = []){
		if(isset($_POST[self::$iden])){
			$options[self::$iden] = $_POST[self::$iden];
			//var_dump($options);exit;
			
			$old_enable = isset($_POST[self::$iden]['old-enabled']) ? $_POST[self::$iden]['old-enabled'] : -1;

			if(isset($options[self::$iden]['old-enabled']))
				unset($options[self::$iden]['old-enabled']);

			/**
			 * Dev mode ON => OFF, do minify
			 */
			if($old_enable == 1 && !isset($_POST[self::$iden]['enabled'])){
				
				@ini_set('max_input_nesting_level','9999');
				@ini_set('max_execution_time','300'); 
				
				remove_dir(theme_features::get_stylesheet_directory() . theme_features::$basedir_js_min);
				theme_features::minify_force(theme_features::get_stylesheet_directory() . theme_features::$basedir_js_src);
				
				remove_dir(theme_features::get_stylesheet_directory() . theme_features::$basedir_css_min);
				theme_features::minify_force(theme_features::get_stylesheet_directory() . theme_features::$basedir_css_src);
				
				theme_features::minify_force(theme_features::get_stylesheet_directory() . theme_features::$basedir_includes);
			}
		}

		return $options;
	}

	public static function hook_footer_performance(){
		?>
		<script>
		<?php
		self::$data['end-time'] =  microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'];
		self::$data['end-query'] = get_num_queries();
		self::$data['end-memory'] = memory_get_usage();
		
		self::$data['theme-time'] = self::$data['end-time'] - self::$data['start-time'];
		self::$data['theme-query'] = self::$data['end-query'] - self::$data['start-query'];
		self::$data['theme-memory'] = self::$data['end-memory'] - self::$data['start-memory'];

		$memory_format = '%08.5f';
		$query_format = '%03.0f';
		$time_format = '%06.3f';

		$lang = [
			'des' => ___('Description'),
			'time' => ___('Time (second)'),
			'query' => ___('Query'),
			'memory' => ___('Memory (MB)'),
		];
		$data = [
			/**
			 * theme
			 */
			[
			$lang['des'] 	=> ___('Theme Performance'),
			$lang['time'] 	=> sprintf($time_format,self::$data['theme-time']),
			$lang['query'] 	=> sprintf($query_format,self::$data['theme-query']),
			$lang['memory'] => sprintf($memory_format,self::$data['theme-memory']/1024/1024),
			],
			/**
			 * basic
			 */
			[
			$lang['des'] 	=> ___('Basic Performance'),
			$lang['time'] 	=> sprintf($time_format,self::$data['start-time']),
			$lang['query'] 	=> sprintf($query_format,self::$data['start-query']),
			$lang['memory'] => sprintf($memory_format,self::$data['start-memory']/1024/1024),
			],
			/**
			 * end
			 */
			[
			$lang['des'] 	=> ___('Final Performance'),
			$lang['time'] 	=> sprintf($time_format,self::$data['end-time']),
			$lang['query'] 	=> sprintf($query_format,self::$data['end-query']),
			$lang['memory'] => sprintf($memory_format,self::$data['end-memory']/1024/1024),
			],
		];
		?>
		(function(){
			console.table(<?= json_encode($data);?>);
		})();
		</script>
		<?php

	}
	public static function hook_footer_queries(){
		global $wpdb;
		?>
		<script>
		console.table(<?= json_encode($wpdb->queries);?>);
		</script>
		<?php
	}
}
?>
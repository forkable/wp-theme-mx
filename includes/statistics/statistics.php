<?php
/*
Feature Name:	统计代码设置
Feature URI:	http://www.inn-studio.com
Version:		1.0.3
Description:	设置主题的统计代码
Author:			INN STUDIO
Author URI:		http://www.inn-studio.com
*/
add_filter('theme_includes',function($fns){
	$fns[] = 'theme_statistics::init';
	return $fns;
});
class theme_statistics{
	public static $iden = 'theme_statistics';

	public static function init(){
		add_filter('theme_options_save', 	get_class() . '::options_save');
		add_action('base_settings', 		get_class() . '::backend_display');
		add_action('wp_footer', 			get_class() . '::frontend_display');
	}
	public static function backend_display(){
		
		$options = theme_options::get_options();
		$statistics = isset($options['statistics']) ? stripslashes($options['statistics']) : null;
		?>
		<!-- 统计设置 -->
		<fieldset>
			<legend><?php echo ___('Statistics Settings');?></legend>
			<p class="description"><?php echo ___('You can put the statistics codes into the text area if you need. (The statistics codes maybe JavasSript)');?></p>
			<table class="form-table">
				<tbody>
					<tr>
						<th scope="row"><?php echo ___('The statistics codes');?></th>
						<td><textarea id="statistics" name="statistics" class="widefat" cols="30" rows="10"><?php echo stripslashes($statistics);?></textarea>
						</td>
					</tr>
				</tbody>
			</table>
		</fieldset>
	<?php
	}
	
	public static function options_save($options){
		if(isset($_POST['statistics'])){
			$options['statistics'] = $_POST['statistics'];
		}
		return $options;
	}
	/* 前台调用 */
	public static function frontend_display(){
		$options = theme_options::get_options();
		if(!isset($options['statistics']) || empty($options['statistics'])) return false;
		echo stripslashes($options['statistics']);
	}
}
?>
<?php
/*
Feature Name:	theme_import_settings
Feature URI:	http://www.inn-studio.com
Version:		3.0.0
Description:	theme_import_settings
Author:			INN STUDIO
Author URI:		http://www.inn-studio.com
*/
add_filter('theme_includes',function($fns){
	$fns[] = 'theme_import_settings::init';
	return $fns;
});
class theme_import_settings{
	public static $iden = 'theme_import_settings';
	
	public static function init(){
		add_action('wp_ajax_' . self::$iden,	__CLASS__ . '::process');
		add_action('after_backend_tab_init',	__CLASS__ . '::backend_seajs_use'); 
		add_filter('backend_seajs_alias' , 		__CLASS__ . '::backend_seajs_alias');
		add_action('backend_css',				__CLASS__ . '::style'); 
		add_action('advanced_settings',			__CLASS__ . '::display_backend',99);		
	}
	public static function display_backend(){
		
		?>
		<fieldset>
			<legend><?= ___('Import & Export Theme Settings');?></legend>
			<p class="description">
				<?= ___('You can select the settings file to upload and restore settings if you have the *.txt file. If you want to export the settings backup, please click the export button.');?>
			</p>
			<table class="form-table">
				<tbody>
					<tr>
						<th scope="row"><?= ___('Import');?></th>
						<td>
							<div id="tis_tip"></div>
							<div id="tis_upload_area">
								<a href="javascript:;" id="tis_upload" class="button"><?= ___('Select a setting file to restore');?></a>
								<input id="tis_file" type="file"/>
							</div>
						</td>
					</tr>
					<tr>
						<th scope="row"><?= ___('Export');?></th>
						<td>
							<a href="<?= theme_features::get_process_url(array('action'=>self;:$iden));?>" id="tis_export" class="button"><?= ___('Start export settings file');?></a>
						</td>
					</tr>
				</tbody>
			</table>
		</fieldset>
	<?php
	}
	/**
	 * Process
	 * 
	 * 
	 * @return 
	 * @version 1.0.0
	 * @author KM@INN STUDIO
	 * 
	 */
	public static function process(){
		if(current_user_can('manage_options'))
			return false;
			
		$output = [];
		
		$type = isset($_GET['type']) && is_string($_GET['type']) ? $_GET['type'] : null;

		switch($type){
			case 'upload':
				$contents = isset($_POST['content']) ? @unserialize(base64_decode($_POST['content'])) : null;
				if(is_array($contents) && !empty($contents)){
					set_theme_mod(theme_options::$iden,$contents);
					$output['status'] = 'success';
					$output['msg'] = ___('Settings has been restored, refreshing page, please wait... ');
				/**
				 * invalid contents
				 */
				}else{
					$output['status'] = 'error';
					$output['msg'] = ___('Invalid content. ');
				}
				break;
			case 'download':
				$options = theme_options::get_options();
				$contents = base64_encode(serialize($options));
				/**
				 * write content to a tmp file
				 */
				$tmp = tempnam('/tmp','tis');
				$handle = fopen($tmp,"w");
				fwrite($handle,$contents);
				fclose($handle);
				/**
				 * output file download
				 */
				header('Content-type: application/txt');
				$download_fn = str_ireplace('http://','',home_url());
				$download_fn = str_ireplace('https://','',$download_fn);
				$download_fn = str_ireplace('/','-',$download_fn);
				$download_fn = $download_fn . '.' . date('Ydm') . '.txt';
				header('Content-Disposition: attachment; filename=" ' . $download_fn . '"');
				readfile($tmp); 
				unlink($tmp);
				exit;
				$output['status'] = 'success';
				$output['msg'] = ___('Settings has been restored, refreshing page, please wait... ');
				break;
		}

		die(theme_features::json_format($output));
	}
	/**
	 * Load style
	 * 
	 * 
	 * @return string HTML
	 * @version 1.0.0
	 * @author KM@INN STUDIO
	 * 
	 */
	public static function style(){
		?>
		<link href="<?= theme_features::get_theme_includes_css(__DIR__,'bakcend',true);?>" rel="stylesheet"  media="all"/>
		<?php
	}
	public static function backend_seajs_alias(array $alias = []){
		$alias[self::$iden] = theme_features::get_theme_includes_js(__DIR__,'backend');
		return $alias;
	}
	public static function backend_seajs_use(){
		?>
		seajs.use('<?= self::$iden;?>',function(m){
			m.config.lang.M00001 = '<?= ___('Processing, please wait...');?>';
			m.config.lang.M00002 = '<?= ___('Error: Your browser does not support HTML5. ');?>';
			m.config.lang.E00001 = '<?= ___('Error: failed to complete the operation. ');?>';
			m.config.lang.E00002 = '<?= ___('Error: Not match file. ');?>';
			m.config.process_url = '<?= theme_features::get_process_url([
				'action' => self::$iden
			]);?>';
			m.init();
		});
		<?php
	}
}

?>

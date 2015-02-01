<?php
/*
Feature Name:	theme_import_settings
Feature URI:	http://www.inn-studio.com
Version:		1.0.3
Description:	theme_import_settings
Author:			INN STUDIO
Author URI:		http://www.inn-studio.com
*/
add_action('wp_ajax_tis_download','theme_import_settings::process');
add_action('wp_ajax_tis_upload','theme_import_settings::process');
add_action('after_backend_tab_init','theme_import_settings::js'); 
add_action('backend_css','theme_import_settings::style'); 
add_action('advanced_settings','theme_import_settings::admin',99);
class theme_import_settings{
	public static $iden = 'theme_import_settings';

	public static function admin(){
		
		$options = theme_options::get_options();
		?>
		<fieldset>
			<legend><?php echo ___('Import & Export Theme Settings');?></legend>
			<p class="description">
				<?php echo ___('You can select the settings file to upload and restore settings if you have the *.txt file. If you want to export the settings backup, please click the export button.');?>
			</p>
			<table class="form-table">
				<tbody>
					<tr>
						<th scope="row">
							<?php echo ___('Import: ');?>
						</th>
						<td>
							<div id="tis_tip"></div>
							<div id="tis_upload_area">
								<a href="javascript:void(0);" id="tis_upload" class="button"><?php echo ___('Select a setting file to restore');?></a>
								<input id="tis_file" type="file"/>
							</div>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<?php echo ___('Export: ');?>
						</th>
						<td>
							<a href="<?php echo theme_features::get_process_url(array('action'=>'tis_download'));?>" id="tis_export" class="button"><?php echo ___('Start export settings file');?></a>
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
		
		$output = null;
		/**
		 * tis_upload
		 */
		if(isset($_GET['action']) && $_GET['action'] === 'tis_upload'){
			/**
			 * is administrator
			 */
			if(current_user_can('administrator')){
				$contents = isset($_POST['tis_content']) ? @unserialize(base64_decode($_POST['tis_content'])) : null;
				if(is_array($contents) && !empty($contents)){
					update_option('theme_options',$contents);
					$output['status'] = 'success';
					$output['des']['content'] = ___('Settings has been restored, refreshing page, please wait... ');
				/**
				 * invalid contents
				 */
				}else{
					$output['status'] = 'error';
					$output['des']['content'] = ___('Invalid content. ');
				}
			/**
			 * invalid user
			 */
			}else{
					$output['status'] = 'error';
					$output['des']['content'] = ___('Invalid user. ');
			}
		}
		/**
		 * download
		 */
		if(isset($_GET['action']) && $_GET['action'] === 'tis_download'){
			if(current_user_can('administrator')){

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
				$output['des']['content'] = ___('Settings has been restored, refreshing page, please wait... ');
			}else{
				$output['status'] = 'error';
				$output['des']['content'] = ___('Invalid user. ');
			}
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
		<link href="<?php echo theme_features::get_theme_includes_css(__FILE__);?>" rel="stylesheet"  media="all"/>
		<?php
	}
	/**
	 * Load js
	 * 
	 * 
	 * @return 
	 * @version 1.0.0
	 * @author KM@INN STUDIO
	 * 
	 */
	public static function js(){
		
		?>
		
		seajs.use('<?php echo theme_features::get_theme_includes_js(__FILE__);?>',function(m){
			m.config.lang.M00001 = '<?php echo ___('Processing, please wait...');?>';
			m.config.lang.M00002 = '<?php echo ___('Error: Your browser does not support HTML5. ');?>';
			m.config.lang.E00001 = '<?php echo ___('Error: failed to complete the operation. ');?>';
			m.config.lang.E00002 = '<?php echo ___('Error: Not match file. ');?>';
			m.config.process_url = '<?php echo theme_features::get_process_url();?>';
			m.init();
		});

		<?php
	}
}

?>

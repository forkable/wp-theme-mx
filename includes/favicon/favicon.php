<?php
/*
Feature Name:	theme_custom_favicon
Feature URI:	http://www.inn-studio.com
Version:		1.0.0
Description:	theme_custom_favicon
Author:			INN STUDIO
Author URI:		http://www.inn-studio.com
*/
add_filter('theme_includes',function($fns){
	$fns[] = 'theme_custom_favicon::init';
	return $fns;
});
class theme_custom_favicon{
	public static $iden = 'theme_custom_favicon';
	public static $file_exts = array('png');
	public static $icon_sizes = [
		'apple-touch-icon' => [57,76,120,152,180],
		'shortcut icon'	=> [16],
		'icon' => [192,128],
		
	];
	public static function init(){

		add_action('page_settings',get_class() . '::display_backend');
		add_action('wp_ajax_' . self::$iden,get_class() . '::process');
		add_filter('theme_options_save',get_class() . '::options_save');

		add_filter('after_backend_tab_init',__CLASS__ . '::backend_seajs_use');
		add_filter('backend_seajs_alias',__CLASS__ . '::backend_seajs_alias');
		add_action('backend_css',__CLASS__ . '::backend_css'); 

		add_action('wp_head',get_class() . '::wp_head');
	}
	private static function get_max_size(){
		static $max_size;
		if($max_size)
			return $max_size;
			
		$sizes = [];
		foreach(self::$icon_sizes as $k => $v){
			foreach($v as $size){
				$sizes[] = $size;
			}
		}
		$max_size = max($sizes);
		return $max_size;
	}
	public static function options_save($options){
		if(!isset($_POST[self::$iden]['url']) || empty($_POST[self::$iden]['url']))
			return $options;
		
		$options[self::$iden] = $_POST[self::$iden];
		$attach_id = $_POST[self::$iden]['attach-id'];
		$max_size_url = null;
		ob_start();
		foreach(self::$icon_sizes as $k => $v){
			foreach($v as $size){
				$url = wp_get_attachment_image_src($attach_id,self::$iden . '-' . $size)[0];
				if(self::get_max_size() == $size){
					$max_size_url = $url;
				}
				
				if(self::get_max_size() != $size && $url == $max_size_url)
					continue;
					
				if(!$url)
					continue;
				?>
				<link rel="<?php echo $k;?>" href="<?php echo esc_url($url);?>" size="<?php echo "{$size}x{$size}";?>">
				<?php
			}
		}
		$content = html_compress(ob_get_contents());
		ob_end_clean();
		$options[self::$iden]['icons'] = $content;

		return $options;
	}
	public static function get_options($key = null){
		static $caches = [];
		if(!isset($caches[self::$iden]))
			$caches[self::$iden] = theme_options::get_options(self::$iden);
		if($key){
			return isset($caches[self::$iden][$key]) ? $caches[self::$iden][$key] : null;
		}
		return $caches[self::$iden];
	}
	public static function wp_head(){
		$icons = self::get_options('icons');
		if(!$icons){
			?>
<link rel="shortcut icon" href="<?php echo theme_features::get_theme_images_url('frontend/favicon.ico',true,true);?>" type="image/x-icon" />
<link rel="apple-touch-icon" href="<?php echo theme_features::get_theme_images_url('frontend/apple-touch-icon.png',true,true);?>" />
<link rel="icon" href="<?php echo theme_features::get_theme_images_url('frontend/apple-touch-icon.png',true,true);?>" />
			<?php
			return;
		}
		echo $icons;
	}
	public static function process(){
		$output = array();
		
		/** 
		 * if not image
		 */
		$filename = isset($_FILES['img']['name']) ? $_FILES['img']['name'] : null;
		$file_ext = $filename ? array_slice(explode('.',$filename),-1,1)[0] : null;
		if(!in_array($file_ext,self::$file_exts)){
			$output['status'] = 'error';
			$output['code'] = 'invaild_file_type';
			$output['msg'] = ___('Invaild file type.');
			die(theme_features::json_format($output));
		}
		/** 
		 * check permission
		 */
		if(!current_user_can('manage_options')){
			$output['status'] = 'error';
			$output['code'] = 'invaild_permission';
			$output['msg'] = ___('You have not permission to upload.');
			die(theme_features::json_format($output));
		}
		/** 
		 * pass
		 */
		require_once( ABSPATH . 'wp-admin/includes/image.php' );
		require_once( ABSPATH . 'wp-admin/includes/file.php' );
		require_once( ABSPATH . 'wp-admin/includes/media.php' );

		foreach(self::$icon_sizes as $k => $v){
			foreach($v as $size){
				add_image_size(self::$iden . '-' . $size,$size,$size,true);	
			}
		}
		
		$attach_id = media_handle_upload('img',0);
		if(is_wp_error($attach_id)){
			$output['status'] = 'error';
			$output['code'] = $attach_id->get_error_code();
			$output['msg'] = $attach_id->get_error_message();
			die(theme_features::json_format($output));
		}else{
			$output['status'] = 'success';
			$output['url'] = wp_get_attachment_image_src($attach_id,self::$iden . '-' . self::get_max_size())[0];
			$output['attach_id'] = $attach_id;
			$output['msg'] = ___('Upload success.');
			die(theme_features::json_format($output));
		}
		die(theme_features::json_format($output));
	}
	public static function display_backend(){
		$opt = self::get_options();
		$url = isset($opt['url']) ? $opt['url'] : null;
		$attach_id = isset($opt['attach-id']) ? (int)$opt['attach-id'] : null;
		
		?>
		<fieldset>
			<legend><?php echo ___('Favicon settings');?></legend>
			<p class="description">
				<?php echo sprintf(___('You can set a PNG image for blog favicon. Image size is %s&times;%s px. Remember save your settings when all done.'),self::get_max_size(),self::get_max_size());?>
			</p>
			<table class="form-table">
			<tbody>
			<tr>
			<th><?php echo ___('Image URL');?></th>
			<td>
				<div id="<?php echo self::$iden;?>-area">
					<input type="url" name="<?php echo self::$iden;?>[url]" id="<?php echo self::$iden;?>-url" class="code url" value="<?php echo esc_url($url);?>" placeholder="<?php echo esc_attr(___('Image URL (include http://)'));?>"/>
					
					<a href="javascript:void(0);" class="button-primary" id="<?php echo self::$iden;?>-upload"><?php echo esc_html(___('Upload image'));?><input type="file" id="<?php echo self::$iden;?>-file"/></a>
				</div>
				<div id="<?php echo self::$iden;?>-tip"></div>
				<input type="hidden" name="<?php echo self::$iden;?>[attach-id]" value="<?php echo $attach_id;?>" id="<?php echo self::$iden;?>-attach-id">
			</td>
			</tr>
			</tbody>
			</table>
		</fieldset>
	<?php
	}

	public static function backend_css(){
		?>
		<link href="<?php echo theme_features::get_theme_includes_css(__DIR__);?>" rel="stylesheet"  media="all"/>
		<?php
	}
	public static function backend_seajs_alias(array $alias = []){
		$alias[self::$iden] = theme_features::get_theme_includes_js(__DIR__,'backend.js');
		return $alias;
	}
	public static function backend_seajs_use(){
		
		?>
		
		seajs.use('<?php echo self::$iden;?>',function(m){
			m.config.process_url = '<?php echo theme_features::get_process_url(array('action'=>self::$iden));?>';
			m.config.lang.M00001 = '<?php echo ___('Loading, please wait...');?>';
			m.config.lang.E00001 = '<?php echo ___('Server error or network is disconnected.');?>';
			m.init();
		});

		<?php
	}

}

?>

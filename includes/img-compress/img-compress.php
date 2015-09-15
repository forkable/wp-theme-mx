<?php
/**
 * img compress
 *
 * @version 1.0.0
 */
add_filter('theme_includes',function($fns){
	$fns[] = 'theme_img_compress::init';
	return $fns;
});
class theme_img_compress{
	public static function init(){
		add_filter('wp_handle_upload_prefilter', __CLASS__ . '::compress_jpeg_quality', 1, 99 );

		add_filter('theme_options_save', __CLASS__ . '::options_save');
		add_filter('theme_options_default', __CLASS__ . '::options_default');

		add_action('base_settings', __CLASS__ . '::display_backend');
		
		if(theme_cache::current_user_can('manage_options'))
			return;
			
		add_filter('wp_handle_upload_prefilter', __CLASS__ . '::filter_wp_handle_upload_prefilter' );
	}
	public static function options_save(array $opts = []){
		if(isset($_POST[__CLASS__]))
			$opts[__CLASS__] = $_POST[__CLASS__];
		return $opts;
	}
	public static function options_default(array $opts = []){
		$opts[__CLASS__] = [
			'jpeg-quality' => 65,
			'png2jpg' => 1,
		];
		return $opts;
	}
	public static function get_options($key = null){
		static $caches = null;
		if($caches === null)
			$caches = (array)theme_options::get_options(__CLASS__);
			
		if($key)
			return isset($caches[$key]) ? $caches[$key] : false;
		return $caches;
	}
	public static function get_jpg_quality(){
		return self::get_options('jpg-quality') ? (int)self::get_options('jpg-quality') : 65;
	}
	public static function is_png2jpg(){
		return self::get_options('png2jpg') == 1;
	}
	public static function display_backend(){
		?>
		<fieldset>
			<legend><?= ___('Image settings');?></legend>
			<p class="description"><?= ___('Global image settings.');?></p>
			<table class="form-table">
				<tr>
					<th><label for="<?= __CLASS__;?>-png2jpg"><?= ___('PNG to JPG format');?></label></th>
					<td>
						<label for="<?= __CLASS__;?>-png2jpg">
							<input type="checkbox" id="<?= __CLASS__;?>-png2jpg" name="<?= __CLASS__;?>[png2jpg]" value="1" <?= self::is_png2jpg() ? 'checked' : null;?> > 
							<?= ___('Enable');?>
						</label>
						<span class="description"><?= ___('It will convent png to jpg image format When user upload image file. This feature always disable if is administrator.');?></span>
					</td>
				</tr>
				<tr>
					<th><label for="<?= __CLASS__;?>-jpeg-quality"><?= ___('JPG image compress quality');?></label></th>
					<td>
						<input type="number" id="<?= __CLASS__;?>-jpeg-quality" name="<?= __CLASS__;?>[jpeg-quality]" value="<?= self::get_jpg_quality();?>" min="1" max="100" step="1" class="short-number"> 
						<span class="description"><?= ___('It will compress image When user upload image file.');?></span>
					</td>
				</tr>
			</table>
		</fieldset>
		<?php
	}
	/**
	 * png to jpg
	 *
	 * @param array $file
	 * @return array $file
	 * @version 1.0.0
	 */
	public static function filter_wp_handle_upload_prefilter( $file ){
		if(!self::is_png2jpg())
			return $file;
			
		$file_ext = strtolower(substr(strrchr($file['name'],'.'), 1));
		
		if(!$file_ext || $file_ext !== 'png')
			return $file;
			
		/** rename png to jpg */
		$file['name'] = basename($file['name']) . '.jpg';
		
		$img = @imagecreatefrompng($file['tmp_name']);
		if(!$img)
			return $file;
		
		imagejpeg($img, $file['tmp_name']);
		imagedestroy($img);

	    return $file;
	}
	public static function compress_jpeg_quality($file){
		$file_ext = strtolower(substr(strrchr($file['name'],'.'), 1));
		if(!$file_ext || !($file_ext === 'jpg' || $file_ext === 'jpeg'))
			return $file;
			
		$img = @imagecreatefromjpeg($file['tmp_name']);
		if(!$img)
			return $file;
		
		imagejpeg($img, $file['tmp_name'],self::get_jpg_quality());
		imagedestroy($img);

	    return $file;
	}
}


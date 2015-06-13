<?php
/** 
 * @version 1.0.0
 */
add_filter('theme_includes',function($fns){
	$fns[] = 'theme_png2jpg::init';
	return $fns;
});
class theme_png2jpg{
	public static $iden = 'theme_png2jpg';
	public static function init(){
		add_filter('wp_handle_upload_prefilter', __CLASS__ . '::filter_wp_handle_upload_prefilter' );

	}

	/**
	 * png to jpg
	 *
	 * @param array $file
	 * @return array $file
	 * @version 1.0.0
	 */
	public static function filter_wp_handle_upload_prefilter( $file ){
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
}
<?php
/**
 * @version 1.0.1
 */
if(!class_exists('emoji_fix')){
	add_filter( 'emoji_url', 'emoji_fix::url' );
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	add_action('wp_footer', 'print_emoji_detection_script', 7 );
	//remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
	//remove_action( 'wp_print_styles', 'print_emoji_styles' );
	//remove_action( 'admin_print_styles', 'print_emoji_styles' ); 
	class emoji_fix{
		public static function url(){
			static $url = null;
			if($url === null)
				$url = set_url_scheme('//cdnjs.cloudflare.com/ajax/libs/twemoji/1.4.1/72x72/');
			return $url;
		}
	}
}


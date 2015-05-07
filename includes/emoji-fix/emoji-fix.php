<?php
/**
 * @version 1.0.0
 */
if(!class_exists('emoji_fix')){
	add_filter( 'emoji_url', 'emoji_fix::url' );
	class emoji_fix{
		public static function url(){
			static $url = null;
			if($url === null)
				$url = set_url_scheme('//cdnjs.cloudflare.com/ajax/libs/twemoji/1.4.1/72x72/');
			return $url;
		}
	}
}


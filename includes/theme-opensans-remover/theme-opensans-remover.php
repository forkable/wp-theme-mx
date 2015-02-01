<?php

add_action('wp_default_styles','theme_opensans_remover::init');
class theme_opensans_remover{
	public static function init($styles){
		$registered = $styles->registered;
		foreach($registered as $k => $v){
			/** 
			 * search open-sans key
			 */
			$remove_key = array_search('open-sans',$v->deps);
			/** 
			 * upset obj Property
			 */
			if($remove_key !== false) unset($registered[$k]->deps[$remove_key]);
		}
	}
}
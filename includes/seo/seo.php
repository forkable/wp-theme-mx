<?php
/*
Feature Name:	SEO PLUS
Feature URI:	http://www.inn-studio.com
Version:		1.4.3
Description:	Improve the seo friendly
*/
add_filter('theme_includes',function($fns){
	$fns[] = 'theme_seo_plus::init';
	return $fns;
});
class theme_seo_plus{
	private static $iden = 'theme_seo_plus';
	private static $keywords_split = ',';
	public static function init(){
		add_action('base_settings',__CLASS__ . '::display_backend',5);
		add_action('wp_head',__CLASS__ . '::get_site_keywords');
		add_action('wp_head',__CLASS__ . '::get_site_description');
		add_filter('theme_options_save',__CLASS__ . '::options_save');
		add_filter('wp_title',__CLASS__ . '::wp_title',10,2);
	}
	public static function wp_title($title, $sep){
		$sep = ' - ';
		return str_replace('|',$sep,$title);
	}
	public static function get_options($key = null){
		static $cache = null;
		if($cache === null)
			$cache = (array)theme_options::get_options(__CLASS__);
		if($key)
			return isset($cache[$key]) ? $cache[$key] : null;
		return $cache;
	}
	public static function display_backend(){
		?>
		<!-- SEO meta -->
		<fieldset>
			<legend><?= ___('SEO settings');?></legend>
			<p class="description"><?= sprintf(___('Fill in the appropriate keywords, can improve search engine friendliness. Use different key words in English comma (%s) to separate.'),self::$keywords_split);?></p>
			<table class="form-table">
				<tbody>
					<tr>
						<th scope="row"><label for="seo_plus_description"><?= ___('Site description');?></label></th>
						<td>
							<input id="seo_plus_description" name="<?= __CLASS__;?>[description]" class="widefat" type="text" value="<?= esc_attr(self::get_options('description'));?>"/>
							<p class="description"><?= ___('Recommend to control that less than 100 words.');?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="seo_plus_keywords"><?= ___('Site keywords');?></label></th>
						<td>
							<input id="seo_plus_keywords" name="<?= __CLASS__;?>[keywords]" class="widefat" type="text" value="<?= esc_attr(self::get_options('keywords'));?>"/>
							<p class="description"><?= sprintf(___('For example: graphic design%s 3D design ...'),self::$keywords_split);?></p>
						</td>
					</tr>
				</tbody>
			</table>
		</fieldset>
	<?php
	}
	
	public static function options_save(array $opts = []){
		if(isset($_POST[__CLASS__])){
			$opts[__CLASS__] = $_POST[__CLASS__];
		}
		return $opts;
	}
	public static function get_site_description($echo = true){
		$descriptions = [];
		/** 
		 * in home page
		 */
		if(is_home()){
			if(!self::get_options('description')){
				$descriptions[] = apply_filters('meta_description_home',self::get_options('description'));
			}else{
				$descriptions[] = apply_filters('meta_description_home',theme_cache::get_bloginfo('description'));
			}
		/** 
		 * other page
		 */
		}else{
			if(theme_cache::is_singular()){
				global $post;

				if(!empty($post->post_excerpt)){
					$descriptions[] = apply_filters('meta_description_singular',$post->post_excerpt);
				}else{
					$descriptions[] = apply_filters('meta_description_singular',mb_substr(strip_tags($post->post_content),0,120));
				}
			}else if(theme_cache::is_category()){
				$category_description = category_description();
				$descriptions[] = apply_filters('meta_description_category',$category_description);
			}else if(theme_cache::is_tag()){
				$tag_description = tag_description();
				$descriptions[] = apply_filters('meta_description_tag',$tag_description);
			}
		
		}
		/**
		 * add a hook
		 */
		$descriptions = array_filter(apply_filters('meta_descriptions',$descriptions));
		if(!empty($descriptions)){
			if($echo !== false){
				echo '<meta name="description" content="' . esc_attr(strip_tags(implode(',',$descriptions))) .'"/>';
			}else{
				return $descriptions;
			}
		}
	}
	/**
	 * get_site_keywords
	 * 
	 * @return string
	 * @example 
	 * @version 1.0.1
	 * @copyright Copyright (c) 2011-2013 INN STUDIO. (http://www.inn-studio.com)
	 **/
	public static function get_site_keywords(){
		$all_tags = [];
		/** 
		 * post page
		 */
		if(theme_cache::is_singular_post()){
			$posttags = get_the_tags();
			if(!empty($posttags)){
				foreach($posttags as $v) {
					$all_tags[] = $v->name;
				}
			}
		/** 
		 * other page
		 */
		}else if(!theme_cache::is_home()){
			$single_term_title = single_term_title('',false);
			$all_tags[] = apply_filters('meta_keywords_not_home',$single_term_title);
		/** 
		 * load keywords
		 */
		}else if(self::get_options('keywords')){
			$theme_kws = explode(self::$keywords_split,self::get_options('keywords'));
			if(!empty($theme_kws)){
				foreach($theme_kws as $v){
					if(!empty($v)) $all_tags[] = trim($v);
				}
			}
		}
		/**
		 * add a hook
		 */
		$all_tags = array_filter(apply_filters('meta_keywords',$all_tags));
		if(!empty($all_tags)){
			echo  '<meta name="keywords" content="' . esc_attr(strip_tags(implode(',',$all_tags))) .'"/>';
		}
	}
}
?>
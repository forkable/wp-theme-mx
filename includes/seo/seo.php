<?php
/*
Feature Name:	SEO PLUS
Feature URI:	http://www.inn-studio.com
Version:		1.3.1
Description:	Improve the seo friendly
Author:			INN STUDIO
Author URI:		http://www.inn-studio.com
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
		add_action('wp_head',__CLASS__ . '::get_site_keywords',1);
		add_action('wp_head',__CLASS__ . '::get_site_description',1);
		add_filter('theme_options_save',__CLASS__ . '::options_save');
		add_filter('wp_title',__CLASS__ . '::wp_title',10,2);
	}
	public static function wp_title($title, $sep){
		$sep = ' - ';
		return str_replace('|',' - ',$title);
	}
	//public static function wp_title($sep = '&raquo;',$display = true,$seplocation = ''){
	//	$title = wp_title($sep,false,$seplocation);
	//	$title .= get_bloginfo( 'name', 'display' );
	//	$site_description = get_bloginfo( 'description', 'display' );
	//	if ( $site_description && ( is_home() || is_front_page() ) ) {
	//		$title = "$title $sep $site_description";
	//	}
	//	if($display){
	//		echo $title;
	//	}else{
	//		return $title;
	//	}
	//}
	public static function display_backend(){
		
		$opt = (array)theme_options::get_options(self::$iden);
		$opt['description'] = isset($opt['description']) ? $opt['description'] : null; 
		$opt['keywords'] = isset($opt['keywords']) ? $opt['keywords'] : null; 
		
		?>
		<!-- SEO meta -->
		<fieldset>
			<legend><?php echo ___('SEO settings');?></legend>
			<p class="description"><?php echo sprintf(___('Fill in the appropriate keywords, can improve search engine friendliness. Use different key words in English comma (%s) to separate.'),self::$keywords_split);?></p>
			<table class="form-table">
				<tbody>
					<tr>
						<th scope="row"><label for="seo_plus_description"><?php echo ___('Site description');?></label></th>
						<td>
							<input id="seo_plus_description" name="<?php echo self::$iden;?>[description]" class="widefat" type="text" value="<?php echo esc_attr($opt['description']);?>"/>
							<p class="description"><?php echo esc_html(___('Recommend to control that less than 100 words.'));?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="seo_plus_keywords"><?php echo ___('Site keywords');?></label></th>
						<td>
							<input id="seo_plus_keywords" name="<?php echo self::$iden;?>[keywords]" class="widefat" type="text" value="<?php echo esc_attr($opt['keywords']);?>"/>
							<p class="description"><?php echo esc_html(sprintf(___('For example: graphic design%s 3D design ...'),self::$keywords_split));?></p>
						</td>
					</tr>
				</tbody>
			</table>
		</fieldset>
	<?php
	}
	
	public static function options_save($options){
		if(isset($_POST[self::$iden])){
			$options[self::$iden] = $_POST[self::$iden];
		}
		return $options;
	}
	public static function get_site_description($echo = true){
		$descriptions = [];
		/** 
		 * in home page
		 */
		if(is_home()){
			$opt = (array)theme_options::get_options(self::$iden);
			if(isset($opt['description']) && !empty($opt['description'])){
				$descriptions[] = apply_filters('meta_description_home',$opt['description']);
			}else{
				$descriptions[] = apply_filters('meta_description_home',get_bloginfo('description'));
			}
		/** 
		 * other page
		 */
		}else{
			if(is_singular()){
				global $post;
				setup_postdata($post);
				// var_dump(get_the_excerpt());
				if(!empty($post->post_excerpt)){
					$descriptions[] = apply_filters('meta_description_singular',$post->post_excerpt);
				}else{
					$descriptions[] = apply_filters('meta_description_singular',mb_substr(strip_tags($post->post_content),0,120));
				}
				wp_reset_postdata();
			}else if(is_category()){
				$category_description = category_description();
				$descriptions[] = apply_filters('meta_description_category',$category_description);
			}else if(is_tag()){
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
	 * @author KM (kmvan.com@gmail.com)
	 * @copyright Copyright (c) 2011-2013 INN STUDIO. (http://www.inn-studio.com)
	 **/
	public static function get_site_keywords(){
		$opt = theme_options::get_options(self::$iden);
		$all_tags = [];
		$content = null;
		/** 
		 * post page
		 */
		if(is_singular('post')){
			$posttags = get_the_tags();
			if(!empty($posttags)){
				foreach($posttags as $v) {
					$all_tags[] = $v->name;
				}
			}
		/** 
		 * other page
		 */
		}else if(!is_home()){
			$single_term_title = single_term_title('',false);
			$all_tags[] = apply_filters('meta_keywords_not_home',$single_term_title);
		/** 
		 * load keywords
		 */
		}else if(isset($opt['keywords']) && !empty($opt['keywords'])){
			$theme_kws = explode(self::$keywords_split,trim($opt['keywords']));
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
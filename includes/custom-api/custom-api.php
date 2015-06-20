<?php
/**
 * @version 1.0.0
 */
add_filter('theme_includes',function($fns){
	$fns[] = 'theme_custom_api::init';
	return $fns;
});
class theme_custom_api{

	public static $iden = 'theme_api';

	public static function init(){

		add_action('wp_ajax_' . self::$iden,	__CLASS__ . '::process');
		add_action('wp_ajax_nopriv_' . self::$iden,	__CLASS__ . '::process');
	}

	public static function process(){
		$output = [];
		
		$type = isset($_REQUEST['type']) && is_string($_REQUEST['type']) ? $_REQUEST['type'] : null;

		

		switch($type){
			/**
			 * get categories
			 */
			case 'get_categories':
				$cats = array_map(function($cat){
					return (array)$cat;
				}, self::get_cats() );
				die(theme_features::json_format($cats));
			/**
			 * get posts
			 */
			case 'get_posts':
				$query_args = [];
				/**
				 * $posts_per_page, max 50 count, default: 20
				 */
				$posts_per_page = isset($_GET['count']) && is_numeric($_GET['count']) ? $_GET['count'] : 20;
				if($posts_per_page > 50)
					$posts_per_page = 50;
				/**
				 * $paged, default: 1
				 */
				$paged = isset($_GET['page']) && is_numeric($_GET['page']) ? $_GET['page'] : 1;
				/**
				 * ignore_sticky, default: false
				 */
				$ignore_sticky = isset($_GET['ignore_sticky']) ? (bool)$_GET['$ignore_sticky'] : false;

				/**
				 * cat,e.g. 1
				 */
				if(isset($_GET['cat']) && is_numeric($_GET['cat'])){
					$query_args['cat'] = (int)$_GET['cat'];
				}
				/**
				 * category_name, e.g. cat_slug
				 */
				if(isset($_GET['category_name']) && is_string($_GET['category_name'])){
					$query_args['category_name'] = $_GET['category_name'];
				}
				/**
				 * category__and, e.g. [1,2,3]
				 */
				if(isset($_GET['category__and']) && is_array($_GET['category__and'])){
					$query_args['category__and'] = $_GET['category__and'];
				}
				/**
				 * category__in, e.g. [1,2,3]
				 */
				if(isset($_GET['category__in']) && is_array($_GET['category__in'])){
					$query_args['category__in'] = $_GET['category__in'];
				}
				/**
				 * category__not_in, e.g. [1,2,3]
				 */
				if(isset($_GET['category__not_in']) && is_array($_GET['category__not_in'])){
					$query_args['category__not_in'] = $_GET['category__not_in'];
				}
				
				$query_args['posts_per_page'] = $posts_per_page;
				$query_args['paged'] = $paged;
				
				global $post;
				$query = new WP_Query($query_args);
				if($query->have_posts()){
					while($query->have_posts()){
						$query->the_post();
						$output[] = self::get_postdata();
					}
					wp_reset_postdata();
				}else{
					$output['status'] = 'error';
					$output['code'] = 'no_content';
					$output['msg'] = ___('Sorry, no content found.');
				}
				die(theme_features::json_format($output));
			default:
				$output['status'] = 'error';
				$output['code'] = 'invaild_type_param';
				$output['msg'] = ___('Sorry, the type param is invaild.');
				die(theme_features::json_format($output));
		}
	}
	public static function get_postdata(){
		global $post;
		
		$output = (array)$post;
		$output['post_excerpt'] = get_the_excerpt();
		$output['post_categories'] = array_map(function($cat){
			return (array)$cat;
		}, get_the_category() );
		/**
		 * thumbnail
		 */
		if(has_post_thumbnail()){
			//$output['post_thumbnail']
		}
		return $output;
	}
	public static function get_options($key = null){
		static $caches = null;
		if($caches === null)
			$caches = theme_options::get_options(self::$iden);

		if($key)
			return isset($caches[$key]) ? $caches[$key] : false;

		return $caches;
	}
	public static function display_backend(){
		
	}
	public static function get_cats(){
		$ids = self::get_options('cats');
		if(!$ids){
			return get_categories();
		}else{
			return get_categories([
				'include' => (array)$ids,
			]);
		}
	}
}
?>
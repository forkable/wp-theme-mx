<?php
/**
 * @version 1.0.1
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
				$output['status'] = 'success';
				$output['categories'] = self::get_cats();
				die(theme_features::json_format($output));
			/**
			 * get posts
			 */
			case 'get_posts':
				$query_args = [];
				/**
				 * $posts_per_page, max 50 count, default: 20
				 */
				$posts_per_page = isset($_GET['posts_per_page']) && is_numeric($_GET['posts_per_page']) ? $_GET['posts_per_page'] : 20;
				
				if($posts_per_page > 50)
					$posts_per_page = 50;

				if($posts_per_page <= 0){
					$posts_per_page = 1;
				}
				$query_args['posts_per_page'] = $posts_per_page;
				/**
				 * $paged, default: 1
				 */
				$paged = isset($_GET['paged']) && is_numeric($_GET['paged']) ? $_GET['paged'] : 1;
				$query_args['paged'] = $paged;
				/**
				 * ignore_sticky, default: false
				 */
				$ignore_sticky_posts = isset($_GET['ignore_sticky_posts']) ? (bool)$_GET['ignore_sticky_posts'] : false;
				$query_args['ignore_sticky_posts'] = $ignore_sticky_posts;
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

				/**
				 * get cache
				 */
				
				/**
				 * create query
				 */
				global $post;
				$query = new WP_Query($query_args);
				if($query->have_posts()){
					foreach($query->posts as $post){
						$output['posts'][] = self::get_postdata();
					}
					wp_reset_postdata();
				}else{
					$output['status'] = 'error';
					$output['code'] = 'no_content';
					$output['msg'] = ___('Sorry, no content found.');
				}
				$output['status'] = 'success';
				die(theme_features::json_format($output));
			/**
			 * get post
			 */
			case 'get_post':
				$post_id = isset($_GET['post_id']) && is_numeric($_GET['post_id']) ? $_GET['post_id'] : null;
				/**
				 * check post id
				 */
				if(!$post_id){
					$output['status'] = 'error';
					$output['code'] = 'invaild_post_id';
					$output['msg'] = ___('Sorry, post ID is invaild.');
					die(theme_features::json_format($output));
				}
				global $post;
				$post = theme_cache::get_post($post_id);

				/**
				 * check post exists
				 */
				if(!$post || $post->post_type !== 'post'){
					$output['status'] = 'error';
					$output['code'] = 'post_not_exist';
					$output['msg'] = ___('Sorry, the post do not exist.');
					die(theme_features::json_format($output));
				}
				$output['status'] = 'success';
				$output['post'] = self::get_postdata($post);
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
		/**
		 * get post content
		 */
		setup_postdata($post);
		ob_start();
		the_content();
		$output['post_content'] = ob_get_contents();
		ob_end_clean();
		
		$output['post_excerpt'] = get_the_excerpt();
		$output['post_categories'] = array_map(function($cat){
			return self::get_cat_data($cat);
		}, get_the_category() );
		/**
		 * post url
		 */
		$output['url'] = theme_cache::get_permalink($post->ID);
		/**
		 * thumbnail
		 */
		$sizes = ['thumbnail', 'medium' ];
		foreach($sizes as $size){
			$output['thumbnail'][$size] = theme_functions::get_thumbnail_src($post->ID,$size);
		}
		/**
		 * storage
		 */
		if(class_exists('theme_custom_storage')){
			$output['download_page'] = theme_custom_storage::get_download_page_url($post->ID);
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
	public static function get_cat_data($cat){
		$cat = (array)$cat;
		$cat['url'] = get_category_link($cat['term_id']);
		return $cat;
	}
	public static function get_cats(){
		$ids = self::get_options('cats');
		if(!$ids){
			$cats = get_categories();
		}else{
			$cats = get_categories([
				'include' => (array)$ids,
			]);
		}
		if( !empty($cats) ){
			return array_map(function($cat){
				return self::get_cat_data($cat);
			},$cats);
		}
		return false;
	}
}
?>
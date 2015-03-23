<?php
/**
 * @version 1.0.0
 */
add_filter('custom_post_fav',function($fns){
	$fns[] = 'custom_post_fav::init';
	return $fns;
});
class custom_post_fav{
	public static $iden = 'custom_post_fav';
	public static $post_meta_key = [
		'users' 		=> 'fav_users',
		'conut' 		=> 'fav_count',
	];
	public static $user_meta_key = [
		'posts' 		=> 'fav_posts',
		'count' 		=> 'fav_count',
		'be_count' 		=> 'fav_be_count',
	];
	public static function init(){
		add_action('wp_ajax_' . self::$iden, get_class() . '::process');
	}
	public static function get_fav_posts(array $args = []){
		$defaults = [
			'user_id' => get_current_user_id(),
			'posts_per_page' => 10,
			'paged' => 1,
			'orderby' => 'desc',
			'order' => 'ID',
		];
		$args = wp_parse_args($args,$defaults);
		$cache_id = crc32(__FUNCTION__ . serialize($args));
		$caches = wp_cache_get($args['user_id'],self::$iden);
		if(isset($caches[$cache_id]))
			return $caches[$cache_id];

		/**
		 * get user fav post ids
		 */
		$post_ids = (array)get_user_meta($args['user_id'],self::$user_meta_key['posts'],true);
		if(empty($post_ids))
			return false;
		/**
		 * get posts from database
		 */
		global $wp_query,$post;
		$wp_query = new WP_Query([
			'posts_per_page' => (int)$args['posts_per_page'],
			'paged' => (int)$args['paged'],
			'post__in' => $post_ids,
			'orderby' => $args['orderby'],
			'order' => $args['order'],
		]);
		$caches[$cache_id] = $wp_query;
		wp_cache_set($args['user_id'],$caches,self::$iden,2505600);
		wp_reset_query();
		wp_reset_postdata();
		
		return $caches[$cache_id];
	}
	public static function get_fav_users(array $args = []){
		$defaults = [
			'post_id' => null,
			'number' => 10,
			'paged' => 1,
			'orderby' => 'ID',
		];
		$args = wp_parse_args($args,$defaults);
		$cache_id = crc32(__FUNCTION__ . serialize($args));
		$caches = wp_cache_get($args['post_id'],self::$iden);
		if(isset($caches[$cache_id]))
			return $caches[$cache_id];

		/**
		 * get user id 
		 */
		$user_ids = (array)get_post_meta($args['post_id'],self::$post_meta_key['users'],true);
		if(empty($user_ids))
			return false;

		$users = get_users([
			'include' => $user_ids,
			'number' => $args['number'],
			'orderby' => $args['orderby'],
		]);
		
		if($args['orderby'] === 'rand')
			shuffle($users);
		
		$caches[$cache_id] = $users;
		wp_cache_set($args['post_ids'],$caches,self::$iden,2505600);

		return $caches[$cache_id];
	}
	public static function get_post_fav_count($post_id){
		static $caches;
		if(isset($caches[$post_id])
			return $caches[$post_id];

		$caches[$post_id] = (int)get_psot_meta($post_id,self::$post_meta_key['users'],true);
		return $caches[$post_id];
	}
	public static function get_user_fav_count($user_id){
		static $caches;
		if(isset($caches[$user_id])
			return $caches[$user_id];

		$caches[$user_id] = (int)get_psot_meta($user_id,self::$user_meta_key['count'],true);
		return $caches[$user_id];
	}
	public static function get_user_be_fav_count($user_id){
		static $caches;
		if(isset($caches[$user_id])
			return $caches[$user_id];

		$caches[$user_id] = (int)get_psot_meta($user_id,self::$user_meta_key['be_count'],true);
		return $caches[$user_id];
	}
	public static function process(){
		$output = [];

		theme_features::check_referer();
		theme_features::check_nonce();

		
		die(theme_features::json_format($output));
	}
}
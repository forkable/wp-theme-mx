<?php
/**
 * @version 1.0.0
 * @author INN STUDIO <inn-studio.com>
 */
add_filter('theme_includes',function($fns){
	$fns[] = 'theme_custom_author_profile::init';
	return $fns;
});
class theme_custom_author_profile{
	public static $iden = 'theme_custom_author_profile';
	public static $cache_expire = 3600*12;

	public static $user_meta_key = array(
		'followers_count' 	=> 'followers_count',
		'following_count' 	=> 'following_count',
		'follower' 			=> 'follower',
		'following' 		=> 'following',
	);
	public static function init(){
		add_filter('query_vars',			__CLASS__ . '::filter_query_vars');
		
		add_filter('wp_title',				__CLASS__ . '::wp_title',10,2);
		
	}
	public static function wp_title($title, $sep){
		if(!is_author())
			return $title;
			
		global $author;
		$tab_active = get_query_var('tab');
		$author_display_name = get_the_author_meta('display_name',$author);
		$tabs = self::get_tabs(null,$author);
		if(!empty($tab_active) && isset($tabs[$tab_active])){
			$title = $tabs[$tab_active]['text'];
		}else{
			$title = $tabs['profile']['text'];
		}
		$title = $author_display_name . $sep . $title;
		return $title . $sep . get_bloginfo('name');
	}
	public static function filter_query_vars($vars){
		if(!in_array('tab',$vars)) $vars[] = 'tab';
		if(!in_array('page',$vars)) $vars[] = 'page';
		return $vars;
	}
	public static function get_count($key,$user_id){

		$cache_id = 'user-count-' . $user_id;
		$caches = (array)wp_cache_get($cache_id);
		switch($key){
			case 'works':
				if(!isset($caches['works'])){
					$caches['works'] = (int)count_user_posts($user_id);
					wp_cache_set($cache_id,$caches,null,self::$cache_expire);
				}
				return $caches['works'];
			case 'comments':
				if(!isset($caches['comments'])){
					$caches['comments'] = (int)theme_features::get_user_comments_count($user_id);
					wp_cache_set($cache_id,$caches,null,self::$cache_expire);
				}
				return $caches['comments'];
			case 'followers_count':
				return (int)get_user_meta($user_id,self::$user_meta_key['followers_count'],true);
			case 'following_count':
				return (int)get_user_meta($user_id,self::$user_meta_key['following_count'],true);
		}
	}
	public static function get_tabs($key = null,$author_id){
		static $caches = [], $baseurl;
		$cache_id = md5(serialize(func_get_args()));
		
		if(isset($caches[$cache_id]))
			return $caches[$cache_id];
		
		if(!$baseurl)
			$baseurl = theme_cache::get_author_posts_url($author_id);
			
		$caches = array(
			'profile' => array(
				'text' => ___('Profile'),
				'icon' => 'newspaper-o',
				'url' => esc_url($baseurl)
			),
			'works' => array(
				'text' => ___('Works'),
				'icon' => 'file-text',
				'url' => esc_url(add_query_arg('tab','works',$baseurl)),
				'count' => self::get_count('works',$author_id),
			),
			'comments' => array(
				'text' => ___('Comments'),
				'icon' => 'comments',
				'url' => esc_url(add_query_arg('tab','comments',$baseurl)),
				'count' => self::get_count('comments',$author_id),
			),
			'followers' => array(
				'text' => ___('Followers'),
				'icon' => 'venus-double',
				'url' => esc_url(add_query_arg('tab','followers',$baseurl)),
				'count' => self::get_count('followers_count',$author_id),
			),
			'following' => array(
				'text' => ___('Following'),
				'icon' => 'venus-mars',
				'url' => esc_url(add_query_arg('tab','following',$baseurl)),
				'count' => self::get_count('following_count',$author_id),
			)
		);
		if($key){
			return isset($caches[$key]) ? $caches[$key] : null;
		}
		return $caches;
	}
}
?>